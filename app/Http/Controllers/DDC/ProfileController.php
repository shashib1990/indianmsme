<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::guard('ddc')->user();
        $states = \App\Models\State::active()->orderBy('name')->get();

        // Fetch all pincodes assigned to this DDC
        $pincodeIds = \Illuminate\Support\Facades\DB::table('ddc_pincodes')->where('ddc_id', $user->id)->pluck('pincode_id')->toArray();
        $assignedPincodes = \Illuminate\Support\Facades\DB::table('pincodes')->whereIn('id', $pincodeIds)->pluck('pincode')->toArray();

        $profile = $user->ddcProfile ?? $user->profile;
        if ($profile) {
            if (!empty($profile->pincode)) $assignedPincodes[] = $profile->pincode;
            if (!empty($profile->ddc_pincode)) $assignedPincodes[] = $profile->ddc_pincode;
            if (!empty($profile->adpl_pincode)) $assignedPincodes[] = $profile->adpl_pincode;
        }

        $assignedPincodes = array_values(array_unique(array_filter($assignedPincodes)));
        sort($assignedPincodes);

        return view('ddc.profile.index', compact('user', 'states', 'assignedPincodes'));
    }

    public function update(Request $request)
    {
        $user = Auth::guard('ddc')->user();
        $profile = $user->profile ?: $user->profile()->create([]);

        $activeTab = $request->input('active_tab', 'basic');

        $rules = [];
        $rules['profile_image'] = ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:5120'];

        if ($activeTab === 'basic') {
            $rules = array_merge($rules, [
                'name'             => ['required', 'string', 'max:255'],
                'email'            => ['required', 'email', 'max:255'],
                'phone'            => ['required', 'digits:10'],
                'company_name'     => ['required', 'string', 'max:255'],
                'trade_name'       => ['required', 'string', 'max:255'],
                'ddc_gst_number'   => ['required', 'string', 'max:50'],
                'pincode'          => ['required', 'digits:6'],
                'address'          => ['required', 'string'],
                'state'            => ['required'],
                'district'         => ['required', 'string'],
            ]);
        } elseif ($activeTab === 'kyc') {
            $rules = array_merge($rules, [
                'master_depot_image'       => ['nullable', 'image', 'mimes:jpeg,jpg', 'max:5120'],
                'contract_document'        => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
                'gst_certificate'          => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
                'aadhaar_card'             => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
                'fssai_certificate'        => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
                'cancelled_cheque'         => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
                'trade_license'            => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
                'ddc_agreement'            => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
                'franchise_deposit_receipt'=> ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            ]);
        } elseif ($activeTab === 'bank') {
            $rules = array_merge($rules, [
                'account_holder_name'  => ['required', 'string', 'max:255'],
                'bank_account_number'  => ['required', 'string', 'max:50'],
                'ifsc_code'            => ['required', 'string', 'max:20'],
                'bank_name'            => ['required', 'string', 'max:255'],
                'branch_name'          => ['required', 'string', 'max:255'],
                'qr_code'              => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            ]);
        }

        $request->validate($rules);

        // Track if any KYC document was uploaded — if yes, set to UNDER_REVIEW
        $kycFileFields = ['master_depot_image','contract_document','gst_certificate','aadhaar_card',
                          'fssai_certificate','cancelled_cheque','trade_license','ddc_agreement','franchise_deposit_receipt'];
        $anyKycUploaded = false;
        if ($activeTab === 'kyc') {
            $anyKycUploaded = collect($kycFileFields)->some(fn($f) => $request->hasFile($f));
        }

        // Upload files (preserve existing if not re-uploaded)
        $uploads = [];
        $profileImage = $profile->profile_image;
        if ($request->hasFile('profile_image')) {
            $profileImage = $this->uploadFileToService($request->file('profile_image'), 'ddc');
        }

        if ($activeTab === 'kyc') {
            foreach ($kycFileFields as $field) {
                if ($request->hasFile($field)) {
                    $uploads[$field] = $this->uploadFileToService($request->file($field), 'ddc');
                }
            }
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $profileData = [];

            if ($request->hasFile('profile_image')) {
                $profileData['profile_image'] = $profileImage;
            }

            if ($activeTab === 'basic') {
                $user->name   = $request->name;
                $user->email  = $request->email;
                $user->mobile = $request->phone;
                $user->save();

                // Resolve State Name from ID
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

                $profileData = array_merge($profileData, [
                    'ddc_owner_name' => $request->name,
                    'official_phone' => $request->phone,
                    'official_email' => $request->email,
                    'phone'          => $request->phone,
                    'email'          => $request->email,
                    'company_name'   => $request->company_name,
                    'trade_name'     => $request->trade_name,
                    'ddc_gst_number' => $request->ddc_gst_number,
                    'pincode'        => $request->pincode,
                    'ddc_pincode'    => $request->pincode,
                    'address'        => $request->address,
                    'ddc_address'    => $request->address,
                    'state'          => $stateName,
                    'state_id'       => $stateId,
                    'district'       => $request->district,
                    'district_id'    => $districtId,
                ]);
            } elseif ($activeTab === 'kyc') {
                // Merge file uploads
                $profileData = array_merge($profileData, $uploads);

                // If any KYC doc uploaded, set to UNDER_REVIEW for admin verification
                if ($anyKycUploaded) {
                    $profileData['kyc_status'] = 'UNDER_REVIEW';
                    $profileData['kyc_remarks'] = null;
                }
            } elseif ($activeTab === 'bank') {
                $bankData = [
                    'account_holder_name' => $request->account_holder_name,
                    'bank_account_number' => $request->bank_account_number,
                    'ifsc_code'           => $request->ifsc_code,
                    'bank_name'           => $request->bank_name,
                    'branch_name'         => $request->branch_name,
                ];

                if ($request->hasFile('qr_code')) {
                    $bankData['qr_code'] = $this->uploadFileToService($request->file('qr_code'), 'ddc');
                } elseif ($request->boolean('remove_qr_code')) {
                    $bankData['qr_code'] = null;
                }

                $profileData = array_merge($profileData, $bankData);
            }

            if (!empty($profileData)) {
                $profile->update($profileData);
            }

            \Illuminate\Support\Facades\DB::commit();

            $msg = 'Profile updated successfully.';
            if ($activeTab === 'kyc' && $anyKycUploaded) {
                $msg = 'Profile updated. KYC documents sent to admin for verification.';
            }
            return back()->with('success', $msg)->with('active_tab', $activeTab);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update profile: ' . $e->getMessage()])->with('active_tab', $activeTab);
        }
    }

    public function showChangePassword()
    {
        return view('ddc.profile.change-password');
    }

    public function changePassword(Request $request)
    {
        $user = Auth::guard('ddc')->user();

        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($request->password);
        $user->force_change_password = '0';
        $user->save();

        if ($user->ddcProfile) {
            $user->ddcProfile->update(['force_change_password' => '0']);
        }

        return back()->with('success', 'Password changed successfully.');
    }
}
