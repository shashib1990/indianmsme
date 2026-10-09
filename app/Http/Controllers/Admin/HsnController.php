<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hsn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class HsnController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');

        $query = Hsn::with(['creator', 'updater']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('hsn_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        // Export functionality
        if ($request->has('export')) {
            $selectedIds = $request->get('ids');
            if ($selectedIds) {
                $query->whereIn('id', explode(',', $selectedIds));
            }
            $records = $query->orderBy($sort, $order)->get();

            return response()->streamDownload(function () use ($records) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['ID', 'HSN Code', 'GST Rate', 'Description', 'Status', 'Created By', 'Created At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->hsn_code,
                        $row->gst_percentage,
                        $row->description ?? '—',
                        $row->status === '1' ? 'Active' : 'Inactive',
                        $row->creator ? $row->creator->name : 'System',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'hsn_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $hsns = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.hsns.index', compact('hsns', 'search', 'status', 'limit', 'sort', 'order'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'hsn_code' => 'required|string|max:50|unique:hsns,hsn_code',
            'gst_percentage' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        Hsn::create([
            'hsn_code' => trim($request->hsn_code),
            'gst_percentage' => $request->gst_percentage ?? 0.00,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return back()->with('success', 'HSN code created successfully.');
    }

    public function update(Request $request, Hsn $hsn)
    {
        $request->validate([
            'hsn_code' => 'required|string|max:50|unique:hsns,hsn_code,' . $hsn->id,
            'gst_percentage' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        $hsn->update([
            'hsn_code' => trim($request->hsn_code),
            'gst_percentage' => $request->gst_percentage ?? 0.00,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return back()->with('success', 'HSN code updated successfully.');
    }

    public function destroy(Hsn $hsn)
    {
        $hsn->delete();

        return back()->with('success', 'HSN code deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,status_active,status_inactive',
            'ids' => 'required|array',
            'ids.*' => 'exists:hsns,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            Hsn::whereIn('id', $ids)->delete();
            return back()->with('success', 'Selected HSN codes deleted successfully.');
        } elseif ($action === 'status_active') {
            Hsn::whereIn('id', $ids)->update(['status' => '1']);
            return back()->with('success', 'Selected HSN codes activated successfully.');
        } elseif ($action === 'status_inactive') {
            Hsn::whereIn('id', $ids)->update(['status' => '0']);
            return back()->with('success', 'Selected HSN codes deactivated successfully.');
        }

        return back()->with('error', 'Invalid action.');
    }

    public function import(Request $request)
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(3600);
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls,txt|max:153600',
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        array_shift($rows); // Remove header row        
        $total = $inserted = $skipped = $failed = 0;
        $details = [];
        $processedHsns = [];

        $hsnCodesInFile = [];
        foreach ($rows as $row) {
            $code = trim($row['A'] ?? '');
            if ($code) {
                $hsnCodesInFile[] = $code;
            }
        }
        $hsnCodesInFile = array_unique($hsnCodesInFile);
        $existingHsns = Hsn::whereIn('hsn_code', $hsnCodesInFile)->pluck('hsn_code')->toArray();
        $existingHsnMap = array_change_key_case(array_fill_keys($existingHsns, true), CASE_LOWER);

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $total++;

                $hsnCode = trim($row['A'] ?? '');
                $gstRate = trim($row['B'] ?? '');
                $description = trim($row['C'] ?? '');

                if (empty($hsnCode)) {
                    $failed++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => '-',
                        'status' => 'Failed',
                        'reason' => 'HSN Code is empty'
                    ];
                    continue;
                }

                $hsnCodeLower = strtolower($hsnCode);

                // Duplicate in file check
                if (in_array($hsnCodeLower, $processedHsns)) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => $hsnCode,
                        'status' => 'Skipped',
                        'reason' => 'Duplicate HSN Code in the uploaded file'
                    ];
                    continue;
                }

                // DB existence check
                if (isset($existingHsnMap[$hsnCodeLower])) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => $hsnCode,
                        'status' => 'Skipped',
                        'reason' => "HSN Code '{$hsnCode}' already exists in database"
                    ];
                    continue;
                }

                Hsn::create([
                    'hsn_code' => $hsnCode,
                    'gst_percentage' => is_numeric($gstRate) ? floatval($gstRate) : 0.00,
                    'description' => $description ?: null,
                    'status' => '1',
                ]);

                $processedHsns[] = $hsnCodeLower;
                $existingHsnMap[$hsnCodeLower] = true;
                $inserted++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return back()->with('import_result', compact('total', 'inserted', 'skipped', 'failed', 'details'));
    }

    public function sample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'HSN Code');
        $sheet->setCellValue('B1', 'GST Rate');
        $sheet->setCellValue('C1', 'Description');

        $sheet->setCellValue('A2', '85171200');
        $sheet->setCellValue('B2', '18');
        $sheet->setCellValue('C2', 'Mobile phones and telephone networks');

        $sheet->setCellValue('A3', '84713010');
        $sheet->setCellValue('B3', '18');
        $sheet->setCellValue('C3', 'Automatic data processing machines (Laptops, PCs)');

        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(40);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, 'sample_hsn.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function toggleStatus($id)
    {
        $hsn = Hsn::findOrFail($id);
        $hsn->status = $hsn->status === '1' ? '0' : '1';
        $hsn->save();

        return back()->with('success', 'HSN status updated successfully.');
    }
}
