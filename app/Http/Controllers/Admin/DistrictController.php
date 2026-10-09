<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\State;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $stateFilter = $request->get('state_id');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'name');
        $order = $request->get('order', 'asc');

        $query = District::with('state')->withCount('pincodes');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($stateFilter) {
            $query->where('state_id', $stateFilter);
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
                fputcsv($file, ['ID', 'District Name', 'State Name', 'Pincodes Count', 'Status', 'Created At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->name,
                        $row->state->name ?? '—',
                        $row->pincodes_count,
                        $row->status === '1' ? 'Active' : 'Inactive',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'districts_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $districts = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();
        $states = State::active()->orderBy('name')->get();

        return view('admin.districts.index', compact('districts', 'states', 'search', 'stateFilter', 'limit', 'sort', 'order'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        // Check duplicate within same state
        $exists = District::where('state_id', $request->state_id)
            ->where('name', $request->name)->exists();

        if ($exists) {
            return back()->with('error', 'This district already exists under the selected state.')
                ->withInput();
        }

        District::create($request->only('state_id', 'name', 'status'));

        return back()->with('success', 'District created successfully.');
    }

    public function update(Request $request, District $district)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        // Check duplicate within same state (excluding current)
        $exists = District::where('state_id', $request->state_id)
            ->where('name', $request->name)
            ->where('id', '!=', $district->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'This district already exists under the state.')
                ->withInput();
        }

        $district->update($request->only('state_id', 'name', 'status'));

        return back()->with('success', 'District updated successfully.');
    }

    public function destroy(District $district)
    {
        if ($district->pincodes()->count() > 0) {
            return back()->with('error', 'Cannot delete district — it has pincodes linked to it. Remove pincodes first.');
        }

        $district->delete();
        return back()->with('success', 'District deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,status_active,status_inactive',
            'ids' => 'required|array',
            'ids.*' => 'exists:districts,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            // Count districts with existing pincodes
            $restricted = District::whereIn('id', $ids)->whereHas('pincodes')->count();
            if ($restricted > 0) {
                return back()->with('error', 'Cannot delete selected districts — some have pincodes linked to them. Remove pincodes first.');
            }

            District::whereIn('id', $ids)->delete();
            return back()->with('success', 'Selected districts deleted successfully.');
        } elseif ($action === 'status_active') {
            District::whereIn('id', $ids)->update(['status' => '1']);
            return back()->with('success', 'Selected districts activated successfully.');
        } elseif ($action === 'status_inactive') {
            District::whereIn('id', $ids)->update(['status' => '0']);
            return back()->with('success', 'Selected districts deactivated successfully.');
        }

        return back()->with('error', 'Invalid action.');
    }
}
