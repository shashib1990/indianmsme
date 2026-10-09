<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'purchase_order_id',
        'adc_order_id',
        'pos_order_id',
        'type',
        'supplier_gstin',
        'invoice_number',
        'financial_year',
        'serial_number',
        'invoice_date',
        'transporter_name',
        'vehicle_number',
        'transporter_gst',
        'eway_bill_number',
        'invoice_file',
        'ddc_invoice_number',
        'ddc_invoice_file',
        'ddc_invoice_date',
        'adc_invoice_number',
        'adc_invoice_file',
        'adc_invoice_date',
        'bill_to_name',
        'bill_to_address',
        'bill_to_district',
        'bill_to_state',
        'bill_to_pincode',
        'bill_to_phone',
        'bill_to_gstin',
        'dispatch_from_name',
        'dispatch_from_address',
        'dispatch_from_district',
        'dispatch_from_state',
        'dispatch_from_pincode',
        'dispatch_from_phone',
        'dispatch_from_gstin',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'ddc_invoice_date' => 'date',
        'adc_invoice_date' => 'date',
        'serial_number' => 'integer',
    ];

    protected static function booted()
    {
        static::created(function ($invoice) {
            try {
                \App\Services\IncentiveHoldService::releaseHoldOnInvoice($invoice);
            } catch (\Throwable $th) {
                \Illuminate\Support\Facades\Log::error('Incentive release on invoice failed: ' . $th->getMessage());
            }
        });
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function adcOrder()
    {
        return $this->belongsTo(AdcOrder::class, 'adc_order_id');
    }

    public function posOrder()
    {
        return $this->type === 'adc_pos_retail' 
            ? $this->belongsTo(AdcPosOrder::class, 'pos_order_id')
            : $this->belongsTo(DdcPosOrder::class, 'pos_order_id');
    }

    public function ddcPosOrder()
    {
        return $this->belongsTo(DdcPosOrder::class, 'pos_order_id');
    }

    public function adcPosOrder()
    {
        return $this->belongsTo(AdcPosOrder::class, 'pos_order_id');
    }

    /**
     * Generate the next invoice number details for a given date and GST registration.
     * Rules:
     * - Invoice series maintained GST Registration wise (supplier_gstin) or Prefix.
     * - Restarts at 0001 every Financial Year (April 1st).
     * - Retail Format: DDB001/R/26-27/0001
     * - Wholesale Format: DDB001/26-27/0001
     */
    public static function generateNextInvoiceNumber($date = null, ?string $supplierGstin = null, ?string $prefix = 'INV', string $mode = 'wholesale'): array
    {
        $carbonDate = $date ? Carbon::parse($date) : now();
        $year = $carbonDate->year;
        
        // Indian financial year: April 1st to March 31st
        if ($carbonDate->month < 4) {
            $startYear = $year - 1;
            $endYear = $year;
        } else {
            $startYear = $year;
            $endYear = $year + 1;
        }
        
        $fyStr = sprintf('%02d-%02d', $startYear % 100, $endYear % 100);
        $basePrefix = !empty($prefix) ? $prefix : 'INV';

        $normalizedMode = strtolower($mode);
        if ($normalizedMode === 'retail') {
            $seriesPrefix = $basePrefix . '/R/' . $fyStr;
        } else {
            $seriesPrefix = $basePrefix . '/' . $fyStr;
        }

        $gstinVal = !empty($supplierGstin) ? $supplierGstin : 'N/A';

        // GST-wise sequence query: Sequence is maintained per Supplier GSTIN per Financial Year
        $query = self::where('financial_year', $fyStr)
            ->where('supplier_gstin', $gstinVal)
            ->where('invoice_number', 'like', $seriesPrefix . '/%');

        $maxSerial = $query->max('serial_number') ?? 0;
        if (!$maxSerial) {
            $lastInv = (clone $query)->orderBy('id', 'desc')->first();
            if ($lastInv && preg_match('/\/(\d{4})$/', $lastInv->invoice_number, $matches)) {
                $maxSerial = (int) $matches[1];
            }
        }

        $nextSerial = $maxSerial + 1;
        $invoiceNumber = $seriesPrefix . '/' . sprintf('%04d', $nextSerial);

        // Fail-safe collision check loop for this specific GSTIN
        while (self::where('supplier_gstin', $gstinVal)->where('invoice_number', $invoiceNumber)->exists()) {
            $nextSerial++;
            $invoiceNumber = $seriesPrefix . '/' . sprintf('%04d', $nextSerial);
        }

        return [
            'invoice_number' => $invoiceNumber,
            'financial_year' => $fyStr,
            'serial_number'  => $nextSerial,
            'supplier_gstin' => $gstinVal,
            'invoice_date'   => $carbonDate->format('Y-m-d'),
        ];
    }

    /**
     * Extract bill to and dispatch from details for dynamic persistence.
     */
    public static function extractInvoiceDetails(PurchaseOrder $purchaseOrder): array
    {
        $ddc = $purchaseOrder->ddc;
        $seller = $purchaseOrder->seller;
        $adcOrder = $purchaseOrder->adcOrder;

        $isVirtualPo = ($purchaseOrder->fulfillment_type === 'virtual') || !empty($purchaseOrder->adc_order_id) || !empty($adcOrder);

        if ($isVirtualPo && $adcOrder) {
            $adc = $adcOrder->adc;
            $adcProfile = $adc?->adcProfile ?? $adc?->profile ?? null;

            $billToName     = $adcProfile?->company_name ?? ($adcProfile?->trade_name ?? ($adc?->name ?? 'ADC Canteen'));
            $billToAddress  = $adcOrder->shipping_address ?: ($adcProfile?->address ?? 'Address not available');
            $billToDistrict = $adcOrder->delivery_district ?: ($adcProfile?->district ?? ($adc?->district ?? null));
            $billToState    = $adcOrder->delivery_state ?: ($adcProfile?->state ?? ($adc?->state ?? null));
            $billToPincode  = $adcOrder->delivery_pincode ?: ($adcProfile?->pincode ?? ($adc?->pincode ?? null));
            $billToPhone    = $adcProfile?->official_phone ?? ($adcProfile?->phone ?? ($adc?->mobile ?? ($adc?->phone ?? null)));
            $billToGstin    = $adcProfile?->gst_number ?? 'N/A';
        } else {
            $ddcProfile = null;
            if ($ddc) {
                $ddcProfile = $ddc->profile ?? $ddc->ddcProfile ?? null;
            }

            $billToName     = $ddcProfile?->company_name ?? ($ddcProfile?->trade_name ?? ($ddc?->name ?? 'Physical DDC'));
            $billToAddress  = $ddcProfile?->address ?? 'Address not available';
            $billToDistrict = $ddcProfile?->district ?? ($ddc?->district ?? null);
            $billToState    = $ddcProfile?->state ?? ($ddc?->state ?? null);
            $billToPincode  = $ddcProfile?->pincode ?? ($ddc?->pincode ?? null);
            $billToPhone    = $ddcProfile?->official_phone ?? ($ddcProfile?->phone ?? ($ddc?->mobile ?? ($ddc?->phone ?? null)));
            $billToGstin    = $ddcProfile?->ddc_gst_number ?? ($ddcProfile?->adpl_gst_number ?? ($ddcProfile?->gst_number ?? 'N/A'));
        }

        $sellerProfile = null;
        if ($seller) {
            $sellerProfile = $seller->sellerProfile ?? null;
        }

        $supplierGstin = $sellerProfile?->gst_number ?? 'N/A';

        $dispatchStore = $purchaseOrder->pickupStore;
        if ($dispatchStore) {
            $dispatchName = $dispatchStore->pickup_store_name ?? ($dispatchStore->contact_person ?? 'Store Outlet');
            $dispatchAddress = $dispatchStore->address ?? ($sellerProfile?->address ?? 'Address not available');
            $dispatchDistrict = $dispatchStore->districtRelation?->name ?? ($dispatchStore->district ?? ($sellerProfile?->district ?? null));
            $dispatchState = $dispatchStore->stateRelation?->name ?? ($dispatchStore->state ?? ($sellerProfile?->state ?? null));
            $dispatchPincode = $dispatchStore->pincode ?? ($sellerProfile?->pincode ?? null);
            $dispatchPhone = $dispatchStore->mobile ?? ($sellerProfile?->alternate_mobile ?? ($seller?->mobile ?? null));
            $dispatchGstin = $dispatchStore->gst_number ?? ($sellerProfile?->gst_number ?? 'N/A');
        } else {
            $dispatchName = $sellerProfile?->company_name ?? ($seller?->name ?? null);
            $dispatchAddress = $sellerProfile?->address ?? ($seller?->address ?? 'Address not available');
            $dispatchDistrict = $sellerProfile?->district ?? ($seller?->district ?? null);
            $dispatchState = $sellerProfile?->state ?? ($seller?->state ?? null);
            $dispatchPincode = $sellerProfile?->pincode ?? ($seller?->pincode ?? null);
            $dispatchPhone = $sellerProfile?->alternate_mobile ?? ($seller?->mobile ?? ($seller?->phone ?? null));
            $dispatchGstin = $supplierGstin;
        }

        return [
            'type' => $isVirtualPo ? 'adc_virtual_po' : 'ddc_po',
            'supplier_gstin' => $supplierGstin,
            'bill_to_name' => $billToName,
            'bill_to_address' => $billToAddress,
            'bill_to_district' => $billToDistrict,
            'bill_to_state' => $billToState,
            'bill_to_pincode' => $billToPincode,
            'bill_to_phone' => $billToPhone,
            'bill_to_gstin' => $billToGstin,

            'dispatch_from_name' => $dispatchName,
            'dispatch_from_address' => $dispatchAddress,
            'dispatch_from_district' => $dispatchDistrict,
            'dispatch_from_state' => $dispatchState,
            'dispatch_from_pincode' => $dispatchPincode,
            'dispatch_from_phone' => $dispatchPhone,
            'dispatch_from_gstin' => $dispatchGstin,
        ];
    }

    /**
     * Extract bill to and dispatch from details for ADC orders.
     */
    public static function extractAdcInvoiceDetails(AdcOrder $adcOrder): array
    {
        $adc = $adcOrder->adc;
        $adcProfile = $adc?->adcProfile ?? $adc?->profile ?? null;

        $dispatchName = null;
        $dispatchAddress = null;
        $dispatchDistrict = null;
        $dispatchState = null;
        $dispatchPincode = null;
        $dispatchPhone = null;
        $dispatchGstin = 'N/A';

        $isPhysicalDdcOrder = ($adcOrder->fulfillment_type === 'physical') || !empty($adcOrder->selected_ddc_id) || !empty($adcOrder->assigned_ddc_id);

        if ($isPhysicalDdcOrder) {
            // Physical DDC Order -> Dispatch From is the Physical DDC
            $ddc = $adcOrder->selectedDdc ?? $adcOrder->assignedDdc ?? $adcOrder->virtualDdc;
            if (!$ddc) {
                $ddcId = $adcOrder->items()->pluck('ddc_id')->filter()->first();
                if ($ddcId) {
                    $ddc = User::with(['profile', 'ddcProfile'])->find($ddcId);
                }
            }
            $ddcProfile = $ddc?->ddcProfile ?? $ddc?->profile ?? null;
            $dispatchName = $ddcProfile?->company_name ?? ($ddcProfile?->trade_name ?? ($ddc?->name ?? 'Physical DDC'));
            $dispatchAddress = $ddcProfile?->address ?? ($ddcProfile?->ddc_address ?? 'Address not available');
            $dispatchDistrict = $ddcProfile?->district ?? ($ddc?->district ?? null);
            $dispatchState = $ddcProfile?->state ?? ($ddc?->state ?? null);
            $dispatchPincode = $ddcProfile?->pincode ?? ($ddc?->pincode ?? null);
            $dispatchPhone = $ddcProfile?->official_phone ?? ($ddcProfile?->phone ?? ($ddc?->mobile ?? null));
            $dispatchGstin = $ddcProfile?->ddc_gst_number ?? ($ddcProfile?->adpl_gst_number ?? ($ddcProfile?->gst_number ?? 'N/A'));
        } else {
            // Virtual DDC Order -> Dispatch From is the Seller / Vendor or Pickup Store
            $poId = $adcOrder->items()->pluck('purchase_order_id')->filter()->first();
            $po = $poId ? PurchaseOrder::with(['seller.sellerProfile', 'pickupStore.districtRelation', 'pickupStore.stateRelation', 'ddc.profile', 'ddc.ddcProfile'])->find($poId) : null;

            if ($po) {
                if ($po->pickupStore) {
                    $store = $po->pickupStore;
                    $dispatchName = $store->pickup_store_name ?? ($store->contact_person ?? 'Store Outlet');
                    $dispatchAddress = $store->address ?? 'Address not available';
                    $dispatchDistrict = $store->districtRelation?->name ?? ($store->district ?? null);
                    $dispatchState = $store->stateRelation?->name ?? ($store->state ?? null);
                    $dispatchPincode = $store->pincode ?? null;
                    $dispatchPhone = $store->mobile ?? ($store->contact_person ?? null);
                    $dispatchGstin = $store->gst_number ?? 'N/A';
                } elseif ($po->seller) {
                    $seller = $po->seller;
                    $sellerProfile = $seller->sellerProfile ?? null;
                    $dispatchName = $sellerProfile?->company_name ?? ($seller->name ?? 'Seller');
                    $dispatchAddress = $sellerProfile?->address ?? ($seller->address ?? 'Address not available');
                    $dispatchDistrict = $sellerProfile?->district ?? ($seller->district ?? null);
                    $dispatchState = $sellerProfile?->state ?? ($seller->state ?? null);
                    $dispatchPincode = $sellerProfile?->pincode ?? ($seller->pincode ?? null);
                    $dispatchPhone = $sellerProfile?->alternate_mobile ?? ($seller->mobile ?? ($seller->phone ?? null));
                    $dispatchGstin = $sellerProfile?->gst_number ?? 'N/A';
                }
            }

            if (!$dispatchName) {
                $sellerId = $adcOrder->items()->pluck('seller_id')->filter()->first();
                if ($sellerId) {
                    $seller = User::with('sellerProfile')->find($sellerId);
                    if ($seller) {
                        $sellerProfile = $seller->sellerProfile ?? null;
                        $dispatchName = $sellerProfile?->company_name ?? ($seller->name ?? 'Seller');
                        $dispatchAddress = $sellerProfile?->address ?? 'Address not available';
                        $dispatchDistrict = $sellerProfile?->district ?? ($seller->district ?? null);
                        $dispatchState = $sellerProfile?->state ?? ($seller->state ?? null);
                        $dispatchPincode = $sellerProfile?->pincode ?? ($seller->pincode ?? null);
                        $dispatchPhone = $sellerProfile?->alternate_mobile ?? ($seller->mobile ?? null);
                        $dispatchGstin = $sellerProfile?->gst_number ?? 'N/A';
                    }
                }
            }

            // Fallback to DDC if no seller or pickup store found
            if (!$dispatchName) {
                $ddc = $adcOrder->virtualDdc ?? $adcOrder->selectedDdc ?? $adcOrder->assignedDdc;
                if (!$ddc) {
                    $firstDdcId = $adcOrder->items()->pluck('virtual_ddc_id')->filter()->first() ?? $adcOrder->items()->pluck('ddc_id')->filter()->first();
                    if ($firstDdcId) {
                        $ddc = User::with(['profile', 'ddcProfile'])->find($firstDdcId);
                    }
                }
                $ddcProfile = $ddc?->ddcProfile ?? $ddc?->profile ?? null;
                $dispatchName = $ddcProfile?->company_name ?? ($ddc?->name ?? 'DDC Hub');
                $dispatchAddress = $ddcProfile?->adpl_address ?? ($ddcProfile?->ddc_address ?? ($ddcProfile?->address ?? 'DDC Address'));
                $dispatchDistrict = $ddcProfile?->adpl_district ?? ($ddcProfile?->district ?? ($ddc?->district ?? null));
                $dispatchState = $ddcProfile?->adpl_state ?? ($ddcProfile?->state ?? ($ddc?->state ?? null));
                $dispatchPincode = $ddcProfile?->adpl_pincode ?? ($ddcProfile?->ddc_pincode ?? ($ddcProfile?->pincode ?? null));
                $dispatchPhone = $ddcProfile?->official_phone ?? ($ddcProfile?->phone ?? ($ddc?->mobile ?? null));
                $dispatchGstin = $ddcProfile?->adpl_gst_number ?? ($ddcProfile?->ddc_gst_number ?? ($ddcProfile?->gst_number ?? 'N/A'));
            }
        }

        return [
            'type' => 'adc_order',
            'supplier_gstin' => $dispatchGstin,
            'adc_order_id' => $adcOrder->id,
            'adc_invoice_number' => $adcOrder->received_invoice_number,
            'adc_invoice_date' => $adcOrder->received_invoice_date,
            'adc_invoice_file' => $adcOrder->received_invoice_file,
            'bill_to_name' => $adcProfile?->company_name ?? ($adcProfile?->trade_name ?? ($adc?->name ?? 'ADC Canteen')),
            'bill_to_address' => $adcOrder->shipping_address ?: ($adcProfile?->address ?? 'Address not available'),
            'bill_to_district' => $adcOrder->delivery_district ?: ($adcProfile?->district ?? ($adc?->district ?? null)),
            'bill_to_state' => $adcOrder->delivery_state ?: ($adcProfile?->state ?? ($adc?->state ?? null)),
            'bill_to_pincode' => $adcOrder->delivery_pincode ?: ($adcProfile?->pincode ?? ($adc?->pincode ?? null)),
            'bill_to_phone' => $adcProfile?->official_phone ?? ($adcProfile?->phone ?? ($adc?->mobile ?? ($adc?->phone ?? null))),
            'bill_to_gstin' => $adcProfile?->gst_number ?? 'N/A',

            'dispatch_from_name' => $dispatchName,
            'dispatch_from_address' => $dispatchAddress,
            'dispatch_from_district' => $dispatchDistrict,
            'dispatch_from_state' => $dispatchState,
            'dispatch_from_pincode' => $dispatchPincode,
            'dispatch_from_phone' => $dispatchPhone,
            'dispatch_from_gstin' => $dispatchGstin,
        ];
    }
}
