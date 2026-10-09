<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use App\Models\State;
use App\Models\Hsn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Enforce allowance config
        if (auth('vendor')->user()->allow_own_products !== 'Yes') {
            abort(403, 'You are not authorized to manage products. Please contact your parent seller or administrator.');
        }

        $search = $request->get('search');
        $status = $request->get('status');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $query = Product::where('seller_id', auth('vendor')->id())
            ->select('id', 'product_code', 'product_name', 'barcode', 'featured_image', 'seller_price_incl_gst', 'mrp', 'gst_rate', 'status', 'admin_remarks', 'brand_id', 'category_id', 'case_pack_quantity', 'unit_of_measure')
            ->with(['brand:id,name', 'category:id,name']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $products = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();
        $brands = Brand::active()->orderBy('name')->get();
        $categories = Category::active()->orderBy('name')->get();

        return view('vendor.products.index', compact('products', 'search', 'status', 'limit', 'sort', 'order', 'brands', 'categories'));
    }

    public function create()
    {
        if (auth('vendor')->user()->allow_own_products !== 'Yes') {
            abort(403);
        }

        $brands = Brand::active()->orderBy('name')->get();
        $categories = Category::active()->orderBy('name')->get();
        $states = State::active()->orderBy('name')->get();

        $companyMargin = (float) \App\Models\SystemSetting::getVal('admin_margin_percentage', 8.0);
        $ddcMargin = (float) \App\Models\SystemSetting::getVal('ddc_margin_percentage', 5.0);
        $mbp = (float) \App\Models\SystemSetting::getVal('mbp_percentage', 2.0);
        $units = \App\Models\UnitOfMeasure::getUnitsList();

        return view('vendor.products.create', compact('brands', 'categories', 'states', 'units', 'companyMargin', 'ddcMargin', 'mbp'));
    }

    public function store(Request $request)
    {
        if (auth('vendor')->user()->allow_own_products !== 'Yes') {
            abort(403);
        }

        $request->validate([
            'product_name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'child_category_id' => 'nullable|exists:child_categories,id',
            'hsn_code' => ['required', 'string', 'max:50', Rule::exists('hsns', 'hsn_code')->where(fn($q) => $q->where('status', '1'))],
            'seller_price_incl_gst' => 'required|numeric|min:0',
            'mrp' => 'required|numeric|min:0|gte:seller_price_incl_gst',
            'gst_rate' => 'required|numeric|min:0|max:100',
            'unit_of_measure' => 'required|string|max:50',
            'net_quantity' => 'nullable|string|max:100',
            'case_pack_quantity' => 'nullable|integer|min:1',
            'description' => 'required|string',
            'product_specification' => 'nullable|string',
            'storage_instructions' => 'nullable|string',
            'country_of_origin' => 'required|string|max:100',
            'shelf_life' => 'nullable|string|max:100',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'dimension_unit' => 'nullable|string|in:cm,inch,mm',
            'weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|in:kg,gm,mg',
            'product_keywords' => 'nullable|array',
            'featured_image' => 'nullable|string|max:1000',
            'gallery' => 'nullable|array',
            'brochure' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $status = ($request->input('submit_approval') == 1) ? 'Pending Approval' : 'Draft';

            $data = $request->all();
            $data['seller_id'] = auth('vendor')->id();
            $data['status'] = $status;
            $data['created_by'] = auth('vendor')->id();

            // Pricing engine calculations (using null to fallback to global system settings)
            $pricing = self::calculatePricingData(
                $request->input('seller_price_incl_gst'),
                $request->input('gst_rate'),
                $request->input('mrp'),
                null,
                null,
            );
            $data = array_merge($data, $pricing);
            $product = Product::create($data);

            // Log Version / Audit Log
            ProductAuditLog::create([
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'modified_by' => auth('vendor')->id(),
                'approval_status' => $status,
                'updated_values' => $data,
            ]);

            if ($status === 'Pending Approval') {
                $this->notifyAdmins(
                    'New Product Pending Approval',
                    "Seller '" . auth('vendor')->user()->name . "' has submitted a new product: '{$product->product_name}'.",
                    route('admin.products.approvals.show', $product->id)
                );
            }

            DB::commit();

            $msg = $status === 'Pending Approval' ? 'Product created and submitted for approval.' : 'Product created as draft.';
            return redirect()->route('vendor.products.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating product: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $product = Product::where('seller_id', auth('vendor')->id())->findOrFail($id);
        $brands = Brand::active()->orderBy('name')->get();
        if ($product->brand_id && !$brands->contains('id', $product->brand_id)) {
            $currentBrand = Brand::find($product->brand_id);
            if ($currentBrand) {
                $brands->push($currentBrand);
            }
        }

        $categories = Category::active()->orderBy('name')->get();
        if ($product->category_id && !$categories->contains('id', $product->category_id)) {
            $currentCategory = Category::find($product->category_id);
            if ($currentCategory) {
                $categories->push($currentCategory);
            }
        }

        $states = State::active()->orderBy('name')->get();

        // Also fetch child elements for category/subcategory dropdown persistence
        $subCategories = SubCategory::where('category_id', $product->category_id)->orderBy('name')->get();
        $childCategories = ChildCategory::where('sub_category_id', $product->sub_category_id)->orderBy('name')->get();

        $companyMargin = (float) \App\Models\SystemSetting::getVal('admin_margin_percentage', 8.0);
        $ddcMargin = (float) \App\Models\SystemSetting::getVal('ddc_margin_percentage', 5.0);
        $mbp = (float) \App\Models\SystemSetting::getVal('mbp_percentage', 2.0);
        $units = \App\Models\UnitOfMeasure::getUnitsList();
        if ($product->unit_of_measure && !in_array($product->unit_of_measure, $units)) {
            $units[] = $product->unit_of_measure;
        }

        return view('vendor.products.edit', compact('product', 'brands', 'categories', 'states', 'subCategories', 'childCategories', 'units', 'companyMargin', 'ddcMargin', 'mbp'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::where('seller_id', auth('vendor')->id())->findOrFail($id);

        $request->validate([
            'product_name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'child_category_id' => 'nullable|exists:child_categories,id',
            'hsn_code' => ['required', 'string', 'max:50', Rule::exists('hsns', 'hsn_code')->where(fn($q) => $q->where('status', '1'))],
            'seller_price_incl_gst' => 'required|numeric|min:0',
            'mrp' => 'required|numeric|min:0|gte:seller_price_incl_gst',
            'gst_rate' => 'required|numeric|min:0|max:100',
            'unit_of_measure' => 'required|string|max:50',
            'net_quantity' => 'nullable|string|max:100',
            'case_pack_quantity' => 'nullable|integer|min:1',
            'description' => 'required|string',
            'product_specification' => 'nullable|string',
            'storage_instructions' => 'nullable|string',
            'country_of_origin' => 'required|string|max:100',
            'shelf_life' => 'nullable|string|max:100',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'dimension_unit' => 'nullable|string|in:cm,inch,mm',
            'weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|in:kg,gm,mg',
            'product_keywords' => 'nullable|array',
            'featured_image' => 'nullable|string|max:1000',
            'gallery' => 'nullable|array',
            'brochure' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $inputData = $request->all();

            // Re-generate gallery if empty
            if (!isset($inputData['gallery'])) {
                $inputData['gallery'] = [];
            }
            if (!isset($inputData['product_keywords'])) {
                $inputData['product_keywords'] = [];
            }

            // Pricing engine calculations (using product's existing values or global settings if null)
            $pricing = self::calculatePricingData(
                $request->input('seller_price_incl_gst'),
                $request->input('gst_rate'),
                $request->input('mrp'),
                $product->admin_margin_percentage,
                $product->ddc_margin_percentage,
                $product->mbp_percentage
            );
            $inputData = array_merge($inputData, $pricing);

            // Compare attributes to build diff
            $previous = [];
            $updated = [];
            $ignoredKeys = ['updated_at', 'status', 'admin_remarks', '_token', '_method', 'submit_approval'];

            foreach ($inputData as $key => $val) {
                if (in_array($key, $ignoredKeys))
                    continue;

                $origVal = $product->$key;

                // Handle array serialization comparison
                if (is_array($origVal) || is_array($val)) {
                    $origCompare = is_array($origVal) ? json_encode($origVal) : json_encode([]);
                    $newCompare = is_array($val) ? json_encode($val) : json_encode([]);
                    if ($origCompare !== $newCompare) {
                        $previous[$key] = $origVal;
                        $updated[$key] = $val;
                    }
                } else {
                    if ((string) $origVal !== (string) $val) {
                        $previous[$key] = $origVal;
                        $updated[$key] = $val;
                    }
                }
            }

            $hasChanges = count($updated) > 0;
            $oldStatus = $product->status;

            // State machine logic:
            // If already Approved, any change pushes it back to Pending Approval.
            // If Draft or Rejected, keeping/resetting it to Draft (unless submitting immediately).
            $newStatus = $oldStatus;
            if ($request->input('submit_approval') == 1) {
                $newStatus = 'Pending Approval';
            } elseif ($oldStatus === 'Approved' && $hasChanges) {
                $newStatus = 'Pending Approval';
            } elseif ($oldStatus === 'Rejected' && $hasChanges) {
                $newStatus = 'Draft';
            }

            // Save details
            $product->fill($inputData);
            $product->status = $newStatus;
            $product->save();

            // Create audit log if there are changes
            if ($hasChanges || $newStatus !== $oldStatus) {
                ProductAuditLog::create([
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'modified_by' => auth('vendor')->id(),
                    'previous_values' => $previous,
                    'updated_values' => $updated,
                    'approval_status' => $newStatus,
                ]);

                if ($newStatus === 'Pending Approval') {
                    $this->notifyAdmins(
                        'Product Modification Pending Approval',
                        "Seller '" . auth('vendor')->user()->name . "' updated product '{$product->product_name}' which is now pending re-approval.",
                        route('admin.products.approvals.show', $product->id)
                    );
                }
            }

            DB::commit();

            $msg = $newStatus === 'Pending Approval' ? 'Product updated and submitted for approval.' : 'Product updated successfully.';
            return redirect()->route('vendor.products.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating product: ' . $e->getMessage());
        }
    }

    public function updateImage(Request $request, $id)
    {
        $vendorId = auth('vendor')->id();
        $product = Product::where('seller_id', $vendorId)->findOrFail($id);

        $request->validate([
            'featured_image' => 'required|string|max:1000',
        ]);

        $product->featured_image = $request->featured_image;
        $product->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product image updated successfully!',
                'featured_image' => $product->featured_image
            ]);
        }

        return redirect()->back()->with('success', 'Product image updated successfully!');
    }

    public function submitApproval($id)
    {
        $product = Product::where('seller_id', auth('vendor')->id())->findOrFail($id);

        if ($product->status === 'Approved' || $product->status === 'Pending Approval') {
            return back()->with('error', 'Product is already approved or pending approval.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $product->status;
            $product->status = 'Pending Approval';
            $product->save();

            ProductAuditLog::create([
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'modified_by' => auth('vendor')->id(),
                'approval_status' => 'Pending Approval',
                'admin_remarks' => 'Submitted by Seller',
            ]);

            $this->notifyAdmins(
                'Product Submitted for Approval',
                "Seller '" . auth('vendor')->user()->name . "' has submitted '{$product->product_name}' for approval.",
                route('admin.products.approvals.show', $product->id)
            );

            DB::commit();
            return back()->with('success', 'Product submitted for approval successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to submit product: ' . $e->getMessage());
        }
    }

    public function toggleActiveStatus($id)
    {
        $product = Product::where('seller_id', auth('vendor')->id())->findOrFail($id);

        if ($product->status === 'Approved') {
            $product->status = 'Inactive';
            $product->save();

            ProductAuditLog::create([
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'modified_by' => auth('vendor')->id(),
                'approval_status' => 'Inactive',
                'admin_remarks' => 'Deactivated by Seller',
            ]);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'status' => 'Inactive',
                    'status_label' => 'Inactive',
                    'badge_class' => 'bg-danger-subtle text-danger',
                    'message' => 'Product deactivated successfully.'
                ]);
            }

            return back()->with('success', 'Product deactivated successfully.');
        } elseif ($product->status === 'Inactive') {
            $product->status = 'Approved';
            $product->save();

            ProductAuditLog::create([
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'modified_by' => auth('vendor')->id(),
                'approval_status' => 'Approved',
                'admin_remarks' => 'Activated by Seller',
            ]);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'status' => 'Approved',
                    'status_label' => 'Active',
                    'badge_class' => 'bg-success-subtle text-success',
                    'message' => 'Product activated successfully.'
                ]);
            }

            return back()->with('success', 'Product activated successfully.');
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Only approved or inactive products can be toggled.'
            ], 422);
        }

        return back()->with('error', 'Only approved or inactive products can be toggled.');
    }

    public function destroy($id)
    {
        $product = Product::where('seller_id', auth('vendor')->id())->findOrFail($id);

        // Remove assignments
        $product->subSellerAssignments()->delete();
        $product->auditLogs()->delete();
        $product->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
                'deleted' => true
            ]);
        }

        return back()->with('success', 'Product deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,submit',
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        DB::beginTransaction();
        try {
            if ($action === 'delete') {
                foreach ($ids as $id) {
                    $product = Product::where('seller_id', auth('vendor')->id())->find($id);
                    if ($product) {
                        $product->subSellerAssignments()->delete();
                        $product->auditLogs()->delete();
                        $product->delete();
                    }
                }
                DB::commit();
                return back()->with('success', 'Selected products deleted successfully.');
            } elseif ($action === 'submit') {
                foreach ($ids as $id) {
                    $product = Product::where('seller_id', auth('vendor')->id())->find($id);
                    if ($product && ($product->status === 'Draft' || $product->status === 'Rejected')) {
                        $product->status = 'Pending Approval';
                        $product->save();

                        ProductAuditLog::create([
                            'product_id' => $product->id,
                            'product_name' => $product->product_name,
                            'modified_by' => auth('vendor')->id(),
                            'approval_status' => 'Pending Approval',
                            'admin_remarks' => 'Bulk Submitted by Seller',
                        ]);

                        $this->notifyAdmins(
                            'Product Submitted for Approval',
                            "Seller '" . auth('vendor')->user()->name . "' has submitted '{$product->product_name}' for approval.",
                            route('admin.products.approvals.show', $product->id)
                        );
                    }
                }
                DB::commit();
                return back()->with('success', 'Selected products submitted for approval.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }

    private function normalizeHeaderArray(array $firstRow): array
    {
        $header = [];
        $aliasMap = [
            'product' => 'product_name',
            'name' => 'product_name',
            'product_title' => 'product_name',
            'cat' => 'category',
            'category_name' => 'category',
            'category_id' => 'category',
            'brand_name' => 'brand',
            'brand_id' => 'brand',
            'hsn' => 'hsn_code',
            'hsn_number' => 'hsn_code',
            'mrp_price' => 'mrp',
            'mrp_incl_gst' => 'mrp',
            'seller_price' => 'seller_price_incl_gst',
            'selling_price' => 'seller_price_incl_gst',
            'seller_price_inc_gst' => 'seller_price_incl_gst',
            'gst' => 'gst_rate',
            'gst_percentage' => 'gst_rate',
            'gst_rate_percentage' => 'gst_rate',
            'keywords' => 'search_keywords',
            'product_keywords' => 'search_keywords',
            'tags' => 'search_keywords',
            'search_keywords_tags' => 'search_keywords',
            'uom' => 'unit_of_measure',
            'unit' => 'unit_of_measure',
            'pack_quantity' => 'case_pack_quantity',
            'case_pack' => 'case_pack_quantity',
            'image' => 'featured_image',
            'feature_image' => 'featured_image',
        ];

        foreach ($firstRow as $idx => $h) {
            $val = strtolower(trim((string)$h));
            $val = preg_replace('/[^a-z0-9]+/', '_', $val);
            $val = trim(preg_replace('/_+/', '_', $val), '_');
            if (empty($val)) {
                $val = 'col_' . $idx;
            }
            $val = $aliasMap[$val] ?? $val;
            $header[] = $val;
        }

        $counts = [];
        $uniqueHeader = [];
        foreach ($header as $key) {
            if (!isset($counts[$key])) {
                $counts[$key] = 0;
                $uniqueHeader[] = $key;
            } else {
                $counts[$key]++;
                $uniqueHeader[] = $key . '_' . $counts[$key];
            }
        }

        return $uniqueHeader;
    }

    private static function parseNumericVal($val, float $default = 0.0): float
    {
        if (is_null($val) || $val === '') return $default;
        $str = (string)$val;
        $clean = preg_replace('/[^\d.]/', '', str_replace(',', '', $str));
        return is_numeric($clean) ? (float)$clean : $default;
    }

    private static function parseBarcodeVal($val): ?string
    {
        if (is_null($val) || $val === '') return null;
        $str = trim((string)$val);
        if ($str === '') return null;
        if (stripos($str, 'e+') !== false || stripos($str, 'e-') !== false) {
            $str = sprintf('%.0f', (float)$str);
        }
        return $str;
    }

    public function downloadSample()
    {
        // Enforce allowance config
        if (auth('vendor')->user()->allow_own_products !== 'Yes') {
            abort(403);
        }

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ProductSampleExport(), 'product_bulk_sample.xlsx');
    }

    public function bulkImport(Request $request)
    {
        // Enforce allowance config
        if (auth('vendor')->user()->allow_own_products !== 'Yes') {
            abort(403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        $file = $request->file('file');
        $import = new \App\Imports\ProductImport();

        try {
            \Maatwebsite\Excel\Facades\Excel::import($import, $file);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to parse file: ' . $e->getMessage());
        }

        $rows = $import->rows;
        if (empty($rows) || count($rows) < 2) {
            return back()->with('error', 'The uploaded file contains no data rows.');
        }

        // Normalize header keys
        $header = $this->normalizeHeaderArray($rows[0]);

        // Pre-fetch Categories and Brands for fast lookup
        $categories = DB::table('categories')->where('status', '1')->get(['id', 'name']);
        $catNameMap = [];
        $catIdMap = [];
        foreach ($categories as $c) {
            $catIdMap[$c->id] = $c->id;
            $cleanCatName = mb_strtolower(trim(preg_replace('/\s+/', ' ', $c->name)));
            $catNameMap[$cleanCatName] = $c->id;
        }

        $brands = DB::table('brands')->where('status', '1')->get(['id', 'name']);
        $brandNameMap = [];
        $brandIdMap = [];
        foreach ($brands as $b) {
            $brandIdMap[$b->id] = $b->id;
            $cleanBrandName = mb_strtolower(trim(preg_replace('/\s+/', ' ', $b->name)));
            $brandNameMap[$cleanBrandName] = $b->id;
        }

        $hsnCodes = [];
        $barcodes = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(array_filter($row, fn($v) => !is_null($v) && trim((string)$v) !== ''))) {
                continue;
            }
            if (count($header) !== count($row)) {
                $row = array_slice(array_pad($row, count($header), null), 0, count($header));
            }
            $data = array_combine($header, $row);

            $hsnCode = trim((string)($data['hsn_code'] ?? ''));
            if ($hsnCode !== '') {
                $hsnCodes[] = $hsnCode;
                $hsnCodes[] = str_pad($hsnCode, 4, '0', STR_PAD_LEFT);
                $hsnCodes[] = str_pad($hsnCode, 8, '0', STR_PAD_LEFT);
            }

            $barcode = self::parseBarcodeVal($data['barcode'] ?? null);
            if ($barcode) {
                $barcodes[] = $barcode;
            }
        }

        $hsnCodes = array_unique($hsnCodes);
        $barcodes = array_unique($barcodes);

        $validHsns = DB::table('hsns')
            ->whereIn('hsn_code', $hsnCodes)
            ->where('status', '1')
            ->get()
            ->keyBy('hsn_code');

        $existingBarcodes = Product::whereIn('barcode', $barcodes)
            ->pluck('barcode')
            ->toArray();
        $existingBarcodeMap = array_fill_keys($existingBarcodes, true);

        $imported = 0;
        $skipped = 0;
        $failed = 0;
        $details = [];

        for ($i = 1; $i < count($rows); $i++) {
            $rowNum = $i + 1;
            $row = $rows[$i];

            if (empty(array_filter($row, fn($v) => !is_null($v) && trim((string)$v) !== ''))) {
                continue;
            }

            if (count($header) !== count($row)) {
                $row = array_slice(array_pad($row, count($header), null), 0, count($header));
            }

            $data = array_combine($header, $row);

            $productName = trim((string)($data['product_name'] ?? ''));
            $mrp = self::parseNumericVal($data['mrp'] ?? 0);
            $sellerPrice = self::parseNumericVal($data['seller_price_incl_gst'] ?? $data['seller_price'] ?? 0);
            $barcode = self::parseBarcodeVal($data['barcode'] ?? null);
            $hsnCode = trim((string)($data['hsn_code'] ?? ''));

            if (empty($productName)) {
                $skipped++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => 'N/A',
                    'status' => 'Skipped',
                    'reason' => 'Product Name is empty.'
                ];
                continue;
            }

            // Resolve Category by Name or ID (falling back to request category_id/category)
            $rawCat = trim((string)($data['category'] ?? $data['category_name'] ?? $data['category_id'] ?? ''));
            if ($rawCat === '') {
                $rawCat = trim((string)($request->input('category_id') ?? $request->input('category') ?? ''));
            }

            $catId = null;
            if (is_numeric($rawCat) && isset($catIdMap[(int) $rawCat])) {
                $catId = (int) $rawCat;
            } else if (!empty($rawCat)) {
                $cleanCat = mb_strtolower(trim(preg_replace('/\s+/', ' ', $rawCat)));
                if (isset($catNameMap[$cleanCat])) {
                    $catId = $catNameMap[$cleanCat];
                }
            }

            if (empty($catId)) {
                $skipped++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => $productName,
                    'status' => 'Skipped',
                    'reason' => "Category '{$rawCat}' not found or inactive in database."
                ];
                continue;
            }

            // Resolve Brand by Name or ID (falling back to request brand_id/brand)
            $rawBrand = trim((string)($data['brand'] ?? $data['brand_name'] ?? $data['brand_id'] ?? ''));
            if ($rawBrand === '') {
                $rawBrand = trim((string)($request->input('brand_id') ?? $request->input('brand') ?? ''));
            }

            $brandId = null;
            if (is_numeric($rawBrand) && isset($brandIdMap[(int) $rawBrand])) {
                $brandId = (int) $rawBrand;
            } else if (!empty($rawBrand)) {
                $cleanBrand = mb_strtolower(trim(preg_replace('/\s+/', ' ', $rawBrand)));
                if (isset($brandNameMap[$cleanBrand])) {
                    $brandId = $brandNameMap[$cleanBrand];
                }
            }

            if (empty($brandId)) {
                $skipped++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => $productName,
                    'status' => 'Skipped',
                    'reason' => "Brand '{$rawBrand}' not found or inactive in database."
                ];
                continue;
            }

            if ($mrp <= 0 || $sellerPrice <= 0) {
                $skipped++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => $productName,
                    'status' => 'Skipped',
                    'reason' => "Invalid pricing values (MRP: {$mrp}, Seller Price: {$sellerPrice})."
                ];
                continue;
            }

            if ($mrp < $sellerPrice) {
                $skipped++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => $productName,
                    'status' => 'Skipped',
                    'reason' => "MRP cannot be less than Seller Price."
                ];
                continue;
            }

            // Validate HSN Code
            if (empty($hsnCode)) {
                $skipped++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => $productName,
                    'status' => 'Skipped',
                    'reason' => "HSN Code is required."
                ];
                continue;
            }

            $padded4 = str_pad($hsnCode, 4, '0', STR_PAD_LEFT);
            $padded8 = str_pad($hsnCode, 8, '0', STR_PAD_LEFT);

            $hsn = $validHsns->get($hsnCode) ?? $validHsns->get($padded4) ?? $validHsns->get($padded8);
            if (!$hsn) {
                $hsnDb = DB::table('hsns')
                    ->where('hsn_code', $hsnCode)
                    ->orWhere('hsn_code', $padded4)
                    ->orWhere('hsn_code', $padded8)
                    ->where('status', '1')
                    ->first();
                if ($hsnDb) {
                    $hsn = $hsnDb;
                }
            }

            if (!$hsn) {
                $skipped++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => $productName,
                    'status' => 'Skipped',
                    'reason' => "HSN Code '{$hsnCode}' is invalid or inactive."
                ];
                continue;
            }

            // GST Rate handling (custom input from file, fallback to HSN master)
            $userGst = $data['gst_rate'] ?? null;
            if ($userGst !== null && $userGst !== '' && is_numeric($userGst)) {
                $gstRate = (float) $userGst;
            } else {
                $gstRate = (float) $hsn->gst_percentage;
            }

            // Search keywords / tags handling
            $rawKeywords = trim($data['search_keywords___tagsv'] ?? $data['search_keywords_/_tagsv'] ?? $data['search_keywords_tagsv'] ?? $data['search_keywords'] ?? $data['product_keywords'] ?? $data['tags'] ?? '');
            $keywordsArray = [];
            if (!empty($rawKeywords)) {
                $keywordsArray = array_values(array_filter(array_map('trim', explode(',', $rawKeywords))));
            }

            // Barcode handling
            if (empty($barcode)) {
                $barcode = null;
            }

            // Populate product data
            $productData = [
                'product_name' => $productName,
                'barcode' => $barcode,
                'seller_id' => auth('vendor')->id(),
                'brand_id' => $brandId,
                'category_id' => $catId,
                'sub_category_id' => null,
                'child_category_id' => null,
                'hsn_code' => $hsnCode,
                'gst_rate' => $gstRate,
                'mrp' => $mrp,
                'seller_price_incl_gst' => $sellerPrice,
                'unit_of_measure' => trim($data['unit_of_measure'] ?? 'Piece'),
                'product_keywords' => $keywordsArray,
                'case_pack_quantity' => !empty($data['case_pack_quantity']) ? (int) $data['case_pack_quantity'] : 1,
                'description' => trim($data['description'] ?? $productName),
                'product_specification' => trim($data['product_specification'] ?? ''),
                'storage_instructions' => trim($data['storage_instructions'] ?? ''),
                'country_of_origin' => trim($data['country_of_origin'] ?? 'India'),
                'shelf_life' => trim($data['shelf_life'] ?? ''),
                'length' => !empty($data['length']) ? (float) $data['length'] : null,
                'width' => !empty($data['width']) ? (float) $data['width'] : null,
                'height' => !empty($data['height']) ? (float) $data['height'] : null,
                'dimension_unit' => trim($data['dimension_unit'] ?? 'cm'),
                'weight' => !empty($data['weight']) ? (float) $data['weight'] : null,
                'weight_unit' => trim($data['weight_unit'] ?? 'gm'),
                'featured_image' => trim($data['feature_image'] ?? $data['featured_image'] ?? $data['image'] ?? 'https://via.placeholder.com/150'),
                'gallery' => [],
                'status' => 'Draft',
                'created_by' => auth('vendor')->id(),
            ];

            // Pricing calculation (Pick custom margins from Excel if provided)
            $adminMarginIn = $data['admin_margin_percentage'] ?? $data['admin_margin'] ?? $data['company_margin'] ?? null;
            $ddcMarginIn   = $data['ddc_margin_percentage'] ?? $data['ddc_margin'] ?? null;
            $mbpIn          = $data['mbp_percentage'] ?? $data['mbp_margin'] ?? $data['mbp'] ?? null;

            $adminMarginPercent = ($adminMarginIn !== null && $adminMarginIn !== '' && is_numeric($adminMarginIn)) ? (float) $adminMarginIn : null;
            $ddcMarginPercent   = ($ddcMarginIn !== null && $ddcMarginIn !== '' && is_numeric($ddcMarginIn)) ? (float) $ddcMarginIn : null;
            $mbpPercent         = ($mbpIn !== null && $mbpIn !== '' && is_numeric($mbpIn)) ? (float) $mbpIn : null;

            $pricing = self::calculatePricingData(
                $sellerPrice,
                $gstRate,
                $mrp,
                $adminMarginPercent,
                $ddcMarginPercent,
                $mbpPercent
            );
            $productData = array_merge($productData, $pricing);

            DB::beginTransaction();
            try {
                $product = Product::create($productData);

                // Add first log
                ProductAuditLog::create([
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'modified_by' => auth('vendor')->id(),
                    'approval_status' => 'Draft',
                    'updated_values' => $productData,
                ]);

                DB::commit();
                if ($barcode) {
                    $existingBarcodeMap[$barcode] = true;
                }
                $imported++;
            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => $productName,
                    'status' => 'Failed',
                    'reason' => $e->getMessage()
                ];
            }
        }

        $importResult = [
            'total' => count($rows) - 1,
            'inserted' => $imported,
            'skipped' => $skipped,
            'failed' => $failed,
            'details' => $details,
        ];

        $message = "Bulk upload completed. {$imported} products imported successfully.";
        if ($skipped > 0 || $failed > 0) {
            $message .= " " . ($skipped + $failed) . " products skipped or failed.";
        }

        return back()->with('success', $message)->with('import_result', $importResult);
    }

    public function downloadUpdateData()
    {
        // Enforce allowance config
        if (auth('vendor')->user()->allow_own_products !== 'Yes') {
            abort(403);
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProductUpdateExport(auth('vendor')->id()),
            'product_bulk_update.xlsx'
        );
    }

    public function bulkUpdate(Request $request)
    {
        // Enforce allowance config
        if (auth('vendor')->user()->allow_own_products !== 'Yes') {
            abort(403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        $file = $request->file('file');
        $import = new \App\Imports\ProductImport();

        try {
            \Maatwebsite\Excel\Facades\Excel::import($import, $file);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to parse file: ' . $e->getMessage());
        }

        $rows = $import->rows;
        if (empty($rows) || count($rows) < 2) {
            return back()->with('error', 'The uploaded file contains no data rows.');
        }

        // Normalize header keys
        $header = array_map(function ($h) {
            return strtolower(trim(str_replace([' ', '-'], '_', $h)));
        }, $rows[0]);

        $productIds = [];
        $hsnCodes = [];
        $barcodes = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(array_filter($row))) {
                continue;
            }
            if (count($header) !== count($row)) {
                $row = array_slice(array_pad($row, count($header), null), 0, count($header));
            }
            $data = array_combine($header, $row);

            $productId = !empty($data['id']) ? (int) $data['id'] : (!empty($data['product_id']) ? (int) $data['product_id'] : null);
            if ($productId) {
                $productIds[] = $productId;
            }

            $hsnCode = trim($data['hsn_code'] ?? '');
            if ($hsnCode) {
                $hsnCodes[] = $hsnCode;
            }

            $barcode = trim($data['barcode'] ?? '');
            if ($barcode) {
                $barcodes[] = $barcode;
            }
        }

        $productIds = array_unique($productIds);
        $hsnCodes = array_unique($hsnCodes);
        $barcodes = array_unique($barcodes);

        $productsLookup = Product::whereIn('id', $productIds)
            ->where('seller_id', auth('vendor')->id())
            ->get()
            ->keyBy('id');

        $validHsns = DB::table('hsns')
            ->whereIn('hsn_code', $hsnCodes)
            ->where('status', '1')
            ->get()
            ->keyBy('hsn_code');

        $existingBarcodes = Product::whereIn('barcode', $barcodes)
            ->select('id', 'barcode')
            ->get()
            ->keyBy('barcode');

        $updatedCount = 0;
        $skipped = 0;
        $failed = 0;
        $details = [];

        for ($i = 1; $i < count($rows); $i++) {
            $rowNum = $i + 1;
            $row = $rows[$i];

            // Ignore completely empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Pad or combine array if counts don't match
            if (count($header) !== count($row)) {
                $row = array_slice(array_pad($row, count($header), null), 0, count($header));
            }

            $data = array_combine($header, $row);

            // Fetch Product ID / ID
            $productId = !empty($data['id']) ? (int) $data['id'] : (!empty($data['product_id']) ? (int) $data['product_id'] : null);

            if (empty($productId)) {
                $skipped++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => 'N/A',
                    'status' => 'Skipped',
                    'reason' => 'Missing Product ID / ID column.'
                ];
                continue;
            }

            // Load product
            $product = $productsLookup->get($productId);

            if (!$product) {
                $skipped++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => "ID #{$productId}",
                    'status' => 'Skipped',
                    'reason' => "Product ID {$productId} not found or unauthorized."
                ];
                continue;
            }

            // Basic validation
            $productName = trim($data['product_name'] ?? '');
            $mrp = (float) ($data['mrp'] ?? 0);
            $sellerPrice = (float) ($data['seller_price_incl_gst'] ?? $data['seller_price'] ?? 0);
            $barcode = trim($data['barcode'] ?? '');
            $hsnCode = trim($data['hsn_code'] ?? '');

            if (empty($productName)) {
                $skipped++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => "ID #{$productId}",
                    'status' => 'Skipped',
                    'reason' => 'Product Name is empty.'
                ];
                continue;
            }

            if ($mrp <= 0 || $sellerPrice <= 0) {
                $skipped++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => $productName,
                    'status' => 'Skipped',
                    'reason' => "Invalid pricing values (MRP: {$mrp}, Seller Price: {$sellerPrice})."
                ];
                continue;
            }

            if ($mrp < $sellerPrice) {
                $skipped++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => $productName,
                    'status' => 'Skipped',
                    'reason' => "MRP cannot be less than Seller Price."
                ];
                continue;
            }

            // Validate HSN Code
            if (empty($hsnCode)) {
                $skipped++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => $productName,
                    'status' => 'Skipped',
                    'reason' => "HSN Code is required."
                ];
                continue;
            }

            $hsn = $validHsns->get($hsnCode);
            if (!$hsn) {
                $skipped++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => $productName,
                    'status' => 'Skipped',
                    'reason' => "HSN Code '{$hsnCode}' is invalid or inactive."
                ];
                continue;
            }

            // GST Rate handling (custom input from file, fallback to HSN master / existing product)
            $userGst = $data['gst_rate'] ?? null;
            if ($userGst !== null && $userGst !== '' && is_numeric($userGst)) {
                $gstRate = (float) $userGst;
            } elseif ($hsn) {
                $gstRate = (float) $hsn->gst_percentage;
            } else {
                $gstRate = (float) ($product->gst_rate ?? 0);
            }

            // Search keywords / tags handling
            $rawKeywords = trim($data['search_keywords___tagsv'] ?? $data['search_keywords_/_tagsv'] ?? $data['search_keywords_tagsv'] ?? $data['search_keywords'] ?? $data['product_keywords'] ?? $data['tags'] ?? '');
            $keywordsArray = [];
            if (!empty($rawKeywords)) {
                $keywordsArray = array_values(array_filter(array_map('trim', explode(',', $rawKeywords))));
            }

            // Barcode handling & unique check
            if (!empty($barcode) && $barcode !== $product->barcode) {
                $barcodeOwner = $existingBarcodes->get($barcode);
                if ($barcodeOwner && $barcodeOwner->id !== $product->id) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNum,
                        'data' => $productName,
                        'status' => 'Skipped',
                        'reason' => "Product with barcode '{$barcode}' already exists."
                    ];
                    continue;
                }
            } else {
                $barcode = $product->barcode;
            }

            // Populate updated values
            $productData = [
                'product_name' => $productName,
                'barcode' => $barcode,
                'hsn_code' => $hsnCode,
                'gst_rate' => $gstRate,
                'mrp' => $mrp,
                'seller_price_incl_gst' => $sellerPrice,
                'unit_of_measure' => trim($data['unit_of_measure'] ?? 'Piece'),
                'product_keywords' => $keywordsArray,
                'case_pack_quantity' => !empty($data['case_pack_quantity']) ? (int) $data['case_pack_quantity'] : ($product->case_pack_quantity ?? 1),
                'description' => trim($data['description'] ?? $productName),
                'product_specification' => trim($data['product_specification'] ?? ''),
                'storage_instructions' => trim($data['storage_instructions'] ?? ''),
                'country_of_origin' => trim($data['country_of_origin'] ?? 'India'),
                'shelf_life' => trim($data['shelf_life'] ?? ''),
                'length' => !empty($data['length']) ? (float) $data['length'] : null,
                'width' => !empty($data['width']) ? (float) $data['width'] : null,
                'height' => !empty($data['height']) ? (float) $data['height'] : null,
                'dimension_unit' => !empty(trim($data['dimension_unit'] ?? '')) ? trim($data['dimension_unit']) : ($product->dimension_unit ?? 'cm'),
                'weight' => !empty($data['weight']) ? (float) $data['weight'] : null,
                'weight_unit' => !empty(trim($data['weight_unit'] ?? '')) ? trim($data['weight_unit']) : ($product->weight_unit ?? 'gm'),
                'featured_image' => trim($data['feature_image'] ?? $data['featured_image'] ?? $data['image'] ?? 'https://via.placeholder.com/150'),
            ];

            // Pricing engine calculations
            $pricing = self::calculatePricingData(
                $sellerPrice,
                $gstRate,
                $mrp,
                $product->admin_margin_percentage,
                $product->ddc_margin_percentage,
                $product->mbp_percentage
            );
            $productData = array_merge($productData, $pricing);

            // Compare attributes to build diff
            $previous = [];
            $updated = [];
            $ignoredKeys = ['updated_at', 'status', 'admin_remarks', 'created_at', 'created_by', 'updated_by'];

            foreach ($productData as $key => $val) {
                if (in_array($key, $ignoredKeys)) {
                    continue;
                }

                $origVal = $product->$key;

                if (is_array($origVal) || is_array($val)) {
                    $origCompare = is_array($origVal) ? json_encode($origVal) : json_encode([]);
                    $newCompare = is_array($val) ? json_encode($val) : json_encode([]);
                    if ($origCompare !== $newCompare) {
                        $previous[$key] = $origVal;
                        $updated[$key] = $val;
                    }
                } else {
                    if (is_numeric($origVal) && is_numeric($val)) {
                        if (abs((float) $origVal - (float) $val) > 0.0001) {
                            $previous[$key] = $origVal;
                            $updated[$key] = $val;
                        }
                    } else {
                        if ((string) $origVal !== (string) $val) {
                            $previous[$key] = $origVal;
                            $updated[$key] = $val;
                        }
                    }
                }
            }

            $hasChanges = count($updated) > 0;
            $oldStatus = $product->status;

            $newStatus = $oldStatus;
            if ($oldStatus === 'Approved' && $hasChanges) {
                $newStatus = 'Pending Approval';
            } elseif ($oldStatus === 'Rejected' && $hasChanges) {
                $newStatus = 'Draft';
            }

            DB::beginTransaction();
            try {
                if ($hasChanges) {
                    $product->fill($productData);
                    $product->status = $newStatus;
                    $product->save();

                    ProductAuditLog::create([
                        'product_id' => $product->id,
                        'product_name' => $product->product_name,
                        'modified_by' => auth('vendor')->id(),
                        'previous_values' => $previous,
                        'updated_values' => $updated,
                        'approval_status' => $newStatus,
                    ]);

                    if ($newStatus === 'Pending Approval') {
                        $this->notifyAdmins(
                            'Product Modification Pending Approval',
                            "Seller '" . auth('vendor')->user()->name . "' updated product '{$product->product_name}' via bulk update which is now pending re-approval.",
                            route('admin.products.approvals.show', $product->id)
                        );
                    }
                }

                DB::commit();
                if (!empty($barcode)) {
                    $existingBarcodes->put($barcode, (object) ['id' => $product->id, 'barcode' => $barcode]);
                }
                $updatedCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;
                $details[] = [
                    'row' => $rowNum,
                    'data' => $productName,
                    'status' => 'Failed',
                    'reason' => $e->getMessage()
                ];
            }
        }

        $importResult = [
            'type' => 'update',
            'total' => count($rows) - 1,
            'inserted' => $updatedCount,
            'skipped' => $skipped,
            'failed' => $failed,
            'details' => $details,
        ];

        $message = "Bulk update completed. {$updatedCount} products processed/updated.";
        if ($skipped > 0 || $failed > 0) {
            $message .= " " . ($skipped + $failed) . " products skipped or failed.";
        }

        return back()->with('success', $message)->with('import_result', $importResult);
    }

    public function getHsnsByCategory($id)
    {
        $hsns = DB::table('hsns')
            ->where('status', '1')
            ->select('id', 'hsn_code', 'description', 'gst_percentage')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($hsns);
    }

    public function ajaxSubCategories(Request $request)
    {
        $categoryId = $request->get('category_id');
        $subCategories = DB::table('sub_categories')
            ->where('category_id', $categoryId)
            ->where('status', '1')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        return response()->json($subCategories);
    }

    public function ajaxChildCategories(Request $request)
    {
        $subCategoryId = $request->get('sub_category_id');
        $childCategories = DB::table('child_categories')
            ->where('sub_category_id', $subCategoryId)
            ->where('status', '1')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        return response()->json($childCategories);
    }

    public static function calculatePricingData($sellerPrice, $gstRate, $mrp, $companyMarginPercent = null, $ddcMarginPercent = null, $mbpPercent = null)
    {
        return \App\Services\PricingService::calculate(
            (float) $sellerPrice,
            (float) $gstRate,
            (float) $mrp,
            $companyMarginPercent !== null ? (float) $companyMarginPercent : null,
            $ddcMarginPercent !== null ? (float) $ddcMarginPercent : null,
            $mbpPercent !== null ? (float) $mbpPercent : null
        );
    }

    public function assignedProducts(Request $request)
    {
        $user = auth('vendor')->user();
        if ($user->parent_id === null) {
            return redirect()->route('vendor.products.index');
        }

        $search = $request->get('search');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        // Fetch all approved products from the parent seller
        $query = Product::where('seller_id', $user->parent_id)
            ->where('status', 'Approved')
            ->select('id', 'product_name', 'barcode', 'featured_image', 'seller_price_incl_gst', 'mrp', 'gst_rate', 'brand_id', 'category_id')
            ->with(['brand:id,name', 'category:id,name']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        // Get sub-seller customizations
        $customizations = DB::table('sub_seller_products')
            ->where('sub_seller_id', $user->id)
            ->get()
            ->keyBy('product_id');

        return view('vendor.products.assigned', compact('products', 'customizations', 'search', 'limit', 'sort', 'order'));
    }

    public function updateMarkup(Request $request, $productId)
    {
        $user = auth('vendor')->user();
        if ($user->parent_id === null) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Verify product belongs to parent and is approved
        $product = Product::where('seller_id', $user->parent_id)
            ->where('status', 'Approved')
            ->findOrFail($productId);

        $request->validate([
            'extra_price' => 'required|numeric|min:0',
        ]);

        $extraPrice = (float) $request->extra_price;
        $subExFactoryPrice = $product->seller_price_incl_gst + $extraPrice;

        if ($subExFactoryPrice > $product->mrp) {
            return response()->json([
                'success' => false,
                'message' => 'Sub-seller price (₹' . number_format($subExFactoryPrice, 2) . ') cannot exceed MRP (₹' . number_format($product->mrp, 2) . ').'
            ], 422);
        }

        // Perform pricing calculation
        $pricing = \App\Services\PricingService::calculate(
            $subExFactoryPrice,
            (float) $product->gst_rate,
            (float) $product->mrp,
            $product->admin_margin_percentage,
            $product->ddc_margin_percentage,
            $product->mbp_percentage
        );

        // Update or create customization record using Eloquent Model
        \App\Models\SubSellerProduct::updateOrCreate(
            [
                'sub_seller_id' => $user->id,
                'product_id' => $product->id,
            ],
            array_merge([
                'margin' => $extraPrice, // store extra markup in margin
                'final_price' => $subExFactoryPrice, // store computed sub-seller price in final_price
            ], $pricing)
        );

        return response()->json([
            'success' => true,
            'message' => 'Product pricing updated successfully.',
            'sub_ex_factory' => number_format($subExFactoryPrice, 2),
            'base_cost' => number_format($pricing['base_cost'], 2),
            'admin_margin_percentage' => number_format($pricing['admin_margin_percentage'] ?? 0, 2),
            'admin_margin_amount' => number_format($pricing['admin_margin_amount'], 2),
            'ddc_margin_percentage' => number_format($pricing['ddc_margin_percentage'] ?? 0, 2),
            'ddc_margin_amount' => number_format($pricing['ddc_margin_amount'], 2),
            'mbp_percentage' => number_format($pricing['mbp_percentage'] ?? 0, 2),
            'mbp_amount' => number_format($pricing['mbp_amount'], 2),
            'ddc_purchase_price' => number_format($pricing['ddc_purchase_price'], 2),
            'adc_purchase_price' => number_format($pricing['adc_purchase_price'], 2),
            'adc_final_margin' => number_format($pricing['adc_final_margin'], 2),
        ]);
    }

    public function toggleAssignedStatus(Request $request, $productId)
    {
        $user = auth('vendor')->user();
        if ($user->parent_id === null) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $product = Product::where('seller_id', $user->parent_id)
            ->where('status', 'Approved')
            ->findOrFail($productId);

        $request->validate([
            'status' => 'required|in:Active,Inactive',
        ]);

        $subSellerProduct = \App\Models\SubSellerProduct::firstOrCreate(
            [
                'sub_seller_id' => $user->id,
                'product_id' => $product->id,
            ],
            [
                'margin' => 0.00,
                'final_price' => $product->seller_price_incl_gst,
                'status' => 'Active',
            ]
        );

        $subSellerProduct->status = $request->status;
        $subSellerProduct->save();

        if ($subSellerProduct->wasRecentlyCreated) {
            $subSellerProduct->recalculatePricing();
        }

        return response()->json([
            'success' => true,
            'message' => 'Product status updated to ' . $request->status . '.',
            'status' => $subSellerProduct->status,
        ]);
    }

    public function quickStoreBrand(Request $request)
    {
        if (auth('vendor')->user()->allow_own_products !== 'Yes') {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = trim($request->name);
        if (empty($name)) {
            return response()->json(['success' => false, 'message' => 'Brand name cannot be empty.'], 422);
        }

        $brand = Brand::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

        if ($brand) {
            if ($brand->status !== '1') {
                $brand->status = '1';
                $brand->save();
            }
        } else {
            $slug = \Illuminate\Support\Str::slug($name);
            $originalSlug = $slug;
            $count = 1;
            while (Brand::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $brand = Brand::create([
                'name' => $name,
                'slug' => $slug,
                'status' => '1',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Brand added successfully.',
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
            ]
        ]);
    }
}
