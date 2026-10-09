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

    public function index(Request $request)
    {
        $adc = $this->adc();
        $query = AdcInventory::with('product')
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

        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $inventories = $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();

        return view('adc.inventory.index', compact('inventories', 'search', 'source', 'perPage', 'expiryFilter'));
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
                'success'    => true,
                'message'    => $statusMsg,
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
            'csp'         => 'nullable|numeric|min:0',
            'mfg_date'    => 'nullable|date',
            'expire_date' => 'nullable|date',
            'margin'      => 'nullable|numeric|min:0|max:100',
            'adc_margin'  => 'nullable|numeric|min:0|max:100',
            'csp_margin'  => 'nullable|numeric|min:0|max:100',
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
            if ($daysLeft == 0) $expiryLabel = 'Today';
            elseif ($daysLeft == 1) $expiryLabel = '1 Day Left';
            else $expiryLabel = $daysLeft . ' Days Left';
        }

        $expiryHtml = '';
        if ($expireDate) {
            $expiryHtml = '<span class="badge-expiry ' . $expiryClass . '"><iconify-icon icon="' . $expiryIcon . '" class="fs-12 align-middle"></iconify-icon> ' . $expiryLabel . '</span>';
        } else {
            $expiryHtml = '<span class="text-muted small">—</span>';
        }

        return response()->json([
            'success'        => true,
            'message'        => 'Inventory updated successfully.',
            'inventory'      => $freshInv,
            'csp'            => number_format((float) $freshInv->csp, 2, '.', ''),
            'csp_margin'     => number_format((float) $freshInv->csp_margin, 2, '.', ''),
            'expiry_html'    => $expiryHtml,
            'expiry_class'   => $expiryClass,
            'expiry_label'   => $expiryLabel,
            'expiry_icon'    => $expiryIcon,
            'days_left'      => $daysLeft,
            'is_expired'     => $isExpired,
            'is_near_expiry' => $isNearExpiry,
        ]);
    }
}
