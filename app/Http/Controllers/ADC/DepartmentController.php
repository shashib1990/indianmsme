<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $query = Department::where('owner_id', auth('adc')->id())
            ->where('owner_type', \App\Models\ADC::class);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
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
                fputcsv($file, ['ID', 'Department Name', 'Description', 'Status', 'Created At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->name,
                        $row->description ?? '—',
                        $row->is_active === '1' ? 'Active' : 'Inactive',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'departments_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $departments = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();
        return view('adc.departments.index', compact('departments', 'search', 'limit', 'sort', 'order'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,status_active,status_inactive',
            'ids' => 'required|array',
            'ids.*' => 'exists:departments,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        // Verify ownership
        $count = Department::whereIn('id', $ids)
            ->where('owner_id', auth('adc')->id())
            ->where('owner_type', \App\Models\ADC::class)
            ->count();
            
        if ($count !== count($ids)) {
            return back()->with('error', 'Unauthorized or invalid departments selected.');
        }

        if ($action === 'delete') {
            try {
                Department::whereIn('id', $ids)->delete();
                return back()->with('success', 'Selected departments deleted successfully.');
            } catch (QueryException $e) {
                return back()->with('error', 'Cannot delete some departments as they are currently in use by staff members.');
            }
        } elseif ($action === 'status_active') {
            Department::whereIn('id', $ids)->update(['is_active' => '1']);
            return back()->with('success', 'Selected departments activated successfully.');
        } elseif ($action === 'status_inactive') {
            Department::whereIn('id', $ids)->update(['is_active' => '0']);
            return back()->with('success', 'Selected departments deactivated successfully.');
        }

        return back()->with('error', 'Invalid action.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => auth('adc')->id(),
            'owner_type' => \App\Models\ADC::class,
            'is_active' => '1',
        ]);

        return back()->with('success', 'Department created successfully.');
    }

    public function update(Request $request, Department $department)
    {
        if ($department->owner_id != auth('adc')->id() || $department->owner_type != \App\Models\ADC::class) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'required|in:0,1',
        ]);

        $department->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->is_active,
        ]);

        return back()->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if ($department->owner_id != auth('adc')->id() || $department->owner_type != \App\Models\ADC::class) {
            abort(403);
        }

        try {
            $department->delete();
            return back()->with('success', 'Department deleted successfully.');
        } catch (QueryException $e) {
            if ($e->getCode() == "23000") {
                return back()->with('error', 'Cannot delete department as it is currently in use.');
            }
            return back()->with('error', 'Something went wrong.');
        }
    }
}