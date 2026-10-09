<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DdcInventory;
use App\Models\DdcLocalProduct;
use App\Models\DdcLocalPurchase;
use App\Models\DdcLocalPurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LocalPurchaseController extends Controller
{
    public function index(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $search = $request->get('search');
        $startDate = $request->get('start_date') ?: $request->get('date_from');
        $endDate = $request->get('end_date') ?: $request->get('date_to');

        $query = DdcLocalPurchase::where('ddc_id', $ddcId)
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

        return view('ddc.local-purchases.index', compact('purchases', 'search', 'startDate', 'endDate'));
    }

    public function create(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $localProducts = DdcLocalProduct::where('ddc_id', $ddcId)
            ->with(['category', 'brand'])
            ->orderBy('product_name')
            ->get();

        $selectedProductId = $request->get('local_product_id');

        return view('ddc.local-purchases.create', compact('localProducts', 'selectedProductId'));
    }

    public function store(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();

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
            'items.*.purchase_price'   => 'required|numeric|min:0',
            'items.*.landing_cost'     => 'nullable|numeric|min:0',
            'items.*.mrp'              => 'required|numeric|min:0',
            'items.*.csp'              => 'required|numeric|min:0',
            'items.*.msp'              => 'required|numeric|min:0',
            'items.*.batch_number'     => 'nullable|string|max:100',
            'items.*.mfg_date'         => 'nullable|date',
            'items.*.expire_date'      => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $purchaseNo = DdcLocalPurchase::generatePurchaseNo($ddcId);
            $totalAmount = 0;

            foreach ($request->items as $itemData) {
                $qty           = (float) $itemData['quantity'];
                $purchasePrice = (float) $itemData['purchase_price'];
                $totalAmount  += ($purchasePrice * $qty);
            }

            $purchase = DdcLocalPurchase::create([
                'ddc_id'                    => $ddcId,
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
                $purchasePrice = (float) $itemData['purchase_price'];
                $landingCost   = (float) ((isset($itemData['landing_cost']) && (float)$itemData['landing_cost'] > 0) ? $itemData['landing_cost'] : $purchasePrice);
                $mrp           = (float) $itemData['mrp'];
                $csp           = (float) $itemData['csp'];
                $msp           = (float) $itemData['msp'];

                if (str_starts_with($productId, 'INV_')) {
                    $invId = (int) str_replace('INV_', '', $productId);
                    $inv = DdcInventory::where('ddc_id', $ddcId)->findOrFail($invId);

                    $product = DdcLocalProduct::firstOrCreate(
                        ['ddc_id' => $ddcId, 'product_name' => $inv->product_name],
                        [
                            'local_product_code' => DdcLocalProduct::generateLocalProductCode(),
                            'category_id'        => $inv->product?->category_id ?: (Category::first()?->id ?: 1),
                            'hsn_code'           => $inv->hsn_code ?: ($inv->product?->hsn_code ?? 'N/A'),
                            'gst_rate'           => $inv->gst_rate ?: 0,
                            'unit_of_measure'    => $inv->unit_of_measure ?: 'Piece',
                            'barcode'            => $inv->barcode,
                            'status'             => 'Active',
                        ]
                    );
                } else {
                    $product = DdcLocalProduct::where('ddc_id', $ddcId)->findOrFail($productId);
                }

                // Create Inventory record with pos_status = 'Inactive' (Inventory Only)
                $inventory = DdcInventory::create([
                    'ddc_id'               => $ddcId,
                    'inventory_source'     => 'LOCAL',
                    'product_id'           => null,
                    'ddc_local_product_id' => $product->id,
                    'product_name'         => $product->product_name,
                    'product_code'         => $product->local_product_code,
                    'barcode'              => $product->barcode,
                    'unit_of_measure'      => $product->unit_of_measure,
                    'quantity'             => $qty,
                    'reserved_quantity'    => 0,
                    'sold_quantity'        => 0,
                    'returned_quantity'    => 0,
                    'mrp'                  => $mrp,
                    'purchase_price'       => $purchasePrice,
                    'landing_cost'         => $landingCost,
                    'csp'                  => $csp,
                    'msp'                  => $msp,
                    'gst_rate'             => (float) ($product->gst_rate ?? 0),
                    'batch_number'         => $itemData['batch_number'] ?? null,
                    'mfg_date'             => $itemData['mfg_date'] ?? null,
                    'expire_date'          => $itemData['expire_date'] ?? null,
                    'reference_type'       => 'Local Purchase',
                    'reference_id'         => $purchase->id,
                    'pos_status'           => 'Inactive', // Default Inventory Only
                ]);

                // Create Purchase Item
                DdcLocalPurchaseItem::create([
                    'ddc_local_purchase_id' => $purchase->id,
                    'ddc_local_product_id'  => $product->id,
                    'quantity'              => $qty,
                    'purchase_price'        => $purchasePrice,
                    'freight_share'         => 0,
                    'loading_share'         => 0,
                    'other_share'           => 0,
                    'landing_cost'          => $landingCost,
                    'mrp'                   => $mrp,
                    'csp'                   => $csp,
                    'msp'                   => $msp,
                    'gst_rate'              => $product->gst_rate,
                    'batch_number'          => $itemData['batch_number'] ?? null,
                    'mfg_date'              => $itemData['mfg_date'] ?? null,
                    'expire_date'           => $itemData['expire_date'] ?? null,
                    'ddc_inventory_id'      => $inventory->id,
                ]);

                // Mark product as Active in Local Master
                $product->status = 'Active';
                $product->save();
            }

            DB::commit();

            return redirect()
                ->route('ddc.local-purchases.index')
                ->with('success', "Regional Stock In recorded successfully with " . count($request->items) . " item(s). Purchase No: {$purchaseNo}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to record Stock In: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $ddcId = Auth::guard('ddc')->id();
        $purchase = DdcLocalPurchase::where('ddc_id', $ddcId)
            ->with(['items.localProduct.category', 'items.localProduct.brand'])
            ->findOrFail($id);

        return view('ddc.local-purchases.show', compact('purchase'));
    }
}
