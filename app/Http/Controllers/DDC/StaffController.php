<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\DDC;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $query = DDC::with(['profile.department', 'department'])
            ->whereHas('profile', function($q) {
                $q->where('parent_id', auth('ddc')->id());
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('export')) {
            $selectedIds = $request->get('ids');
            if ($selectedIds) {
                $query->whereIn('id', explode(',', $selectedIds));
            }
            $records = $query->orderBy($sort, $order)->get();

            return response()->streamDownload(function () use ($records) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['ID', 'Staff Name', 'Email', 'Phone', 'Department', 'Status', 'Created At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->name,
                        $row->email,
                        $row->phone ?? '—',
                        $row->department->name ?? '—',
                        $row->is_active === '1' ? 'Active' : 'Inactive',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'staff_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $staff = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();
        return view('ddc.staff.index', compact('staff', 'search', 'limit', 'sort', 'order'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,status_active,status_inactive',
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        return DB::transaction(function () use ($ids, $action) {
            $records = DDC::whereIn('id', $ids)
                ->whereHas('profile', function($q) {
                    $q->where('parent_id', auth('ddc')->id());
                })
                ->get();

            foreach ($records as $record) {
                if ($action === 'delete') {
                    $record->delete();
                } elseif ($action === 'status_active') {
                    $record->update(['is_active' => '1']);
                } elseif ($action === 'status_inactive') {
                    $record->update(['is_active' => '0']);
                }
            }

            return back()->with('success', 'Bulk action completed successfully.');
        });
    }

    public function create()
    {
        $departments = Department::where('owner_id', auth('ddc')->id())
            ->where('owner_type', DDC::class)
            ->where('is_active', '1')
            ->get();
            
        return view('ddc.staff.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'department_id' => ['required', 'exists:departments,id'],
            'is_active' => ['required', 'in:0,1'],
        ]);

        return DB::transaction(function () use ($request) {
            DDC::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'department_id' => $request->department_id,
                'parent_id' => auth('ddc')->id(),
                'is_active' => $request->is_active,
                'force_change_password' => '0',
            ]);

            return redirect()->route('ddc.staff.index')->with('success', 'Staff member created successfully.');
        });
    }

    public function edit(DDC $staff)
    {
        if ($staff->parent_id != auth('ddc')->id()) abort(403);

        $departments = Department::where('owner_id', auth('ddc')->id())
            ->where('owner_type', DDC::class)
            ->get();
            
        return view('ddc.staff.edit', compact('staff', 'departments'));
    }

    public function update(Request $request, DDC $staff)
    {
        if ($staff->parent_id != auth('ddc')->id()) abort(403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'department_id' => ['required', 'exists:departments,id'],
            'is_active' => ['required', 'in:0,1'],
        ]);

        return DB::transaction(function () use ($request, $staff) {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'department_id' => $request->department_id,
                'is_active' => $request->is_active,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $staff->update($data);

            return redirect()->route('ddc.staff.index')->with('success', 'Staff updated successfully.');
        });
    }

    public function destroy(DDC $staff)
    {
        if ($staff->parent_id != auth('ddc')->id()) abort(403);

        return DB::transaction(function () use ($staff) {
            $staff->delete();
            return back()->with('success', 'Staff deleted successfully.');
        });
    }
}
