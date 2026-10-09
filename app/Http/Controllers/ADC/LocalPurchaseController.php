<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\AdcInventory;
use App\Models\AdcLocalProduct;
use App\Models\AdcLocalPurchase;
use App\Models\AdcLocalPurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LocalPurchaseController extends Controller
{
    public function index(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $search = $request->get('search');
        $startDate = $request->get('start_date') ?: $request->get('date_from');
        $endDate = $request->get('end_date') ?: $request->get('date_to');

        $query = AdcLocalPurchase::where('adc_id', $adcId)
            ->with(['items.localProduct']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_no', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        if ($startDate) {
            $query->whereDate('purchase_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('purchase_date', '<=', $endDate);
        }

        $purchases = $query->latest('purchase_date')->latest('id')->paginate(15)->withQueryString();

        return view('adc.local-purchases.index', compact('purchases', 'search', 'startDate', 'endDate'));
    }

    public function create(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $localProducts = AdcLocalProduct::where('adc_id', $adcId)
            ->with(['category', 'brand'])
            ->orderBy('product_name')
            ->get();

        $selectedProductId = $request->get('local_product_id');

        return view('adc.local-purchases.create', compact('localProducts', 'selectedProductId'));
    }

    public function store(Request $request)
    {
        $adcId = Auth::guard('adc')->id();

        $request->validate([
            'purchase_date'            => 'required|date',
            'supplier_name'            => 'required|string|max:255',
            'supplier_mobile'          => 'nullable|string|max:20',
            'invoice_number'           => 'nullable|string|max:100',
            'invoice_date'             => 'nullable|date',
            'remarks'                  => 'nullable|string',

            'items'                    => 'required|array|min:1',
            'items.*.local_product_id' => 'required|string',
            'items.*.quantity'         => 'required|numeric|min:0.001',
            'items.*.mrp'              => 'required|numeric|min:0',
            'items.*.landing_cost'     => 'nullable|numeric|min:0',
            'items.*.csp'              => 'required|numeric|min:0',
            'items.*.purchase_price'   => 'nullable|numeric|min:0',
            'items.*.msp'              => 'nullable|numeric|min:0',
            'items.*.batch_number'     => 'nullable|string|max:100',
            'items.*.mfg_date'         => 'nullable|date',
            'items.*.expire_date'      => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $purchaseNo = AdcLocalPurchase::generatePurchaseNo();
            $totalAmount = 0;

            foreach ($request->items as $itemData) {
                $qty         = (float) $itemData['quantity'];
                $mrp         = (float) $itemData['mrp'];
                $landingCost = (float) ($itemData['landing_cost'] ?? 0);
                $cost        = (float) (isset($itemData['purchase_price']) && (float)$itemData['purchase_price'] > 0
                                ? $itemData['purchase_price']
                                : ($landingCost > 0 ? $landingCost : $mrp));
                $totalAmount += ($cost * $qty);
            }

            $purchase = AdcLocalPurchase::create([
                'adc_id'                    => $adcId,
                'purchase_no'               => $purchaseNo,
                'purchase_date'             => $request->purchase_date,
                'supplier_name'             => $request->supplier_name,
                'supplier_mobile'           => $request->supplier_mobile,
                'invoice_number'            => $request->invoice_number,
                'invoice_date'              => $request->invoice_date,
                'freight_charges'           => 0,
                'loading_unloading_charges' => 0,
                'other_charges'             => 0,
                'total_amount'              => $totalAmount,
                'remarks'                   => $request->remarks,
            ]);

            foreach ($request->items as $itemData) {
                $productId     = $itemData['local_product_id'];
                $qty           = (float) $itemData['quantity'];
                $mrp           = (float) $itemData['mrp'];
                $landingCost   = (float) ((isset($itemData['landing_cost']) && (float)$itemData['landing_cost'] > 0) ? $itemData['landing_cost'] : $mrp);
                $purchasePrice = (float) (isset($itemData['purchase_price']) && (float)$itemData['purchase_price'] > 0
                                    ? $itemData['purchase_price']
                                    : $landingCost);
                $csp           = (float) ($itemData['csp'] ?? 0);
                if ($csp <= 0 && $mrp > 0 && $purchasePrice > 0) {
                    $csp = round($purchasePrice + (($mrp - $purchasePrice) / 2), 2);
                }
                $msp           = (float) (isset($itemData['msp']) && (float)$itemData['msp'] > 0
                                    ? $itemData['msp']
                                    : $csp);

                if (str_starts_with($productId, 'INV_')) {
                    $invId = (int) str_replace('INV_', '', $productId);
                    $inv = AdcInventory::where('adc_id', $adcId)->findOrFail($invId);

                    $product = AdcLocalProduct::firstOrCreate(
                        ['adc_id' => $adcId, 'product_name' => $inv->product_name],
                        [
                            'local_product_code' => AdcLocalProduct::generateLocalProductCode(),
                            'category_id'        => $inv->product?->category_id ?: (Category::first()?->id ?: 1),
                            'hsn_code'           => $inv->hsn_code ?: ($inv->product?->hsn_code ?? 'N/A'),
                            'gst_rate'           => $inv->gst_rate ?: 0,
                            'unit_of_measure'    => $inv->unit_of_measure ?: 'Piece',
                            'barcode'            => $inv->barcode,
                            'status'             => 'Active',
                        ]
                    );
                } else {
                    $product = AdcLocalProduct::where('adc_id', $adcId)->findOrFail($productId);
                }

                // Formula: CSP Margin (%) = ((MRP - CSP) / MRP) * 100
                $cspMargin = $mrp > 0 ? round((($mrp - $csp) / $mrp) * 100, 2) : 0;

                // Create Inventory record for ADC
                $inventory = AdcInventory::create([
                    'adc_id'               => $adcId,
                    'inventory_source'     => 'LOCAL',
                    'product_id'           => null,
                    'adc_local_product_id' => $product->id,
                    'product_name'         => $product->product_name,
                    'product_code'         => $product->local_product_code,
                    'barcode'              => $product->barcode,
                    'unit_of_measure'      => $product->unit_of_measure,
                    'quantity'             => (int) $qty,
                    'reserved_quantity'    => 0,
                    'sold_quantity'        => 0,
                    'returned_quantity'    => 0,
                    'mrp'                  => $mrp,
                    'purchase_price'       => $purchasePrice,
                    'landing_cost'         => $landingCost,
                    'msp'                  => $msp,
                    'adc_price'            => $csp,
                    'csp'                  => $csp,
                    'adc_margin'           => $cspMargin,
                    'csp_margin'           => $cspMargin,
                    'gst_rate'             => $product->gst_rate ?? 0,
                    'batch_number'         => $itemData['batch_number'] ?? null,
                    'mfg_date'             => $itemData['mfg_date'] ?? null,
                    'expire_date'          => $itemData['expire_date'] ?? null,
                    'reference_type'       => 'LOCAL_PURCHASE',
                    'reference_id'         => $purchase->id,
                    'pos_status'           => 'Active',
                ]);

                // Create Local Purchase Item
                AdcLocalPurchaseItem::create([
                    'adc_local_purchase_id' => $purchase->id,
                    'adc_local_product_id'  => $product->id,
                    'quantity'              => (int) $qty,
                    'purchase_price'        => $purchasePrice,
                    'freight_share'         => 0,
                    'loading_share'         => 0,
                    'other_share'           => 0,
                    'landing_cost'          => $landingCost,
                    'mrp'                   => $mrp,
                    'csp'                   => $csp,
                    'msp'                   => $msp,
                    'gst_rate'              => $product->gst_rate ?? 0,
                    'batch_number'          => $itemData['batch_number'] ?? null,
                    'mfg_date'              => $itemData['mfg_date'] ?? null,
                    'expire_date'           => $itemData['expire_date'] ?? null,
                    'adc_inventory_id'      => $inventory->id,
                ]);

                // Mark product active
                if ($product->status !== 'Active') {
                    $product->update(['status' => 'Active']);
                }
            }

            DB::commit();

            return redirect()->route('adc.local-purchases.show', $purchase->id)
                ->with('success', "Local Purchase Invoice #{$purchaseNo} recorded and Stock-In performed successfully.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to record local purchase: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $adcId = Auth::guard('adc')->id();
        $purchase = AdcLocalPurchase::where('adc_id', $adcId)
            ->with(['items.localProduct.category', 'items.localProduct.brand'])
            ->findOrFail($id);

        return view('adc.local-purchases.show', compact('purchase'));
    }
}
