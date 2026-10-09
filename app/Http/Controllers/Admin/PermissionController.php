<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $module = $request->get('module');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'module');
        $order = $request->get('order', 'asc');

        $query = Permission::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($module) {
            $query->where('module', $module);
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
                fputcsv($file, ['ID', 'Permission Name', 'Slug', 'Module', 'Type', 'Created At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->name,
                        $row->slug,
                        $row->module,
                        $row->type,
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'permissions_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $permissions = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();
        $modules = Permission::select('module')->distinct()->pluck('module');

        return view('admin.permissions.index', compact('permissions', 'modules', 'search', 'module', 'limit', 'sort', 'order'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:admin_permissions,slug',
            'action' => 'required_without:slug|string|max:100',
        ]);

        $slug = $request->slug ? Str::lower($request->slug) : Str::lower($request->module) . '.' . Str::lower($request->action);

        Permission::create([
            'name' => $request->name,
            'slug' => $slug,
            'module' => $request->module,
            'type' => Str::contains($slug, ['view', 'read', 'index']) ? 'read' : 'write',
        ]);

        return back()->with('success', 'Permission created successfully.');
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:admin_permissions,slug,' . $permission->id,
            'action' => 'required_without:slug|string|max:100',
        ]);

        $slug = $request->slug ? Str::lower($request->slug) : Str::lower($request->module) . '.' . Str::lower($request->action);

        $permission->update([
            'name' => $request->name,
            'slug' => $slug,
            'module' => $request->module,
            'type' => Str::contains($slug, ['view', 'read', 'index']) ? 'read' : 'write',
        ]);

        return back()->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        $permission->roles()->detach();
        $permission->delete();
        return back()->with('success', 'Permission deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:admin_permissions,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            // Detach and delete
            foreach ($ids as $id) {
                $permission = Permission::find($id);
                if ($permission) {
                    $permission->roles()->detach();
                    $permission->delete();
                }
            }

            return back()->with('success', 'Selected permissions deleted successfully.');
        }

        return back()->with('error', 'Invalid action.');
    }
}
