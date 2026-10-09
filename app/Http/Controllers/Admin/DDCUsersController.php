<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DDC;
use App\Models\DdcWallet;
use App\Models\DdcWalletRequest;
use App\Models\Pincode;
use App\Models\DdcPincode;
use App\Models\DdcPincodeAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Services\TheekHaiService;

class DDCUsersController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $limit = $request->get('limit', 15);

        $query = DDC::query()->withCount('assignedPincodes');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($profileQuery) use ($search) {
                        $profileQuery->where('district', 'like', "%{$search}%")
                            ->orWhere('state', 'like', "%{$search}%");
                    });
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
                fputcsv($file, ['ID', 'Name', 'Email', 'Phone', 'District', 'State', 'Status', 'Created At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->name,
                        $row->email,
                        $row->mobile ?? $row->phone ?? '—',
                        $row->district ?? ($row->profile?->district ?? '—'),
                        $row->state ?? ($row->profile?->state ?? '—'),
                        $row->status === '1' ? 'Active' : 'Inactive',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'ddc_users_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $users = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.external.ddc.index', compact('users', 'search', 'sort', 'order', 'limit'));
    }

    public function create()
    {
        $states = \App\Models\State::active()->orderBy('name')->get();
        $ddcCodes = \App\Models\DdcCode::orderBy('code')->get();

        $adplStateId = \App\Models\SystemSetting::getVal('adpl_state_id', '');
        $adplDistrictId = \App\Models\SystemSetting::getVal('adpl_district_id', '');
        $adplDistrictName = '';
        if ($adplDistrictId) {
            $dstObj = \App\Models\District::find($adplDistrictId);
            if ($dstObj) {
                $adplDistrictName = $dstObj->name;
            }
        }

        $adplDefaults = [
            'company_name'  => \App\Models\SystemSetting::getVal('adpl_company_name', ''),
            'gst_number'    => \App\Models\SystemSetting::getVal('adpl_gst_number', ''),
            'state_id'      => $adplStateId,
            'district_id'   => $adplDistrictId,
            'district_name' => $adplDistrictName,
            'pincode'       => \App\Models\SystemSetting::getVal('adpl_pincode', ''),
            'address'       => \App\Models\SystemSetting::getVal('adpl_address', ''),
        ];

        return view('admin.external.ddc.create', compact('states', 'ddcCodes', 'adplDefaults'));
    }

    public function checkCode(Request $request)
    {
        $code = $request->get('code');
        if (!$code) {
            return response()->json(['status' => 'error', 'message' => 'Code parameter is required.'], 400);
        }

        $exists = \App\Models\DdcCode::where('code', $code)->first();
        if (!$exists) {
            return response()->json(['status' => 'available', 'assigned' => false]);
        }

        if ($exists->is_assigned) {
            return response()->json(['status' => 'assigned', 'assigned' => true, 'message' => 'DDC Code is already assigned.']);
        }

        return response()->json(['status' => 'available', 'assigned' => false]);
    }

    public function generateCode(Request $request)
    {
        // Find highest numeric code in ddc_codes (e.g. DDB800)
        $latest = \App\Models\DdcCode::where('code', 'REGEXP', '^DDB[0-9]+$')
            ->get()
            ->sortByDesc(function ($item) {
                return (int) substr($item->code, 3);
            })
            ->first();

        $nextNumber = 0; // Default starts at 000 (DDB000)
        if ($latest) {
            $nextNumber = ((int) substr($latest->code, 3)) + 1;
        }

        $newCode = 'DDB' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        while (\App\Models\DdcCode::where('code', $newCode)->exists()) {
            $nextNumber++;
            $newCode = 'DDB' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        // Save to database
        $ddcCode = \App\Models\DdcCode::create([
            'code' => $newCode,
            'is_assigned' => false,
            'assigned_to_user_id' => null,
        ]);

        return response()->json([
            'status' => 'success',
            'code' => $newCode
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ddc_owner_name' => ['required', 'string', 'max:255'],
            'official_phone' => ['required', 'numeric', 'digits:10'],
            'official_email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'state' => ['required'],
            'district' => ['required', 'string'],
            'company_name' => ['required', 'string', 'max:255'],
            'ddc_code' => ['required', 'string'],
            
            // Mandatory fields per USER_REQUEST
            'trade_name' => ['required', 'string', 'max:255'],
            'master_depot_image' => ['required', 'image', 'mimes:jpeg,jpg', 'max:5120'],
            'contract_document' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            
            // Mandatory KYC documents
            'gst_certificate' => ['required', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            'aadhaar_card' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            'fssai_certificate' => ['required', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            'cancelled_cheque' => ['required', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            'trade_license' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            'ddc_agreement' => ['required', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            'franchise_deposit_receipt' => ['required', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            
            // ADPL and Depot Details (now mandatory)
            'adpl_address' => ['required', 'string'],
            'ddc_address' => ['required', 'string'],
            'adpl_gst_number' => ['required', 'string', 'min:15', 'max:15'],
            'adpl_company_name' => ['required', 'string', 'max:255'],
            'ddc_gst_number' => ['required', 'string', 'min:15', 'max:15'],
            'adpl_pincode' => ['required', 'numeric', 'digits:6'],
            'ddc_pincode' => ['required', 'numeric', 'digits:6'],
            'adpl_state' => ['required'],
            'adpl_district' => ['required', 'string'],
        ]);

        $ddcCode = $request->ddc_code;

        // Check if DDC code is already assigned in database
        $ddcCodeRecord = \App\Models\DdcCode::where('code', $ddcCode)->first();
        if (!$ddcCodeRecord) {
            return back()->withInput()->withErrors(['ddc_code' => 'Invalid DDC Code.']);
        }
        if ($ddcCodeRecord->is_assigned) {
            return back()->withInput()->withErrors(['ddc_code' => 'DDC Code is already assigned.']);
        }

        // Call TheekHai Register API to register mobile number
        $theekHaiResult = TheekHaiService::registerUser($request->ddc_owner_name, $request->official_phone);
        if (!$theekHaiResult['success']) {
            \Illuminate\Support\Facades\Log::warning('TheekHai Register API Notice for DDC creation', [
                'name'    => $request->ddc_owner_name,
                'mobile'  => $request->official_phone,
                'message' => $theekHaiResult['message'] ?? 'Unable to register on TheekHai API',
            ]);
        }

        // Upload files
        $masterDepotImage = null;
        if ($request->hasFile('master_depot_image')) {
            $masterDepotImage = $this->uploadFileToService($request->file('master_depot_image'), 'ddc');
        }

        $contractDocument = null;
        if ($request->hasFile('contract_document')) {
            $contractDocument = $this->uploadFileToService($request->file('contract_document'), 'ddc');
        }

        $gstCertificate = null;
        if ($request->hasFile('gst_certificate')) {
            $gstCertificate = $this->uploadFileToService($request->file('gst_certificate'), 'ddc');
        }

        $aadhaarCard = null;
        if ($request->hasFile('aadhaar_card')) {
            $aadhaarCard = $this->uploadFileToService($request->file('aadhaar_card'), 'ddc');
        }

        $fssaiCertificate = null;
        if ($request->hasFile('fssai_certificate')) {
            $fssaiCertificate = $this->uploadFileToService($request->file('fssai_certificate'), 'ddc');
        }

        $cancelledCheque = null;
        if ($request->hasFile('cancelled_cheque')) {
            $cancelledCheque = $this->uploadFileToService($request->file('cancelled_cheque'), 'ddc');
        }

        $tradeLicense = null;
        if ($request->hasFile('trade_license')) {
            $tradeLicense = $this->uploadFileToService($request->file('trade_license'), 'ddc');
        }

        $ddcAgreement = null;
        if ($request->hasFile('ddc_agreement')) {
            $ddcAgreement = $this->uploadFileToService($request->file('ddc_agreement'), 'ddc');
        }

        $franchiseDepositReceipt = null;
        if ($request->hasFile('franchise_deposit_receipt')) {
            $franchiseDepositReceipt = $this->uploadFileToService($request->file('franchise_deposit_receipt'), 'ddc');
        }

        // Resolve State Names from IDs
        $stateName = '';
        $stateId = null;
        if ($request->state) {
            $stateObj = \App\Models\State::find($request->state);
            if ($stateObj) {
                $stateName = $stateObj->name;
                $stateId = $stateObj->id;
            } else {
                $stateName = $request->state;
            }
        }

        $districtId = null;
        if ($request->district && $stateId) {
            $distObj = \App\Models\District::where('name', $request->district)->where('state_id', $stateId)->first();
            if ($distObj) {
                $districtId = $distObj->id;
            }
        }

        $adplStateName = '';
        $adplStateId = null;
        if ($request->adpl_state) {
            $adplStateObj = \App\Models\State::find($request->adpl_state);
            if ($adplStateObj) {
                $adplStateName = $adplStateObj->name;
                $adplStateId = $adplStateObj->id;
            } else {
                $adplStateName = $request->adpl_state;
            }
        }

        $adplDistrictId = null;
        if ($request->adpl_district && $adplStateId) {
            $adplDistObj = \App\Models\District::where('name', $request->adpl_district)->where('state_id', $adplStateId)->first();
            if ($adplDistObj) {
                $adplDistrictId = $adplDistObj->id;
            }
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Create user
            $user = new \App\Models\User();
            $user->name = $request->ddc_owner_name; // Set name to Owner name
            $user->email = $request->official_email;
            $user->mobile = $request->official_phone;
            $user->role = 'ddc';
            $user->password = Hash::make($request->password);
            $user->status = '1';
            $user->save();

            // Create/update profile
            $profile = $user->profile()->firstOrCreate([]);

            $profile->update([
                'ddc_code' => $ddcCode,
                'type' => (strtoupper(trim($ddcCode)) === 'DDB000' || trim($ddcCode) === '000') ? 'virtual' : 'physical', // DDB000 is virtual, subsequent are physical
                'ddc_owner_name' => $request->ddc_owner_name,
                'official_phone' => $request->official_phone,
                'official_email' => $request->official_email,
                'adpl_address' => $request->adpl_address,
                'ddc_address' => $request->ddc_address,
                'adpl_gst_number' => $request->adpl_gst_number,
                'adpl_company_name' => $request->adpl_company_name,
                'ddc_gst_number' => $request->ddc_gst_number,
                'company_name' => $request->company_name,
                'phone' => $request->official_phone,
                'email' => $request->official_email,
                'adpl_pincode' => $request->adpl_pincode,
                'ddc_pincode' => $request->ddc_pincode,
                'master_depot_image' => $masterDepotImage,
                'contract_document' => $contractDocument,
                'trade_name' => $request->trade_name,
                'adpl_state' => $adplStateName,
                'adpl_state_id' => $adplStateId,
                'adpl_district' => $request->adpl_district,
                'adpl_district_id' => $adplDistrictId,
                'state' => $stateName,
                'state_id' => $stateId,
                'district' => $request->district,
                'district_id' => $districtId,
                'gst_certificate' => $gstCertificate,
                'aadhaar_card' => $aadhaarCard,
                'fssai_certificate' => $fssaiCertificate,
                'cancelled_cheque' => $cancelledCheque,
                'trade_license' => $tradeLicense,
                'ddc_agreement' => $ddcAgreement,
                'franchise_deposit_receipt' => $franchiseDepositReceipt,
            ]);

            // Mark DDC code as assigned
            $ddcCodeRecord->update([
                'is_assigned' => true,
                'assigned_to_user_id' => $user->id,
            ]);

            \Illuminate\Support\Facades\DB::commit();

            // Share login details via TheekHai Message API
            $loginUrl = route('ddc.login');
            $smsMessage = "Welcome to Indian MSME! Your DDC account has been successfully created. Name: {$user->name}, DDC Code: {$ddcCode}, Password: {$request->password}. Portal URL: {$loginUrl}";

            TheekHaiService::sendMessage($user->mobile, $smsMessage);

            return redirect()->route('admin.external.ddc.index')->with('success', 'DDC User created successfully with DDC Code: ' . $ddcCode . '. Registered on TheekHai API & login credentials sent to ' . $user->mobile);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create DDC User: ' . $e->getMessage()]);
        }
    }

    public function edit(int $id)
    {
        $user = DDC::findOrFail($id);
        $states = \App\Models\State::active()->orderBy('name')->get();
        $ddcCodes = \App\Models\DdcCode::orderBy('code')->get();
        return view('admin.external.ddc.edit', compact('user', 'states', 'ddcCodes'));
    }

    public function update(Request $request, int $id)
    {
        $user = DDC::findOrFail($id);
        $profile = $user->profile ?: $user->profile()->create([]);

        $request->validate([
            'ddc_owner_name' => ['required', 'string', 'max:255'],
            'official_phone' => ['required', 'numeric', 'digits:10'],
            'official_email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
            'state' => ['required'],
            'district' => ['required', 'string'],
            'company_name' => ['required', 'string', 'max:255'],
            'ddc_code' => ['required', 'string'],
            'trade_name' => ['required', 'string', 'max:255'],
            
            // Files are optional on edit/update
            'master_depot_image' => ['nullable', 'image', 'mimes:jpeg,jpg', 'max:5120'],
            'contract_document' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'gst_certificate' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            'aadhaar_card' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            'fssai_certificate' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            'cancelled_cheque' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            'trade_license' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            'ddc_agreement' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            'franchise_deposit_receipt' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],

            'adpl_address' => ['required', 'string'],
            'ddc_address' => ['required', 'string'],
            'adpl_gst_number' => ['required', 'string', 'min:15', 'max:15'],
            'adpl_company_name' => ['required', 'string', 'max:255'],
            'ddc_gst_number' => ['required', 'string', 'min:15', 'max:15'],
            'adpl_pincode' => ['required', 'numeric', 'digits:6'],
            'ddc_pincode' => ['required', 'numeric', 'digits:6'],
            'adpl_state' => ['required'],
            'adpl_district' => ['required', 'string'],

            // Bank details (optional)
            'account_holder_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'ifsc_code' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],

            // QR Code
            'qr_code' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
        ]);

        $oldDdcCode = $profile->ddc_code;
        $newDdcCode = $request->ddc_code;

        if ($oldDdcCode !== $newDdcCode) {
            // Check if new DDC code is already assigned
            $newCodeRecord = \App\Models\DdcCode::where('code', $newDdcCode)->first();
            if (!$newCodeRecord) {
                return back()->withInput()->withErrors(['ddc_code' => 'Invalid DDC Code.']);
            }
            if ($newCodeRecord->is_assigned && $newCodeRecord->assigned_to_user_id !== $user->id) {
                return back()->withInput()->withErrors(['ddc_code' => 'DDC Code is already assigned to another user.']);
            }

            // Release old DDC code
            if ($oldDdcCode) {
                \App\Models\DdcCode::where('code', $oldDdcCode)->update([
                    'is_assigned' => false,
                    'assigned_to_user_id' => null,
                ]);
            }

            // Assign new DDC code
            $newCodeRecord->update([
                'is_assigned' => true,
                'assigned_to_user_id' => $user->id,
            ]);
        }

        // Upload files
        $masterDepotImage = $profile->master_depot_image;
        if ($request->hasFile('master_depot_image')) {
            $masterDepotImage = $this->uploadFileToService($request->file('master_depot_image'), 'ddc');
        }

        $contractDocument = $profile->contract_document;
        if ($request->hasFile('contract_document')) {
            $contractDocument = $this->uploadFileToService($request->file('contract_document'), 'ddc');
        }

        $gstCertificate = $profile->gst_certificate;
        if ($request->hasFile('gst_certificate')) {
            $gstCertificate = $this->uploadFileToService($request->file('gst_certificate'), 'ddc');
        }

        $aadhaarCard = $profile->aadhaar_card;
        if ($request->hasFile('aadhaar_card')) {
            $aadhaarCard = $this->uploadFileToService($request->file('aadhaar_card'), 'ddc');
        }

        $fssaiCertificate = $profile->fssai_certificate;
        if ($request->hasFile('fssai_certificate')) {
            $fssaiCertificate = $this->uploadFileToService($request->file('fssai_certificate'), 'ddc');
        }

        $cancelledCheque = $profile->cancelled_cheque;
        if ($request->hasFile('cancelled_cheque')) {
            $cancelledCheque = $this->uploadFileToService($request->file('cancelled_cheque'), 'ddc');
        }

        $tradeLicense = $profile->trade_license;
        if ($request->hasFile('trade_license')) {
            $tradeLicense = $this->uploadFileToService($request->file('trade_license'), 'ddc');
        }

        $ddcAgreement = $profile->ddc_agreement;
        if ($request->hasFile('ddc_agreement')) {
            $ddcAgreement = $this->uploadFileToService($request->file('ddc_agreement'), 'ddc');
        }

        $franchiseDepositReceipt = $profile->franchise_deposit_receipt;
        if ($request->hasFile('franchise_deposit_receipt')) {
            $franchiseDepositReceipt = $this->uploadFileToService($request->file('franchise_deposit_receipt'), 'ddc');
        }

        $qrCode = $profile->qr_code;
        if ($request->hasFile('qr_code')) {
            $qrCode = $this->uploadFileToService($request->file('qr_code'), 'ddc');
        }

        // Resolve State Names from IDs
        $stateName = '';
        $stateId = null;
        if ($request->state) {
            $stateObj = \App\Models\State::find($request->state);
            if ($stateObj) {
                $stateName = $stateObj->name;
                $stateId = $stateObj->id;
            } else {
                $stateName = $request->state;
            }
        }

        $districtId = null;
        if ($request->district && $stateId) {
            $distObj = \App\Models\District::where('name', $request->district)->where('state_id', $stateId)->first();
            if ($distObj) {
                $districtId = $distObj->id;
            }
        }

        $adplStateName = '';
        $adplStateId = null;
        if ($request->adpl_state) {
            $adplStateObj = \App\Models\State::find($request->adpl_state);
            if ($adplStateObj) {
                $adplStateName = $adplStateObj->name;
                $adplStateId = $adplStateObj->id;
            } else {
                $adplStateName = $request->adpl_state;
            }
        }

        $adplDistrictId = null;
        if ($request->adpl_district && $adplStateId) {
            $adplDistObj = \App\Models\District::where('name', $request->adpl_district)->where('state_id', $adplStateId)->first();
            if ($adplDistObj) {
                $adplDistrictId = $adplDistObj->id;
            }
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Update user details
            $user->name = $request->ddc_owner_name;
            $user->email = $request->official_email;
            $user->mobile = $request->official_phone;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            $profile->update([
                'ddc_code' => $newDdcCode,
                'type' => (strtoupper(trim($newDdcCode)) === 'DDB000' || trim($newDdcCode) === '000') ? 'virtual' : 'physical',
                'ddc_owner_name' => $request->ddc_owner_name,
                'official_phone' => $request->official_phone,
                'official_email' => $request->official_email,
                'adpl_address' => $request->adpl_address,
                'ddc_address' => $request->ddc_address,
                'adpl_gst_number' => $request->adpl_gst_number,
                'adpl_company_name' => $request->adpl_company_name,
                'ddc_gst_number' => $request->ddc_gst_number,
                'company_name' => $request->company_name,
                'phone' => $request->official_phone,
                'email' => $request->official_email,
                'adpl_pincode' => $request->adpl_pincode,
                'ddc_pincode' => $request->ddc_pincode,
                'master_depot_image' => $masterDepotImage,
                'contract_document' => $contractDocument,
                'trade_name' => $request->trade_name,
                'adpl_state' => $adplStateName,
                'adpl_state_id' => $adplStateId,
                'adpl_district' => $request->adpl_district,
                'adpl_district_id' => $adplDistrictId,
                'state' => $stateName,
                'state_id' => $stateId,
                'district' => $request->district,
                'district_id' => $districtId,
                'gst_certificate' => $gstCertificate,
                'aadhaar_card' => $aadhaarCard,
                'fssai_certificate' => $fssaiCertificate,
                'cancelled_cheque' => $cancelledCheque,
                'trade_license' => $tradeLicense,
                'ddc_agreement' => $ddcAgreement,
                'franchise_deposit_receipt' => $franchiseDepositReceipt,
                'qr_code' => $qrCode,
                // Bank details
                'account_holder_name' => $request->account_holder_name,
                'bank_account_number' => $request->bank_account_number,
                'ifsc_code' => $request->ifsc_code,
                'bank_name' => $request->bank_name,
                'branch_name' => $request->branch_name,
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('admin.external.ddc.index')->with('success', 'DDC User updated successfully.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update DDC User: ' . $e->getMessage()]);
        }
    }

    public function resetPassword(Request $request, int $id)
    {
        $request->validate([
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = DDC::findOrFail($id);

        $user->update([
            'password' => Hash::make($request->new_password),
            'force_change_password' => '1',
        ]);

        return back()->with('success', "Password reset for {$user->name}. They will be prompted to change it on next login.");
    }

    public function toggleStatus(int $id)
    {
        $user = DDC::findOrFail($id);
        $newStatus = $user->status === '1' ? '0' : '1';
        $user->update(['status' => $newStatus]);

        $status = $newStatus === '1' ? 'activated' : 'deactivated';
        return back()->with('success', "DDC user {$status} successfully.");
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,status_active,status_inactive',
            'ids' => 'required|array',
            'ids.*' => 'exists:ddcs,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            DDC::whereIn('id', $ids)->delete();
            return back()->with('success', 'Selected DDC users deleted successfully.');
        } elseif ($action === 'status_active') {
            DDC::whereIn('id', $ids)->update(['status' => '1']);
            return back()->with('success', 'Selected DDC users activated successfully.');
        } elseif ($action === 'status_inactive') {
            DDC::whereIn('id', $ids)->update(['status' => '0']);
            return back()->with('success', 'Selected DDC users deactivated successfully.');
        }

        return back()->with('error', 'Invalid action.');
    }

    public function show(int $id)
    {
        $user = DDC::with([
            'profile',
            'assignedPincodes.district',
            'assignedPincodes.state',
            'pincodeAuditLogs.modifier'
        ])->findOrFail($id);
        return view('admin.external.ddc.show', compact('user'));
    }

    public function updateKycStatus(Request $request, int $id)
    {
        $request->validate([
            'kyc_status' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\KycStatus::class)],
            'kyc_remarks' => ['nullable', 'string'],
        ]);

        $user = DDC::findOrFail($id);
        $profile = $user->profile ?: $user->profile()->create([]);

        $profile->update([
            'kyc_status' => $request->kyc_status,
            'kyc_remarks' => $request->kyc_status === 'REJECTED' ? $request->kyc_remarks : null,
        ]);

        return back()->with('success', 'DDC KYC status updated successfully to ' . $request->kyc_status);
    }

    public function walletRequests(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        // Validate sorting columns
        $validSort = ['created_at', 'amount', 'utr_number', 'status'];
        if (!in_array($sort, $validSort)) {
            $sort = 'created_at';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        $query = DdcWalletRequest::with(['ddc', 'ddc.profile']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('utr_number', 'like', "%{$search}%")
                  ->orWhereHas('ddc', function ($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();
        return view('admin.external.ddc.wallet_requests', compact('requests', 'status', 'search', 'limit', 'sort', 'order'));
    }

    public function approveWalletRequest(Request $request, int $id)
    {
        $walletRequest = DdcWalletRequest::findOrFail($id);

        if ($walletRequest->status !== 'PENDING') {
            return back()->with('error', 'This wallet request has already been processed.');
        }

        DB::transaction(function () use ($walletRequest) {
            // Update request status
            $walletRequest->update([
                'status' => 'APPROVED',
                'remarks' => 'Approved by admin'
            ]);

            // Load DDC coordinator user
            $ddc = $walletRequest->ddc;
            $profile = $ddc->profile ?: $ddc->profile()->create([]);

            // Increment profile balance
            $oldBalance = $profile->wallet_balance ?? 0;
            $newBalance = $oldBalance + $walletRequest->amount;
            $profile->update(['wallet_balance' => $newBalance]);

            // Create credit transaction in ddc_wallets
            DdcWallet::create([
                'ddc_id' => $ddc->id,
                'amount' => $walletRequest->amount,
                'type' => 'credit',
                'description' => 'Top-up Approved. UTR: ' . $walletRequest->utr_number,
            ]);
        });

        return back()->with('success', 'DDC Wallet request approved successfully. Amount has been credited.');
    }

    public function rejectWalletRequest(Request $request, int $id)
    {
        $request->validate([
            'remarks' => ['required', 'string', 'max:1000'],
        ]);

        $walletRequest = DdcWalletRequest::findOrFail($id);

        if ($walletRequest->status !== 'PENDING') {
            return back()->with('error', 'This wallet request has already been processed.');
        }

        $walletRequest->update([
            'status' => 'REJECTED',
            'remarks' => $request->remarks
        ]);

        return back()->with('success', 'DDC Wallet request has been rejected.');
    }

    public function getPincodes(Request $request, int $id)
    {
        $ddc = DDC::findOrFail($id);
        $currentPincodeIds = $ddc->assignedPincodes()->pluck('pincodes.id')->toArray();

        $districtName = $ddc->profile?->district ?? null;
        $stateName = $ddc->profile?->state ?? null;

        $query = Pincode::with(['state', 'district', 'ddcMapping.ddc.profile'])->active();

        if ($districtName) {
            $query->whereHas('district', function ($q) use ($districtName, $stateName) {
                $q->where('name', $districtName);
                if ($stateName) {
                    $q->whereHas('state', function ($sq) use ($stateName) {
                        $sq->where('name', $stateName);
                    });
                }
            });
        }

        // Get the active pincodes matching the district
        $pincodes = $query->get()->map(function ($p) use ($currentPincodeIds) {
            $assignedDdc = $p->ddcMapping->ddc ?? null;
            return [
                'id' => $p->id,
                'pincode' => $p->pincode,
                'state_name' => $p->state->name ?? '—',
                'district_name' => $p->district->name ?? '—',
                'assigned_ddc_id' => $assignedDdc ? $assignedDdc->id : null,
                'assigned_ddc_code' => $assignedDdc?->profile?->ddc_code ?? ($assignedDdc?->name ?? null),
                'assigned_ddc_name' => $assignedDdc?->name ?? null,
                'is_assigned_to_current' => in_array($p->id, $currentPincodeIds),
            ];
        });

        return response()->json([
            'status' => true,
            'ddc' => [
                'id' => $ddc->id,
                'name' => $ddc->name,
                'code' => $ddc->profile?->ddc_code ?? $ddc->name,
                'district' => $districtName,
                'state' => $stateName,
            ],
            'pincodes' => $pincodes,
        ]);
    }

    public function assignPincodes(Request $request, int $id)
    {
        $ddc = DDC::findOrFail($id);
        
        $request->validate([
            'pincode_ids' => ['nullable', 'array'],
            'pincode_ids.*' => ['exists:pincodes,id'],
        ]);

        $pincodeIds = $request->get('pincode_ids', []);

        // Check for duplicate assignments (conflicts with other DDCs)
        $conflicts = DdcPincode::whereIn('pincode_id', $pincodeIds)
            ->where('ddc_id', '!=', $ddc->id)
            ->with(['pincode', 'ddc.profile'])
            ->get();

        if ($conflicts->count() > 0) {
            $conflictDetails = $conflicts->map(function ($c) {
                $ddcCode = $c->ddc->profile?->ddc_code ?? $c->ddc->name;
                return "Pincode {$c->pincode->pincode} is already assigned to DDC {$ddcCode}";
            })->toArray();

            return response()->json([
                'status' => false,
                'message' => 'Duplicate Assignment Warning: ' . implode(', ', $conflictDetails),
            ], 422);
        }

        DB::transaction(function () use ($ddc, $pincodeIds) {
            // Get current mappings
            $currentMappings = $ddc->assignedPincodes()
                ->pluck('pincode', 'pincodes.id')
                ->toArray();

            $requestedPincodes = Pincode::whereIn('id', $pincodeIds)
                ->pluck('pincode', 'id')
                ->toArray();

            // Calculate additions and removals
            $currentKeys = array_keys($currentMappings);
            
            $addedIds = array_diff($pincodeIds, $currentKeys);
            $removedIds = array_diff($currentKeys, $pincodeIds);

            // Sync the pivot table
            $ddc->assignedPincodes()->sync($pincodeIds);

            // Log additions in audit trail
            foreach ($addedIds as $aid) {
                DdcPincodeAuditLog::create([
                    'ddc_id' => $ddc->id,
                    'pincode_id' => $aid,
                    'pincode' => $requestedPincodes[$aid] ?? '—',
                    'action' => 'ASSIGNED',
                    'modified_by' => auth('admin')->id(),
                ]);
            }

            // Log removals in audit trail
            foreach ($removedIds as $rid) {
                DdcPincodeAuditLog::create([
                    'ddc_id' => $ddc->id,
                    'pincode_id' => $rid,
                    'pincode' => $currentMappings[$rid] ?? '—',
                    'action' => 'REMOVED',
                    'modified_by' => auth('admin')->id(),
                ]);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Pincode mappings updated successfully.',
        ]);
    }
}
