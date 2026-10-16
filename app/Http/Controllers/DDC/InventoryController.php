<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\DdcInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Build shared inventory query based on search and filters
     */
    private function buildInventoryQuery(Request $request)
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
                  ->orWhereDate('expire_date', '>', $today);
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
                ->whereDate('expire_date', '>', $today)
                ->whereDate('expire_date', '<=', now()->addDays(30)->toDateString());
        } elseif ($expiryFilter === '3M') {
            $query->whereNotNull('expire_date')
                ->whereDate('expire_date', '>', now()->addDays(30)->toDateString())
                ->whereDate('expire_date', '<=', now()->addDays(90)->toDateString());
        } elseif ($expiryFilter === '6M') {
            $query->whereNotNull('expire_date')
                ->whereDate('expire_date', '>', now()->addDays(90)->toDateString())
                ->whereDate('expire_date', '<=', now()->addDays(180)->toDateString());
        }

        return $query;
    }

    public function index(Request $request)
    {
        $source = strtoupper($request->get('source', 'ALL'));
        if (!in_array($source, ['ALL', 'MSME', 'LOCAL'])) {
            $source = 'ALL';
        }
        $search = $request->input('search');
        $expiryFilter = $request->get('expiry_filter');

        $query = $this->buildInventoryQuery($request);
        $inventories = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('ddc.inventory.index', compact('inventories', 'search', 'source', 'expiryFilter'));
    }

    /**
     * Export Inventory to CSV (Table fields only, direct clean data)
     */
    public function export(Request $request)
    {
        $ddc = Auth::guard('ddc')->user();
        $ddcProfile = $ddc?->ddcProfile ?: $ddc?->profile;
        $ddcCode = $ddcProfile?->ddc_code ?: ($ddc?->ddc_code ?: ('DDC' . str_pad($ddc?->id ?? 0, 3, '0', STR_PAD_LEFT)));

        $query = $this->buildInventoryQuery($request);
        $inventories = $query->orderBy('id', 'desc')->get();

        $filename = "DDC_Inventory_{$ddcCode}_" . date('Ymd_His') . ".csv";

        return response()->streamDownload(function () use ($inventories) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel support
            fputs($handle, "\xEF\xBB\xBF");

            $putCsv = function (array $fields) use ($handle) {
                fputcsv($handle, $fields, ',', '"', "\\");
            };

            // Exactly the Table Column Headers
            $putCsv([
                'S.No.',
                'POS Status',
                'Product Name',
                'Product Code',
                'Barcode',
                'Source',
                'Batch',
                'UOM',
                'Received',
                'Sold',
                'Returned',
                'Balance',
                'MRP',
                'Purchase Price',
                'ADC Price',
                'MSP',
                'CSP',
                'MSP Margin (%)',
                'CSP Margin (%)',
                'GST (%)',
                'Mfg Date',
                'Expire Date',
                'Status',
            ]);

            $today = \Carbon\Carbon::today()->startOfDay();

            $formatQty = function ($qty) {
                $v = (float) $qty;
                if (abs($v - round($v)) < 0.0001) {
                    return (string) ((int) round($v));
                }
                return rtrim(rtrim(number_format($v, 3, '.', ''), '0'), '.');
            };

            foreach ($inventories as $idx => $inv) {
                $mrpVal = (float) ($inv->mrp ?? 0);
                $purchasePriceVal = (float) ($inv->purchase_price ?? 0);
                $adcPriceVal = (float) ($inv->product?->adc_purchase_price ?? $inv->purchase_price ?? 0);
                $mspVal = (float) ($inv->msp ?? 0);
                $cspVal = (float) ($inv->csp ?? 0);

                // MSP Margin % = ((MSP - Purchase Price) / MSP) * 100
                $calcAdcMargin = $mspVal > 0 ? round((($mspVal - $purchasePriceVal) / $mspVal) * 100, 2) : 0;
                $adcMarginVal = $inv->adc_margin !== null ? (float) $inv->adc_margin : ($inv->margin !== null ? (float) $inv->margin : $calcAdcMargin);

                // CSP Margin % = ((CSP - MSP) / MSP) * 100
                $calcCspMargin = $mspVal > 0 ? round((($cspVal - $mspVal) / $mspVal) * 100, 2) : 0;
                $cspMarginVal = $inv->csp_margin !== null ? (float) $inv->csp_margin : $calcCspMargin;

                $soldQty = (float) ($inv->sold_quantity ?? 0);
                $returnedQty = (float) ($inv->returned_quantity ?? 0);
                $balanceQty = (float) ($inv->quantity ?? 0);
                $receivedQty = $balanceQty + $soldQty - $returnedQty;

                $uom = $inv->unit_of_measure ?? ($inv->product?->unit_of_measure ?? ($inv->localProduct?->unit_of_measure ?? 'Units'));
                $gstRateVal = (float) (
                    $inv->gst_rate
                    ?? $inv->product?->gst_rate
                    ?? $inv->localProduct?->gst_rate
                    ?? 0
                );

                $daysLeft = $inv->expire_date ? (int) floor($today->diffInDays(\Carbon\Carbon::parse($inv->expire_date)->startOfDay(), false)) : null;
                $expiryStatus = '';
                if ($inv->expire_date) {
                    if ($daysLeft !== null && $daysLeft < 0) {
                        $expiryStatus = 'Expired';
                    } elseif ($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 30) {
                        if ($daysLeft == 0) {
                            $expiryStatus = 'Today';
                        } elseif ($daysLeft == 1) {
                            $expiryStatus = '1 Day Left';
                        } else {
                            $expiryStatus = $daysLeft . ' Days Left';
                        }
                    } else {
                        $expiryStatus = 'Valid';
                    }
                }

                $putCsv([
                    $idx + 1,
                    $inv->pos_status ?? 'Active',
                    $inv->product_name ?? ($inv->product?->product_name ?? ''),
                    $inv->product_code ?? '',
                    $inv->barcode ?? '',
                    $inv->inventory_source ?? 'MSME',
                    $inv->batch_number ?? '',
                    $uom,
                    $formatQty($receivedQty),
                    $formatQty($soldQty),
                    $formatQty($returnedQty),
                    $formatQty($balanceQty),
                    number_format($mrpVal, 2, '.', ''),
                    number_format($purchasePriceVal, 2, '.', ''),
                    number_format($adcPriceVal, 2, '.', ''),
                    number_format($mspVal, 2, '.', ''),
                    number_format($cspVal, 2, '.', ''),
                    number_format($adcMarginVal, 2, '.', '') . '%',
                    number_format($cspMarginVal, 2, '.', '') . '%',
                    number_format($gstRateVal, 2, '.', '') . '%',
                    $inv->mfg_date ? \Carbon\Carbon::parse($inv->mfg_date)->format('d-m-Y') : '',
                    $inv->expire_date ? \Carbon\Carbon::parse($inv->expire_date)->format('d-m-Y') : '',
                    $expiryStatus,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
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
            ->whereDate('expire_date', '<=', $today);

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
            ->whereDate('expire_date', '<=', $today)
            ->count();

        $totalExpiredQuantity = (float) (DdcInventory::where('ddc_id', $ddc->id)
            ->whereNotNull('expire_date')
            ->whereDate('expire_date', '<=', $today)
            ->sum('quantity') ?? 0);

        $totalExpiredValue = (float) (DdcInventory::where('ddc_id', $ddc->id)
            ->whereNotNull('expire_date')
            ->whereDate('expire_date', '<=', $today)
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

            if ($inv->expire_date && Carbon::parse($inv->expire_date)->startOfDay()->lte(now()->startOfDay())) {
                $errors[] = 'Cannot enable POS for expired product (Expired on ' . Carbon::parse($inv->expire_date)->format('d M Y') . '). It has been quarantined.';
            }

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
            if ($inv->expire_date && Carbon::parse($inv->expire_date)->startOfDay()->lte(now()->startOfDay())) {
                $inv->pos_status = 'Inactive';
            }
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
        $isExpired = $daysLeft !== null && $daysLeft <= 0;
        $isNearExpiry = $daysLeft !== null && $daysLeft > 0 && $daysLeft <= 30;

        $expiryClass = $isExpired ? 'badge-expiry-danger' : ($isNearExpiry ? 'badge-expiry-warn' : 'badge-expiry-ok');

        $expiryLabel = 'Valid';
        $expiryIcon = 'solar:check-circle-bold';
        if ($isExpired) {
            $expiryLabel = 'Expired';
            $expiryIcon = 'solar:danger-circle-bold';
        } elseif ($isNearExpiry) {
            $expiryIcon = 'solar:clock-circle-bold';
            if ($daysLeft == 1) $expiryLabel = '1 Day Left';
            else $expiryLabel = $daysLeft . ' Days Left';
        }

        $respMessage = $isExpired
            ? "Product '{$freshInv->product_name}' has expired and has been moved from Active Inventory to the Expired Products quarantine list."
            : 'Inventory updated successfully.';

        return response()->json([
            'success'        => true,
            'message'        => $respMessage,
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

        $today = now()->toDateString();
        $baseQuery = DdcInventory::where('ddc_id', $ddc->id)
            ->where(function ($q) use ($today) {
                $q->whereNull('expire_date')
                  ->orWhereDate('expire_date', '>', $today);
            })
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
            ->where(function ($q) use ($today) {
                $q->whereNull('expire_date')
                  ->orWhereDate('expire_date', '>', $today);
            })
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
