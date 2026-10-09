<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pincode;
use App\Models\District;
use App\Models\State;
use Illuminate\Http\Request;

class PincodeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $stateFilter = $request->get('state_id');
        $districtFilter = $request->get('district_id');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'pincode');
        $order = $request->get('order', 'asc');

        $query = Pincode::with(['state', 'district']);

        if ($search) {
            $query->where('pincode', 'like', "%{$search}%");
        }

        if ($stateFilter) {
            $query->where('state_id', $stateFilter);
        }

        if ($districtFilter) {
            $query->where('district_id', $districtFilter);
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
                fputcsv($file, ['ID', 'Pincode', 'District Name', 'State Name', 'Status', 'Created At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->pincode,
                        $row->district->name ?? '—',
                        $row->state->name ?? '—',
                        $row->status === '1' ? 'Active' : 'Inactive',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'pincodes_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $pincodes = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();
        $states = State::active()->orderBy('name')->get();

        return view('admin.pincodes.index', compact('pincodes', 'states', 'search', 'stateFilter', 'districtFilter', 'limit', 'sort', 'order'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'district_id' => 'required|exists:districts,id',
            'pincode' => 'required|string|max:10',
            'pincodes_bulk' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        // Bulk add mode
        if ($request->filled('pincodes_bulk')) {
            $rawPins = preg_split('/[\s,;]+/', trim($request->pincodes_bulk));
            $inserted = 0;
            $skipped = 0;

            foreach ($rawPins as $pin) {
                $pin = trim($pin);
                if (empty($pin) || !preg_match('/^\d{4,10}$/', $pin)) {
                    $skipped++;
                    continue;
                }

                $exists = Pincode::where('district_id', $request->district_id)
                    ->where('pincode', $pin)->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                Pincode::create([
                    'state_id' => $request->state_id,
                    'district_id' => $request->district_id,
                    'pincode' => $pin,
                    'status' => $request->status,
                ]);
                $inserted++;
            }

            return back()->with('success', "Bulk add complete: {$inserted} inserted, {$skipped} skipped.");
        }

        // Single add mode
        $exists = Pincode::where('district_id', $request->district_id)
            ->where('pincode', $request->pincode)->exists();

        if ($exists) {
            return back()->with('error', 'This pincode already exists under the selected district.')
                ->withInput();
        }

        Pincode::create($request->only('state_id', 'district_id', 'pincode', 'status'));

        return back()->with('success', 'Pincode added successfully.');
    }

    public function update(Request $request, Pincode $pincode)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'district_id' => 'required|exists:districts,id',
            'pincode' => 'required|string|max:10',
            'status' => 'required|in:0,1',
        ]);

        // Check duplicate within same district (excluding current)
        $exists = Pincode::where('district_id', $request->district_id)
            ->where('pincode', $request->pincode)
            ->where('id', '!=', $pincode->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'This pincode already exists under the selected district.')
                ->withInput();
        }

        $pincode->update($request->only('state_id', 'district_id', 'pincode', 'status'));

        return back()->with('success', 'Pincode updated successfully.');
    }

    public function destroy(Pincode $pincode)
    {
        $pincode->delete();
        return back()->with('success', 'Pincode deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,status_active,status_inactive',
            'ids' => 'required|array',
            'ids.*' => 'exists:pincodes,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            Pincode::whereIn('id', $ids)->delete();
            return back()->with('success', 'Selected pincodes deleted successfully.');
        } elseif ($action === 'status_active') {
            Pincode::whereIn('id', $ids)->update(['status' => '1']);
            return back()->with('success', 'Selected pincodes activated successfully.');
        } elseif ($action === 'status_inactive') {
            Pincode::whereIn('id', $ids)->update(['status' => '0']);
            return back()->with('success', 'Selected pincodes deactivated successfully.');
        }

        return back()->with('error', 'Invalid action.');
    }
}
