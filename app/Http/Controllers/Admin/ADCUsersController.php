<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ADC;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ADCUsersController extends Controller
{
    public function index(Request $request)
    {
        $filterStatus = $request->get('filter_status');
        $search = $request->get('search');
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $limit = $request->get('limit', 15);

        // Pre-calculate metric card counts
        $totalAdcCount = ADC::count();
        $activeAdcCount = ADC::where('users.status', '1')->count();
        $inactiveAdcCount = max(0, $totalAdcCount - $activeAdcCount);

        $query = ADC::with(['adcProfile', 'profile']);

        if ($filterStatus === 'active') {
            $query->where('users.status', '1');
        } elseif ($filterStatus === 'inactive') {
            $query->where(function ($q) {
                $q->where('users.status', '!=', '1')
                  ->orWhereNull('users.status');
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhereHas('adcProfile', function ($profileQuery) use ($search) {
                        $profileQuery->where('district', 'like', "%{$search}%")
                            ->orWhere('state', 'like', "%{$search}%")
                            ->orWhere('designation', 'like', "%{$search}%")
                            ->orWhere('adc_code', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%")
                            ->orWhere('trade_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('profile', function ($profileQuery) use ($search) {
                        $profileQuery->where('district', 'like', "%{$search}%")
                            ->orWhere('state', 'like', "%{$search}%")
                            ->orWhere('designation', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%")
                            ->orWhere('trade_name', 'like', "%{$search}%");
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
                fputcsv($file, ['ID', 'ADC Code', 'Profile Code', 'Name', 'Canteen Name', 'Email', 'Phone', 'District', 'State', 'Status', 'Created At']);
                foreach ($records as $row) {
                    $adcCode = $row->adcProfile?->adc_code ?? $row->adc_code ?? ('ASC' . str_pad($row->id, 3, '0', STR_PAD_LEFT));
                    $profileCode = $row->adcProfile?->code ?? '—';
                    $canteenName = $row->adcProfile?->company_name ?? ($row->adcProfile?->trade_name ?? ($row->profile?->company_name ?? '—'));
                    fputcsv($file, [
                        $row->id,
                        $adcCode,
                        $profileCode,
                        $row->name,
                        $canteenName,
                        $row->email,
                        $row->mobile ?? $row->phone ?? '—',
                        $row->district ?? ($row->profile?->district ?? '—'),
                        $row->state ?? ($row->profile?->state ?? '—'),
                        $row->status === '1' ? 'Active' : 'Inactive',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'adc_users_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $users = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.external.adc.index', compact(
            'users', 
            'search', 
            'sort', 
            'order', 
            'limit', 
            'totalAdcCount', 
            'activeAdcCount', 
            'inactiveAdcCount', 
            'filterStatus'
        ));
    }

    public function getDetails($id)
    {
        $adc = ADC::with(['adcProfile'])->find($id);

        if (!$adc) {
            return response()->json(['status' => false, 'message' => 'ADC User not found'], 404);
        }

        $profile = $adc->adcProfile;
        $pincode = $profile?->pincode;

        // Fetch eligible DDCs based on pincode mapping (Active DDCs only)
        $eligibleDdcs = [];
        if ($pincode) {
            $pincodeObj = \App\Models\Pincode::where('pincode', $pincode)->first();
            if ($pincodeObj) {
                $ddcIds = \Illuminate\Support\Facades\DB::table('ddc_pincodes')
                    ->where('pincode_id', $pincodeObj->id)
                    ->pluck('ddc_id');

                if ($ddcIds->isNotEmpty()) {
                    $eligibleDdcs = \App\Models\User::whereIn('id', $ddcIds)
                        ->where('role', 'ddc')
                        ->where('status', '1')
                        ->with('ddcProfile')
                        ->get()
                        ->map(function ($u) {
                            return [
                                'id' => $u->id,
                                'name' => $u->name,
                                'code' => $u->ddcProfile?->ddc_code ?? 'DDC',
                                'company_name' => $u->ddcProfile?->company_name ?? $u->name,
                                'district' => $u->ddcProfile?->district ?? '',
                            ];
                        });
                }
            }

            if (empty($eligibleDdcs) || (is_object($eligibleDdcs) && $eligibleDdcs->isEmpty())) {
                $eligibleDdcs = \App\Models\User::where('role', 'ddc')
                    ->where('status', '1')
                    ->whereHas('ddcProfile', function ($q) use ($pincode) {
                        $q->where('pincode', $pincode)
                          ->orWhere('ddc_pincode', $pincode)
                          ->orWhere('adpl_pincode', $pincode);
                    })
                    ->with('ddcProfile')
                    ->get()
                    ->map(function ($u) {
                        return [
                            'id' => $u->id,
                            'name' => $u->name,
                            'code' => $u->ddcProfile?->ddc_code ?? 'DDC',
                            'company_name' => $u->ddcProfile?->company_name ?? $u->name,
                            'district' => $u->ddcProfile?->district ?? '',
                        ];
                    });
            }
        }

        $allDdcs = \App\Models\User::where('role', 'ddc')
            ->where('status', '1')
            ->with('ddcProfile')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'code' => $u->ddcProfile?->ddc_code ?? 'DDC',
                    'company_name' => $u->ddcProfile?->company_name ?? $u->name,
                    'district' => $u->ddcProfile?->district ?? '',
                ];
            });

        $virtualDdc = \App\Models\AdcWalletRequest::getVirtualDdc();

        return response()->json([
            'status' => true,
            'adc' => [
                'id' => $adc->id,
                'name' => $adc->name,
                'email' => $adc->email,
                'mobile' => $adc->mobile ?? $adc->phone ?? '',
                'created_at' => $adc->created_at ? $adc->created_at->format('Y-m-d') : null,
                'canteen_code' => $profile?->code ?? ('ASC' . str_pad($adc->id, 3, '0', STR_PAD_LEFT)),
                'type' => $profile?->type ?? 'Canteen',
                'canteen_name' => $profile?->company_name ?? $adc->name,
                'address' => $profile?->address ?? '',
                'state' => $profile?->state ?? '',
                'state_id' => $profile?->state_id ?? null,
                'district' => $profile?->district ?? '',
                'district_id' => $profile?->district_id ?? null,
                'pincode' => $profile?->pincode ?? '',
                'gst_number' => $profile?->gst_number ?? '',
                'aadhaar_no' => $profile?->aadhaar_no ?? '',
                'pan_no' => $profile?->pan_no ?? '',
                'trade_name' => $profile?->trade_name ?? '',
                'official_phone' => $profile?->official_phone ?? '',
                'official_email' => $profile?->official_email ?? '',
                'kyc_status' => $profile?->kyc_status ?? 'PENDING',
                'kyc_remarks' => $profile?->kyc_remarks ?? '',
                'master_depot_image' => $profile?->master_depot_image ? (filter_var($profile->master_depot_image, FILTER_VALIDATE_URL) ? $profile->master_depot_image : asset('storage/' . $profile->master_depot_image)) : null,
                'contract_document' => $profile?->contract_document ? (filter_var($profile->contract_document, FILTER_VALIDATE_URL) ? $profile->contract_document : asset('storage/' . $profile->contract_document)) : null,
                'gst_certificate' => $profile?->gst_certificate ? (filter_var($profile->gst_certificate, FILTER_VALIDATE_URL) ? $profile->gst_certificate : asset('storage/' . $profile->gst_certificate)) : null,
                'aadhaar_card' => $profile?->aadhaar_card ? (filter_var($profile->aadhaar_card, FILTER_VALIDATE_URL) ? $profile->aadhaar_card : asset('storage/' . $profile->aadhaar_card)) : null,
                'pan_card' => $profile?->pan_card ? (filter_var($profile->pan_card, FILTER_VALIDATE_URL) ? $profile->pan_card : asset('storage/' . $profile->pan_card)) : null,
                'fssai_certificate' => $profile?->fssai_certificate ? (filter_var($profile->fssai_certificate, FILTER_VALIDATE_URL) ? $profile->fssai_certificate : asset('storage/' . $profile->fssai_certificate)) : null,
                'cancelled_cheque' => $profile?->cancelled_cheque ? (filter_var($profile->cancelled_cheque, FILTER_VALIDATE_URL) ? $profile->cancelled_cheque : asset('storage/' . $profile->cancelled_cheque)) : null,
                'trade_license' => $profile?->trade_license ? (filter_var($profile->trade_license, FILTER_VALIDATE_URL) ? $profile->trade_license : asset('storage/' . $profile->trade_license)) : null,
                'adc_agreement' => $profile?->adc_agreement ? (filter_var($profile->adc_agreement, FILTER_VALIDATE_URL) ? $profile->adc_agreement : asset('storage/' . $profile->adc_agreement)) : null,
                'franchise_deposit_receipt' => $profile?->franchise_deposit_receipt ? (filter_var($profile->franchise_deposit_receipt, FILTER_VALIDATE_URL) ? $profile->franchise_deposit_receipt : asset('storage/' . $profile->franchise_deposit_receipt)) : null,
                'account_holder_name' => $profile?->account_holder_name ?? '',
                'bank_account_number' => $profile?->bank_account_number ?? '',
                'ifsc_code' => $profile?->ifsc_code ?? '',
                'bank_name' => $profile?->bank_name ?? '',
                'branch_name' => $profile?->branch_name ?? '',
                'qr_code' => $profile?->qr_code ? (filter_var($profile->qr_code, FILTER_VALIDATE_URL) ? $profile->qr_code : asset('storage/' . $profile->qr_code)) : null,
                'assigned_ddc_id' => $profile?->assigned_ddc_id ?? null,
                'code' => $profile?->code ?? ('ASC' . str_pad($adc->id, 3, '0', STR_PAD_LEFT)),
            ],
            'eligible_ddcs' => $eligibleDdcs,
            'all_ddcs' => $allDdcs,
            'virtual_ddc' => $virtualDdc ? [
                'id' => $virtualDdc->id,
                'name' => $virtualDdc->name . ' (Virtual DDC)',
                'code' => $virtualDdc->ddcProfile?->ddc_code ?? 'DDB000',
            ] : null,
        ]);
    }

    public function getEligibleDdcs(Request $request)
    {
        $pincode = $request->get('pincode');
        $eligibleDdcs = [];

        if ($pincode) {
            $pincodeObj = \App\Models\Pincode::where('pincode', $pincode)->first();
            if ($pincodeObj) {
                $ddcIds = \Illuminate\Support\Facades\DB::table('ddc_pincodes')
                    ->where('pincode_id', $pincodeObj->id)
                    ->pluck('ddc_id');

                if ($ddcIds->isNotEmpty()) {
                    $eligibleDdcs = \App\Models\User::whereIn('id', $ddcIds)
                        ->where('role', 'ddc')
                        ->where('status', '1')
                        ->with('ddcProfile')
                        ->get()
                        ->map(function ($u) {
                            return [
                                'id' => $u->id,
                                'name' => $u->name,
                                'code' => $u->ddcProfile?->ddc_code ?? 'DDC',
                                'company_name' => $u->ddcProfile?->company_name ?? $u->name,
                                'district' => $u->ddcProfile?->district ?? '',
                            ];
                        });
                }
            }

            if (empty($eligibleDdcs) || (is_object($eligibleDdcs) && $eligibleDdcs->isEmpty())) {
                $eligibleDdcs = \App\Models\User::where('role', 'ddc')
                    ->where('status', '1')
                    ->whereHas('ddcProfile', function ($q) use ($pincode) {
                        $q->where('pincode', $pincode)
                          ->orWhere('ddc_pincode', $pincode)
                          ->orWhere('adpl_pincode', $pincode);
                    })
                    ->with('ddcProfile')
                    ->get()
                    ->map(function ($u) {
                        return [
                            'id' => $u->id,
                            'name' => $u->name,
                            'code' => $u->ddcProfile?->ddc_code ?? 'DDC',
                            'company_name' => $u->ddcProfile?->company_name ?? $u->name,
                            'district' => $u->ddcProfile?->district ?? '',
                        ];
                    });
            }
        }

        $allDdcs = \App\Models\User::where('role', 'ddc')
            ->where('status', '1')
            ->with('ddcProfile')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'code' => $u->ddcProfile?->ddc_code ?? 'DDC',
                    'company_name' => $u->ddcProfile?->company_name ?? $u->name,
                    'district' => $u->ddcProfile?->district ?? '',
                ];
            });

        $virtualDdc = \App\Models\AdcWalletRequest::getVirtualDdc();

        return response()->json([
            'status' => true,
            'eligible_ddcs' => $eligibleDdcs,
            'all_ddcs' => $allDdcs,
            'virtual_ddc' => $virtualDdc ? [
                'id' => $virtualDdc->id,
                'name' => $virtualDdc->name . ' (Virtual DDC)',
                'code' => $virtualDdc->ddcProfile?->ddc_code ?? 'DDB000',
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'adc_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'state' => ['required'],
            'district' => ['required', 'string'],
            'pincode' => ['required', 'digits:6'],
            'gst_number' => ['required', 'string', 'min:15', 'max:15'],
        ]);

        $adc = ADC::findOrFail($request->adc_id);
        $adc->name = $request->name;
        $adc->mobile = $request->mobile;
        $adc->email = $request->email;
        $adc->save();

        // Resolve State
        $stateName = $request->state;
        $stateId = null;
        $stateObj = \App\Models\State::find($request->state);
        if ($stateObj) {
            $stateName = $stateObj->name;
            $stateId = $stateObj->id;
        }

        \App\Models\AdcProfile::updateOrCreate(
            ['user_id' => $adc->id],
            [
                'company_name' => $request->canteen_name,
                'code' => $request->canteen_code,
                'adc_code' => $request->adc_code ?: ('ADC' . str_pad($adc->id, 3, '0', STR_PAD_LEFT)),
                'type' => $request->type ?? 'ASC',
                'address' => $request->address,
                'state' => $stateName,
                'state_id' => $stateId,
                'district' => $request->district,
                'pincode' => $request->pincode,
                'gst_number' => $request->gst_number,
                'aadhaar_no' => $request->aadhaar_no,
                'pan_no' => $request->pan_no,
                'assigned_ddc_id' => $request->supply_ddc_id,
            ]
        );

        return redirect()->route('admin.external.adc.index')->with('success', 'Canteen / ADC details saved successfully.');
    }

    public function resetPassword(Request $request, int $id)
    {
        $request->validate([
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = ADC::findOrFail($id);

        $user->update([
            'password' => Hash::make($request->new_password),
            'force_change_password' => '1',
        ]);

        return back()->with('success', "Password reset for {$user->name}. They will be prompted to change it on next login.");
    }

    public function toggleStatus(int $id)
    {
        $user = ADC::findOrFail($id);
        $newStatus = $user->status === '1' ? '0' : '1';
        $user->update(['status' => $newStatus]);

        $status = $newStatus === '1' ? 'activated' : 'deactivated';
        return back()->with('success', "ADC user {$status} successfully.");
    }

    // public function togglePosStatus(Request $request, int $id)
    // {
    //     $user = ADC::findOrFail($id);
    //     $profile = $user->adcProfile ?: $user->adcProfile()->create([]);

    //     $currentShowOrder = strtolower($profile->show_order ?? 'yes');
    //     $newShowOrder = ($currentShowOrder === 'yes') ? 'No' : 'Yes';
    //     $newPosStatus = ($newShowOrder === 'Yes') ? 'Active' : 'Inactive';

    //     $profile->update([
    //         'show_order' => $newShowOrder,
    //         'pos_status' => $newPosStatus,
    //     ]);

    //     if ($request->ajax() || $request->wantsJson()) {
    //         return response()->json([
    //             'success' => true,
    //             'status' => $newShowOrder,
    //             'message' => "Show Order changed to {$newShowOrder} for {$user->name}.",
    //         ]);
    //     }

    //     return back()->with('success', "Show Order for {$user->name} set to {$newShowOrder}.");
    // }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,status_active,status_inactive',
            'ids' => 'required|array',
            'ids.*' => 'exists:adcs,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            ADC::whereIn('id', $ids)->delete();
            return back()->with('success', 'Selected ADC users deleted successfully.');
        } elseif ($action === 'status_active') {
            ADC::whereIn('id', $ids)->update(['status' => '1']);
            return back()->with('success', 'Selected ADC users activated successfully.');
        } elseif ($action === 'status_inactive') {
            ADC::whereIn('id', $ids)->update(['status' => '0']);
            return back()->with('success', 'Selected ADC users deactivated successfully.');
        }

        return back()->with('error', 'Invalid action.');
    }

    public function walletRequests(Request $request)
    {
        $query = \App\Models\AdcWalletRequest::with(['adc.adcProfile', 'assignedDdc.ddcProfile', 'processedBy']);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('utr_number', 'like', "%{$search}%")
                  ->orWhere('pincode', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%")
                  ->orWhereHas('adc', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('assignedDdc', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->type) {
            $query->whereIn('request_type', [$request->type, strtolower($request->type), strtoupper($request->type)]);
        }

        if ($request->status) {
            $query->whereIn('status', [$request->status, strtolower($request->status), strtoupper($request->status)]);
        }

        $requests = $query->latest()->paginate(15)->withQueryString();

        return view('admin.external.adc.wallet_requests', compact('requests'));
    }

    public function approveWalletRequest(Request $request, $id)
    {
        $walletReq = \App\Models\AdcWalletRequest::findOrFail($id);

        if (strtoupper($walletReq->status) !== 'PENDING') {
            return back()->with('error', 'This request has already been processed.');
        }

        if (strtoupper($walletReq->request_type) === 'PHYSICAL') {
            return back()->with('error', 'Physical DDC wallet requests can only be approved/rejected by the assigned Physical DDC.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($walletReq, $request) {
            $walletReq->update([
                'status' => 'APPROVED',
                'admin_remarks' => $request->admin_remarks ?? 'Approved by Virtual DDC (Admin)',
                'processed_by' => auth('admin')->id(),
                'processed_at' => now(),
            ]);

            // Credit ADC wallet balance
            $adc = $walletReq->adc;
            $adcProfile = $adc->adcProfile()->firstOrCreate([]);
            $adcProfile->increment('wallet_balance', $walletReq->amount);

            $freshBal = (float) $adcProfile->fresh()->wallet_balance;
            if ($freshBal > 0) {
                if (str_contains($adcProfile->deactivation_reason ?? '', 'Wallet balance') || $adc->status == 0 || $adc->status === '0') {
                    $adcProfile->update(['deactivation_reason' => null]);
                    \App\Models\User::where('id', $adc->id)->update(['status' => '1']);
                }
            }

            $desc = "Wallet topup approved by Virtual DDC (Admin) - UTR: {$walletReq->utr_number}";

            \App\Models\AdcWallet::create([
                'adc_id' => $walletReq->adc_id,
                'amount' => $walletReq->amount,
                'type' => 'credit',
                'description' => $desc,
            ]);
        });

        NotificationService::notifyAdc(
            $walletReq->adc_id,
            'Wallet Request Approved',
            "Your wallet top-up request of ₹{$walletReq->amount} has been approved by Admin.",
            route('adc.wallet.index')
        );

        return back()->with('success', 'ADC Wallet top-up request approved successfully.');
    }

    public function rejectWalletRequest(Request $request, $id)
    {
        $request->validate([
            'admin_remarks' => 'required|string|max:500',
        ], [
            'admin_remarks.required' => 'Please provide a reason for rejecting the request.',
        ]);

        $walletReq = \App\Models\AdcWalletRequest::findOrFail($id);

        if (strtoupper($walletReq->status) !== 'PENDING') {
            return back()->with('error', 'This request has already been processed.');
        }

        if (strtoupper($walletReq->request_type) === 'PHYSICAL') {
            return back()->with('error', 'Physical DDC wallet requests can only be approved/rejected by the assigned Physical DDC.');
        }

        $walletReq->update([
            'status' => 'REJECTED',
            'admin_remarks' => $request->admin_remarks,
            'processed_by' => auth('admin')->id(),
            'processed_at' => now(),
        ]);

        NotificationService::notifyAdc(
            $walletReq->adc_id,
            'Wallet Request Rejected',
            "Your wallet top-up request of ₹{$walletReq->amount} was rejected. Reason: {$request->admin_remarks}",
            route('adc.wallet.index')
        );

        return back()->with('info', 'ADC Wallet top-up request rejected.');
    }

    public function create()
    {
        return redirect()->route('admin.external.adc.index');
    }

    public function edit($id)
    {
        return $this->getDetails($id);
    }

    public function update(Request $request, $id)
    {
        return $this->store($request);
    }

    public function updateKycStatus(Request $request, int $id)
    {
        $request->validate([
            'kyc_status'  => ['required', 'string', 'in:APPROVED,REJECTED,UNDER_REVIEW,PENDING'],
            'kyc_remarks' => ['nullable', 'string'],
        ]);

        $user = ADC::findOrFail($id);
        $profile = $user->adcProfile ?: $user->adcProfile()->create([]);

        $profile->update([
            'kyc_status'  => $request->kyc_status,
            'kyc_remarks' => $request->kyc_status === 'REJECTED' ? $request->kyc_remarks : null,
        ]);

        NotificationService::notifyAdc(
            $user->id,
            'KYC Status Updated',
            "Your KYC status has been updated to {$request->kyc_status} by Admin." . ($request->kyc_remarks ? " Reason: {$request->kyc_remarks}" : ""),
            route('adc.profile')
        );

        return back()->with('success', 'ADC KYC status updated successfully to ' . $request->kyc_status);
    }
}
