<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\State;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'name');
        $order = $request->get('order', 'asc');

        $query = State::withCount(['districts', 'pincodes']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
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
                fputcsv($file, ['ID', 'State Name', 'State Code', 'Districts Count', 'Pincodes Count', 'Status', 'Created At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->name,
                        $row->code ?? '—',
                        $row->districts_count,
                        $row->pincodes_count,
                        $row->status === '1' ? 'Active' : 'Inactive',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'states_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $states = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.states.index', compact('states', 'search', 'limit', 'sort', 'order'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:states,name',
            'code' => 'nullable|string|max:10|unique:states,code',
            'status' => 'required|in:0,1',
        ]);

        State::create($request->only('name', 'code', 'status'));

        return back()->with('success', 'State created successfully.');
    }

    public function update(Request $request, State $state)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:states,name,' . $state->id,
            'code' => 'nullable|string|max:10|unique:states,code,' . $state->id,
            'status' => 'required|in:0,1',
        ]);

        $state->update($request->only('name', 'code', 'status'));

        return back()->with('success', 'State updated successfully.');
    }

    public function destroy(State $state)
    {
        if ($state->districts()->count() > 0) {
            return back()->with('error', 'Cannot delete state — it has districts linked to it. Remove districts first.');
        }

        $state->delete();
        return back()->with('success', 'State deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,status_active,status_inactive',
            'ids' => 'required|array',
            'ids.*' => 'exists:states,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            // Count states with existing districts
            $restricted = State::whereIn('id', $ids)->whereHas('districts')->count();
            if ($restricted > 0) {
                return back()->with('error', 'Cannot delete selected states — some have districts linked to them. Remove districts first.');
            }

            State::whereIn('id', $ids)->delete();
            return back()->with('success', 'Selected states deleted successfully.');
        } elseif ($action === 'status_active') {
            State::whereIn('id', $ids)->update(['status' => '1']);
            return back()->with('success', 'Selected states activated successfully.');
        } elseif ($action === 'status_inactive') {
            State::whereIn('id', $ids)->update(['status' => '0']);
            return back()->with('success', 'Selected states deactivated successfully.');
        }

        return back()->with('error', 'Invalid action.');
    }
}
