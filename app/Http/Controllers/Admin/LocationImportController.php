<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\District;
use App\Models\Pincode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class LocationImportController extends Controller
{
    // ─────────────────────────────────────────────────
    //  STATE IMPORT (Columns: state_name, state_code)
    // ─────────────────────────────────────────────────

    public function importStates(Request $request)
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(3600);
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls,txt|max:153600',
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        array_shift($rows); // remove header

        $total = $inserted = $skipped = $failed = 0;
        $details = [];
        $processedNames = [];
        $processedCodes = [];

        $existingStates = State::all();
        $existingStateNamesMap = array_change_key_case(array_fill_keys($existingStates->pluck('name')->toArray(), true), CASE_LOWER);
        $existingStateCodesMap = array_change_key_case(array_fill_keys(array_filter($existingStates->pluck('code')->toArray()), true), CASE_UPPER);

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // header is row 1
                $total++;
                $name = ucwords(strtolower(trim($row['A'] ?? $row[array_key_first($row)] ?? '')));
                $code = strtoupper(trim($row['B'] ?? ''));

                if (empty($name)) {
                    $failed++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => '-',
                        'status' => 'Failed',
                        'reason' => 'State name is empty'
                    ];
                    continue;
                }

                $nameLower = strtolower($name);
                $codeUpper = strtoupper($code);

                // Check in-file duplicates
                if (in_array($nameLower, $processedNames)) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => $name,
                        'status' => 'Skipped',
                        'reason' => 'Duplicate state name in the uploaded file'
                    ];
                    continue;
                }

                if (!empty($codeUpper) && in_array($codeUpper, $processedCodes)) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => "{$name} ({$codeUpper})",
                        'status' => 'Skipped',
                        'reason' => 'Duplicate state code in the uploaded file'
                    ];
                    continue;
                }

                // Check database duplicates (unique name or unique code)
                if (isset($existingStateNamesMap[$nameLower])) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => $name,
                        'status' => 'Skipped',
                        'reason' => "State '{$name}' already exists in database"
                    ];
                    continue;
                }

                if (!empty($codeUpper) && isset($existingStateCodesMap[$codeUpper])) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => "{$name} ({$codeUpper})",
                        'status' => 'Skipped',
                        'reason' => "State code '{$codeUpper}' already exists in database"
                    ];
                    continue;
                }

                State::create([
                    'name' => $name,
                    'code' => $codeUpper ?: null,
                    'status' => '1',
                ]);

                $processedNames[] = $nameLower;
                if (!empty($codeUpper)) {
                    $processedCodes[] = $codeUpper;
                    $existingStateCodesMap[$codeUpper] = true;
                }
                $existingStateNamesMap[$nameLower] = true;
                $inserted++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return back()->with('import_result', compact('total', 'inserted', 'skipped', 'failed', 'details'));
    }

    public function stateSample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'state_name');
        $sheet->setCellValue('B1', 'state_code');

        $sampleData = [
            ['Maharashtra', 'MH'],
            ['Delhi', 'DL'],
            ['Uttar Pradesh', 'UP'],
            ['Gujarat', 'GJ'],
            ['Karnataka', 'KA'],
            ['Tamil Nadu', 'TN'],
            ['West Bengal', 'WB'],
        ];

        $rowNum = 2;
        foreach ($sampleData as $data) {
            $sheet->setCellValue("A{$rowNum}", $data[0]);
            $sheet->setCellValue("B{$rowNum}", $data[1]);
            $rowNum++;
        }

        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, 'sample_states.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─────────────────────────────────────────────────
    //  DISTRICT IMPORT (Columns: state_name, district_name)
    // ─────────────────────────────────────────────────

    public function importDistricts(Request $request)
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(3600);
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls,txt|max:153600',
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        array_shift($rows); // remove header

        $total = $inserted = $skipped = $failed = 0;
        $details = [];
        $processedDistricts = [];

        // Build states map: lowercase name => ['id' => id, 'name' => original_name]
        $states = State::all();
        $statesMap = [];
        foreach ($states as $st) {
            $statesMap[strtolower(trim($st->name))] = [
                'id' => $st->id,
                'name' => $st->name,
            ];
        }

        // Build existing districts map: 'state_id_districtname_lower' => true
        $allDistricts = District::all();
        $existingDistrictsMap = [];
        foreach ($allDistricts as $d) {
            $key = $d->state_id . '_' . strtolower(trim($d->name));
            $existingDistrictsMap[$key] = true;
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // header is row 1
                $total++;

                $stateNameInput = trim($row['A'] ?? $row[array_key_first($row)] ?? '');
                $districtNameInput = trim($row['B'] ?? '');

                if (empty($stateNameInput)) {
                    $failed++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => '-',
                        'status' => 'Failed',
                        'reason' => 'State name is empty'
                    ];
                    continue;
                }

                if (empty($districtNameInput)) {
                    $failed++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => "{$stateNameInput} / -",
                        'status' => 'Failed',
                        'reason' => 'District name is empty'
                    ];
                    continue;
                }

                $stateKey = strtolower($stateNameInput);
                if (!isset($statesMap[$stateKey])) {
                    $failed++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => "{$stateNameInput} / {$districtNameInput}",
                        'status' => 'Failed',
                        'reason' => "State '{$stateNameInput}' does not exist in State Master"
                    ];
                    continue;
                }

                $matchedState = $statesMap[$stateKey];
                $districtNameFormatted = ucwords(strtolower($districtNameInput));
                $compositeKey = $matchedState['id'] . '_' . strtolower($districtNameFormatted);

                if (in_array($compositeKey, $processedDistricts)) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => "{$matchedState['name']} / {$districtNameFormatted}",
                        'status' => 'Skipped',
                        'reason' => "Duplicate district entry in state '{$matchedState['name']}' in uploaded file"
                    ];
                    continue;
                }

                if (isset($existingDistrictsMap[$compositeKey])) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => "{$matchedState['name']} / {$districtNameFormatted}",
                        'status' => 'Skipped',
                        'reason' => "District '{$districtNameFormatted}' already exists in state '{$matchedState['name']}'"
                    ];
                    continue;
                }

                District::create([
                    'state_id' => $matchedState['id'],
                    'name'     => $districtNameFormatted,
                    'status'   => '1',
                ]);

                $processedDistricts[] = $compositeKey;
                $existingDistrictsMap[$compositeKey] = true;
                $inserted++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return back()->with('import_result', compact('total', 'inserted', 'skipped', 'failed', 'details'));
    }

    public function districtSample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'state_name');
        $sheet->setCellValue('B1', 'district_name');

        $sampleData = [
            ['Maharashtra', 'Mumbai'],
            ['Maharashtra', 'Pune'],
            ['Maharashtra', 'Nashik'],
            ['Delhi', 'New Delhi'],
            ['Uttar Pradesh', 'Lucknow'],
            ['Gujarat', 'Ahmedabad'],
        ];

        $rowNum = 2;
        foreach ($sampleData as $data) {
            $sheet->setCellValue("A{$rowNum}", $data[0]);
            $sheet->setCellValue("B{$rowNum}", $data[1]);
            $rowNum++;
        }

        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(25);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, 'sample_districts.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─────────────────────────────────────────────────
    //  PINCODE IMPORT (Columns: state_name, district_name, pincode)
    // ─────────────────────────────────────────────────

    public function importPincodes(Request $request)
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
        $processedCompositeKeys = [];

        // Build states map: lowercase name => ['id' => id, 'name' => original_name]
        $states = State::all();
        $statesMap = [];
        foreach ($states as $st) {
            $statesMap[strtolower(trim($st->name))] = [
                'id' => $st->id,
                'name' => $st->name,
            ];
        }

        // Build districts map: 'state_id_districtname_lower' => ['id' => id, 'name' => original_name]
        $allDistricts = District::all();
        $districtsMap = [];
        foreach ($allDistricts as $d) {
            $key = $d->state_id . '_' . strtolower(trim($d->name));
            $districtsMap[$key] = [
                'id' => $d->id,
                'name' => $d->name,
            ];
        }

        // Build existing pincodes map: 'district_id_pincode' => true
        $allPincodes = Pincode::all();
        $existingPincodesMap = [];
        foreach ($allPincodes as $p) {
            $key = $p->district_id . '_' . trim($p->pincode);
            $existingPincodesMap[$key] = true;
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // header is row 1
                $total++;

                $stateNameInput = trim($row['A'] ?? $row[array_key_first($row)] ?? '');
                $districtNameInput = trim($row['B'] ?? '');
                $pincodeInput = trim($row['C'] ?? '');

                if (empty($stateNameInput) || empty($districtNameInput) || empty($pincodeInput)) {
                    $failed++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => "{$stateNameInput} / {$districtNameInput} / {$pincodeInput}",
                        'status' => 'Failed',
                        'reason' => 'State Name, District Name, or Pincode is empty'
                    ];
                    continue;
                }

                if (!preg_match('/^\d{4,10}$/', $pincodeInput)) {
                    $failed++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => "{$stateNameInput} / {$districtNameInput} / {$pincodeInput}",
                        'status' => 'Failed',
                        'reason' => 'Invalid pincode format (must be 4 to 10 digits)'
                    ];
                    continue;
                }

                $stateKey = strtolower($stateNameInput);
                if (!isset($statesMap[$stateKey])) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => "{$stateNameInput} / {$districtNameInput} / {$pincodeInput}",
                        'status' => 'Skipped',
                        'reason' => "State '{$stateNameInput}' does not exist in database table"
                    ];
                    continue;
                }

                $matchedState = $statesMap[$stateKey];
                $districtKey = $matchedState['id'] . '_' . strtolower($districtNameInput);

                if (!isset($districtsMap[$districtKey])) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => "{$matchedState['name']} / {$districtNameInput} / {$pincodeInput}",
                        'status' => 'Skipped',
                        'reason' => "District '{$districtNameInput}' does not exist in state '{$matchedState['name']}' in database"
                    ];
                    continue;
                }

                $matchedDistrict = $districtsMap[$districtKey];
                $pincodeKey = $matchedDistrict['id'] . '_' . $pincodeInput;

                if (in_array($pincodeKey, $processedCompositeKeys)) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => "{$matchedState['name']} / {$matchedDistrict['name']} / {$pincodeInput}",
                        'status' => 'Skipped',
                        'reason' => "Duplicate pincode entry in uploaded file"
                    ];
                    continue;
                }

                if (isset($existingPincodesMap[$pincodeKey])) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => "{$matchedState['name']} / {$matchedDistrict['name']} / {$pincodeInput}",
                        'status' => 'Skipped',
                        'reason' => "Pincode '{$pincodeInput}' already exists for district '{$matchedDistrict['name']}'"
                    ];
                    continue;
                }

                Pincode::create([
                    'state_id' => $matchedState['id'],
                    'district_id' => $matchedDistrict['id'],
                    'pincode' => $pincodeInput,
                    'status' => '1',
                ]);

                $processedCompositeKeys[] = $pincodeKey;
                $existingPincodesMap[$pincodeKey] = true;
                $inserted++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return back()->with('import_result', compact('total', 'inserted', 'skipped', 'failed', 'details'));
    }

    public function pincodeSample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'state_name');
        $sheet->setCellValue('B1', 'district_name');
        $sheet->setCellValue('C1', 'pincode');

        $sampleData = [
            ['Maharashtra', 'Mumbai', '400001'],
            ['Maharashtra', 'Mumbai', '400002'],
            ['Maharashtra', 'Pune', '411001'],
            ['Delhi', 'New Delhi', '110001'],
            ['Uttar Pradesh', 'Lucknow', '226001'],
        ];

        $rowNum = 2;
        foreach ($sampleData as $data) {
            $sheet->setCellValue("A{$rowNum}", $data[0]);
            $sheet->setCellValue("B{$rowNum}", $data[1]);
            $sheet->setCellValue("C{$rowNum}", $data[2]);
            $rowNum++;
        }

        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(15);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, 'sample_pincodes.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

}
