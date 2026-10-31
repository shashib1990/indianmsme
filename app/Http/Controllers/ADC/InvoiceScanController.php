<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcInventory;
use App\Models\AdcLocalProduct;
use App\Models\AdcLocalPurchase;
use App\Models\AdcLocalPurchaseItem;
use App\Models\Brand;
use App\Models\Category;
use App\Models\UnitOfMeasure;
use App\Services\GeminiInvoiceExtractorService;
use App\Services\ProductMatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceScanController extends Controller
{
    protected ProductMatchingService $matchingService;
    protected GeminiInvoiceExtractorService $geminiService;

    public function __construct(
        ProductMatchingService $matchingService,
        GeminiInvoiceExtractorService $geminiService
    ) {
        $this->matchingService = $matchingService;
        $this->geminiService = $geminiService;
    }

    /**
     * Show the Smart Invoice Scanner & Import Page
     */
    public function index()
    {
        $adcId = Auth::guard('adc')->id();

        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::active()->orderBy('name')->get();
        $units = UnitOfMeasure::getUnitsList();

        $localProducts = AdcLocalProduct::where('adc_id', $adcId)
            ->with(['category', 'brand'])
            ->orderBy('product_name')
            ->get();

        $geminiConfigured = $this->geminiService->isConfigured();
        $geminiModel = $this->geminiService->getModel();

        return view('adc.local-inventory.invoice-scan', compact('categories', 'brands', 'units', 'localProducts', 'geminiConfigured', 'geminiModel'));
    }

    /**
     * Process uploaded invoice file (or camera capture) exclusively via Google Gemini AI
     */
    public function process(Request $request): JsonResponse
    {
        $adcId = Auth::guard('adc')->id();

        try {
            if (!$this->geminiService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI is not configured. Please set the API key in the .env file.',
                ], 422);
            }

            $fileData = null;

            if ($request->hasFile('invoice_file')) {
                $file = $request->file('invoice_file');
                $request->validate([
                    'invoice_file' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:20480',
                ]);
                $fileData = $this->storeUploadedInvoiceFile($file, $adcId);
            } elseif ($request->filled('captured_image')) {
                $base64 = $request->input('captured_image');
                $fileData = $this->storeCapturedInvoiceImage($base64, $adcId);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide an invoice file (Image/PDF) or capture via camera.',
                ], 422);
            }

            // Extract structured data using AI Multimodal Vision
            $parsed = $this->geminiService->extract($fileData['full_path'], $fileData['mime_type']);

            $extractionEngine = 'ai';
            $engineLabel = 'AI';
            $engineNotice = 'Extracted using AI vision.';

            // Match extracted products against local & central product masters
            $matchedProducts = $this->matchingService->matchProducts($parsed['products'] ?? [], $adcId);

            // Check if this invoice already exists in the system to prevent duplicate entries
            $duplicateWarning = null;
            if (!empty($parsed['invoice_number']) && !empty($parsed['supplier_name'])) {
                $exists = AdcLocalPurchase::where('adc_id', $adcId)
                    ->where('invoice_number', $parsed['invoice_number'])
                    ->where('supplier_name', $parsed['supplier_name'])
                    ->first();

                if ($exists) {
                    $duplicateWarning = "Warning: An invoice with number '{$parsed['invoice_number']}' from supplier '{$parsed['supplier_name']}' was already imported on " . $exists->created_at->format('d M Y') . " (Purchase #{$exists->purchase_no}).";
                }
            }

            return response()->json([
                'success' => true,
                'engine' => $extractionEngine,
                'engine_label' => $engineLabel,
                'engine_notice' => $engineNotice,
                'preview_url' => $fileData['preview_url'],
                'file_name' => $fileData['file_name'],
                'file_path' => $fileData['file_path'],
                'is_pdf' => $fileData['is_pdf'],
                'page_count' => 1,
                'method' => 'AI_VISION',
                'supplier_name' => $parsed['supplier_name'],
                'supplier_mobile' => $parsed['supplier_mobile'],
                'supplier_gstin' => $parsed['supplier_gstin'],
                'invoice_number' => $parsed['invoice_number'],
                'invoice_date' => $parsed['invoice_date'],
                'purchase_date' => $parsed['purchase_date'],
                'total_amount' => $parsed['total_amount'],
                'is_reconciled' => $parsed['is_reconciled'],
                'items_calculated' => $parsed['items_calculated'],
                'reconciliation_diff' => $parsed['reconciliation_diff'],
                'reconciliation_note' => $parsed['reconciliation_note'],
                'products' => $matchedProducts,
                'duplicate_warning' => $duplicateWarning,
            ]);

        } catch (\Throwable $e) {
            Log::error('Invoice Scan Process Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process invoice via AI: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store uploaded invoice file to public disk for browser viewing
     */
    protected function storeUploadedInvoiceFile($file, int $adcId): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        $storedDir = "adc_invoices/{$adcId}";
        $filename = 'inv_' . time() . '_' . Str::random(8) . '.' . $extension;
        $path = $file->storeAs($storedDir, $filename, 'public');
        $fullPath = storage_path("app/public/{$path}");
        $publicUrl = asset("storage/{$path}");
        $isPdf = ($extension === 'pdf' || str_contains($mime, 'pdf'));

        return [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'full_path' => $fullPath,
            'preview_url' => $publicUrl,
            'is_pdf' => $isPdf,
            'mime_type' => $isPdf ? 'application/pdf' : ($extension === 'png' ? 'image/png' : ($extension === 'webp' ? 'image/webp' : 'image/jpeg')),
        ];
    }

    /**
     * Store camera snapshot (base64) to public disk for browser viewing
     */
    protected function storeCapturedInvoiceImage(string $base64Data, int $adcId): array
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            $ext = strtolower($type[1]);
            if ($ext === 'jpeg')
                $ext = 'jpg';
        } else {
            $ext = 'jpg';
        }

        $decoded = base64_decode($base64Data);
        if ($decoded === false) {
            throw new \InvalidArgumentException('Invalid base64 camera image data.');
        }

        $storedDir = "adc_invoices/{$adcId}";
        $filename = 'inv_cam_' . time() . '_' . Str::random(8) . '.' . $ext;
        $relativeDir = storage_path("app/public/{$storedDir}");
        if (!file_exists($relativeDir)) {
            mkdir($relativeDir, 0755, true);
        }

        $fullPath = "{$relativeDir}/{$filename}";
        file_put_contents($fullPath, $decoded);

        $path = "{$storedDir}/{$filename}";
        $publicUrl = asset("storage/{$path}");

        return [
            'file_name' => $filename,
            'file_path' => $path,
            'full_path' => $fullPath,
            'preview_url' => $publicUrl,
            'is_pdf' => false,
            'mime_type' => ($ext === 'png') ? 'image/png' : 'image/jpeg',
        ];
    }

    /**
     * Save confirmed invoice & perform stock-in
     */
    public function save(Request $request): JsonResponse
    {
        $adcId = Auth::guard('adc')->id();

        $request->validate([
            'purchase_date' => 'required|date',
            'supplier_name' => 'required|string|max:255',
            'supplier_mobile' => 'nullable|string|max:20',
            'invoice_number' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'prevent_duplicate' => 'nullable|boolean',

            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.local_product_id' => 'nullable|string',
            'items.*.category_id' => 'required|integer|exists:categories,id',
            'items.*.brand_id' => 'nullable|integer',
            'items.*.unit_of_measure' => 'required|string',
            'items.*.hsn_code' => 'nullable|string|max:50',
            'items.*.gst_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.mrp' => 'required|numeric|min:0',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.csp' => 'required|numeric|min:0.01',
            'items.*.barcode' => 'nullable|string|max:100',
            'items.*.batch_number' => 'nullable|string|max:100',
            'items.*.mfg_date' => 'nullable|date',
            'items.*.expire_date' => 'nullable|date',
        ], [
            'items.*.category_id.required' => 'Please select a Category for all products (cannot be left as No Selection).',
            'items.*.category_id.exists' => 'The selected Category is invalid.',
            'items.*.csp.required' => 'Customer Sale Price (CSP) must be entered manually for all items.',
            'items.*.csp.min' => 'Customer Sale Price (CSP) must be greater than 0 for all items.',
        ]);

        // Duplicate Submission Check: Ensure same invoice isn't submitted simultaneously twice
        if (!empty($request->invoice_number) && !empty($request->supplier_name)) {
            $duplicateCheck = AdcLocalPurchase::where('adc_id', $adcId)
                ->where('invoice_number', $request->invoice_number)
                ->where('supplier_name', $request->supplier_name)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->first();

            if ($duplicateCheck && !$request->boolean('allow_duplicate')) {
                return response()->json([
                    'success' => false,
                    'is_duplicate_error' => true,
                    'message' => "This invoice (#{$request->invoice_number}) was already saved just moments ago (Purchase #{$duplicateCheck->purchase_no}). Stock has already been added.",
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $purchaseNo = AdcLocalPurchase::generatePurchaseNo();
            $totalAmount = 0;

            foreach ($request->items as $itemData) {
                $qty = (float) $itemData['quantity'];
                $cost = (float) $itemData['purchase_price'];
                $totalAmount += ($cost * $qty);
            }

            // Create Local Purchase Record
            $purchase = AdcLocalPurchase::create([
                'adc_id' => $adcId,
                'purchase_no' => $purchaseNo,
                'purchase_date' => $request->purchase_date,
                'supplier_name' => $request->supplier_name,
                'supplier_mobile' => $request->supplier_mobile,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'freight_charges' => 0,
                'loading_unloading_charges' => 0,
                'other_charges' => 0,
                'total_amount' => round($totalAmount, 2),
                'remarks' => $request->remarks ?: 'Imported via Smart Invoice Scanner',
            ]);

            foreach ($request->items as $itemData) {
                $productId = $itemData['local_product_id'] ?? null;
                $pName = trim($itemData['product_name']);
                $qty = (float) $itemData['quantity'];
                $mrp = (float) $itemData['mrp'];
                $purchasePrice = (float) $itemData['purchase_price'];
                $csp = (float) $itemData['csp'];
                $barcode = !empty($itemData['barcode']) ? trim($itemData['barcode']) : null;
                $hsn = !empty($itemData['hsn_code']) ? trim($itemData['hsn_code']) : null;
                $gstRate = (float) ($itemData['gst_rate'] ?? 0);
                $uom = $itemData['unit_of_measure'] ?? 'Piece';
                $categoryId = (int) $itemData['category_id'];
                $brandId = !empty($itemData['brand_id']) ? (int) $itemData['brand_id'] : null;

                // CSP is strictly entered manually by the user; no auto-calculation fallback

                $product = null;

                // Case 1: Linked to an existing AdcLocalProduct
                if (!empty($productId) && $productId !== 'NEW' && !str_starts_with($productId, 'INV_')) {
                    $product = AdcLocalProduct::where('adc_id', $adcId)->find($productId);
                }

                // Case 2: Linked to an existing inventory item
                if (!$product && !empty($productId) && str_starts_with($productId, 'INV_')) {
                    $invId = (int) str_replace('INV_', '', $productId);
                    $inv = AdcInventory::where('adc_id', $adcId)->find($invId);
                    if ($inv) {
                        $product = AdcLocalProduct::firstOrCreate(
                            ['adc_id' => $adcId, 'product_name' => $inv->product_name],
                            [
                                'local_product_code' => AdcLocalProduct::generateLocalProductCode(),
                                'category_id' => $categoryId,
                                'brand_id' => $brandId,
                                'hsn_code' => $hsn ?: ($inv->hsn_code ?? 'N/A'),
                                'gst_rate' => $gstRate,
                                'unit_of_measure' => $uom,
                                'barcode' => $barcode ?: $inv->barcode,
                                'status' => 'Active',
                            ]
                        );
                    }
                }

                // Case 3: Brand new regional product
                if (!$product) {
                    // Check if already created with exact same name to prevent duplicates
                    $product = AdcLocalProduct::where('adc_id', $adcId)
                        ->whereRaw('LOWER(TRIM(product_name)) = ?', [strtolower($pName)])
                        ->first();

                    if (!$product) {
                        $code = AdcLocalProduct::generateLocalProductCode();
                        $product = AdcLocalProduct::create([
                            'adc_id' => $adcId,
                            'local_product_code' => $code,
                            'product_name' => $pName,
                            'category_id' => $categoryId,
                            'brand_id' => $brandId,
                            'hsn_code' => $hsn,
                            'gst_rate' => $gstRate,
                            'unit_of_measure' => $uom,
                            'barcode' => $barcode,
                            'status' => 'Active',
                        ]);
                    }
                }

                // Margin calculation
                $cspMargin = $mrp > 0 ? round((($mrp - $csp) / $mrp) * 100, 2) : 0;

                // Create ADC Inventory record with inventory_source = 'LOCAL'
                $inventory = AdcInventory::create([
                    'adc_id' => $adcId,
                    'inventory_source' => 'LOCAL',
                    'product_id' => null,
                    'adc_local_product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'product_code' => $product->local_product_code,
                    'barcode' => $barcode ?: $product->barcode,
                    'unit_of_measure' => $uom,
                    'quantity' => (int) $qty,
                    'reserved_quantity' => 0,
                    'sold_quantity' => 0,
                    'returned_quantity' => 0,
                    'mrp' => $mrp,
                    'purchase_price' => $purchasePrice,
                    'landing_cost' => $purchasePrice,
                    'msp' => $csp,
                    'adc_price' => $csp,
                    'csp' => $csp,
                    'adc_margin' => $cspMargin,
                    'csp_margin' => $cspMargin,
                    'gst_rate' => $gstRate,
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'mfg_date' => $itemData['mfg_date'] ?? null,
                    'expire_date' => $itemData['expire_date'] ?? null,
                    'reference_type' => 'LOCAL_PURCHASE',
                    'reference_id' => $purchase->id,
                    'pos_status' => 'Active',
                ]);

                // Create Local Purchase Item
                AdcLocalPurchaseItem::create([
                    'adc_local_purchase_id' => $purchase->id,
                    'adc_local_product_id' => $product->id,
                    'quantity' => (int) $qty,
                    'purchase_price' => $purchasePrice,
                    'freight_share' => 0,
                    'loading_share' => 0,
                    'other_share' => 0,
                    'landing_cost' => $purchasePrice,
                    'mrp' => $mrp,
                    'csp' => $csp,
                    'msp' => $csp,
                    'gst_rate' => $gstRate,
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'mfg_date' => $itemData['mfg_date'] ?? null,
                    'expire_date' => $itemData['expire_date'] ?? null,
                    'adc_inventory_id' => $inventory->id,
                ]);

                if ($product->status !== 'Active') {
                    $product->update(['status' => 'Active']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Invoice #{$purchaseNo} successfully recorded and " . count($request->items) . " items stocked into Local Inventory.",
                'purchase_id' => $purchase->id,
                'redirect_url' => route('adc.local-purchases.show', $purchase->id),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Invoice Scan Save Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save invoice & stock in: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new Brand directly from the invoice scan interface
     */
    public function storeBrand(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $name = trim($request->name);

        $brand = Brand::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->first();
        if (!$brand) {
            $brand = Brand::create([
                'name' => $name,
                'status' => '1',
                'created_by' => Auth::guard('adc')->id() ?? Auth::id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Brand '{$brand->name}' saved successfully.",
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
            ],
        ]);
    }
}
