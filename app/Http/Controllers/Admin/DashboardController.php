<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Vendor;
use App\Models\DDC;
use App\Models\ADC;
use App\Models\PickupStore;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\AdcOrder;
use App\Models\DdcPosOrder;
use App\Models\AdcPosOrder;
use App\Models\DdcPosSalesReturn;
use App\Models\AdcPosSalesReturn;
use App\Models\DdcWalletRequest;
use App\Models\AdcWalletRequest;
use App\Models\IncentiveHold;
use App\Models\DdcProfile;
use App\Models\SellerProfile;
use App\Models\DepotRack;
use App\Models\DepotRackBooking;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brand;
use App\Models\ContactInquiry;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. KPI Counts
        $totalVendors = Vendor::count();
        $pendingKycVendors = SellerProfile::where('kyc_status', 'pending')->count();
        $totalDdcs = DDC::count();
        $virtualDdcs = DdcProfile::where('type', 'virtual')->count();
        $physicalDdcs = DdcProfile::where('type', 'physical')->count();
        $totalAdcs = ADC::count();
        $totalPickupStores = PickupStore::count();
        
        // Products & Catalog
        $totalProducts = Product::count();
        $pendingProductsCount = Product::where('is_new', true)->count();
        $approvedProductsCount = Product::where('is_new', false)->count();
        $totalCategories = Category::count();
        $totalSubCategories = SubCategory::count();
        $totalBrands = Brand::count();

        // Revenue & Orders Breakdown
        $totalPoCount = PurchaseOrder::count();
        $totalPoRevenue = (float) PurchaseOrder::whereIn('status', ['Completed', 'COMPLETED', 'Received', 'RECEIVED'])->sum('total_amount');
        
        $totalAdcOrderCount = AdcOrder::count();
        $totalAdcOrderRevenue = (float) AdcOrder::whereIn('status', ['COMPLETED', 'DISPATCHED', 'DELIVERED', 'Received', 'RECEIVED', 'Processing'])->sum('total_amount');

        // POS Sales
        $totalDdcPosCount = DdcPosOrder::count();
        $totalDdcPosRevenue = (float) DdcPosOrder::sum('total_amount');

        $totalAdcPosCount = AdcPosOrder::count();
        $totalAdcPosRevenue = (float) AdcPosOrder::sum('total_amount');

        $totalPosCount = $totalDdcPosCount + $totalAdcPosCount;
        $totalPosRevenue = $totalDdcPosRevenue + $totalAdcPosRevenue;

        // Grand Total Revenue
        $totalRevenue = $totalPoRevenue + $totalAdcOrderRevenue + $totalPosRevenue;

        // POS Sales Returns
        $ddcPosReturnsCount = DdcPosSalesReturn::count();
        $ddcPosReturnsAmount = (float) DdcPosSalesReturn::sum('total_return_amount');
        
        $adcPosReturnsCount = AdcPosSalesReturn::count();
        $adcPosReturnsAmount = (float) AdcPosSalesReturn::sum('total_return_amount');

        $totalPosReturnsCount = $ddcPosReturnsCount + $adcPosReturnsCount;
        $totalPosReturnsAmount = $ddcPosReturnsAmount + $adcPosReturnsAmount;

        // 2. Wallet & Financial Stats
        $pendingDdcWalletRequestsCount = DdcWalletRequest::where('status', 'PENDING')->count();
        $pendingDdcWalletRequestsAmount = (float) DdcWalletRequest::where('status', 'PENDING')->sum('amount');

        $pendingAdcWalletRequestsCount = AdcWalletRequest::where('status', 'PENDING')->count();
        $pendingAdcWalletRequestsAmount = (float) AdcWalletRequest::where('status', 'PENDING')->sum('amount');

        $activeIncentiveHoldsCount = IncentiveHold::where('status', 'held')->count();
        $activeIncentiveHoldsAmount = (float) IncentiveHold::where('status', 'held')->sum('amount');

        // Depot Racks
        $totalRacksCount = DepotRack::count();
        $activeRackBookingsCount = DepotRackBooking::where('status', 'active')->count();

        // System Activity
        $totalInquiries = ContactInquiry::count();
        $pendingInquiries = ContactInquiry::whereDate('created_at', '>=', now()->subDays(7))->count();
        $totalSubscribers = NewsletterSubscriber::count();

        // 3. Live Data Queues
        $recentPurchaseOrders = PurchaseOrder::with(['ddc.profile', 'seller.sellerProfile'])
            ->latest()
            ->take(5)
            ->get();

        $recentAdcOrders = AdcOrder::with(['adc.adcProfile', 'assignedDdc.profile', 'selectedDdc.profile'])
            ->latest()
            ->take(5)
            ->get();

        $recentDdcPosOrders = DdcPosOrder::with(['ddc.profile'])
            ->latest()
            ->take(5)
            ->get();

        $recentVendors = Vendor::with('sellerProfile')
            ->latest()
            ->take(5)
            ->get();

        $pendingProducts = Product::with(['seller.sellerProfile', 'category'])
            ->where('status', 'Pending Approval')
            ->latest()
            ->take(5)
            ->get();

        // 4. Monthly Performance Chart Data (Last 6 Months)
        $chartMonths = [];
        $poMonthlyData = [];
        $adcMonthlyData = [];
        $posMonthlyData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel = $date->format('M Y');
            $year = $date->year;
            $month = $date->month;

            $chartMonths[] = $monthLabel;

            $poMonthlyData[] = round((float) PurchaseOrder::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('total_amount'), 2);

            $adcMonthlyData[] = round((float) AdcOrder::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('total_amount'), 2);

            $ddcPosSum = (float) DdcPosOrder::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('total_amount');

            $adcPosSum = (float) AdcPosOrder::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('total_amount');

            $posMonthlyData[] = round($ddcPosSum + $adcPosSum, 2);
        }

        // Registration & Partner Stats (matching top-level vendors view)
        $mainVendorQuery = function ($q) {
            $q->whereDoesntHave('profile')
              ->orWhereHas('profile', function ($qp) {
                  $qp->whereNull('parent_id');
              });
        };

        $totalVendors = Vendor::where($mainVendorQuery)->count();

        $verifiedMsmeCount = Vendor::where($mainVendorQuery)
            ->whereHas('profile', function ($qp) {
                $qp->whereIn('kyc_status', ['APPROVED', 'approved'])
                   ->where(function ($lq) {
                       $lq->whereNotNull('state_id')
                          ->orWhereNotNull('district_id')
                          ->orWhereNotNull('pincode')
                          ->orWhereNotNull('state')
                          ->orWhereNotNull('district');
                   });
            })
            ->whereHas('products', function ($q) {
                $q->where('status', 'Approved');
            })
            ->count();

        $pendingMsmeCount = Vendor::where($mainVendorQuery)
            ->whereHas('profile', function ($qp) {
                $qp->whereIn('kyc_status', ['PENDING', 'pending', 'UNDER_REVIEW', 'under_review']);
            })->count();

        $productActiveSellersCount = Vendor::where($mainVendorQuery)
            ->whereHas('products', function ($q) {
                $q->where('status', 'Approved');
            })->count();

        // Orders Breakdown
        $placedOrdersCount = AdcOrder::whereIn('status', ['PENDING', 'PLACED', 'CREATED', 'CONFIRMED'])->count();
        $dispatchOrdersCount = AdcOrder::whereIn('status', ['DISPATCHED', 'SHIPPED', 'IN_TRANSIT'])->count();
        $receivedOrdersCount = AdcOrder::whereIn('status', ['COMPLETED', 'DELIVERED', 'RECEIVED'])->count();
        $paymentCount = AdcOrder::whereIn('status', ['COMPLETED', 'DISPATCHED', 'DELIVERED'])->count();
        $onlinePaymentCount = AdcOrder::whereNotNull('payment_method')->where('payment_method', '!=', 'COD')->count();

        // Product Catalog Breakdown (synchronized with ProductApprovalController)
        $activatedProductsCount = Product::where('status', 'Approved')->count();
        $deactivatedProductsCount = Product::whereIn('status', ['Inactive', 'Rejected'])->count();
        $reviewProductsCount = Product::where('status', 'Pending Approval')->count();
        $overcostedProductsCount = Product::where('mrp', '>', 5000)->count();
        $totalLiveProductsCount = Product::where('status', 'Approved')->count();

        $stats = [
            'admins' => Admin::count(),
            'msme' => $totalVendors,
            'total_vendors' => $totalVendors,
            'vendors' => $totalVendors,
            'adc' => $totalAdcs,
            'total_adcs' => $totalAdcs,
            'adcs' => $totalAdcs,
            'ddc' => $totalDdcs,
            'total_ddcs' => $totalDdcs,
            'ddcs' => $totalDdcs,
            'verified_msme' => $verifiedMsmeCount,
            'pending_msme' => $pendingMsmeCount,
            'pending_kyc_vendors' => $pendingMsmeCount,
            'product_active_sellers' => $productActiveSellersCount,
            'virtual_ddcs' => $virtualDdcs,
            'physical_ddcs' => $physicalDdcs,
            'total_pickup_stores' => $totalPickupStores,

            // Orders Section
            'ddb_po' => $totalPoCount,
            'total_po_count' => $totalPoCount,
            'online_order' => $totalAdcOrderCount,
            'total_adc_order_count' => $totalAdcOrderCount,
            'payment_count' => $paymentCount,
            'online_payment_count' => $onlinePaymentCount,
            'placed_orders' => $placedOrdersCount,
            'dispatch_orders' => $dispatchOrdersCount,
            'received_orders' => $receivedOrdersCount,

            // Account Section
            'ddb_sales' => $totalPoRevenue,
            'total_po_revenue' => $totalPoRevenue,
            'online_sales' => $totalAdcOrderRevenue,
            'total_adc_order_revenue' => $totalAdcOrderRevenue,
            'total_pos_count' => $totalPosCount,
            'total_pos_revenue' => $totalPosRevenue,
            'total_pos_returns_count' => $totalPosReturnsCount,
            'total_pos_returns_amount' => $totalPosReturnsAmount,
            'total_sales' => $totalRevenue,
            'total_revenue' => $totalRevenue,

            // Product Section
            'total_products' => $totalProducts,
            'activated_products' => $activatedProductsCount,
            'deactivated_products' => $deactivatedProductsCount,
            'review_products' => $reviewProductsCount,
            'pending_products_count' => $pendingProductsCount,
            'approved_products_count' => $approvedProductsCount,
            'overcosted_products' => $overcostedProductsCount,
            'total_live_products' => $totalLiveProductsCount,
            'new_products' => $pendingProductsCount,
            'old_products' => $approvedProductsCount,
            'total_categories' => $totalCategories,
            'total_sub_categories' => $totalSubCategories,
            'total_brands' => $totalBrands,

            // System & Financial Requests
            'pending_ddc_wallet_requests_count' => $pendingDdcWalletRequestsCount,
            'pending_ddc_wallet_requests_amount' => $pendingDdcWalletRequestsAmount,
            'pending_adc_wallet_requests_count' => $pendingAdcWalletRequestsCount,
            'pending_adc_wallet_requests_amount' => $pendingAdcWalletRequestsAmount,
            'active_incentive_holds_count' => $activeIncentiveHoldsCount,
            'active_incentive_holds_amount' => $activeIncentiveHoldsAmount,
            'total_racks_count' => $totalRacksCount,
            'active_rack_bookings_count' => $activeRackBookingsCount,
            'total_inquiries' => $totalInquiries,
            'pending_inquiries' => $pendingInquiries,
            'total_subscribers' => $totalSubscribers,
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recentPurchaseOrders',
            'recentAdcOrders',
            'recentDdcPosOrders',
            'recentVendors',
            'pendingProducts',
            'chartMonths',
            'poMonthlyData',
            'adcMonthlyData',
            'posMonthlyData'
        ));
    }
}
