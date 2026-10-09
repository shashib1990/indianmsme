<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;

class UnitOfMeasureController extends Controller
{
    /**
     * Display a listing of the units of measure.
     */
    public function index(Request $request)
    {
        $query = UnitOfMeasure::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status') === '1');
        }

        if ($request->has('export')) {
            return response()->streamDownload(function () use ($query) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['ID', 'Unit Name', 'Short Code', 'Sort Order', 'Status', 'Created At']);

                $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->chunk(250, function ($units) use ($file) {
                    foreach ($units as $row) {
                        fputcsv($file, [
                            $row->id,
                            $row->name,
                            $row->short_code ?? '—',
                            $row->sort_order ?? 0,
                            $row->status ? 'Active' : 'Inactive',
                            $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '—'
                        ]);
                    }
                });

                fclose($file);
            }, 'units_of_measure_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $units = $query->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.units-of-measure.index', compact('units'));
    }

    /**
     * Store a newly created unit of measure.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:unit_of_measures,name',
            'short_code' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ]);

        UnitOfMeasure::create([
            'name' => trim($request->name),
            'short_code' => $request->short_code ? strtolower(trim($request->short_code)) : null,
            'sort_order' => $request->input('sort_order', 0),
            'status' => $request->has('status') ? (bool) $request->status : true,
        ]);

        return redirect()->route('admin.units-of-measure.index')
            ->with('success', 'Unit of Measure created successfully.');
    }

    /**
     * Update the specified unit of measure.
     */
    public function update(Request $request, $id)
    {
        $unit = UnitOfMeasure::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100|unique:unit_of_measures,name,' . $unit->id,
            'short_code' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ]);

        $unit->update([
            'name' => trim($request->name),
            'short_code' => $request->short_code ? strtolower(trim($request->short_code)) : null,
            'sort_order' => $request->input('sort_order', 0),
            'status' => $request->has('status') ? (bool) $request->status : $unit->status,
        ]);

        return redirect()->route('admin.units-of-measure.index')
            ->with('success', 'Unit of Measure updated successfully.');
    }

    /**
     * Toggle status (Active / Inactive).
     */
    public function toggleStatus($id)
    {
        $unit = UnitOfMeasure::findOrFail($id);
        $unit->status = !$unit->status;
        $unit->save();

        return redirect()->route('admin.units-of-measure.index')
            ->with('success', "Unit '{$unit->name}' status updated successfully.");
    }

    /**
     * Remove the specified unit of measure.
     */
    public function destroy($id)
    {
        $unit = UnitOfMeasure::findOrFail($id);
        $unit->delete();

        return redirect()->route('admin.units-of-measure.index')
            ->with('success', 'Unit of Measure deleted successfully.');
    }
}
