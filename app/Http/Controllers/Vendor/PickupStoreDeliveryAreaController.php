<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PickupStore;
use App\Models\PickupStoreDeliveryArea;
use App\Models\State;
use App\Models\District;
use App\Imports\PincodeImport;
use App\Exports\PincodeExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PickupStoreDeliveryAreaController extends Controller
{
    private function checkAccess(PickupStore $store)
    {
        $user = Auth::guard('vendor')->user();
        if ($store->owner_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function index($id, Request $request)
    {
        $store = PickupStore::findOrFail($id);
        $this->checkAccess($store);

        $search = $request->get('search');
        $status = $request->get('status');
        $coverage_type = $request->get('coverage_type');

        // Get currently active coverage type for this store
        $activeCoverage = $store->delivery_coverage_type ?: 'pan_india';

        $query = PickupStoreDeliveryArea::where('pickup_store_id', $store->id)
            ->with(['state', 'district']);

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pincode', 'like', "%{$search}%")
                  ->orWhereHas('state', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('district', function ($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($coverage_type) {
            $query->where('coverage_type', $coverage_type);
        }

        $deliveryAreas = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $states = State::active()->orderBy('name')->get();

        return view('vendor.pickup-stores.delivery-areas', compact(
            'store',
            'deliveryAreas',
            'activeCoverage',
            'search',
            'status',
            'coverage_type',
            'states'
        ));
    }

    public function updateCoverage($id, Request $request)
    {
        $store = PickupStore::findOrFail($id);
        $this->checkAccess($store);

        $request->validate([
            'coverage_type' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\DeliveryCoverageType::class)],
        ]);

        $newCoverage = $request->input('coverage_type');

        DB::beginTransaction();
        try {
            // Update active coverage type on the store
            $store->delivery_coverage_type = $newCoverage;
            $store->save();

            // Set all existing delivery areas of OTHER coverage types to Inactive
            PickupStoreDeliveryArea::where('pickup_store_id', $store->id)
                ->where('coverage_type', '!=', $newCoverage)
                ->update(['status' => 'Inactive']);

            // Set all existing delivery areas of the NEW coverage type to Active
            PickupStoreDeliveryArea::where('pickup_store_id', $store->id)
                ->where('coverage_type', $newCoverage)
                ->update(['status' => 'Active']);

            // If Pan India, ensure a record exists and is Active
            if ($newCoverage === 'pan_india') {
                $exists = PickupStoreDeliveryArea::where('pickup_store_id', $store->id)
                    ->where('coverage_type', 'pan_india')
                    ->first();

                if ($exists) {
                    $exists->status = 'Active';
                    $exists->save();
                } else {
                    PickupStoreDeliveryArea::create([
                        'pickup_store_id' => $store->id,
                        'coverage_type' => 'pan_india',
                        'status' => 'Active',
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Active Delivery Coverage Type updated to: ' . ucwords(str_replace('_', ' ', $newCoverage)));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating coverage type: ' . $e->getMessage());
        }
    }

    public function store($id, Request $request)
    {
        $store = PickupStore::findOrFail($id);
        $this->checkAccess($store);

        $request->validate([
            'coverage_type' => ['required', \Illuminate\Validation\Rule::in([
                \App\Enums\DeliveryCoverageType::STATE->value,
                \App\Enums\DeliveryCoverageType::DISTRICT->value,
                \App\Enums\DeliveryCoverageType::PINCODE->value,
            ])],
        ]);

        $coverageType = $request->input('coverage_type');

        // Check if there is another active coverage type
        $activeCoverage = $store->delivery_coverage_type ?: 'pan_india';

        if ($activeCoverage && $activeCoverage !== $coverageType) {
            return redirect()->back()->with('error', "Cannot add {$coverageType} area. The active coverage type is currently: " . ucwords(str_replace('_', ' ', $activeCoverage)));
        }

        DB::beginTransaction();
        try {
            $successMsg = 'Delivery area added successfully.';

            if ($coverageType === 'state') {
                $request->validate([
                    'state_ids'   => 'nullable|array',
                    'state_ids.*' => 'exists:states,id',
                    'state_id'    => 'nullable|exists:states,id',
                ]);

                $stateIds = $request->input('state_ids', []);
                if (empty($stateIds) && $request->filled('state_id')) {
                    $stateIds = [$request->input('state_id')];
                }

                if (empty($stateIds)) {
                    return redirect()->back()->with('error', 'Please select at least one state.');
                }

                $addedCount = 0;
                $alreadyActiveCount = 0;

                foreach ($stateIds as $sId) {
                    $exists = PickupStoreDeliveryArea::where('pickup_store_id', $store->id)
                        ->where('coverage_type', 'state')
                        ->where('state_id', $sId)
                        ->first();

                    if ($exists) {
                        if ($exists->status !== 'Active') {
                            $exists->status = 'Active';
                            $exists->save();
                            $addedCount++;
                        } else {
                            $alreadyActiveCount++;
                        }
                    } else {
                        PickupStoreDeliveryArea::create([
                            'pickup_store_id' => $store->id,
                            'coverage_type'  => 'state',
                            'state_id'       => $sId,
                            'status'         => 'Active',
                        ]);
                        $addedCount++;
                    }
                }

                $successMsg = "Successfully added {$addedCount} state(s) to delivery coverage.";
                if ($alreadyActiveCount > 0) {
                    $successMsg .= " ({$alreadyActiveCount} state(s) were already active).";
                }

            } elseif ($coverageType === 'district') {
                $request->validate([
                    'state_id'       => 'required|exists:states,id',
                    'district_ids'   => 'nullable|array',
                    'district_ids.*' => 'exists:districts,id',
                    'district_id'    => 'nullable|exists:districts,id',
                ]);
                $stateId = $request->input('state_id');
                $districtIds = $request->input('district_ids', []);

                if (empty($districtIds) && $request->filled('district_id')) {
                    $districtIds = [$request->input('district_id')];
                }

                if (empty($districtIds)) {
                    return redirect()->back()->with('error', 'Please select at least one district.');
                }

                $addedCount = 0;
                $alreadyActiveCount = 0;

                foreach ($districtIds as $dId) {
                    $exists = PickupStoreDeliveryArea::where('pickup_store_id', $store->id)
                        ->where('coverage_type', 'district')
                        ->where('district_id', $dId)
                        ->first();

                    if ($exists) {
                        if ($exists->status !== 'Active') {
                            $exists->status = 'Active';
                            $exists->save();
                            $addedCount++;
                        } else {
                            $alreadyActiveCount++;
                        }
                    } else {
                        PickupStoreDeliveryArea::create([
                            'pickup_store_id' => $store->id,
                            'coverage_type'  => 'district',
                            'state_id'       => $stateId,
                            'district_id'    => $dId,
                            'status'         => 'Active',
                        ]);
                        $addedCount++;
                    }
                }

                $successMsg = "Successfully added {$addedCount} district(s) to delivery coverage.";
                if ($alreadyActiveCount > 0) {
                    $successMsg .= " ({$alreadyActiveCount} district(s) were already active).";
                }

            } elseif ($coverageType === 'pincode') {
                $request->validate([
                    'pincode' => 'required',
                ]);

                $rawPincodes = preg_split('/[\s,]+/', (string) $request->input('pincode'), -1, PREG_SPLIT_NO_EMPTY);
                $validPincodes = [];
                foreach ($rawPincodes as $pin) {
                    $pin = trim($pin);
                    if (preg_match('/^\d{6}$/', $pin)) {
                        $validPincodes[] = $pin;
                    }
                }
                $validPincodes = array_unique($validPincodes);

                if (empty($validPincodes)) {
                    return redirect()->back()->with('error', 'Please enter valid 6-digit Indian pincode(s).');
                }

                $pincodeLookup = DB::table('pincodes')
                    ->whereIn('pincode', $validPincodes)
                    ->get(['pincode', 'state_id', 'district_id'])
                    ->keyBy('pincode');

                $addedCount = 0;
                $alreadyActiveCount = 0;

                foreach ($validPincodes as $pincode) {
                    $exists = PickupStoreDeliveryArea::where('pickup_store_id', $store->id)
                        ->where('coverage_type', 'pincode')
                        ->where('pincode', $pincode)
                        ->first();

                    if ($exists) {
                        if ($exists->status !== 'Active') {
                            $exists->status = 'Active';
                            $exists->save();
                            $addedCount++;
                        } else {
                            $alreadyActiveCount++;
                        }
                    } else {
                        $pincodeInfo = $pincodeLookup->get($pincode);
                        $stateId = $pincodeInfo ? $pincodeInfo->state_id : null;
                        $districtId = $pincodeInfo ? $pincodeInfo->district_id : null;

                        PickupStoreDeliveryArea::create([
                            'pickup_store_id' => $store->id,
                            'coverage_type'  => 'pincode',
                            'state_id'       => $stateId,
                            'district_id'    => $districtId,
                            'pincode'        => $pincode,
                            'status'         => 'Active',
                        ]);
                        $addedCount++;
                    }
                }

                $successMsg = "Successfully added {$addedCount} pincode(s) to delivery coverage.";
                if ($alreadyActiveCount > 0) {
                    $successMsg .= " ({$alreadyActiveCount} pincode(s) were already active).";
                }
            }

            DB::commit();
            return redirect()->back()->with('success', $successMsg);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error adding delivery area: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id, $areaId, Request $request)
    {
        $store = PickupStore::findOrFail($id);
        $this->checkAccess($store);

        $area = PickupStoreDeliveryArea::where('pickup_store_id', $store->id)->findOrFail($areaId);

        $request->validate([
            'status' => 'required|in:Active,Inactive',
        ]);

        $newStatus = $request->input('status');

        if ($newStatus === 'Active') {
            // Check if there is another active coverage type
            $activeCoverage = $store->delivery_coverage_type ?: 'pan_india';

            if ($activeCoverage && $activeCoverage !== $area->coverage_type) {
                return response()->json(['error' => "Cannot activate this area. The active coverage type is currently: " . ucwords(str_replace('_', ' ', $activeCoverage))], 422);
            }
        }

        $area->status = $newStatus;
        $area->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'status' => $area->status,
        ]);
    }

    public function destroy($id, $areaId)
    {
        $store = PickupStore::findOrFail($id);
        $this->checkAccess($store);

        $area = PickupStoreDeliveryArea::where('pickup_store_id', $store->id)->findOrFail($areaId);
        $area->delete();

        return redirect()->back()->with('success', 'Delivery area removed successfully.');
    }

    public function bulkImport($id, Request $request)
    {
        $store = PickupStore::findOrFail($id);
        $this->checkAccess($store);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $rawCoverage = $store->delivery_coverage_type;
        $activeCoverage = $rawCoverage instanceof \App\Enums\DeliveryCoverageType ? $rawCoverage->value : ($rawCoverage ?: 'pan_india');

        if ($activeCoverage === 'pan_india') {
            $activeCoverage = 'district';
            $store->update(['delivery_coverage_type' => 'district']);
        }

        try {
            $import = new PincodeImport();
            Excel::import($import, $request->file('file'));

            $rows = $import->rows;
            if (empty($rows)) {
                return redirect()->back()->with('error', 'The uploaded file is empty.');
            }

            if ($activeCoverage === 'district') {
                return $this->importDistricts($store, $rows);
            } elseif ($activeCoverage === 'state') {
                return $this->importStates($store, $rows);
            } else {
                return $this->importPincodes($store, $rows);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing delivery areas: ' . $e->getMessage());
        }
    }

    private function importDistricts($store, array $rows)
    {
        if (empty($rows)) {
            return redirect()->back()->with('error', 'No data rows found in uploaded file.');
        }

        $header = array_map('strtolower', array_map(fn($v) => trim(str_replace("\r", "", (string)$v)), $rows[0]));

        $districtIndex = false;
        $stateIndex = false;

        foreach ($header as $idx => $colName) {
            $cleanColName = preg_replace('/[^a-z0-9_]/', '', str_replace('*', '', $colName));
            if (in_array($cleanColName, ['district', 'district_name', 'districtname', 'districts', 'district_id', 'districtid'])) {
                $districtIndex = $idx;
            }
            if (in_array($cleanColName, ['state', 'state_name', 'statename', 'states', 'state_id', 'stateid', 'state_code'])) {
                $stateIndex = $idx;
            }
        }

        if ($districtIndex === false) {
            $districtIndex = 0;
            if (count($header) > 1 && $stateIndex === false) {
                $stateIndex = 1;
            }
        }

        $firstCell = trim(str_replace("\r", "", (string)($rows[0][$districtIndex] ?? '')));
        $cleanFirstCell = preg_replace('/[^a-z0-9]/', '', strtolower($firstCell));
        $isHeader = ($districtIndex !== false && count($header) > 1)
            || in_array($cleanFirstCell, ['district', 'districtname', 'districts', 'districtid'])
            || !District::whereRaw('LOWER(name) = ?', [strtolower($firstCell)])->exists();
        $startRow = $isHeader ? 1 : 0;

        $importedCount = 0;
        $ignoredCount = 0;

        $existingDistricts = PickupStoreDeliveryArea::where('pickup_store_id', $store->id)
            ->where('coverage_type', 'district')
            ->pluck('status', 'district_id')
            ->toArray();

        DB::beginTransaction();
        try {
            for ($i = $startRow; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (!isset($row[$districtIndex])) continue;

                $distVal = trim(str_replace("\r", "", (string)$row[$districtIndex]));
                if (empty($distVal)) continue;

                $distClean = preg_replace('/[^a-z0-9]/', '', strtolower($distVal));
                if (in_array($distClean, ['district', 'districtname', 'districts', 'districtid'])) {
                    continue;
                }

                $stateVal = ($stateIndex !== false && isset($row[$stateIndex])) ? trim(str_replace("\r", "", (string)$row[$stateIndex])) : null;

                $query = District::query();
                if (is_numeric($distVal)) {
                    $query->where('id', $distVal);
                } else {
                    $query->whereRaw('LOWER(name) = ?', [strtolower($distVal)]);
                    if (!empty($stateVal)) {
                        $query->whereHas('state', function ($q) use ($stateVal) {
                            if (is_numeric($stateVal)) {
                                $q->where('id', $stateVal);
                            } else {
                                $q->whereRaw('LOWER(name) = ?', [strtolower($stateVal)])
                                  ->orWhereRaw('LOWER(code) = ?', [strtolower($stateVal)]);
                            }
                        });
                    }
                }

                $district = $query->first();

                if (!$district) {
                    $ignoredCount++;
                    continue;
                }

                if (array_key_exists($district->id, $existingDistricts)) {
                    if ($existingDistricts[$district->id] !== 'Active') {
                        PickupStoreDeliveryArea::where('pickup_store_id', $store->id)
                            ->where('coverage_type', 'district')
                            ->where('district_id', $district->id)
                            ->update(['status' => 'Active']);
                        $existingDistricts[$district->id] = 'Active';
                        $importedCount++;
                    }
                } else {
                    PickupStoreDeliveryArea::create([
                        'pickup_store_id' => $store->id,
                        'coverage_type' => 'district',
                        'state_id' => $district->state_id,
                        'district_id' => $district->id,
                        'status' => 'Active',
                    ]);
                    $existingDistricts[$district->id] = 'Active';
                    $importedCount++;
                }
            }

            $store->update(['delivery_coverage_type' => 'district']);

            DB::commit();

            $msg = "Successfully imported {$importedCount} districts.";
            if ($ignoredCount > 0) {
                $msg .= " Ignored {$ignoredCount} invalid/unmatched district records.";
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error importing districts: ' . $e->getMessage());
        }
    }

    private function importStates($store, array $rows)
    {
        $header = array_map('strtolower', array_map(fn($v) => trim(str_replace("\r", "", (string)$v)), $rows[0]));
        $stateIndex = false;

        foreach ($header as $idx => $colName) {
            $cleanColName = preg_replace('/[^a-z0-9_]/', '', str_replace('*', '', $colName));
            if (in_array($cleanColName, ['state', 'state_name', 'statename', 'states', 'state_code', 'state_id', 'stateid'])) {
                $stateIndex = $idx;
                break;
            }
        }

        if ($stateIndex === false) {
            $stateIndex = 0;
        }

        $firstCell = trim(str_replace("\r", "", (string)($rows[0][$stateIndex] ?? '')));
        $cleanFirstCell = preg_replace('/[^a-z0-9]/', '', strtolower($firstCell));
        $isHeader = in_array($cleanFirstCell, ['state', 'statename', 'states', 'statecode', 'stateid'])
            || !State::whereRaw('LOWER(name) = ?', [strtolower($firstCell)])
                ->orWhereRaw('LOWER(code) = ?', [strtolower($firstCell)])
                ->exists();
        $startRow = $isHeader ? 1 : 0;

        $importedCount = 0;
        $ignoredCount = 0;

        $existingStates = PickupStoreDeliveryArea::where('pickup_store_id', $store->id)
            ->where('coverage_type', 'state')
            ->pluck('status', 'state_id')
            ->toArray();

        DB::beginTransaction();
        try {
            for ($i = $startRow; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (!isset($row[$stateIndex])) continue;

                $stateVal = trim(str_replace("\r", "", (string)$row[$stateIndex]));
                if (empty($stateVal)) continue;

                $stateClean = preg_replace('/[^a-z0-9]/', '', strtolower($stateVal));
                if (in_array($stateClean, ['state', 'statename', 'states', 'statecode', 'stateid'])) {
                    continue;
                }

                $state = State::where(function ($q) use ($stateVal) {
                    if (is_numeric($stateVal)) {
                        $q->where('id', $stateVal);
                    } else {
                        $q->whereRaw('LOWER(name) = ?', [strtolower($stateVal)])
                          ->orWhereRaw('LOWER(code) = ?', [strtolower($stateVal)]);
                    }
                })->first();

                if (!$state) {
                    $ignoredCount++;
                    continue;
                }

                if (array_key_exists($state->id, $existingStates)) {
                    if ($existingStates[$state->id] !== 'Active') {
                        PickupStoreDeliveryArea::where('pickup_store_id', $store->id)
                            ->where('coverage_type', 'state')
                            ->where('state_id', $state->id)
                            ->update(['status' => 'Active']);
                        $existingStates[$state->id] = 'Active';
                        $importedCount++;
                    }
                } else {
                    PickupStoreDeliveryArea::create([
                        'pickup_store_id' => $store->id,
                        'coverage_type' => 'state',
                        'state_id' => $state->id,
                        'status' => 'Active',
                    ]);
                    $existingStates[$state->id] = 'Active';
                    $importedCount++;
                }
            }

            DB::commit();

            $msg = "Successfully imported {$importedCount} states.";
            if ($ignoredCount > 0) {
                $msg .= " Ignored {$ignoredCount} invalid/unmatched state records.";
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error importing states: ' . $e->getMessage());
        }
    }

    private function importPincodes($store, array $rows)
    {
        $header = array_map('strtolower', array_map(fn($v) => trim(str_replace("\r", "", (string)$v)), $rows[0]));
        $pincodeIndex = false;

        foreach ($header as $idx => $colName) {
            $cleanColName = preg_replace('/[^a-z0-9_]/', '', str_replace('*', '', $colName));
            if (in_array($cleanColName, ['pincode', 'pin_code', 'pin', 'postalcode', 'zip'])) {
                $pincodeIndex = $idx;
                break;
            }
        }

        if ($pincodeIndex === false) {
            $pincodeIndex = 0;
        }

        $firstCell = trim((string)($rows[0][$pincodeIndex] ?? ''));
        $startRow = !is_numeric($firstCell) ? 1 : 0;

        $importedCount = 0;
        $ignoredCount = 0;
        $pincodesToInsert = [];

        $existingPincodes = PickupStoreDeliveryArea::where('pickup_store_id', $store->id)
            ->where('coverage_type', 'pincode')
            ->pluck('status', 'pincode')
            ->toArray();

        $pincodesInFile = [];
        for ($i = $startRow; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (isset($row[$pincodeIndex])) {
                $pincode = trim($row[$pincodeIndex]);
                if (preg_match('/^\d{6}$/', $pincode)) {
                    $pincodesInFile[] = $pincode;
                }
            }
        }
        $pincodesInFile = array_unique($pincodesInFile);

        $pincodeLookup = DB::table('pincodes')
            ->whereIn('pincode', $pincodesInFile)
            ->get(['pincode', 'state_id', 'district_id'])
            ->keyBy('pincode');

        $pincodesToActivate = [];

        DB::beginTransaction();
        try {
            for ($i = $startRow; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (!isset($row[$pincodeIndex])) {
                    continue;
                }

                $pincode = trim($row[$pincodeIndex]);
                if (empty($pincode)) {
                    continue;
                }

                if (!preg_match('/^\d{6}$/', $pincode)) {
                    $ignoredCount++;
                    continue;
                }

                if (isset($pincodesToInsert[$pincode])) {
                    continue;
                }

                if (array_key_exists($pincode, $existingPincodes)) {
                    if ($existingPincodes[$pincode] !== 'Active') {
                        $pincodesToActivate[] = $pincode;
                        $importedCount++;
                    }
                    continue;
                }

                $pincodeInfo = $pincodeLookup->get($pincode);
                $stateId = $pincodeInfo ? $pincodeInfo->state_id : null;
                $districtId = $pincodeInfo ? $pincodeInfo->district_id : null;

                $pincodesToInsert[$pincode] = [
                    'pickup_store_id' => $store->id,
                    'coverage_type' => 'pincode',
                    'state_id' => $stateId,
                    'district_id' => $districtId,
                    'pincode' => $pincode,
                    'status' => 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($pincodesToActivate)) {
                PickupStoreDeliveryArea::where('pickup_store_id', $store->id)
                    ->where('coverage_type', 'pincode')
                    ->whereIn('pincode', $pincodesToActivate)
                    ->update(['status' => 'Active']);
            }

            if (!empty($pincodesToInsert)) {
                PickupStoreDeliveryArea::insert(array_values($pincodesToInsert));
                $importedCount += count($pincodesToInsert);
            }

            DB::commit();

            $msg = "Successfully imported {$importedCount} pincodes.";
            if ($ignoredCount > 0) {
                $msg .= " Ignored {$ignoredCount} invalid pincodes.";
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error importing pincodes: ' . $e->getMessage());
        }
    }

    public function sampleTemplate($id, Request $request)
    {
        $store = PickupStore::findOrFail($id);
        $this->checkAccess($store);

        $rawCoverage = $store->delivery_coverage_type;
        $activeCoverage = $rawCoverage instanceof \App\Enums\DeliveryCoverageType ? $rawCoverage->value : ($rawCoverage ?: 'pan_india');
        $type = $request->get('type', $activeCoverage);

        if ($type === 'state') {
            $headers = ['State Name'];
            $rows = [
                ['Uttar Pradesh'],
                ['Maharashtra'],
                ['Delhi'],
                ['Karnataka'],
            ];
            $filename = 'sample_delivery_states.csv';
        } elseif ($type === 'district') {
            $headers = ['District Name', 'State Name'];
            $rows = [
                ['Lucknow', 'Uttar Pradesh'],
                ['Kanpur Nagar', 'Uttar Pradesh'],
                ['Mumbai', 'Maharashtra'],
                ['Bengaluru Urban', 'Karnataka'],
            ];
            $filename = 'sample_delivery_districts.csv';
        } else {
            $headers = ['Pincode'];
            $rows = [
                ['226001'],
                ['226002'],
                ['226003'],
                ['400001'],
            ];
            $filename = 'sample_delivery_pincodes.csv';
        }

        $output = implode(',', $headers) . "\n";
        foreach ($rows as $row) {
            $output .= implode(',', $row) . "\n";
        }

        return response($output, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function export($id)
    {
        $store = PickupStore::findOrFail($id);
        $this->checkAccess($store);

        $pincodes = PickupStoreDeliveryArea::where('pickup_store_id', $store->id)
            ->where('coverage_type', 'pincode')
            ->orderBy('pincode')
            ->get();

        $data = [];
        foreach ($pincodes as $p) {
            $data[] = [
                $p->pincode,
                $p->status,
                $p->created_at->format('Y-m-d H:i:s'),
            ];
        }

        $filename = 'pincodes_' . $store->pickup_store_code . '_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new PincodeExport($data), $filename);
    }
}
