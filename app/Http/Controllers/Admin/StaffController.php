<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use App\Models\Department;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        $query = Admin::with(['roles', 'profile.department', 'department'])->whereHas('profile', function ($q) {
            $q->where('is_super_admin', '0');
        });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
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
                // Prepend UTF-8 BOM
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['ID', 'Staff Name', 'Email', 'Phone', 'Department', 'Status', 'Created At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->name,
                        $row->email,
                        $row->phone ?? $row->mobile ?? '—',
                        $row->department->name ?? '—',
                        $row->is_active === '1' ? 'Active' : 'Inactive',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'staff_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $staff = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.staff.index', compact('staff', 'search', 'limit', 'sort', 'order'));
    }

    public function create()
    {
        $departments = Department::where('is_active', '1')->get();
        $permissions = Permission::all()->groupBy('module');
        return view('admin.staff.create', compact('departments', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'department_id' => ['required', 'exists:departments,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:admin_permissions,id'],
            'is_active' => ['required', 'in:0,1'],
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Create the Staff User — no forced password change, they can login directly
            $staff = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'department_id' => $request->department_id,
                'is_active' => $request->is_active,
                'is_super_admin' => '0',
                'force_change_password' => '0',
                'parent_id' => auth('admin')->id(),
            ]);

            // 2. Create a unique Role for this Staff member
            $role = Role::create([
                'name' => 'Staff: ' . $staff->name . ' (' . $staff->email . ')',
                'slug' => 'staff-' . $staff->id . '-' . Str::random(5),
                'department_id' => $request->department_id,
                'description' => $request->description ?? ('Personal permissions for ' . $staff->name),
            ]);

            // 3. Assign permissions to the role (if any selected)
            if ($request->has('permissions') && is_array($request->permissions)) {
                $role->permissions()->sync($request->permissions);
            }

            // 4. Link Staff to this Role
            $staff->roles()->sync([$role->id]);

            return redirect()->route('admin.staff.index')
                ->with('success', 'Staff member "' . $staff->name . '" created successfully.');
        });
    }

    public function edit(Admin $staff)
    {
        if ($staff->is_super_admin === '1') {
            return redirect()->route('admin.staff.index')->with('error', 'Super Admin cannot be edited here.');
        }

        $departments = Department::where('is_active', '1')->get();
        $permissions = Permission::all()->groupBy('module');

        // Get permissions from all their roles (usually just one in this new system)
        $staffPermissions = $staff->roles()->with('permissions')->get()
            ->flatMap(fn($role) => $role->permissions)
            ->pluck('id')->unique()->toArray();

        return view('admin.staff.edit', compact('staff', 'departments', 'permissions', 'staffPermissions'));
    }

    public function update(Request $request, Admin $staff)
    {
        if ($staff->is_super_admin === '1') {
            return redirect()->route('admin.staff.index')->with('error', 'Super Admin cannot be edited here.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'department_id' => ['required', 'exists:departments,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:admin_permissions,id'],
            'is_active' => ['required', 'in:0,1'],
        ]);

        return DB::transaction(function () use ($request, $staff) {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'department_id' => $request->department_id,
                'is_active' => $request->is_active,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $staff->update($data);

            // Update or Create Role for this Staff
            $role = $staff->roles->first();

            if (!$role) {
                $role = Role::create([
                    'name' => 'Staff: ' . $staff->name,
                    'slug' => 'staff-' . $staff->id . '-' . Str::random(5),
                    'department_id' => $request->department_id,
                    'description' => $request->description,
                ]);
                $staff->roles()->sync([$role->id]);
            } else {
                $role->update([
                    'name' => 'Staff: ' . $staff->name . ' (' . $staff->email . ')',
                    'department_id' => $request->department_id,
                    'description' => $request->description ?? $role->description,
                ]);
            }

            $role->permissions()->sync($request->permissions ?? []);

            return redirect()->route('admin.staff.index')
                ->with('success', 'Staff member "' . $staff->name . '" updated successfully.');
        });
    }

    public function destroy(Admin $staff)
    {
        if ($staff->is_super_admin === '1') {
            return back()->with('error', 'Super Admin cannot be deleted.');
        }

        return DB::transaction(function () use ($staff) {
            // Delete associated roles if they are staff-specific
            foreach ($staff->roles as $role) {
                if (Str::startsWith($role->slug, 'staff-')) {
                    $role->delete();
                }
            }
            $staff->delete();
            return back()->with('success', 'Staff member and their permissions deleted successfully.');
        });
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,status_active,status_inactive',
            'ids' => 'required|array',
            'ids.*' => 'exists:admins,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        return DB::transaction(function () use ($ids, $action) {
            $records = Admin::whereIn('id', $ids)->get();

            foreach ($records as $record) {
                // Safeguard against super admins
                if ($record->is_super_admin === '1' || ($record->profile && $record->profile->is_super_admin === '1')) {
                    continue;
                }

                if ($action === 'delete') {
                    // Delete associated roles if they are staff-specific
                    foreach ($record->roles as $role) {
                        if (Str::startsWith($role->slug, 'staff-')) {
                            $role->delete();
                        }
                    }
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
}
