<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $query = Role::with('department')->withCount('permissions');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
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
                // Prepend UTF-8 BOM
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['ID', 'Role Name', 'Slug', 'Department Name', 'Permissions Count', 'Description', 'Created At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->name,
                        $row->slug,
                        $row->department->name ?? '—',
                        $row->permissions_count,
                        $row->description ?? '—',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'roles_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $roles = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.roles.index', compact('roles', 'search', 'limit', 'sort', 'order'));
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy('module');
        $departments = \App\Models\Department::all();
        return view('admin.roles.create', compact('permissions', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:admin_roles'],
            'department_id' => ['required', 'exists:departments,id'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:admin_permissions,id'],
        ]);

        $role = Role::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'department_id' => $request->department_id,
            'description' => $request->description,
        ]);

        if ($request->permissions) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy('module');
        $departments = \App\Models\Department::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('admin.roles.edit', compact('role', 'permissions', 'departments', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:admin_roles,name,' . $role->id],
            'department_id' => ['required', 'exists:departments,id'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:admin_permissions,id'],
        ]);

        $role->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'department_id' => $request->department_id,
            'description' => $request->description,
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->admins()->count() > 0) {
            return back()->with('error', 'Cannot delete role — it is currently assigned to staff members.');
        }

        $role->permissions()->detach();
        $role->admins()->detach();
        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:admin_roles,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            // Count any admin users linked to these roles
            $restricted = 0;
            foreach ($ids as $id) {
                $role = Role::find($id);
                if ($role && $role->admins()->count() > 0) {
                    $restricted++;
                }
            }

            if ($restricted > 0) {
                return back()->with('error', 'Cannot delete selected roles — ' . $restricted . ' of them are currently assigned to admin users.');
            }

            // Detach permissions, admins and delete roles
            foreach ($ids as $id) {
                $role = Role::find($id);
                if ($role) {
                    $role->permissions()->detach();
                    $role->admins()->detach();
                    $role->delete();
                }
            }

            return back()->with('success', 'Selected roles deleted successfully.');
        }

        return back()->with('error', 'Invalid action.');
    }
}
