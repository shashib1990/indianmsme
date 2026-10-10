<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\State;
use App\Models\District;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $vendor = Auth::guard('vendor')->user();
        $categories = Category::active()->get();
        $states = State::active()->orderBy('name')->get();

        // Resolve selected state ID and its active districts
        $selectedStateId = $vendor->sellerProfile?->state_id;
        $selectedDistrictId = $vendor->sellerProfile?->district_id;

        if (!$selectedStateId && $vendor->state) {
            $selectedState = State::where('name', $vendor->state)->first();
            if ($selectedState) {
                $selectedStateId = $selectedState->id;
            }
        }

        if ($selectedStateId) {
            $districts = District::where('state_id', $selectedStateId)->active()->orderBy('name')->get();
        } else {
            $districts = collect();
        }

        if (!$selectedDistrictId && $vendor->district && $selectedStateId) {
            $selectedDistrict = District::where('name', $vendor->district)->where('state_id', $selectedStateId)->first();
            if ($selectedDistrict) {
                $selectedDistrictId = $selectedDistrict->id;
            }
        }

        // Fetch Admin Official Bank Details for DDC Subscription
        $adminBankName      = SystemSetting::getVal('admin_bank_name', 'HDFC Bank');
        $adminAccountName   = SystemSetting::getVal('admin_account_name', 'ASC DISTRIBUTOR PVT LTD');
        $adminAccountNumber = SystemSetting::getVal('admin_account_number', '50200089652670');
        $adminIfscCode      = SystemSetting::getVal('admin_ifsc_code', 'HDFC0000886');
        $adminBankBranch    = SystemSetting::getVal('admin_bank_branch', 'Naraina Plaza, Sec A, Rohini, Delhi');
        $adminUpiId         = SystemSetting::getVal('admin_upi_id', 'ascdistributor@hdfcbank');

        $adminQrCodeRaw = SystemSetting::getVal('admin_qr_code');
        if ($adminQrCodeRaw) {
            if (filter_var($adminQrCodeRaw, FILTER_VALIDATE_URL)) {
                $adminQrCode = $adminQrCodeRaw;
            } elseif (str_starts_with($adminQrCodeRaw, 'storage/')) {
                $adminQrCode = asset($adminQrCodeRaw);
            } else {
                $adminQrCode = asset('storage/' . $adminQrCodeRaw);
            }
        } else {
            $adminQrCode = asset('admin-assets/images/qr-code.png');
        }

        return view('vendor.profile.index', compact(
            'vendor', 'categories', 'states', 'selectedStateId', 'selectedDistrictId', 'districts',
            'adminBankName', 'adminAccountName', 'adminAccountNumber', 'adminIfscCode', 'adminBankBranch', 'adminUpiId', 'adminQrCode'
        ));
    }

    public function update(Request $request)
    {
        $vendor = Auth::guard('vendor')->user();
        $sellerProfile = $vendor->sellerProfile ?: $vendor->sellerProfile()->create([]);
        $activeTab = $request->input('active_tab', 'basic');

        $rules = [];
        $userData = [];
        $profileData = [];

        // profile_image is visible and uploadable from all tabs on the left panel
        $rules['profile_image'] = ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'];

        if ($activeTab === 'basic') {
            $rules = array_merge($rules, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email'],
                'alternate_email' => ['nullable', 'email', 'max:255'],
                'mobile' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
                'alternate_mobile' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
                'gst_number' => ['nullable', 'string', 'min:15', 'max:15'],
                'seller_type' => ['required', 'in:Trader,Manufacturer,Distributor'],
                'brand_name' => ['nullable'],
                'authorized_rep_name' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string'],
                'state_id' => ['required', 'exists:states,id'],
                'district_id' => ['required', 'exists:districts,id'],
                'pincode' => ['required', 'string', 'max:20'],
                'category_id' => ['required', 'exists:categories,id'],
            ]);
        } elseif ($activeTab === 'kyc') {
            $rules = array_merge($rules, [
                'gst_certificate' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                'pan_card' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                'udyam_certificate' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                'incorporation_certificate' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                'cancelled_cheque' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                'company_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                'company_banner' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            ]);
        } elseif ($activeTab === 'bank') {
            $rules = array_merge($rules, [
                'account_holder_name' => ['required', 'string', 'max:255'],
                'bank_account_number' => ['required', 'string', 'max:255'],
                'ifsc_code' => ['required', 'string', 'max:20'],
                'bank_name' => ['required', 'string', 'max:255'],
                'branch_name' => ['required', 'string', 'max:255'],
            ]);
        }

        $messages = [
            'gst_number.min' => 'GST number must be exactly 15 characters long.',
            'gst_number.max' => 'GST number must be exactly 15 characters long.',
        ];

        $request->validate($rules, $messages);

        if ($activeTab === 'basic') {
            $userData['name'] = $request->input('name');
            $userData['email'] = $request->input('email');
            $userData['mobile'] = $request->input('mobile');

            $profileData['authorized_rep_name'] = $request->input('authorized_rep_name');
            $profileData['alternate_mobile'] = $request->input('alternate_mobile');
            $profileData['alternate_email'] = $request->input('alternate_email');
            $profileData['gst_number'] = $request->input('gst_number') ? strtoupper(trim($request->input('gst_number'))) : null;
            $profileData['seller_type'] = $request->input('seller_type');
            $profileData['address'] = $request->input('address');
            $profileData['state_id'] = $request->input('state_id');
            $profileData['state'] = null;
            $profileData['district_id'] = $request->input('district_id');
            $profileData['district'] = null;
            $profileData['pincode'] = $request->input('pincode');
            $profileData['category_id'] = $request->input('category_id');

            $brandNameInput = $request->input('brand_name');
            if (is_array($brandNameInput)) {
                $profileData['brand_name'] = implode(', ', array_filter(array_map('trim', $brandNameInput)));
            } else {
                $profileData['brand_name'] = trim((string) $brandNameInput);
            }
        } elseif ($activeTab === 'bank') {
            $profileData['account_holder_name'] = $request->input('account_holder_name');
            $profileData['bank_account_number'] = $request->input('bank_account_number');
            $profileData['ifsc_code'] = $request->input('ifsc_code');
            $profileData['bank_name'] = $request->input('bank_name');
            $profileData['branch_name'] = $request->input('branch_name');
        }

        // Upload profile image
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $path = $this->uploadFileToService($file, 'profile');

            $oldPath = $sellerProfile->profile_image;
            if ($oldPath && !filter_var($oldPath, FILTER_VALIDATE_URL) && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }

            $profileData['profile_image'] = $path;
        }

        if ($activeTab === 'kyc') {
            $kycFiles = [
                'gst_certificate',
                'pan_card',
                'udyam_certificate',
                'incorporation_certificate',
                'cancelled_cheque',
                'company_logo',
                'company_banner'
            ];
            $anyUploaded = false;
            foreach ($kycFiles as $kf) {
                if ($request->hasFile($kf)) {
                    $file = $request->file($kf);
                    $path = $this->uploadFileToService($file, 'kyc');

                    $oldPath = $sellerProfile->{$kf};
                    if ($oldPath && !filter_var($oldPath, FILTER_VALIDATE_URL) && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                    }

                    $profileData[$kf] = $path;
                    $anyUploaded = true;
                }
            }
            if ($anyUploaded || $sellerProfile->kyc_status === 'REJECTED') {
                $profileData['kyc_status'] = 'PENDING';
            }
        }

        if (!empty($userData)) {
            $vendor->update($userData);
        }

        if (!empty($profileData)) {
            $sellerProfile->update($profileData);
        }

        // Refresh model relations
        $vendor->unsetRelation('sellerProfile');

        // Notify admins about vendor profile update
        $this->notifyAdmins(
            "Vendor Profile Updated",
            "Vendor {$vendor->name} (" . ($sellerProfile->seller_id ?? 'N/A') . ") updated their profile details (Tab: " . ucfirst($activeTab) . ").",
            route('admin.external.vendors.profile', $vendor->id)
        );

        return back()->with('success', 'Profile updated successfully.')->with('active_tab', $activeTab);
    }

    public function showChangePassword()
    {
        return view('vendor.profile.change-password');
    }

    public function changePassword(Request $request)
    {
        $vendor = Auth::guard('vendor')->user();

        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($request->current_password, $vendor->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $vendor->update([
            'password' => Hash::make($request->password),
            'force_change_password' => '0',
        ]);

        // Notify admins about vendor password change
        $this->notifyAdmins(
            "Vendor Password Changed",
            "Vendor {$vendor->name} changed their account password.",
            route('admin.external.vendors.profile', $vendor->id)
        );

        return back()->with('success', 'Password changed successfully.');
    }

    public function updateDdcSubscription(Request $request)
    {
        $vendor = Auth::guard('vendor')->user();
        
        // Check eligibility for DDC Model subscription
        if (!$vendor->isEligibleForDdcSubscription()) {
            return back()->with('error', 'You are not eligible to subscribe to DDC Model.');
        }

        $request->validate([
            'ddc_subscription_utr' => 'required|string|max:100',
            'ddc_subscription_slip' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $profile = $vendor->sellerProfile;
        if (!$profile) {
            $profile = \App\Models\SellerProfile::create([
                'user_id' => $vendor->id,
                'seller_id' => \App\Models\SellerProfile::generateSellerId(),
            ]);
        }

        // Upload payment slip using the controller helper
        $fileUrl = $this->uploadFileToService($request->file('ddc_subscription_slip'), 'ddc_subscriptions');

        $profile->update([
            'ddc_subscription_status' => 'pending',
            'ddc_subscription_utr' => $request->input('ddc_subscription_utr'),
            'ddc_subscription_slip' => $fileUrl,
            'ddc_subscription_submitted_at' => now(),
            'ddc_subscription_reject_reason' => null, // Clear any previous rejection reason
        ]);

        // Notify admins about vendor DDC subscription request
        if (method_exists($this, 'notifyAdmins')) {
            $this->notifyAdmins(
                "New DDC Subscription Request",
                "Vendor {$vendor->name} (" . ($vendor->seller_id ?? 'N/A') . ") has requested DDC Model subscription. UTR: " . $request->input('ddc_subscription_utr') . ".",
                route('admin.external.vendors.profile', $vendor->id)
            );
        }

        return back()->with('success', 'Your DDC Subscription request has been submitted successfully and is pending admin approval.');
    }

    public function showSmartSalesIncentive()
    {
        $vendor = Auth::guard('vendor')->user();
        $profile = $vendor->sellerProfile;

        if (!$profile || !$profile->is_ddc_subscribed) {
            return redirect()->route('vendor.dashboard')->with('error', 'You must subscribe to the DDC Model to access Smart Sales Incentive.');
        }

        $slabs = \App\Models\SellerSsiSlab::where('seller_id', $vendor->id)
            ->orderBy('min_amount', 'asc')
            ->get();

        return view('vendor.profile.smart_sales_incentive', compact('vendor', 'profile', 'slabs'));
    }

    public function updateSmartSalesIncentive(Request $request)
    {
        $vendor = Auth::guard('vendor')->user();
        $profile = $vendor->sellerProfile;

        if (!$profile || !$profile->is_ddc_subscribed) {
            return redirect()->route('vendor.dashboard')->with('error', 'You must subscribe to the DDC Model to access Smart Sales Incentive.');
        }

        $request->validate([
            'slabs' => 'nullable|array',
            'slabs.*.min_amount' => 'required_with:slabs|numeric|min:0',
            'slabs.*.max_amount' => 'nullable|numeric|gte:slabs.*.min_amount',
            'slabs.*.percentage' => 'required_with:slabs|numeric|min:0|max:100',
        ], [
            'slabs.*.min_amount.required_with' => 'Minimum Shopping Amount is required.',
            'slabs.*.max_amount.gte' => 'Maximum Shopping Amount must be greater than or equal to Minimum Shopping Amount.',
            'slabs.*.percentage.required_with' => 'Incentive Percentage is required.',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($vendor, $request, $profile) {
            // Delete old slabs
            \App\Models\SellerSsiSlab::where('seller_id', $vendor->id)->delete();

            $firstPercentage = 0;
            $rawSlabs = $request->input('slabs', []);
            
            if (is_array($rawSlabs) && count($rawSlabs) > 0) {
                foreach ($rawSlabs as $idx => $sData) {
                    $min = (float) ($sData['min_amount'] ?? 0);
                    $max = isset($sData['max_amount']) && $sData['max_amount'] !== '' && $sData['max_amount'] !== null 
                        ? (float) $sData['max_amount'] 
                        : null;
                    $pct = (float) ($sData['percentage'] ?? 0);

                    if ($idx === 0) {
                        $firstPercentage = $pct;
                    }

                    \App\Models\SellerSsiSlab::create([
                        'seller_id' => $vendor->id,
                        'min_amount' => $min,
                        'max_amount' => $max,
                        'percentage' => $pct,
                    ]);
                }
            }

            // Sync fallback percentage on sellerProfile
            $profile->update([
                'ddc_ama_commission_percentage' => $firstPercentage,
            ]);
        });

        return back()->with('success', 'Slab-wise Smart Sales Incentive (SSI) updated successfully.');
    }

    public function destroySlab($id)
    {
        $vendor = Auth::guard('vendor')->user();
        
        $slab = \App\Models\SellerSsiSlab::where('seller_id', $vendor->id)
            ->where('id', $id)
            ->first();

        if ($slab) {
            $slab->delete();

            // Re-evaluate first percentage for seller profile fallback
            $firstSlab = \App\Models\SellerSsiSlab::where('seller_id', $vendor->id)
                ->orderBy('min_amount', 'asc')
                ->first();

            if ($vendor->sellerProfile) {
                $vendor->sellerProfile->update([
                    'ddc_ama_commission_percentage' => $firstSlab ? $firstSlab->percentage : 0,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Slab deleted successfully.']);
    }

    public function showSmartSalesIncentiveDeductions(Request $request)
    {
        $vendor = Auth::guard('vendor')->user();
        $profile = $vendor->sellerProfile;

        if (!$profile || !$profile->is_ddc_subscribed) {
            return redirect()->route('vendor.dashboard')->with('error', 'You must subscribe to the DDC Model to access Smart Sales Incentive deductions.');
        }

        $search = $request->get('search');
        $limit = (int) $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $query = \App\Models\PurchaseOrder::with(['ddc', 'ddc.profile', 'items', 'items.product', 'incentiveHolds'])
            ->where('seller_id', $vendor->id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('ddc', function ($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%")
                        ->orWhereHas('profile', function ($dpq) use ($search) {
                            $dpq->where('company_name', 'like', "%{$search}%");
                        });
                  })
                  ->orWhereHas('items.product', function ($pq) use ($search) {
                      $pq->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%");
                  });
            });
        }

        $allPos = $query->orderBy($sort, $order)->get();

        // Filter exclusively to Purchase Orders where SSI deduction > 0
        $deductionsPos = $allPos->filter(function ($po) {
            return $po->ssi_amount > 0;
        })->values();

        $totalDeducted = (float) $deductionsPos->sum(function ($po) {
            return $po->ssi_amount;
        });

        $totalOrders = $deductionsPos->count();

        // Manual pagination
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $paginatedPos = new \Illuminate\Pagination\LengthAwarePaginator(
            $deductionsPos->forPage($page, $limit),
            $deductionsPos->count(),
            $limit,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('vendor.profile.smart_sales_incentive_deductions', compact(
            'vendor', 'profile', 'paginatedPos', 'totalDeducted', 'totalOrders', 'search', 'limit', 'sort', 'order'
        ));
    }
}
