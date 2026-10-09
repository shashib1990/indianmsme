<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\DepotRack;
use App\Models\DepotRackImage;
use App\Models\DepotRackBooking;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RackBookingController extends Controller
{
    public function index(Request $request)
    {
        $seller = Auth::guard('vendor')->user();
        $search = $request->input('search');

        // Auto release expired bookings
        DepotRackBooking::releaseExpiredBookings();

        $query = DepotRackBooking::where('seller_id', $seller->id)
            ->with(['owner', 'depotRack']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('booking_no', 'like', "%{$search}%")
                  ->orWhere('payment_utr', 'like', "%{$search}%")
                  ->orWhereHas('owner', function($oq) use ($search) {
                      $oq->where('name', 'like', "%{$search}%")
                         ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('vendor.rack-bookings.index', compact('bookings', 'search'));
    }

    public function marketplace(Request $request)
    {
        // Auto release expired bookings first
        DepotRackBooking::releaseExpiredBookings();

        $ownerTypeFilter = strtolower($request->input('owner_type', 'all'));
        $unitTypeFilter  = $request->input('unit_type', 'all');
        $search          = $request->input('search');

        // Rule 6: No location/pincode restriction! Show all active DDC & ADC racks across India
        $query = DepotRack::where('status', 'active')
            ->where('available_units', '>', 0)
            ->with(['owner', 'approvedImages']);

        if ($ownerTypeFilter !== 'all' && !empty($ownerTypeFilter)) {
            $query->where('owner_type', $ownerTypeFilter);
        }

        if ($unitTypeFilter !== 'all' && !empty($unitTypeFilter)) {
            $query->where('unit_type', strtoupper($unitTypeFilter));
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('depot_code', 'like', "%{$search}%")
                  ->orWhereHas('owner', function($oq) use ($search) {
                      $oq->where('name', 'like', "%{$search}%")
                         ->orWhere('company_name', 'like', "%{$search}%")
                         ->orWhere('district', 'like', "%{$search}%")
                         ->orWhere('state', 'like', "%{$search}%");
                  });
            });
        }

        $racks = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $allCount = DepotRack::where('status', 'active')->where('available_units', '>', 0)->count();
        $ddcCount = DepotRack::where('status', 'active')->where('available_units', '>', 0)->where('owner_type', 'ddc')->count();
        $adcCount = DepotRack::where('status', 'active')->where('available_units', '>', 0)->where('owner_type', 'adc')->count();

        return view('vendor.rack-bookings.marketplace', compact(
            'racks', 'ownerTypeFilter', 'unitTypeFilter', 'search', 'allCount', 'ddcCount', 'adcCount'
        ));
    }

    public function getRackImages($id)
    {
        $rack = DepotRack::with('owner')->findOrFail($id);

        // Fetch all depot store photos uploaded for this depot owner
        $images = DepotRackImage::where('owner_id', $rack->owner_id)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'rack'    => [
                'depot_code' => $rack->depot_code,
                'unit_type'  => $rack->unit_type,
                'owner_name' => $rack->owner->company_name ?? $rack->owner->name ?? 'Depot',
                'district'   => $rack->owner->district ?? $rack->owner->state ?? 'Location',
            ],
            'images'  => $images
        ]);
    }

    public function storeBooking(Request $request)
    {
        $seller = Auth::guard('vendor')->user();

        $validated = $request->validate([
            'depot_rack_id'   => 'required|exists:depot_racks,id',
            'start_date'      => 'required|date',
            'duration_months' => 'required|in:3,6,12',
            'quantity'        => 'required|integer|min:1',
            'seller_remarks'  => 'nullable|string|max:1000',
        ]);

        $rack = DepotRack::where('id', $validated['depot_rack_id'])
            ->where('status', 'active')
            ->firstOrFail();

        $qty = (int) $validated['quantity'];
        if ($qty > $rack->available_units) {
            return back()->with('error', "Only {$rack->available_units} unit(s) are currently available in this depot.");
        }

        $durationMonths = (int) $validated['duration_months'];
        $monthlyRate = (float) $rack->amount_per_unit;
        $totalAmount = $qty * $monthlyRate * $durationMonths;
        $bookingNo = DepotRackBooking::generateBookingNo();

        $booking = DepotRackBooking::create([
            'booking_no'      => $bookingNo,
            'seller_id'       => $seller->id,
            'owner_type'      => $rack->owner_type,
            'owner_id'        => $rack->owner_id,
            'depot_rack_id'   => $rack->id,
            'unit_type'       => $rack->unit_type,
            'quantity'        => $qty,
            'duration_months' => $durationMonths,
            'start_date'      => $validated['start_date'],
            'expiry_date'     => Carbon::parse($validated['start_date'])->addMonths($durationMonths)->format('Y-m-d'),
            'monthly_rate'    => $monthlyRate,
            'total_amount'    => $totalAmount,
            'status'          => 'Pending Payment Verification',
            'seller_remarks'  => $validated['seller_remarks'] ?? null,
            'request_date'    => now(),
        ]);

        // Redirect directly to Payment Submission Page
        return redirect()->route('vendor.rack-bookings.payment', $booking->id)
            ->with('success', 'Booking details saved! Please complete payment to submit for Admin verification.');
    }

    public function paymentPage($id)
    {
        $seller = Auth::guard('vendor')->user();
        $booking = DepotRackBooking::where('id', $id)->where('seller_id', $seller->id)->with(['owner', 'depotRack'])->firstOrFail();

        $bankName      = SystemSetting::getVal('admin_bank_name', 'HDFC Bank');
        $accountName   = SystemSetting::getVal('admin_account_name', 'ASC DISTRIBUTOR PVT LTD');
        $accountNumber = SystemSetting::getVal('admin_account_number', '50200089652670');
        $ifscCode      = SystemSetting::getVal('admin_ifsc_code', 'HDFC0000886');
        $bankBranch    = SystemSetting::getVal('admin_bank_branch', 'Naraina Plaza, Sec A, Rohini, Delhi');
        $upiId         = SystemSetting::getVal('admin_upi_id', 'ascdistributor@hdfcbank');

        $qrCodeRaw = SystemSetting::getVal('admin_qr_code');
        if ($qrCodeRaw) {
            if (filter_var($qrCodeRaw, FILTER_VALIDATE_URL)) {
                $qrCode = $qrCodeRaw;
            } elseif (str_starts_with($qrCodeRaw, 'storage/')) {
                $qrCode = asset($qrCodeRaw);
            } else {
                $qrCode = asset('storage/' . $qrCodeRaw);
            }
        } else {
            $qrCode = asset('admin-assets/images/qr-code.png');
        }

        return view('vendor.rack-bookings.payment', compact('booking', 'bankName', 'accountName', 'accountNumber', 'ifscCode', 'bankBranch', 'upiId', 'qrCode'));
    }

    public function submitPayment(Request $request, $id)
    {
        $seller = Auth::guard('vendor')->user();
        $booking = DepotRackBooking::where('id', $id)->where('seller_id', $seller->id)->firstOrFail();

        $validated = $request->validate([
            'payment_method' => 'nullable|string|max:100',
            'payment_utr'    => 'required|string|max:100',
            'payment_slip'   => 'nullable|image|max:2048',
        ]);

        $slipUrl = $booking->payment_slip;
        if ($request->hasFile('payment_slip')) {
            $slipUrl = $this->uploadFileToService($request->file('payment_slip'), 'payment_slips');
        }

        $booking->update([
            'payment_method' => $validated['payment_method'] ?? 'Bank Transfer / UPI',
            'payment_utr'    => $validated['payment_utr'],
            'payment_slip'   => $slipUrl,
            'status'         => 'Pending Payment Verification',
            'request_date'   => now(),
        ]);

        return redirect()->route('vendor.rack-bookings.index')
            ->with('success', "Payment details for Booking #{$booking->booking_no} submitted successfully! Your request is currently under Admin Payment Verification.");
    }
}
