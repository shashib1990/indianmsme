<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\DdcInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $ddc = Auth::guard('ddc')->user();
        $source = strtoupper($request->get('source', 'ALL'));
        if (!in_array($source, ['ALL', 'MSME', 'LOCAL'])) {
            $source = 'ALL';
        }

        $today = now()->toDateString();

        $query = DdcInventory::with(['product', 'localProduct', 'purchaseOrder'])
            ->where('ddc_id', $ddc->id)
            ->where(function ($q) use ($today) {
                $q->whereNull('expire_date')
                  ->orWhere('expire_date', '>=', $today);
            });

        if ($source !== 'ALL') {
            $query->where('inventory_source', $source);
        }

        $search = $request->input('search');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('purchaseOrder', function ($poQ) use ($search) {
                        $poQ->where('po_number', 'like', "%{$search}%");
                    });
            });
        }

        $expiryFilter = $request->get('expiry_filter');
        if ($expiryFilter === '1M') {
            $query->whereNotNull('expire_date')
                ->where('expire_date', '>=', $today)
                ->where('expire_date', '<=', now()->addDays(30)->toDateString());
        } elseif ($expiryFilter === '3M') {
            $query->whereNotNull('expire_date')
                ->where('expire_date', '>', now()->addDays(30)->toDateString())
                ->where('expire_date', '<=', now()->addDays(90)->toDateString());
        } elseif ($expiryFilter === '6M') {
            $query->whereNotNull('expire_date')
                ->where('expire_date', '>', now()->addDays(90)->toDateString())
                ->where('expire_date', '<=', now()->addDays(180)->toDateString());
        }

        $inventories = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('ddc.inventory.index', compact('inventories', 'search', 'source', 'expiryFilter'));
    }

    public function expiredProducts(Request $request)
    {
        $ddc = Auth::guard('ddc')->user();
        $source = strtoupper($request->get('source', 'ALL'));
        if (!in_array($source, ['ALL', 'MSME', 'LOCAL'])) {
            $source = 'ALL';
        }

        $today = now()->toDateString();

        $query = DdcInventory::with(['product', 'localProduct'])
            ->where('ddc_id', $ddc->id)
            ->whereNotNull('expire_date')
            ->where('expire_date', '<', $today);

        if ($source !== 'ALL') {
            $query->where('inventory_source', $source);
        }

        $search = $request->input('search');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%");
            });
        }

        $expiredItems = $query->orderBy('expire_date', 'desc')->paginate(20)->withQueryString();

        $totalExpiredCount = DdcInventory::where('ddc_id', $ddc->id)
            ->whereNotNull('expire_date')
            ->where('expire_date', '<', $today)
            ->count();

        $totalExpiredQuantity = (float) (DdcInventory::where('ddc_id', $ddc->id)
            ->whereNotNull('expire_date')
            ->where('expire_date', '<', $today)
            ->sum('quantity') ?? 0);

        $totalExpiredValue = (float) (DdcInventory::where('ddc_id', $ddc->id)
            ->whereNotNull('expire_date')
            ->where('expire_date', '<', $today)
            ->selectRaw('SUM(quantity * mrp) as total_val')
            ->value('total_val') ?? 0);

        return view('ddc.inventory.expired-products', compact(
            'expiredItems',
            'search',
            'source',
            'totalExpiredCount',
            'totalExpiredQuantity',
            'totalExpiredValue'
        ));
    }

    public function togglePosStatus(Request $request, $id)
    {
        $ddc = Auth::guard('ddc')->user();

        $inv = DdcInventory::where('id', $id)
            ->where('ddc_id', $ddc->id)
            ->firstOrFail();

        $targetStatus = $inv->pos_status === 'Active' ? 'Inactive' : 'Active';

        if ($targetStatus === 'Active') {
            // Validation before enabling POS
            $errors = [];

            if ($inv->quantity <= 0) {
                $errors[] = 'Quantity must be greater than 0.';
            }
            if ((float) $inv->mrp <= 0) {
                $errors[] = 'MRP must be set and greater than 0.';
            }
            if ((float) $inv->csp <= 0) {
                $errors[] = 'CSP (Canteen Selling Price) must be set and greater than 0.';
            }
            if ((float) $inv->msp <= 0) {
                $errors[] = 'MSP (Wholesale Price) must be set and greater than 0.';
            }

            if ($inv->inventory_source === 'LOCAL') {
                $landingCost = (float) ($inv->landing_cost > 0 ? $inv->landing_cost : $inv->purchase_price);
                if ($landingCost <= 0) {
                    $errors[] = 'Landing Cost must be greater than 0.';
                }
            }

            if (!empty($errors)) {
                $msg = "Cannot enable POS for '{$inv->product_name}': " . implode(' ', $errors);
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->with('error', $msg);
            }
        }

        $inv->pos_status = $targetStatus;
        $inv->save();

        $statusMsg = $targetStatus === 'Active'
            ? "POS Status for '{$inv->product_name}' enabled successfully. It will now be visible in POS."
            : "POS Status for '{$inv->product_name}' disabled. Status set to Inventory Only.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $statusMsg,
                'pos_status' => $inv->pos_status,
            ]);
        }

        return back()->with('success', $statusMsg);
    }

    public function update(Request $request, $id)
    {
        $ddc = Auth::guard('ddc')->user();

        $inv = DdcInventory::where('id', $id)
            ->where('ddc_id', $ddc->id)
            ->firstOrFail();

        $validated = $request->validate([
            'mfg_date'    => 'nullable|date',
            'expire_date' => 'nullable|date',
            'msp'         => 'nullable|numeric|min:0',
            'csp'         => 'nullable|numeric|min:0',
        ]);

        $mrp = (float) ($inv->mrp ?? 0);
        $purchasePrice = (float) ($inv->purchase_price ?? 0);
        $landingCost = (float) ($inv->landing_cost > 0 ? $inv->landing_cost : $purchasePrice);

        if (array_key_exists('msp', $validated) && $validated['msp'] !== null) {
            $newMsp = (float) $validated['msp'];
            if ($mrp > 0 && $newMsp > $mrp) {
                return response()->json([
                    'success' => false,
                    'message' => 'MSP (₹' . number_format($newMsp, 2) . ') cannot be greater than MRP (₹' . number_format($mrp, 2) . ').',
                ], 422);
            }
            $inv->msp = $newMsp;
        }

        if (array_key_exists('csp', $validated) && $validated['csp'] !== null) {
            $newCsp = (float) $validated['csp'];
            if ($mrp > 0 && $newCsp > $mrp) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSP (₹' . number_format($newCsp, 2) . ') cannot be greater than MRP (₹' . number_format($mrp, 2) . ').',
                ], 422);
            }
            $inv->csp = $newCsp;
        }

        if (array_key_exists('mfg_date', $validated)) {
            $inv->mfg_date = $validated['mfg_date'];
        }
        if (array_key_exists('expire_date', $validated)) {
            $inv->expire_date = $validated['expire_date'];
        }

        // Recalculate Margins based on updated MSP & CSP
        $curMsp = (float) ($inv->msp ?? 0);
        $curCsp = (float) ($inv->csp ?? 0);

        // MSP Margin (ADC Margin) = ((MSP - Purchase Price) / MSP) * 100
        if ($curMsp > 0 && $landingCost > 0) {
            $adcMargin = round((($curMsp - $landingCost) / $curMsp) * 100, 2);
        } else {
            $adcMargin = 0;
        }

        // CSP Margin = ((MRP - CSP) / MRP) * 100
        $cspMargin = ($mrp > 0 && $curCsp > 0) ? round((($mrp - $curCsp) / $mrp) * 100, 2) : 0;

        $inv->adc_margin = $adcMargin;
        $inv->margin = $adcMargin;
        $inv->csp_margin = $cspMargin;

        $inv->save();

        $today = now()->startOfDay();
        $freshInv = $inv->fresh();
        $expireDate = $freshInv->expire_date ? Carbon::parse($freshInv->expire_date)->startOfDay() : null;
        $daysLeft = $expireDate ? (int) floor($today->diffInDays($expireDate, false)) : null;
        $isExpired = $daysLeft !== null && $daysLeft < 0;
        $isNearExpiry = $daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 30;

        $expiryClass = $isExpired ? 'badge-expiry-danger' : ($isNearExpiry ? 'badge-expiry-warn' : 'badge-expiry-ok');

        $expiryLabel = 'Valid';
        $expiryIcon = 'solar:check-circle-bold';
        if ($isExpired) {
            $expiryLabel = 'Expired';
            $expiryIcon = 'solar:danger-circle-bold';
        } elseif ($isNearExpiry) {
            $expiryIcon = 'solar:clock-circle-bold';
            if ($daysLeft == 0) $expiryLabel = 'Today';
            elseif ($daysLeft == 1) $expiryLabel = '1 Day Left';
            else $expiryLabel = $daysLeft . ' Days Left';
        }

        return response()->json([
            'success'        => true,
            'message'        => 'Inventory updated successfully.',
            'inventory'      => $freshInv,
            'is_expired'     => $isExpired,
            'is_near_expiry' => $isNearExpiry,
            'days_left'      => $daysLeft,
            'expiry_class'   => $expiryClass,
            'expiry_label'   => $expiryLabel,
            'expiry_icon'    => $expiryIcon,
        ]);
    }

    public function missingBarcodes(Request $request)
    {
        $ddc = Auth::guard('ddc')->user();
        $source = strtoupper($request->get('source', 'ALL'));
        if (!in_array($source, ['ALL', 'MSME', 'LOCAL'])) {
            $source = 'ALL';
        }

        $baseQuery = DdcInventory::where('ddc_id', $ddc->id)
            ->where(function ($q) {
                $q->whereNull('barcode')
                  ->orWhere('barcode', '')
                  ->orWhere('barcode', 'N/A')
                  ->orWhere('barcode', '—')
                  ->orWhere('barcode', '0');
            });

        // Metrics stats
        $totalMissingCount = (clone $baseQuery)->count();
        $msmeMissingCount  = (clone $baseQuery)->where('inventory_source', 'MSME')->count();
        $localMissingCount = (clone $baseQuery)->where('inventory_source', 'LOCAL')->count();

        $query = DdcInventory::with(['product', 'localProduct'])
            ->where('ddc_id', $ddc->id)
            ->where(function ($q) {
                $q->whereNull('barcode')
                  ->orWhere('barcode', '')
                  ->orWhere('barcode', 'N/A')
                  ->orWhere('barcode', '—')
                  ->orWhere('barcode', '0');
            });

        if ($source !== 'ALL') {
            $query->where('inventory_source', $source);
        }

        $search = $request->input('search');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%")
                  ->orWhere('batch_number', 'like', "%{$search}%");
            });
        }

        $inventories = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $summaryStats = [
            'total_missing' => $totalMissingCount,
            'msme_missing'  => $msmeMissingCount,
            'local_missing' => $localMissingCount,
        ];

        return view('ddc.inventory.missing-barcodes', compact('inventories', 'search', 'source', 'summaryStats'));
    }

    public function updateBarcode(Request $request, $id)
    {
        $ddc = Auth::guard('ddc')->user();

        $request->validate([
            'barcode' => 'required|string|max:100',
        ]);

        $inv = DdcInventory::where('id', $id)
            ->where('ddc_id', $ddc->id)
            ->firstOrFail();

        $newBarcode = trim($request->input('barcode'));

        // Update all inventory entries for the same product under this DDC
        if ($inv->product_id) {
            DdcInventory::where('ddc_id', $ddc->id)
                ->where('product_id', $inv->product_id)
                ->update(['barcode' => $newBarcode]);
            
            \App\Models\Product::where('id', $inv->product_id)
                ->where(function($q) {
                    $q->whereNull('barcode')->orWhere('barcode', '')->orWhere('barcode', 'N/A');
                })
                ->update(['barcode' => $newBarcode]);
        } elseif ($inv->ddc_local_product_id) {
            DdcInventory::where('ddc_id', $ddc->id)
                ->where('ddc_local_product_id', $inv->ddc_local_product_id)
                ->update(['barcode' => $newBarcode]);

            \App\Models\DdcLocalProduct::where('id', $inv->ddc_local_product_id)
                ->update(['barcode' => $newBarcode]);
        } else {
            $inv->barcode = $newBarcode;
            $inv->save();
        }

        $msg = "Barcode '{$newBarcode}' updated successfully for '{$inv->product_name}'.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'barcode' => $newBarcode,
            ]);
        }

        return back()->with('success', $msg);
    }

    public function bulkUpdateBarcodes(Request $request)
    {
        $ddc = Auth::guard('ddc')->user();
        $barcodes = $request->input('barcodes', []);

        $updatedCount = 0;
        foreach ($barcodes as $id => $barcode) {
            $barcodeVal = trim($barcode);
            if (empty($barcodeVal)) continue;

            $inv = DdcInventory::where('id', $id)
                ->where('ddc_id', $ddc->id)
                ->first();

            if ($inv) {
                if ($inv->product_id) {
                    DdcInventory::where('ddc_id', $ddc->id)
                        ->where('product_id', $inv->product_id)
                        ->update(['barcode' => $barcodeVal]);
                    \App\Models\Product::where('id', $inv->product_id)
                        ->where(function($q) {
                            $q->whereNull('barcode')->orWhere('barcode', '')->orWhere('barcode', 'N/A');
                        })
                        ->update(['barcode' => $barcodeVal]);
                } elseif ($inv->ddc_local_product_id) {
                    DdcInventory::where('ddc_id', $ddc->id)
                        ->where('ddc_local_product_id', $inv->ddc_local_product_id)
                        ->update(['barcode' => $barcodeVal]);
                    \App\Models\DdcLocalProduct::where('id', $inv->ddc_local_product_id)
                        ->update(['barcode' => $barcodeVal]);
                } else {
                    $inv->barcode = $barcodeVal;
                    $inv->save();
                }
                $updatedCount++;
            }
        }

        return back()->with('success', "{$updatedCount} product barcode(s) updated successfully.");
    }
}
