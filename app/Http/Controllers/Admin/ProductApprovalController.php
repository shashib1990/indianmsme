<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAuditLog;
use App\Exports\ProductMarginsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductApprovalController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'updated_at');
        $order = $request->get('order', 'desc');
        
        // Get tab from route defaults if not explicitly passed as a query param
        $tab = $request->route('tab') ?? $request->get('tab', 'new_approvals');

        // Fetch counts for all tabs
        $newApprovalsCount = Product::where('status', 'Pending Approval')->where('is_new', true)->count();
        $updateApprovalsCount = Product::where('status', 'Pending Approval')->where('is_new', false)->count();
        $approvedCount = Product::where('status', 'Approved')->count();
        $rejectedCount = Product::whereIn('status', ['Rejected', 'Inactive'])->count();

        $query = Product::with(['seller.sellerProfile', 'brand', 'category']);

        if ($request->get('filter') === 'overcosted') {
            $query->where('mrp', '>', 5000);
        } else {
            if ($tab === 'new_approvals') {
                $query->where('status', 'Pending Approval')->where('is_new', true);
            } elseif ($tab === 'update_approvals') {
                $query->where('status', 'Pending Approval')->where('is_new', false);
            } elseif ($tab === 'approved') {
                $query->where('status', 'Approved');
            } elseif ($tab === 'rejected') {
                $query->whereIn('status', ['Rejected', 'Inactive']);
            } else {
                $query->where('status', 'Pending Approval')->where('is_new', true);
                $tab = 'new_approvals';
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%")
                  ->orWhereHas('seller', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhereHas('sellerProfile', function ($spq) use ($search) {
                            $spq->where('company_name', 'like', "%{$search}%");
                        });
                  });
            });
        }

        if ($request->has('export')) {
            $filename = 'products_' . $tab . '_' . date('Y-m-d_H-i-s') . '.csv';

            return response()->streamDownload(function () use ($query, $sort, $order) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, [
                    'ID',
                    'Product Code',
                    'Barcode',
                    'Product Name',
                    'Category',
                    'Brand',
                    'Vendor / Seller',
                    'Vendor Code',
                    'Net Qty / Pack Size',
                    'Case Pack Qty',
                    'Unit of Measure',
                    'Seller Price (Incl GST)',
                    'GST Rate (%)',
                    'MRP',
                    'Status',
                    'Date'
                ]);

                $query->orderBy($sort, $order)->chunk(250, function ($products) use ($file) {
                    foreach ($products as $product) {
                        $vendorName = $product->seller?->sellerProfile?->company_name ?: ($product->seller?->name ?? '—');
                        $vendorCode = $product->seller?->sellerProfile?->seller_id ?: ($product->seller_id ? 'IMV' . $product->seller_id : '—');
                        $statusStr = is_object($product->status) ? ($product->status->value ?? (string)$product->status) : (string)$product->status;

                        fputcsv($file, [
                            $product->id,
                            $product->product_code ?? '—',
                            $product->barcode ?? '—',
                            $product->product_name,
                            $product->category?->name ?? '—',
                            $product->brand?->name ?? '—',
                            $vendorName,
                            $vendorCode,
                            $product->net_quantity ?? '—',
                            $product->case_pack_quantity ?? '—',
                            $product->unit_of_measure ?? '—',
                            number_format((float)$product->seller_price_incl_gst, 2, '.', ''),
                            number_format((float)$product->gst_rate, 2, '.', ''),
                            number_format((float)$product->mrp, 2, '.', ''),
                            $statusStr,
                            $product->updated_at ? $product->updated_at->format('Y-m-d H:i:s') : ($product->created_at ? $product->created_at->format('Y-m-d H:i:s') : '—')
                        ]);
                    }
                });

                fclose($file);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        $products = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.products.approvals.index', compact(
            'products', 'search', 'limit', 'sort', 'order', 'tab',
            'newApprovalsCount', 'updateApprovalsCount', 'approvedCount', 'rejectedCount'
        ));
    }

    public function show($id)
    {
        $product = Product::with(['seller.sellerProfile', 'brand', 'category', 'subCategory', 'childCategory', 'auditLogs.modifier'])
            ->findOrFail($id);

        return view('admin.products.approvals.show', compact('product'));
    }

    public function processApproval(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'action' => 'required|in:approve,reject,update_margins',
            'admin_remarks' => 'required_if:action,reject|nullable|string|max:1000',
            'admin_margin_percentage' => 'nullable|numeric|min:0|max:100',
            'ddc_margin_percentage' => 'nullable|numeric|min:0|max:100',
            'mbp_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $isNew = $product->is_new;

        DB::beginTransaction();
        try {
            $action = $request->action;
            $oldStatus = $product->status;
            
            if ($action === 'update_margins') {
                $newStatus = $oldStatus;
            } else {
                $newStatus = $action === 'approve' ? 'Approved' : 'Rejected';
            }
            
            $remarks = $request->admin_remarks;

            // Save previous values vs updated values
            $previous = ['status' => $oldStatus, 'admin_remarks' => $product->admin_remarks];
            $updated = ['status' => $newStatus, 'admin_remarks' => $remarks];

            if ($action === 'approve' || $action === 'update_margins') {
                $adminMarginPercent = $request->input('admin_margin_percentage') !== null ? (float) $request->input('admin_margin_percentage') : null;
                $ddcMarginPercent = $request->input('ddc_margin_percentage') !== null ? (float) $request->input('ddc_margin_percentage') : null;
                $mbpPercent = $request->input('mbp_percentage') !== null ? (float) $request->input('mbp_percentage') : null;

                // Run calculations
                $pricing = \App\Services\PricingService::calculate(
                    (float) $product->seller_price_incl_gst,
                    (float) $product->gst_rate,
                    (float) $product->mrp,
                    $adminMarginPercent,
                    $ddcMarginPercent,
                    $mbpPercent
                );

                // Update product attributes with calculated values
                foreach ($pricing as $key => $val) {
                    $previous[$key] = $product->$key;
                    $product->$key = $val;
                    $updated[$key] = $val;
                }
                // Mark as no longer new
                $product->is_new = false;
            }

            $product->status = $newStatus;
            if ($action !== 'update_margins') {
                $product->admin_remarks = $remarks;
            }
            $product->save();

            if ($action === 'approve' || $action === 'update_margins') {
                foreach ($product->subSellerAssignments as $assignment) {
                    $assignment->recalculatePricing();
                }
            }

            // Log the approval action
            ProductAuditLog::create([
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'modified_by' => auth('admin')->id(),
                'previous_values' => $previous,
                'updated_values' => $updated,
                'approval_status' => $newStatus,
                'admin_remarks' => $remarks,
            ]);

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $action === 'update_margins' ? 'Product margins updated successfully.' : $msg,
                    'status' => $newStatus,
                    'status_text' => $newStatus
                ]);
            }

            if ($action === 'update_margins') {
                return redirect()->route('admin.products.approvals.show', $product->id)->with('success', 'Product margins updated successfully.');
            }

            $msg = $action === 'approve' ? 'Product approved successfully.' : 'Product rejected successfully.';
            $redirectRoute = $isNew ? 'admin.products.approvals.new' : 'admin.products.approvals.updated';
            return redirect()->route($redirectRoute)->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process product status: ' . $e->getMessage());
        }
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
            'admin_remarks' => 'required_if:action,reject|nullable|string|max:1000',
        ]);

        $ids = $request->ids;
        $action = $request->action;
        $remarks = $request->admin_remarks;
        $newStatus = $action === 'approve' ? 'Approved' : 'Rejected';

        $firstProduct = Product::find($ids[0]);
        $isNew = $firstProduct ? $firstProduct->is_new : true;

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $product = Product::find($id);
                if ($product && $product->status === 'Pending Approval') {
                    $oldStatus = $product->status;
                    $previous = ['status' => $oldStatus, 'admin_remarks' => $product->admin_remarks];
                    $updated = ['status' => $newStatus, 'admin_remarks' => $remarks];

                    $product->status = $newStatus;
                    $product->admin_remarks = $remarks;

                    if ($action === 'approve') {
                        // Recalculate using product's existing margins (or system defaults if null)
                        $pricing = \App\Services\PricingService::calculate(
                            (float) $product->seller_price_incl_gst,
                            (float) $product->gst_rate,
                            (float) $product->mrp,
                            $product->admin_margin_percentage,
                            $product->ddc_margin_percentage,
                            $product->mbp_percentage
                        );

                        foreach ($pricing as $key => $val) {
                            $previous[$key] = $product->$key;
                            $product->$key = $val;
                            $updated[$key] = $val;
                        }
                        $product->is_new = false;
                    }

                    $product->save();

                    if ($action === 'approve') {
                        foreach ($product->subSellerAssignments as $assignment) {
                            $assignment->recalculatePricing();
                        }
                    }

                    ProductAuditLog::create([
                        'product_id' => $product->id,
                        'product_name' => $product->product_name,
                        'modified_by' => auth('admin')->id(),
                        'previous_values' => $previous,
                        'updated_values' => $updated,
                        'approval_status' => $newStatus,
                        'admin_remarks' => $remarks,
                    ]);
                }
            }
            DB::commit();
            $msg = $action === 'approve' ? 'Selected products approved.' : 'Selected products rejected.';
            $redirectRoute = $isNew ? 'admin.products.approvals.new' : 'admin.products.approvals.updated';
            return redirect()->route($redirectRoute)->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }

    public function allProducts(Request $request)
    {
        $search = $request->get('search');
        $sellerId = $request->get('seller_id');
        $status = $request->get('status');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');

        $primaryVendors = \App\Models\Vendor::whereHas('sellerProfile', function ($q) {
            $q->whereNull('parent_id');
        })->with('sellerProfile')->orderBy('name')->get();

        $subSellers = \App\Models\Vendor::whereHas('sellerProfile', function ($q) {
            $q->whereNotNull('parent_id');
        })->with('sellerProfile')->orderBy('name')->get();

        // Attach parent user model to sub-sellers for display
        foreach ($subSellers as $sub) {
            if ($sub->sellerProfile?->parent_id) {
                $sub->parentVendorModel = \App\Models\User::with('sellerProfile')->find($sub->sellerProfile->parent_id);
            }
        }

        $query = Product::with(['seller.sellerProfile', 'brand', 'category', 'subSellerAssignments.subSeller.sellerProfile']);

        if ($sellerId) {
            $selectedSeller = \App\Models\Vendor::with('sellerProfile')->find($sellerId);
            if ($selectedSeller && $selectedSeller->sellerProfile?->parent_id) {
                // If sub-seller is selected, show products owned by sub-seller OR assigned to sub-seller
                $query->where(function ($q) use ($sellerId) {
                    $q->where('seller_id', $sellerId)
                      ->orWhereHas('subSellerAssignments', function ($sq) use ($sellerId) {
                          $sq->where('sub_seller_id', $sellerId);
                      });
                });
            } else {
                // Primary Vendor selected
                $query->where('seller_id', $sellerId);
            }
        }

        if ($request->get('filter') === 'overcosted') {
            $query->where('mrp', '>', 5000);
        }

        if ($status && $status !== 'all') {
            if ($status === 'Rejected') {
                $query->whereIn('status', ['Rejected', 'Inactive']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%")
                  ->orWhereHas('seller', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhereHas('sellerProfile', function ($spq) use ($search) {
                            $spq->where('company_name', 'like', "%{$search}%")
                               ->orWhere('seller_id', 'like', "%{$search}%");
                        });
                  });
            });
        }

        $defaultAdminMargin = (float) \App\Models\SystemSetting::getVal('admin_margin_percentage', 8.00);
        $defaultDdcMargin   = (float) \App\Models\SystemSetting::getVal('ddc_margin_percentage', 6.00);
        $defaultMbp         = (float) \App\Models\SystemSetting::getVal('mbp_percentage', 10.00);

        if ($request->has('export')) {
            return response()->streamDownload(function () use ($query, $sort, $order, $defaultAdminMargin, $defaultDdcMargin, $defaultMbp) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, [
                    'ID',
                    'Product Code',
                    'Barcode',
                    'Product Name',
                    'Category',
                    'Brand',
                    'HSN Code',
                    'Vendor / Seller',
                    'Vendor Code',
                    'Sub-Sellers',
                    'Net Qty / Pack Size',
                    'Case Pack Qty',
                    'Unit of Measure',
                    'Seller Price (Incl GST)',
                    'GST Rate (%)',
                    'MRP',
                    'Admin Margin (%)',
                    'DDC Purchase Price',
                    'DDC Margin (%)',
                    'ADC Purchase Price',
                    'MBP Margin (%)',
                    'Status',
                    'Created At'
                ]);

                $query->orderBy($sort, $order)->chunk(250, function ($products) use ($file, $defaultAdminMargin, $defaultDdcMargin, $defaultMbp) {
                    foreach ($products as $product) {
                        $admPct = $product->admin_margin_percentage !== null ? (float)$product->admin_margin_percentage : $defaultAdminMargin;
                        $ddcPct = $product->ddc_margin_percentage !== null ? (float)$product->ddc_margin_percentage : $defaultDdcMargin;
                        $mbpPct = $product->mbp_percentage !== null ? (float)$product->mbp_percentage : $defaultMbp;

                        $pricing = \App\Services\PricingService::calculate(
                            (float) $product->seller_price_incl_gst,
                            (float) $product->gst_rate,
                            (float) $product->mrp,
                            $admPct,
                            $ddcPct,
                            $mbpPct
                        );

                        $vendorName = $product->seller?->sellerProfile?->company_name ?: ($product->seller?->name ?? '—');
                        $vendorCode = $product->seller?->sellerProfile?->seller_id ?: ($product->seller_id ? 'IMV' . $product->seller_id : '—');
                        $subSellersStr = $product->subSellerAssignments->map(function ($assignment) {
                            $sub = $assignment->subSeller;
                            return $sub?->sellerProfile?->company_name ?: ($sub?->name ?? 'Sub-Seller #' . $assignment->sub_seller_id);
                        })->implode(', ');

                        $statusStr = is_object($product->status) ? ($product->status->value ?? (string)$product->status) : (string)$product->status;

                        fputcsv($file, [
                            $product->id,
                            $product->product_code ?? '—',
                            $product->barcode ?? '—',
                            $product->product_name,
                            $product->category?->name ?? '—',
                            $product->brand?->name ?? '—',
                            $product->hsn_code ?? '—',
                            $vendorName,
                            $vendorCode,
                            $subSellersStr ?: '—',
                            $product->net_quantity ?? '—',
                            $product->case_pack_quantity ?? '—',
                            $product->unit_of_measure ?? '—',
                            number_format((float)$product->seller_price_incl_gst, 2, '.', ''),
                            number_format((float)$product->gst_rate, 2, '.', ''),
                            number_format((float)$product->mrp, 2, '.', ''),
                            number_format($admPct, 2, '.', ''),
                            number_format((float)($pricing['ddc_purchase_price'] ?? $product->ddc_purchase_price), 2, '.', ''),
                            number_format($ddcPct, 2, '.', ''),
                            number_format((float)($pricing['adc_purchase_price'] ?? $product->adc_purchase_price), 2, '.', ''),
                            number_format($mbpPct, 2, '.', ''),
                            $statusStr,
                            $product->created_at ? $product->created_at->format('Y-m-d H:i:s') : '—'
                        ]);
                    }
                });

                fclose($file);
            }, 'products_margins_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $products = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.products.all', compact(
            'products',
            'primaryVendors',
            'subSellers',
            'sellerId',
            'status',
            'search',
            'limit',
            'sort',
            'order',
            'defaultAdminMargin',
            'defaultDdcMargin',
            'defaultMbp'
        ));
    }

    public function updateMargins(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'admin_margin_percentage' => 'nullable|numeric|min:0|max:100',
            'ddc_margin_percentage'   => 'nullable|numeric|min:0|max:100',
            'mbp_percentage'          => 'nullable|numeric|min:0|max:100',
        ]);

        $adminMargin = $request->input('admin_margin_percentage') !== null && $request->input('admin_margin_percentage') !== ''
            ? (float) $request->input('admin_margin_percentage')
            : null;
        $ddcMargin = $request->input('ddc_margin_percentage') !== null && $request->input('ddc_margin_percentage') !== ''
            ? (float) $request->input('ddc_margin_percentage')
            : null;
        $mbpPercent = $request->input('mbp_percentage') !== null && $request->input('mbp_percentage') !== ''
            ? (float) $request->input('mbp_percentage')
            : null;

        DB::beginTransaction();
        try {
            $product->admin_margin_percentage = $adminMargin;
            $product->ddc_margin_percentage   = $ddcMargin;
            $product->mbp_percentage          = $mbpPercent;

            $pricing = \App\Services\PricingService::calculate(
                (float) $product->seller_price_incl_gst,
                (float) $product->gst_rate,
                (float) $product->mrp,
                $adminMargin,
                $ddcMargin,
                $mbpPercent
            );

            foreach ($pricing as $key => $val) {
                $product->$key = $val;
            }

            $product->save();

            if ($product->subSellerAssignments) {
                foreach ($product->subSellerAssignments as $assignment) {
                    $assignment->recalculatePricing();
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Margins & Pricing updated successfully for ' . $product->product_name,
                    'pricing' => $pricing
                ]);
            }

            return redirect()->back()->with('success', 'Margins & Pricing updated successfully for ' . $product->product_name);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Failed to update margins: ' . $e->getMessage());
        }
    }

    public function bulkUpdateMargins(Request $request)
    {
        $request->validate([
            'margins' => 'required|array',
            'margins.*.id' => 'required|exists:products,id',
            'margins.*.admin_margin' => 'nullable|numeric|min:0|max:100',
            'margins.*.ddc_margin'   => 'nullable|numeric|min:0|max:100',
            'margins.*.mbp_margin'   => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $updatedCount = 0;
            foreach ($request->margins as $item) {
                $product = Product::find($item['id']);
                if (!$product) continue;

                $adminMargin = isset($item['admin_margin']) && $item['admin_margin'] !== '' ? (float) $item['admin_margin'] : null;
                $ddcMargin   = isset($item['ddc_margin']) && $item['ddc_margin'] !== '' ? (float) $item['ddc_margin'] : null;
                $mbpPercent  = isset($item['mbp_margin']) && $item['mbp_margin'] !== '' ? (float) $item['mbp_margin'] : null;

                $product->admin_margin_percentage = $adminMargin;
                $product->ddc_margin_percentage   = $ddcMargin;
                $product->mbp_percentage          = $mbpPercent;

                $pricing = \App\Services\PricingService::calculate(
                    (float) $product->seller_price_incl_gst,
                    (float) $product->gst_rate,
                    (float) $product->mrp,
                    $adminMargin,
                    $ddcMargin,
                    $mbpPercent
                );

                foreach ($pricing as $key => $val) {
                    $product->$key = $val;
                }

                $product->save();

                if ($product->subSellerAssignments) {
                    foreach ($product->subSellerAssignments as $assignment) {
                        $assignment->recalculatePricing();
                    }
                }
                $updatedCount++;
            }
            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => "Updated margins for {$updatedCount} products."]);
            }

            return redirect()->back()->with('success', "Updated margins for {$updatedCount} products successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Bulk update failed: ' . $e->getMessage());
        }
    }

    /**
     * Dedicated endpoint to export products margin configuration template file for upload.
     */
    public function exportMargins(Request $request)
    {
        $sellerId = $request->get('seller_id');
        $status = $request->get('status');
        $search = $request->get('search');
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'asc');

        $query = Product::query()->with(['seller.sellerProfile']);

        if ($sellerId) {
            $selectedSeller = \App\Models\Vendor::with('sellerProfile')->find($sellerId);
            if ($selectedSeller && $selectedSeller->sellerProfile?->parent_id) {
                $query->where(function ($q) use ($sellerId) {
                    $q->where('seller_id', $sellerId)
                      ->orWhereHas('subSellerAssignments', function ($sq) use ($sellerId) {
                          $sq->where('sub_seller_id', $sellerId);
                      });
                });
            } else {
                $query->where('seller_id', $sellerId);
            }
        }

        if ($status && $status !== 'all') {
            if ($status === 'Rejected') {
                $query->whereIn('status', ['Rejected', 'Inactive']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%")
                  ->orWhereHas('seller', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhereHas('sellerProfile', function ($spq) use ($search) {
                            $spq->where('company_name', 'like', "%{$search}%")
                               ->orWhere('seller_id', 'like', "%{$search}%");
                        });
                  });
            });
        }

        $format = strtolower($request->get('format', 'xlsx'));
        $filename = 'products_margins_template_' . date('Y-m-d_His') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportQuery = $query->orderBy($sort, $order);

        if ($format === 'csv') {
            return Excel::download(new ProductMarginsExport($exportQuery), $filename, \Maatwebsite\Excel\Excel::CSV);
        }
        return Excel::download(new ProductMarginsExport($exportQuery), $filename);
    }

    /**
     * Upload and update margins via Excel (.xlsx, .xls) or CSV file.
     * Uses Product ID as unique identifier.
     * Reference columns (Product Name, Product Code, Seller Name, IMV Code) are NOT modified.
     * Recalculates product pricing using existing PricingService logic.
     */
    public function uploadMargins(Request $request)
    {
        $request->validate([
            'margin_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:20480',
        ], [
            'margin_file.required' => 'Please select an Excel or CSV file to upload.',
            'margin_file.mimes'    => 'Uploaded file must be a valid .xlsx, .xls, or .csv document.',
        ]);

        try {
            $file = $request->file('margin_file');
            $sheetData = Excel::toArray([], $file);
            $rows = $sheetData[0] ?? [];

            if (empty($rows) || count($rows) < 2) {
                return redirect()->back()->with('error', 'The uploaded file is empty or missing data rows.');
            }

            // Identify header row
            $headerRow = array_map(function ($h) {
                return strtolower(trim((string)$h));
            }, $rows[0]);

            // Locate required columns by header name or fallback to template index
            $colProductId = null;
            $colAdminMargin = null;
            $colDdcMargin = null;

            foreach ($headerRow as $idx => $header) {
                $clean = preg_replace('/[^a-z0-9]/', '', $header);
                if (in_array($clean, ['productid', 'id']) || str_contains($clean, 'productid')) {
                    if ($colProductId === null) $colProductId = $idx;
                } elseif (str_contains($clean, 'adminmargin') || ($clean === 'admin')) {
                    if ($colAdminMargin === null) $colAdminMargin = $idx;
                } elseif (str_contains($clean, 'ddcmargin') || ($clean === 'ddc')) {
                    if ($colDdcMargin === null) $colDdcMargin = $idx;
                }
            }

            // Fallback for standard templates: 9-column template (0: ID, 7: Admin, 8: DDC) or 7-column (0: ID, 5: Admin, 6: DDC)
            if ($colProductId === null) $colProductId = 0;
            if ($colAdminMargin === null) $colAdminMargin = count($headerRow) >= 9 ? 7 : 5;
            if ($colDdcMargin === null) $colDdcMargin = count($headerRow) >= 9 ? 8 : 6;

            $totalRows = 0;
            $updatedCount = 0;
            $unchangedCount = 0;
            $errors = [];

            DB::beginTransaction();

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $excelRowNum = $i + 1; // 1-indexed for spreadsheet users

                // Skip completely empty rows
                if (empty(array_filter($row, fn($cell) => $cell !== null && trim((string)$cell) !== ''))) {
                    continue;
                }

                $totalRows++;

                $productIdRaw = trim((string)($row[$colProductId] ?? ''));
                $adminMarginRaw = trim((string)($row[$colAdminMargin] ?? ''));
                $ddcMarginRaw = trim((string)($row[$colDdcMargin] ?? ''));

                if ($productIdRaw === '' || !is_numeric($productIdRaw)) {
                    $errors[] = "Row {$excelRowNum}: Invalid or missing Product ID ('{$productIdRaw}').";
                    continue;
                }

                $productId = (int)$productIdRaw;
                $product = Product::with('subSellerAssignments')->find($productId);

                if (!$product) {
                    $errors[] = "Row {$excelRowNum}: Product ID #{$productId} does not exist in the database.";
                    continue;
                }

                // Margin validation
                if ($adminMarginRaw === '' || !is_numeric($adminMarginRaw)) {
                    $errors[] = "Row {$excelRowNum} (Product #{$productId}): Admin Margin must be a valid numeric percentage.";
                    continue;
                }

                if ($ddcMarginRaw === '' || !is_numeric($ddcMarginRaw)) {
                    $errors[] = "Row {$excelRowNum} (Product #{$productId}): DDC Margin must be a valid numeric percentage.";
                    continue;
                }

                $adminMargin = round((float)$adminMarginRaw, 2);
                $ddcMargin   = round((float)$ddcMarginRaw, 2);

                if ($adminMargin < 0 || $adminMargin > 100) {
                    $errors[] = "Row {$excelRowNum} (Product #{$productId}): Admin Margin ({$adminMargin}%) must be between 0% and 100%.";
                    continue;
                }

                if ($ddcMargin < 0 || $ddcMargin > 100) {
                    $errors[] = "Row {$excelRowNum} (Product #{$productId}): DDC Margin ({$ddcMargin}%) must be between 0% and 100%.";
                    continue;
                }

                // Check if margin values actually changed
                $curAdmin = $product->admin_margin_percentage !== null ? round((float)$product->admin_margin_percentage, 2) : null;
                $curDdc   = $product->ddc_margin_percentage !== null ? round((float)$product->ddc_margin_percentage, 2) : null;

                if ($curAdmin === $adminMargin && $curDdc === $ddcMargin) {
                    $unchangedCount++;
                    continue;
                }

                // Update margins ONLY (reference columns Product Name, Code, Seller Name, IMV Code are NOT modified)
                $product->admin_margin_percentage = $adminMargin;
                $product->ddc_margin_percentage   = $ddcMargin;

                // Recalculate applicable product prices using existing pricing logic
                $pricing = \App\Services\PricingService::calculate(
                    (float) $product->seller_price_incl_gst,
                    (float) $product->gst_rate,
                    (float) $product->mrp,
                    $adminMargin,
                    $ddcMargin,
                    $product->mbp_percentage
                );

                foreach ($pricing as $key => $val) {
                    $product->$key = $val;
                }

                $product->save();

                // Recalculate sub-seller assignments if any exist
                if ($product->subSellerAssignments && $product->subSellerAssignments->isNotEmpty()) {
                    foreach ($product->subSellerAssignments as $assignment) {
                        $assignment->recalculatePricing();
                    }
                }

                $updatedCount++;
            }

            DB::commit();

            $summary = [
                'total_rows'      => $totalRows,
                'updated_count'   => $updatedCount,
                'unchanged_count' => $unchangedCount,
                'errors_count'    => count($errors),
                'errors'          => array_slice($errors, 0, 15),
                'has_more_errors' => count($errors) > 15 ? count($errors) - 15 : 0,
            ];

            return redirect()->route('admin.products.all')
                ->with('margin_upload_summary', $summary)
                ->with('success', "Margins upload processed: {$updatedCount} products updated, {$unchangedCount} unchanged" . (count($errors) > 0 ? ", " . count($errors) . " errors." : "."));

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process margins file: ' . $e->getMessage());
        }
    }
}
