<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\IncentiveHold;
use App\Models\PickupStore;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $vendor = Auth::guard('vendor')->user();
        $vendorId = $vendor->id;
        $profile = $vendor->sellerProfile;

        // Purchase Orders Stats
        $poQuery = PurchaseOrder::where('seller_id', $vendorId);
        $totalPos = (clone $poQuery)->count();
        $pendingPoCount = (clone $poQuery)->where('status', 'Pending')->count();
        $acceptedPoCount = (clone $poQuery)->where('status', 'Accepted')->count();
        $dispatchedPoCount = (clone $poQuery)->where('status', 'Dispatched')->count();
        $cancelledPoCount = (clone $poQuery)->whereIn('status', ['Cancelled', 'Rejected'])->count();

        // Revenue Stats
        $acceptedPos = (clone $poQuery)->whereIn('status', ['Accepted', 'Dispatched', 'Delivered'])->get();
        $totalRevenue = (float) $acceptedPos->sum(function ($po) {
            return (float) ($po->seller_accepted_amount > 0 ? $po->seller_accepted_amount : ($po->seller_total_amount > 0 ? $po->seller_total_amount : $po->total_amount));
        });
        $walletBalance = (float) ($profile?->wallet_balance ?? 0.00);

        // Products Stats
        $productQuery = Product::where('seller_id', $vendorId);
        $totalProducts = (clone $productQuery)->count();
        $approvedProducts = (clone $productQuery)->where('status', 'approved')->count();
        $pendingProducts = (clone $productQuery)->where('status', 'pending')->count();

        // Logistics & Network Stats
        $totalPickupStores = PickupStore::where('owner_id', $vendorId)->count();
        $activePickupStores = PickupStore::where('owner_id', $vendorId)->where('status', 'ACTIVE')->count();

        $totalSubSellers = User::whereHas('sellerProfile', function ($q) use ($vendorId) {
            $q->where('parent_id', $vendorId);
        })->count();

        // Recent Purchase Orders (Top 5)
        $recentPos = (clone $poQuery)->with(['ddc', 'ddc.profile'])->orderBy('created_at', 'desc')->take(5)->get();

        // Recent Products (Top 5)
        $recentProducts = (clone $productQuery)->with('category')->orderBy('created_at', 'desc')->take(5)->get();

        // Smart Sales Incentive (SSI) Deductions Total for this seller
        $isDdcSubscribed = (bool) ($profile?->is_ddc_subscribed ?? false);
        $totalSsiDeducted = 0.00;
        if ($isDdcSubscribed) {
            $allVendorPos = (clone $poQuery)->get();
            $totalSsiDeducted = (float) $allVendorPos->sum(function ($po) {
                return $po->ssi_amount;
            });
        }

        return view('vendor.dashboard', compact(
            'vendor',
            'profile',
            'totalPos',
            'pendingPoCount',
            'acceptedPoCount',
            'dispatchedPoCount',
            'cancelledPoCount',
            'totalRevenue',
            'walletBalance',
            'totalProducts',
            'approvedProducts',
            'pendingProducts',
            'totalPickupStores',
            'activePickupStores',
            'totalSubSellers',
            'recentPos',
            'recentProducts',
            'totalSsiDeducted',
            'isDdcSubscribed'
        ));
    }
}
