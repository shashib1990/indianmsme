<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VendorUsersController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $limit = $request->get('limit', 15);
        $parentId = $request->get('parent_id');
        $filterStatus = $request->get('filter_status');
        $parentSeller = null;

        // Base query for stats (scoped to parent or main sellers)
        $statsBaseQuery = Vendor::query();
        if ($parentId) {
            $parentSeller = \App\Models\User::findOrFail($parentId);
            $statsBaseQuery->whereHas('profile', function ($qp) use ($parentId) {
                $qp->where('parent_id', $parentId);
            });
        } else {
            $statsBaseQuery->where(function ($q) {
                $q->whereDoesntHave('profile')
                  ->orWhereHas('profile', function ($qp) {
                      $qp->whereNull('parent_id');
                  });
            });
        }

        $totalSellersCount = (clone $statsBaseQuery)->count();
        $activeSellersCount = (clone $statsBaseQuery)
            ->where('users.status', '1')
            ->whereHas('profile', function ($qp) {
                $qp->whereIn('kyc_status', ['APPROVED', 'approved']);
            })
            ->has('products')
            ->has('pickupStores')
            ->count();
        $inactiveSellersCount = max(0, $totalSellersCount - $activeSellersCount);

        // Main table query
        $query = Vendor::query()
            ->with(['profile'])
            ->withCount(['subSellers', 'products', 'pickupStores']);

        if ($parentId) {
            $query->whereHas('profile', function ($qp) use ($parentId) {
                $qp->where('parent_id', $parentId);
            });
        } elseif (!$search) {
            $query->where(function ($q) {
                $q->whereDoesntHave('profile')
                  ->orWhereHas('profile', function ($qp) {
                      $qp->whereNull('parent_id');
                  });
            });
        }

        // Active vs Inactive Seller Card Filter
        if ($filterStatus === 'active') {
            $query->where('users.status', '1')
                ->whereHas('profile', function ($qp) {
                    $qp->whereIn('kyc_status', ['APPROVED', 'approved']);
                })
                ->has('products')
                ->has('pickupStores');
        } elseif ($filterStatus === 'inactive') {
            $query->where(function ($q) {
                $q->where('users.status', '!=', '1')
                  ->orWhereDoesntHave('profile', function ($qp) {
                      $qp->whereIn('kyc_status', ['APPROVED', 'approved']);
                  })
                  ->orDoesntHave('products')
                  ->orDoesntHave('pickupStores');
            });
        }

        if ($request->filled('kyc_status')) {
            $kycStatus = $request->kyc_status;
            if (strtolower($kycStatus) === 'approved') {
                $query->whereHas('profile', function ($qp) {
                    $qp->whereIn('kyc_status', ['APPROVED', 'approved'])
                       ->where(function ($lq) {
                            $lq->whereNotNull('state_id')
                               ->orWhereNotNull('district_id')
                               ->orWhereNotNull('pincode')
                               ->orWhereNotNull('state')
                               ->orWhereNotNull('district');
                       });
                })->whereHas('products', function ($q) {
                    $q->where('status', 'Approved');
                });
            } else {
                $statuses = [strtolower($kycStatus), strtoupper($kycStatus)];
                if (in_array(strtoupper($kycStatus), ['PENDING', 'UNDER_REVIEW'])) {
                    $statuses = ['PENDING', 'pending', 'UNDER_REVIEW', 'under_review'];
                }
                $query->whereHas('profile', function ($qp) use ($statuses) {
                    $qp->whereIn('kyc_status', $statuses);
                });
            }
        }

        if ($request->filled('seller_type')) {
            $sellerType = $request->seller_type;
            $query->whereHas('profile', function ($qp) use ($sellerType) {
                $qp->where('seller_type', $sellerType);
            });
        }

        if ($request->filled('active_sellers')) {
            $query->whereHas('products', function ($q) {
                $q->where('status', 'Approved');
            });
        }

        if ($request->filled('account_status')) {
            $query->where('status', (string) $request->account_status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($profileQuery) use ($search) {
                        $profileQuery->where('company_name', 'like', "%{$search}%")
                            ->orWhere('gst_number', 'like', "%{$search}%")
                            ->orWhere('seller_id', 'like', "%{$search}%")
                            ->orWhere('seller_type', 'like', "%{$search}%")
                            ->orWhere('brand_name', 'like', "%{$search}%")
                            ->orWhere('district', 'like', "%{$search}%")
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
                fputcsv($file, [
                    'ID',
                    'Name',
                    'Email',
                    'Phone',
                    'Company Name',
                    'Seller Type',
                    'GST Number',
                    'Account Status',
                    'KYC Status',
                    'Products Count',
                    'Pickup Stores Count',
                    'Seller Status',
                    'Missing Requirements',
                    'Created At'
                ]);
                foreach ($records as $row) {
                    $missing = collect($row->missing_requirements)->pluck('label')->implode(', ');
                    fputcsv($file, [
                        $row->id,
                        $row->name,
                        $row->email,
                        $row->mobile ?? $row->phone ?? '—',
                        $row->company_name ?? ($row->profile?->company_name ?? '—'),
                        $row->profile?->seller_type ?? '—',
                        $row->gst_number ?? ($row->profile?->gst_number ?? '—'),
                        $row->is_account_active ? 'Active (1)' : 'Inactive (0)',
                        $row->profile?->kyc_status ?: 'Unverified',
                        $row->products_count ?? $row->products()->count(),
                        $row->pickup_stores_count ?? $row->pickupStores()->count(),
                        $row->is_seller_active ? 'Active' : 'Inactive',
                        $missing ?: 'None (All Compliant)',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'vendor_users_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $users = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();
        $sellerType = $request->get('seller_type');

        return view('admin.external.vendors.index', compact(
            'users',
            'search',
            'sort',
            'order',
            'limit',
            'parentSeller',
            'parentId',
            'sellerType',
            'filterStatus',
            'totalSellersCount',
            'activeSellersCount',
        ));
    }

    public function resetPassword(Request $request, int $id)
    {
        $request->validate([
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = Vendor::findOrFail($id);

        $user->update([
            'password' => Hash::make($request->new_password),
            'force_change_password' => '1',
        ]);

        return back()->with('success', "Password reset for {$user->name}. They will be prompted to change it on next login.");
    }

    public function toggleStatus(Request $request, int $id)
    {
        $user = Vendor::findOrFail($id);
        $newStatus = ($user->status == '1' || $user->status === 1) ? '0' : '1';
        $user->status = $newStatus;
        $user->save();

        $status = $newStatus === '1' ? 'activated' : 'deactivated';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Vendor {$status} successfully.",
                'is_active' => $newStatus === '1',
                'status' => $newStatus === '1' ? 1 : 0,
                'status_text' => $newStatus === '1' ? 'Active' : 'Inactive'
            ]);
        }

        return back()->with('success', "Vendor {$status} successfully.");
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,status_active,status_inactive',
            'ids' => 'required|array',
            'ids.*' => 'exists:vendors,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            Vendor::whereIn('id', $ids)->delete();
            $msg = 'Selected vendors deleted successfully.';
        } elseif ($action === 'status_active') {
            Vendor::whereIn('id', $ids)->update(['status' => '1']);
            $msg = 'Selected vendors activated successfully.';
        } elseif ($action === 'status_inactive') {
            Vendor::whereIn('id', $ids)->update(['status' => '0']);
            $msg = 'Selected vendors deactivated successfully.';
        } else {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid action.'], 422);
            }
            return back()->with('error', 'Invalid action.');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    public function profile(int $id)
    {
        $vendor = Vendor::findOrFail($id);
        return view('admin.external.vendors.profile', compact('vendor'));
    }

    public function updateKycStatus(Request $request, int $id)
    {
        $request->validate([
            'kyc_status' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\KycStatus::class)],
        ]);

        $vendor = Vendor::findOrFail($id);
        $profile = $vendor->profile()->firstOrCreate([]);
        $profile->update([
            'kyc_status' => $request->kyc_status
        ]);

        $msg = 'Vendor KYC status updated successfully to ' . $request->kyc_status;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'status_text' => $request->kyc_status
            ]);
        }

        return back()->with('success', $msg);
    }

    public function markAllNotificationsRead()
    {
        \App\Models\Notification::where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function approveDdcSubscription(int $id)
    {
        $vendor = Vendor::findOrFail($id);
        $profile = $vendor->profile()->firstOrCreate([]);
        
        $profile->update([
            'is_ddc_subscribed' => true,
            'ddc_subscription_status' => 'approved',
            'ddc_subscription_reject_reason' => null,
        ]);

        return back()->with('success', 'DDC Model Subscription approved successfully.');
    }

    public function rejectDdcSubscription(Request $request, int $id)
    {
        $request->validate([
            'ddc_subscription_reject_reason' => 'required|string|max:1000',
        ]);

        $vendor = Vendor::findOrFail($id);
        $profile = $vendor->profile()->firstOrCreate([]);

        $profile->update([
            'is_ddc_subscribed' => false,
            'ddc_subscription_status' => 'rejected',
            'ddc_subscription_reject_reason' => $request->ddc_subscription_reject_reason,
        ]);

        return back()->with('success', 'DDC Model Subscription request rejected successfully.');
    }

    public function cancelDdcSubscription(int $id)
    {
        $vendor = Vendor::findOrFail($id);
        $profile = $vendor->profile()->firstOrCreate([]);

        $profile->update([
            'is_ddc_subscribed' => false,
            'ddc_subscription_status' => 'none',
            'ddc_subscription_utr' => null,
            'ddc_subscription_slip' => null,
            'ddc_subscription_reject_reason' => null,
            'ddc_subscription_submitted_at' => null,
        ]);

        return back()->with('success', 'DDC Model Subscription cancelled successfully.');
    }

    public function ddcRequests(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $limit = $request->get('limit', 15);

        $query = Vendor::query()
            ->whereHas('profile', function ($qp) {
                $qp->where('ddc_subscription_status', 'pending');
            })
            ->with('profile');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($profileQuery) use ($search) {
                        $profileQuery->where('company_name', 'like', "%{$search}%")
                            ->orWhere('gst_number', 'like', "%{$search}%")
                            ->orWhere('ddc_subscription_utr', 'like', "%{$search}%");
                    });
            });
        }

        $users = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.external.vendors.ddc_requests', compact('users', 'search', 'sort', 'order', 'limit'));
    }

    public function ddcApproved(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $limit = $request->get('limit', 15);

        $query = Vendor::query()
            ->whereHas('profile', function ($qp) {
                $qp->where('ddc_subscription_status', 'approved');
            })
            ->with('profile');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($profileQuery) use ($search) {
                        $profileQuery->where('company_name', 'like', "%{$search}%")
                            ->orWhere('gst_number', 'like', "%{$search}%")
                            ->orWhere('ddc_subscription_utr', 'like', "%{$search}%");
                    });
            });
        }

        $users = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.external.vendors.ddc_approved', compact('users', 'search', 'sort', 'order', 'limit'));
    }
}
