<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::guard('adc')->user();
        $profile = $user->adcProfile;
        $states = State::active()->orderBy('name')->get();

        $currentStateId = null;
        if ($profile && $profile->state_id) {
            $currentStateId = $profile->state_id;
        } elseif ($profile && $profile->state) {
            $st = State::where('name', $profile->state)->first();
            if (!$st) {
                $st = State::where('name', 'like', '%' . trim($profile->state) . '%')->first();
            }
            if ($st) {
                $currentStateId = $st->id;
            }
        }

        $pincode = $profile?->pincode ?? $user->pincode;
        $matchedDdc = null;
        $matchedDdcProfile = null;

        if (!empty($pincode)) {
            $pincodeObj = \App\Models\Pincode::where('pincode', $pincode)->first();
            if ($pincodeObj) {
                $ddcId = DB::table('ddc_pincodes')->where('pincode_id', $pincodeObj->id)->value('ddc_id');
                if ($ddcId) {
                    $matchedDdc = \App\Models\User::where('id', $ddcId)
                        ->where('role', 'ddc')
                        ->where('status', '1')
                        ->first();
                    if ($matchedDdc) {
                        $matchedDdcProfile = $matchedDdc->ddcProfile;
                    }
                }
            }
        }

        return view('adc.profile.index', compact('user', 'profile', 'states', 'currentStateId', 'matchedDdc', 'matchedDdcProfile'));
    }

    public function update(Request $request)
    {
        $user = Auth::guard('adc')->user();
        $profile = $user->adcProfile ?: $user->adcProfile()->create([]);

        $activeTab = $request->input('active_tab', 'basic');

        $rules = [];
        $rules['profile_image'] = ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'];

        if ($activeTab === 'basic') {
            $rules = array_merge($rules, [
                'name'         => ['required', 'string', 'max:255'],
                'email'        => ['required', 'email', 'max:255'],
                'phone'        => ['required', 'digits:10'],
                'company_name' => ['required', 'string', 'max:255'],
                'trade_name'   => ['nullable', 'string', 'max:255'],
                'gst_number'   => ['nullable', 'string', 'max:50'],
                'pan_no'       => ['nullable', 'string', 'max:20'],
                'aadhaar_no'   => ['nullable', 'string', 'max:20'],
                'pincode'      => ['required', 'digits:6'],
                'address'      => ['required', 'string'],
                'state'        => ['required'],
                'district'     => ['required', 'string'],
            ]);
        } elseif ($activeTab === 'kyc') {
            $rules = array_merge($rules, [
                'aadhaar_card'     => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png,webp', 'max:5120'],
                'pan_card'        => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png,webp', 'max:5120'],
                'gst_certificate'  => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png,webp', 'max:5120'],
                'cancelled_cheque' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png,webp', 'max:5120'],
            ]);
        } elseif ($activeTab === 'bank') {
            $rules = array_merge($rules, [
                'account_holder_name' => ['required', 'string', 'max:255'],
                'bank_account_number' => ['required', 'regex:/^[0-9]+$/', 'max:30'],
                'ifsc_code'           => ['required', 'string', 'max:20'],
                'bank_name'           => ['required', 'string', 'max:255'],
                'branch_name'         => ['nullable', 'string', 'max:255'],
                'qr_code'             => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            ]);
        }

        $request->validate($rules);

        // Track if any KYC document was uploaded
        $kycFileFields = [
            'aadhaar_card', 'pan_card', 'gst_certificate', 'cancelled_cheque'
        ];
        $anyKycUploaded = false;
        if ($activeTab === 'kyc') {
            $anyKycUploaded = collect($kycFileFields)->some(fn($f) => $request->hasFile($f));
        }

        $uploads = [];
        $profileImage = $profile->profile_image;
        if ($request->hasFile('profile_image')) {
            $profileImage = $this->uploadFileToService($request->file('profile_image'), 'adc');
        }

        if ($activeTab === 'kyc') {
            foreach ($kycFileFields as $field) {
                if ($request->hasFile($field)) {
                    $uploads[$field] = $this->uploadFileToService($request->file($field), 'adc');
                }
            }
        }

        DB::beginTransaction();
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

                // Resolve State Name & ID
                $stateName = '';
                $stateId = null;
                if ($request->state) {
                    $stateObj = State::find($request->state);
                    if ($stateObj) {
                        $stateName = $stateObj->name;
                        $stateId = $stateObj->id;
                    } else {
                        $stateName = $request->state;
                    }
                }

                $districtId = null;
                if ($request->district && $stateId) {
                    $distObj = District::where('name', $request->district)->where('state_id', $stateId)->first();
                    if ($distObj) {
                        $districtId = $distObj->id;
                    }
                }

                $profileData = array_merge($profileData, [
                    'company_name'   => $request->company_name,
                    'trade_name'     => $request->trade_name,
                    'official_phone' => $request->phone,
                    'official_email' => $request->email,
                    'gst_number'     => $request->gst_number,
                    'pan_no'         => $request->pan_no,
                    'aadhaar_no'     => $request->aadhaar_no,
                    'pincode'        => $request->pincode,
                    'address'        => $request->address,
                    'state'          => $stateName,
                    'state_id'       => $stateId,
                    'district'       => $request->district,
                    'district_id'    => $districtId,
                ]);
            } elseif ($activeTab === 'kyc') {
                $profileData = array_merge($profileData, $uploads);

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
                    $bankData['qr_code'] = $this->uploadFileToService($request->file('qr_code'), 'adc');
                } elseif ($request->boolean('remove_qr_code')) {
                    $bankData['qr_code'] = null;
                }

                $profileData = array_merge($profileData, $bankData);
            }

            // Automatically set KYC status to UNDER_REVIEW on any profile / KYC / bank update
            $profileData['kyc_status'] = 'UNDER_REVIEW';
            $profileData['kyc_remarks'] = null;

            if (!empty($profileData)) {
                $profile->update($profileData);
            }

            DB::commit();

            // Notify Admin about the ADC KYC & Profile update
            try {
                \App\Services\NotificationService::notifyAdmin(
                    'ADC Profile & KYC Update Submitted',
                    "ADC {$user->name} ({$profile->adc_code}) updated their {$activeTab} details. Admin verification required.",
                    route('admin.external.adc.index')
                );
            } catch (\Exception $e) {
                // Ignore notification exceptions
            }

            $msg = 'Profile & KYC details updated and submitted for Admin verification (KYC Status: Under Review).';
            return back()->with('success', $msg)->with('active_tab', $activeTab);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update profile: ' . $e->getMessage()])->with('active_tab', $activeTab);
        }
    }

    public function showChangePassword()
    {
        return view('adc.profile.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Please enter your current password.',
            'password.required'         => 'Please enter a new password.',
            'password.min'              => 'The new password must be at least 8 characters long.',
            'password.confirmed'        => 'The new password and confirmation password do not match.',
        ]);

        $user = Auth::guard('adc')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password you entered is incorrect.'])->withInput();
        }

        $user->password = Hash::make($request->password);
        $user->force_change_password = '0';
        $user->save();

        DB::table('adc_profiles')->where('user_id', $user->id)->update(['force_change_password' => '0']);
        if ($user->adcProfile) {
            $user->adcProfile->force_change_password = '0';
        }

        return redirect()->route('adc.dashboard')->with('success', 'Password updated successfully! You can now access your dashboard.');
    }
}
