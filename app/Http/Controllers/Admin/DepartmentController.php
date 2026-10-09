<?php

namespace App\Http\Controllers\Admin;

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

        $query = Department::query();

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
                // Prepend UTF-8 BOM
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

        return view('admin.departments.index', compact('departments', 'search', 'limit', 'sort', 'order'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string',
            'is_active' => 'required|in:0,1'
        ]);

        Department::create($request->all());

        return back()->with('success', 'Department created successfully.');
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
            'is_active' => 'required|in:0,1'
        ]);

        $department->update($request->all());

        return back()->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $id = $department->id;
        $hasUsers = \App\Models\User::where('department_id', $id)->exists();

        if ($hasUsers) {
            return back()->with('error', 'Cannot delete department — it is currently assigned to active users.');
        }

        try {
            $department->delete();
            return back()->with('success', 'Department deleted successfully.');
        } catch (QueryException $e) {
            return back()->with('error', 'An error occurred while deleting the department.');
        }
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

        if ($action === 'delete') {
            $restricted = 0;
            foreach ($ids as $id) {
                $hasUsers = \App\Models\User::where('department_id', $id)->exists();

                if ($hasUsers) {
                    $restricted++;
                }
            }

            if ($restricted > 0) {
                return back()->with('error', 'Cannot delete selected departments — ' . $restricted . ' of them are linked to active users.');
            }

            Department::whereIn('id', $ids)->delete();
            return back()->with('success', 'Selected departments deleted successfully.');
        } elseif ($action === 'status_active') {
            Department::whereIn('id', $ids)->update(['is_active' => '1']);
            return back()->with('success', 'Selected departments activated successfully.');
        } elseif ($action === 'status_inactive') {
            Department::whereIn('id', $ids)->update(['is_active' => '0']);
            return back()->with('success', 'Selected departments deactivated successfully.');
        }

        return back()->with('error', 'Invalid action.');
    }
}
