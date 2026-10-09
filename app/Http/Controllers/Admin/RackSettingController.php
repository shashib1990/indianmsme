<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\DepotRack;
use App\Models\DepotRackImage;
use App\Models\DepotRackBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RackSettingController extends Controller
{
    public function racksList(Request $request)
    {
        // Auto release expired bookings first
        DepotRackBooking::releaseExpiredBookings();

        $ownerTypeFilter = $request->input('owner_type', 'all');
        $unitTypeFilter  = $request->input('unit_type', 'all');
        $search          = $request->input('search');

        $query = DepotRack::with(['owner', 'approvedImages', 'bookings.seller']);

        if ($ownerTypeFilter !== 'all' && !empty($ownerTypeFilter)) {
            $query->where('owner_type', strtolower($ownerTypeFilter));
        }

        if ($unitTypeFilter !== 'all' && !empty($unitTypeFilter)) {
            $query->where('unit_type', strtoupper($unitTypeFilter));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('depot_code', 'like', "%{$search}%")
                  ->orWhereHas('owner', function ($oq) use ($search) {
                      $oq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%")
                         ->orWhereHas('adcProfile', function ($apq) use ($search) {
                             $apq->where('company_name', 'like', "%{$search}%")
                                 ->orWhere('district', 'like', "%{$search}%")
                                 ->orWhere('state', 'like', "%{$search}%");
                         })
                         ->orWhereHas('ddcProfile', function ($dpq) use ($search) {
                             $dpq->where('company_name', 'like', "%{$search}%")
                                 ->orWhere('ddc_code', 'like', "%{$search}%")
                                 ->orWhere('district', 'like', "%{$search}%")
                                 ->orWhere('state', 'like', "%{$search}%");
                         });
                  });
            });
        }

        $racks = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $allCount = DepotRack::count();
        $ddcCount = DepotRack::where('owner_type', 'ddc')->count();
        $adcCount = DepotRack::where('owner_type', 'adc')->count();

        return view('admin.racks.list', compact(
            'racks', 'ownerTypeFilter', 'unitTypeFilter', 'search', 'allCount', 'ddcCount', 'adcCount'
        ));
    }

    public function index()
    {
        $ddcRackAmount  = (float) SystemSetting::getVal('ddc_rack_amount', 4000.00);
        $ddcShelfAmount = (float) SystemSetting::getVal('ddc_shelf_amount', 1000.00);
        $adcRackAmount  = (float) SystemSetting::getVal('adc_rack_amount', 2000.00);
        $adcShelfAmount = (float) SystemSetting::getVal('adc_shelf_amount', 1000.00);

        $pendingImagesCount   = DepotRackImage::where('approval_status', 'pending')->count();
        $pendingPaymentsCount = DepotRackBooking::where('status', 'Pending Payment Verification')->count();

        return view('admin.racks.settings', compact(
            'ddcRackAmount', 'ddcShelfAmount', 'adcRackAmount', 'adcShelfAmount',
            'pendingImagesCount', 'pendingPaymentsCount'
        ));
    }

    public function updatePricing(Request $request)
    {
        $request->validate([
            'ddc_rack_amount'  => 'required|numeric|min:0',
            'ddc_shelf_amount' => 'required|numeric|min:0',
            'adc_rack_amount'  => 'required|numeric|min:0',
            'adc_shelf_amount' => 'required|numeric|min:0',
        ]);

        SystemSetting::setVal('ddc_rack_amount', (float) $request->input('ddc_rack_amount'));
        SystemSetting::setVal('ddc_shelf_amount', (float) $request->input('ddc_shelf_amount'));
        SystemSetting::setVal('adc_rack_amount', (float) $request->input('adc_rack_amount'));
        SystemSetting::setVal('adc_shelf_amount', (float) $request->input('adc_shelf_amount'));

        return redirect()->back()->with('success', 'Rack & shelf pricing updated successfully.');
    }

    public function updateBankDetails(Request $request)
    {
        $request->validate([
            'bank_account_name'   => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_ifsc'           => 'nullable|string|max:255',
            'bank_name'           => 'nullable|string|max:255',
            'bank_upi_id'         => 'nullable|string|max:255',
        ]);

        if ($request->has('bank_account_name')) SystemSetting::setVal('bank_account_name', $request->input('bank_account_name'));
        if ($request->has('bank_account_number')) SystemSetting::setVal('bank_account_number', $request->input('bank_account_number'));
        if ($request->has('bank_ifsc')) SystemSetting::setVal('bank_ifsc', $request->input('bank_ifsc'));
        if ($request->has('bank_name')) SystemSetting::setVal('bank_name', $request->input('bank_name'));
        if ($request->has('bank_upi_id')) SystemSetting::setVal('bank_upi_id', $request->input('bank_upi_id'));

        return redirect()->back()->with('success', 'Bank settings updated successfully.');
    }

    public function pendingImages(Request $request)
    {
        $ownerType = strtolower($request->input('owner_type', 'all'));
        $statusFilter = strtolower($request->input('status', 'all'));
        $search = $request->input('search');

        $query = DepotRackImage::with(['depotRack', 'owner']);

        if ($ownerType !== 'all') {
            $query->where('owner_type', $ownerType);
        }

        if ($statusFilter !== 'all') {
            $query->where('approval_status', $statusFilter);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('image_label', 'like', "%{$search}%")
                  ->orWhereHas('owner', function ($oq) use ($search) {
                      $oq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                  })
                  ->orWhereHas('depotRack', function ($rq) use ($search) {
                      $rq->where('depot_code', 'like', "%{$search}%");
                  });
            });
        }

        $images = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Tab Counts
        $allCount     = DepotRackImage::count();
        $ddcCount     = DepotRackImage::where('owner_type', 'ddc')->count();
        $adcCount     = DepotRackImage::where('owner_type', 'adc')->count();
        $pendingCount = DepotRackImage::where('approval_status', 'pending')->count();

        return view('admin.racks.images', compact(
            'images', 'ownerType', 'statusFilter', 'search', 'allCount', 'ddcCount', 'adcCount', 'pendingCount'
        ));
    }

    public function approveImage($id)
    {
        $image = DepotRackImage::findOrFail($id);
        $image->approval_status = 'approved';
        $image->rejection_reason = null;
        $image->save();

        return back()->with('success', 'Depot rack image APPROVED successfully.');
    }

    public function rejectImage(Request $request, $id)
    {
        $image = DepotRackImage::findOrFail($id);
        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $image->approval_status = 'rejected';
        $image->rejection_reason = $request->input('rejection_reason');
        $image->save();

        return back()->with('success', 'Depot rack image REJECTED successfully.');
    }

    public function paymentVerifications(Request $request)
    {
        $ownerType = strtolower($request->input('owner_type', 'all'));
        $status    = $request->input('status', 'Pending Payment Verification');
        $search    = $request->input('search');

        $query = DepotRackBooking::with(['seller.sellerProfile', 'owner', 'depotRack']);

        if ($ownerType !== 'all' && !empty($ownerType)) {
            $query->where('owner_type', $ownerType);
        }

        if ($status !== 'all' && !empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_no', 'like', "%{$search}%")
                  ->orWhere('payment_utr', 'like', "%{$search}%")
                  ->orWhereHas('seller', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                  })
                  ->orWhereHas('owner', function ($oq) use ($search) {
                      $oq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                  })
                  ->orWhereHas('depotRack', function ($rq) use ($search) {
                      $rq->where('depot_code', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $allCount = DepotRackBooking::count();
        $ddcCount = DepotRackBooking::where('owner_type', 'ddc')->count();
        $adcCount = DepotRackBooking::where('owner_type', 'adc')->count();

        return view('admin.racks.payments', compact(
            'bookings', 'status', 'ownerType', 'search', 'allCount', 'ddcCount', 'adcCount'
        ));
    }

    public function verifyPayment(Request $request, $id)
    {
        $booking = DepotRackBooking::findOrFail($id);

        if ($booking->status !== 'Pending Payment Verification') {
            return back()->with('error', 'Booking request has already been processed.');
        }

        $rack = $booking->depotRack;
        if (!$rack || $rack->available_units < $booking->quantity) {
            return back()->with('error', 'Insufficient available units in depot rack to complete allotment.');
        }

        DB::beginTransaction();
        try {
            $startDate = Carbon::now();
            $expiryDate = $startDate->copy()->addMonths($booking->duration_months);

            // Revenue split calculation: 50% Admin Share / 50% DDC or ADC Share
            $adminShare = round($booking->total_amount * 0.50, 2);
            $depotShare = round($booking->total_amount - $adminShare, 2);

            $booking->update([
                'status'        => 'Approved',
                'start_date'    => $startDate->format('Y-m-d'),
                'expiry_date'   => $expiryDate->format('Y-m-d'),
                'admin_share'   => $adminShare,
                'depot_share'   => $depotShare,
                'admin_remarks' => $request->input('admin_remarks', 'Payment verified and unit allotted.'),
                'action_date'   => now(),
            ]);

            // Automatically allot unit & reduce available count
            $rack->available_units = max(0, $rack->available_units - $booking->quantity);
            $rack->allotted_units  = $rack->allotted_units + $booking->quantity;
            $rack->save();

            DB::commit();

            return back()->with('success', "Payment Verified! Booking #{$booking->booking_no} has been APPROVED and unit automatically allotted to Seller.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process payment verification: ' . $e->getMessage());
        }
    }

    public function rejectPayment(Request $request, $id)
    {
        $booking = DepotRackBooking::findOrFail($id);
        $request->validate(['admin_remarks' => 'required|string|max:1000']);

        if ($booking->status !== 'Pending Payment Verification') {
            return back()->with('error', 'Booking request has already been processed.');
        }

        $booking->update([
            'status'        => 'Rejected',
            'admin_remarks' => $request->input('admin_remarks'),
            'action_date'   => now(),
        ]);

        return back()->with('success', "Booking #{$booking->booking_no} payment has been REJECTED.");
    }
}
