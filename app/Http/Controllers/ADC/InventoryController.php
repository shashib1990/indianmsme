<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    private function adc()
    {
        return Auth::guard('adc')->user();
    }

    /**
     * Build shared inventory query based on search and filters
     */
    private function buildInventoryQuery(Request $request)
    {
        $adc = $this->adc();
        $query = AdcInventory::with(['product', 'localProduct'])
            ->where('adc_id', $adc->id);

        $search = trim($request->input('search', ''));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%");
            });
        }

        $source = strtoupper($request->input('source', 'ALL'));
        if ($source === 'MSME') {
            $query->where(function ($q) {
                $q->where('inventory_source', 'MSME')
                    ->orWhereNotNull('product_id');
            });
        } elseif ($source === 'LOCAL') {
            $query->where(function ($q) {
                $q->where('inventory_source', 'LOCAL')
                    ->orWhereNotNull('adc_local_product_id');
            });
        }

        // Expiry Filtering
        $expiryFilter = $request->input('expiry_filter', 'ALL');
        $todayStr = \Carbon\Carbon::today()->format('Y-m-d');
        $oneMonthStr = \Carbon\Carbon::today()->addDays(30)->format('Y-m-d');
        $threeMonthsStr = \Carbon\Carbon::today()->addDays(90)->format('Y-m-d');
        $sixMonthsStr = \Carbon\Carbon::today()->addDays(180)->format('Y-m-d');

        if ($expiryFilter === 'expired') {
            $query->whereNotNull('expire_date')
                ->where('expire_date', '<', $todayStr);
        } elseif ($expiryFilter === '1_month') {
            $query->whereNotNull('expire_date')
                ->where('expire_date', '>=', $todayStr)
                ->where('expire_date', '<=', $oneMonthStr);
        } elseif ($expiryFilter === '3_months') {
            $query->whereNotNull('expire_date')
                ->where('expire_date', '>', $oneMonthStr)
                ->where('expire_date', '<=', $threeMonthsStr);
        } elseif ($expiryFilter === '6_months') {
            $query->whereNotNull('expire_date')
                ->where('expire_date', '>', $threeMonthsStr)
                ->where('expire_date', '<=', $sixMonthsStr);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->buildInventoryQuery($request);

        $search = trim($request->input('search', ''));
        $source = strtoupper($request->input('source', 'ALL'));
        $expiryFilter = $request->input('expiry_filter', 'ALL');

        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $inventories = $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();

        return view('adc.inventory.index', compact('inventories', 'search', 'source', 'perPage', 'expiryFilter'));
    }

    /**
     * Export Inventory to CSV (Table fields only, direct clean data)
     */
    public function export(Request $request)
    {
        $adc = $this->adc();
        $adcProfile = $adc->adcProfile ?: $adc->profile;
        $adcCode = $adcProfile?->adc_code ?: ($adc->adc_code ?: ('ADC' . str_pad($adc->id, 3, '0', STR_PAD_LEFT)));

        $query = $this->buildInventoryQuery($request);
        $inventories = $query->orderBy('id', 'desc')->get();

        $filename = "ADC_Inventory_{$adcCode}_" . date('Ymd_His') . ".csv";

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
                'CSP',
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
                $calculatedCsp = $purchasePriceVal + (($mrpVal - $purchasePriceVal) / 2);
                $cspVal = (float) ($inv->csp > 0 ? $inv->csp : ($calculatedCsp > 0 ? $calculatedCsp : $purchasePriceVal));

                $calcMargin = $mrpVal > 0 ? round((($mrpVal - $cspVal) / $mrpVal) * 100, 2) : 0;
                $cspMarginVal = $inv->csp_margin !== null ? (float) $inv->csp_margin : $calcMargin;

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

                $daysLeft = $inv->expire_date ? (int) floor($today->diffInDays($inv->expire_date->startOfDay(), false)) : null;
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
                    number_format($cspVal, 2, '.', ''),
                    number_format($cspMarginVal, 2, '.', '') . '%',
                    number_format($gstRateVal, 2, '.', '') . '%',
                    $inv->mfg_date ? $inv->mfg_date->format('d-m-Y') : '',
                    $inv->expire_date ? $inv->expire_date->format('d-m-Y') : '',
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

    public function togglePosStatus(Request $request, $id)
    {
        $adc = $this->adc();
        $inv = AdcInventory::where('id', $id)
            ->where('adc_id', $adc->id)
            ->firstOrFail();

        $targetStatus = ($inv->pos_status === 'Active') ? 'Inactive' : 'Active';

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

            if ($inv->inventory_source === 'LOCAL') {
                $landingCost = (float) ($inv->landing_cost > 0 ? $inv->landing_cost : $inv->purchase_price);
                if ($landingCost <= 0) {
                    $errors[] = 'Landing Cost / Purchase Price must be greater than 0.';
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
        $adc = $this->adc();

        $inv = AdcInventory::where('id', $id)
            ->where('adc_id', $adc->id)
            ->firstOrFail();

        $validated = $request->validate([
            'csp' => 'nullable|numeric|min:0',
            'mfg_date' => 'nullable|date',
            'expire_date' => 'nullable|date',
            'margin' => 'nullable|numeric|min:0|max:100',
            'adc_margin' => 'nullable|numeric|min:0|max:100',
            'csp_margin' => 'nullable|numeric|min:0|max:100',
        ]);

        $mrp = (float) ($inv->mrp ?? 0);
        $cost = (float) ($inv->purchase_price ?: 0);

        if (array_key_exists('csp', $validated) && $validated['csp'] !== null) {
            $cspVal = (float) $validated['csp'];
            if ($mrp > 0) {
                $validated['csp_margin'] = round((($mrp - $cspVal) / $mrp) * 100, 2);
            }
        } elseif (array_key_exists('csp_margin', $validated) && $validated['csp_margin'] !== null) {
            $marginVal = (float) $validated['csp_margin'];
            if ($mrp > 0) {
                $validated['csp'] = round($mrp * (1 - ($marginVal / 100)), 2);
            }
            $validated['adc_margin'] = $marginVal;
            $validated['margin'] = $marginVal;
        }

        $mrp = (float) ($inv->mrp ?? 0);
        if ($mrp > 0 && isset($validated['csp']) && $validated['csp'] > $mrp) {
            return response()->json([
                'success' => false,
                'message' => 'Calculated CSP (₹' . number_format($validated['csp'], 2) . ') cannot be greater than MRP (₹' . number_format($mrp, 2) . ').',
            ], 422);
        }

        $inv->update($validated);

        $freshInv = $inv->fresh();
        $today = \Carbon\Carbon::today()->startOfDay();
        $expireDate = $freshInv->expire_date ? \Carbon\Carbon::parse($freshInv->expire_date)->startOfDay() : null;
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
            if ($daysLeft == 0)
                $expiryLabel = 'Today';
            elseif ($daysLeft == 1)
                $expiryLabel = '1 Day Left';
            else
                $expiryLabel = $daysLeft . ' Days Left';
        }

        $expiryHtml = '';
        if ($expireDate) {
            $expiryHtml = '<span class="badge-expiry ' . $expiryClass . '"><iconify-icon icon="' . $expiryIcon . '" class="fs-12 align-middle"></iconify-icon> ' . $expiryLabel . '</span>';
        } else {
            $expiryHtml = '<span class="text-muted small">—</span>';
        }

        return response()->json([
            'success' => true,
            'message' => 'Inventory updated successfully.',
            'inventory' => $freshInv,
            'csp' => number_format((float) $freshInv->csp, 2, '.', ''),
            'csp_margin' => number_format((float) $freshInv->csp_margin, 2, '.', ''),
            'expiry_html' => $expiryHtml,
            'expiry_class' => $expiryClass,
            'expiry_label' => $expiryLabel,
            'expiry_icon' => $expiryIcon,
            'days_left' => $daysLeft,
            'is_expired' => $isExpired,
            'is_near_expiry' => $isNearExpiry,
        ]);
    }
}
