<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\DepotRack;
use App\Models\DepotRackImage;
use App\Models\DepotRackBooking;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RackManagementController extends Controller
{
    public function index(Request $request)
    {
        $adcUser = Auth::guard('adc')->user();
        $adcId = $adcUser->id;

        // Auto release any expired bookings first
        DepotRackBooking::releaseExpiredBookings();

        // Admin Configured Pricing (ADC Rack & Shelf)
        $adcRackAmount  = (float) SystemSetting::getVal('adc_rack_amount', 2000.00);
        $adcShelfAmount = (float) SystemSetting::getVal('adc_shelf_amount', 1000.00);

        $search = $request->input('search');

        $query = DepotRack::where('owner_type', 'adc')
            ->where('owner_id', $adcId)
            ->with(['bookings.seller']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('unit_type', 'like', "%{$search}%")
                  ->orWhere('depot_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $racks = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // All store photos for this ADC Depot
        $depotImagesList = DepotRackImage::where('owner_type', 'adc')
            ->where('owner_id', $adcId)
            ->get();

        $depotImages = $depotImagesList->keyBy('image_key');
        $predefinedSlots = DepotRackImage::getPredefinedSlots('adc');

        // ADC Allotments list
        $allotments = DepotRackBooking::where('owner_type', 'adc')
            ->where('owner_id', $adcId)
            ->with(['seller.sellerProfile', 'depotRack'])
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'allotments_page');

        return view('adc.racks.manage', compact(
            'racks', 'allotments', 'adcRackAmount', 'adcShelfAmount', 'search', 'adcUser',
            'depotImages', 'depotImagesList', 'predefinedSlots'
        ));
    }

    public function store(Request $request)
    {
        $adcUser = Auth::guard('adc')->user();

        $validated = $request->validate([
            'unit_type'   => 'required|in:RACK,SHELF',
            'total_units' => 'required|integer|min:1',
            'description' => 'nullable|string|max:1000',
        ]);

        $unitType = strtoupper($validated['unit_type']);
        $totalUnits = (int) $validated['total_units'];

        // Admin configured price per unit
        if ($unitType === 'RACK') {
            $amountPerUnit = (float) SystemSetting::getVal('adc_rack_amount', 2000.00);
        } else {
            $amountPerUnit = (float) SystemSetting::getVal('adc_shelf_amount', 1000.00);
        }

        $totalAmount = $totalUnits * $amountPerUnit;
        $depotCode = $adcUser->adc_code ?: ($adcUser->adcProfile?->adc_code ?: ($adcUser->adcProfile?->code ?: ($adcUser->code ?: ('ADC' . str_pad($adcUser->id, 3, '0', STR_PAD_LEFT)))));

        DepotRack::create([
            'owner_type'      => 'adc',
            'owner_id'        => $adcUser->id,
            'depot_code'      => $depotCode,
            'unit_type'       => $unitType,
            'total_units'     => $totalUnits,
            'available_units' => $totalUnits,
            'allotted_units'  => 0,
            'amount_per_unit' => $amountPerUnit,
            'total_amount'    => $totalAmount,
            'status'          => 'active',
            'description'     => $validated['description'] ?? null,
        ]);

        return back()->with('success', "{$totalUnits} {$unitType}(s) added successfully to Manage Rack List.");
    }

    public function toggleStatus($id)
    {
        $adcId = Auth::guard('adc')->id();
        $rack = DepotRack::where('id', $id)->where('owner_type', 'adc')->where('owner_id', $adcId)->firstOrFail();

        // Important Rule: Inactive option ONLY available if allotted_units == 0
        if ($rack->status === 'active' && $rack->allotted_units > 0) {
            return back()->with('error', "Cannot set {$rack->unit_type} to Inactive as {$rack->allotted_units} unit(s) are currently allotted to sellers.");
        }

        $newStatus = $rack->status === 'active' ? 'inactive' : 'active';
        $rack->status = $newStatus;
        $rack->save();

        return back()->with('success', "{$rack->unit_type} status updated to {$newStatus}.");
    }

    public function uploadDepotImages(Request $request)
    {
        $adcUser = Auth::guard('adc')->user();
        $predefinedSlots = DepotRackImage::getPredefinedSlots('adc');

        $uploadedCount = 0;

        foreach ($predefinedSlots as $key => $label) {
            if ($request->hasFile("images.{$key}")) {
                $file = $request->file("images.{$key}");
                try {
                    $url = $this->uploadFileToService($file, 'rack_images');
                } catch (\Exception $e) {
                    $filename = 'adc_depot_img_' . $key . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('uploads/rack_images', $filename, 'public');
                    $url = asset('storage/' . $path);
                }

                DepotRackImage::updateOrCreate(
                    [
                        'owner_type' => 'adc',
                        'owner_id'   => $adcUser->id,
                        'image_key'  => $key,
                    ],
                    [
                        'image_label'     => $label,
                        'image_url'       => $url,
                        'approval_status' => 'pending', // Sent to Admin for approval
                        'rejection_reason'=> null,
                    ]
                );

                $uploadedCount++;
            }
        }

        if ($uploadedCount > 0) {
            return back()->with('success', "{$uploadedCount} depot image(s) uploaded successfully! Submitted to Admin for approval.");
        }

        return back()->with('info', 'No new image files were selected for upload.');
    }
}
