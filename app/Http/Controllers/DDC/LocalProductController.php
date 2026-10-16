<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\DdcInventory;
use App\Models\DdcLocalProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class LocalProductController extends Controller
{
    public const STANDARD_UNITS = [
        'Piece',
        'Box',
        'Pack',
        'Bottle',
        'Kg',
        'Gram',
        'Litre',
        'ML',
        'Pair',
        'Set',
        'Meter',
        'Dozen',
        'Container',
        'Other',
    ];

    public function index(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $search = trim($request->get('search', ''));
        $categoryId = $request->get('category_id');

        $query = DdcLocalProduct::where('ddc_id', $ddcId)
            ->with(['category', 'brand']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('local_product_code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $categories = Category::active()->orderBy('name')->get();

        return view('ddc.local-products.index', compact('products', 'categories', 'search', 'categoryId'));
    }

    public function create()
    {
        $ddcId = Auth::guard('ddc')->id();
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::active()->orderBy('name')->get();
        $units = \App\Models\UnitOfMeasure::getUnitsList();

        $existingProducts = DdcInventory::where('ddc_id', $ddcId)
            ->select('id', 'product_name', 'product_code', 'barcode', 'unit_of_measure')
            ->distinct()
            ->orderBy('product_name')
            ->get();

        return view('ddc.local-products.create', compact('categories', 'brands', 'units', 'existingProducts'));
    }

    public function searchSuggestions(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['success' => true, 'suggestions' => []]);
        }

        $products = Product::whereIn('status', ['Approved', 'Active', '1'])
            ->where(function ($query) use ($q) {
                $query->where('product_name', 'like', "%{$q}%")
                      ->orWhere('product_code', 'like', "%{$q}%")
                      ->orWhere('barcode', 'like', "%{$q}%");
            })
            ->with(['category', 'brand'])
            ->limit(15)
            ->get();

        $suggestions = $products->map(function ($p) {
            return [
                'type'            => 'MSME',
                'id'              => $p->id,
                'msme_product_id' => $p->id,
                'product_name'    => $p->product_name,
                'product_code'    => $p->product_code,
                'category_id'     => $p->category_id,
                'category_name'   => $p->category?->name ?? '—',
                'brand_id'        => $p->brand_id,
                'brand_name'      => $p->brand?->name ?? 'Unbranded',
                'hsn_code'        => $p->hsn_code,
                'gst_rate'        => (int) round((float) ($p->gst_rate ?? 0)),
                'uom'             => $p->unit_of_measure ?: 'Piece',
                'barcode'         => $p->barcode,
                'featured_image'  => $p->featured_image,
                'image_url'       => $p->featured_image ? (str_starts_with($p->featured_image, 'http://') || str_starts_with($p->featured_image, 'https://') || str_starts_with($p->featured_image, '/') ? $p->featured_image : asset('storage/' . $p->featured_image)) : null,
            ];
        });

        return response()->json([
            'success'     => true,
            'suggestions' => $suggestions,
        ]);
    }

    public function getCategoryHsn(Request $request)
    {
        $hsns = DB::table('hsns')
            ->where('status', '1')
            ->select('id', 'hsn_code', 'description', 'gst_percentage')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'hsns'    => $hsns,
        ]);
    }

    public function store(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();

        $request->validate([
            'product_name'    => 'required|string|max:255',
            'msme_product_id' => 'nullable|exists:products,id',
            'category_id'     => 'required|exists:categories,id',
            'hsn_code'        => 'required|string|max:50',
            'gst_rate'        => 'required|numeric|min:0|max:100',
            'unit_of_measure' => 'required|string|max:100',
            'brand_id'        => 'nullable|integer',
            'barcode'         => 'nullable|string|max:100',
            'product_image'   => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $hsn = DB::table('hsns')->where('hsn_code', $request->hsn_code)->first();
        if (!$hsn) {
            return back()
                ->withInput()
                ->withErrors(['hsn_code' => 'Invalid HSN Code selected.']);
        }

        $msmeProductId = $request->msme_product_id;
        $msmeProduct   = $msmeProductId ? Product::find($msmeProductId) : null;

        $imagePath = null;
        if ($request->hasFile('product_image')) {
            $imagePath = $this->uploadFileToService($request->file('product_image'), 'local_products');
        } elseif ($request->filled('fetched_image_url')) {
            $imagePath = trim($request->fetched_image_url);
        } elseif ($msmeProduct && !empty($msmeProduct->featured_image)) {
            $imagePath = $msmeProduct->featured_image;
        }

        if ($msmeProduct && !empty($msmeProduct->product_code)) {
            $code = $msmeProduct->product_code;
        } else {
            $code = DdcLocalProduct::generateLocalProductCode();
        }

        $validatedBrandId = null;
        if ($request->filled('brand_id') && (int) $request->brand_id > 0) {
            $checkBrand = (int) $request->brand_id;
            if (Brand::where('id', $checkBrand)->exists()) {
                $validatedBrandId = $checkBrand;
            }
        }

        $gstRate = $request->has('gst_rate') ? $request->gst_rate : $hsn->gst_percentage;

        // If local product already exists for this DDC & MSME product code, update it
        $existing = DdcLocalProduct::where('ddc_id', $ddcId)
            ->where(function ($q) use ($msmeProductId, $code) {
                if ($msmeProductId) {
                    $q->where('msme_product_id', $msmeProductId);
                }
                $q->orWhere('local_product_code', $code);
            })
            ->first();

        if ($existing) {
            $existing->update([
                'msme_product_id' => $msmeProductId ?: $existing->msme_product_id,
                'product_name'    => $request->product_name,
                'category_id'     => $request->category_id,
                'hsn_code'        => $hsn->hsn_code,
                'gst_rate'        => $gstRate,
                'brand_id'        => $validatedBrandId,
                'unit_of_measure' => $request->unit_of_measure,
                'barcode'         => $request->barcode,
                'product_image'   => $imagePath ?: $existing->product_image,
            ]);

            return redirect()
                ->route('ddc.local-products.index')
                ->with('success', "Regional Product '{$existing->product_name}' ({$existing->local_product_code}) updated successfully.");
        }

        $localProduct = DdcLocalProduct::create([
            'ddc_id'             => $ddcId,
            'msme_product_id'    => $msmeProductId,
            'local_product_code' => $code,
            'product_name'       => $request->product_name,
            'category_id'        => $request->category_id,
            'hsn_code'           => $hsn->hsn_code,
            'gst_rate'           => $gstRate,
            'brand_id'           => $validatedBrandId,
            'unit_of_measure'    => $request->unit_of_measure,
            'barcode'            => $request->barcode,
            'product_image'      => $imagePath,
            'status'             => 'Draft',
        ]);

        return redirect()
            ->route('ddc.local-products.index')
            ->with('success', "Regional Product '{$localProduct->product_name}' ({$code}) created successfully.");
    }

    public function show($id)
    {
        $ddcId = Auth::guard('ddc')->id();
        $product = DdcLocalProduct::where('ddc_id', $ddcId)
            ->with(['category', 'brand', 'inventories'])
            ->findOrFail($id);

        return view('ddc.local-products.show', compact('product'));
    }

    public function edit($id)
    {
        $ddcId = Auth::guard('ddc')->id();
        $product = DdcLocalProduct::where('ddc_id', $ddcId)->findOrFail($id);

        $categories = Category::active()->orderBy('name')->get();

        $brands = Brand::active()->orderBy('name')->get();
        $units = \App\Models\UnitOfMeasure::getUnitsList();

        return view('ddc.local-products.edit', compact('product', 'categories', 'brands', 'units'));
    }

    public function update(Request $request, $id)
    {
        $ddcId = Auth::guard('ddc')->id();
        $product = DdcLocalProduct::where('ddc_id', $ddcId)->findOrFail($id);

        $request->validate([
            'product_name'    => 'required|string|max:255',
            'msme_product_id' => 'nullable|exists:products,id',
            'category_id'     => 'required|exists:categories,id',
            'hsn_code'        => 'required|string|max:50',
            'gst_rate'        => 'required|numeric|min:0|max:100',
            'unit_of_measure' => 'required|string|max:100',
            'brand_id'        => 'nullable|integer',
            'barcode'         => 'nullable|string|max:100',
            'product_image'   => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $hsn = DB::table('hsns')->where('hsn_code', $request->hsn_code)->first();
        if (!$hsn) {
            return back()
                ->withInput()
                ->withErrors(['hsn_code' => 'Invalid HSN Code selected.']);
        }

        if ($request->hasFile('product_image')) {
            $product->product_image = $this->uploadFileToService($request->file('product_image'), 'local_products');
        } elseif ($request->filled('fetched_image_url')) {
            $product->product_image = trim($request->fetched_image_url);
        } elseif (empty($product->product_image) && $request->filled('msme_product_id')) {
            $msmeProduct = Product::find($request->msme_product_id);
            if ($msmeProduct && !empty($msmeProduct->featured_image)) {
                $product->product_image = $msmeProduct->featured_image;
            }
        }

        $validatedBrandId = null;
        if ($request->filled('brand_id') && (int) $request->brand_id > 0) {
            $checkBrand = (int) $request->brand_id;
            if (Brand::where('id', $checkBrand)->exists()) {
                $validatedBrandId = $checkBrand;
            }
        }

        $product->product_name    = $request->product_name;
        $product->msme_product_id = $request->msme_product_id;
        $product->category_id     = $request->category_id;
        $product->hsn_code        = $hsn->hsn_code;
        $product->gst_rate        = $request->has('gst_rate') ? $request->gst_rate : $hsn->gst_percentage;
        $product->brand_id        = $validatedBrandId;
        $product->unit_of_measure = $request->unit_of_measure;
        $product->barcode         = $request->barcode;
        $product->save();

        return redirect()
            ->route('ddc.local-products.index')
            ->with('success', "Regional Product '{$product->product_name}' updated successfully.");
    }

    public function destroy($id)
    {
        $ddcId = Auth::guard('ddc')->id();
        $product = DdcLocalProduct::where('ddc_id', $ddcId)->findOrFail($id);

        if ($product->inventories()->exists()) {
            return back()->with('error', "Cannot delete Regional Product '{$product->product_name}' as it has existing inventory/purchase records.");
        }

        $product->delete();

        return back()->with('success', "Regional Product '{$product->product_name}' deleted successfully.");
    }

    public function quickStoreBrand(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = trim($request->name);
        $brand = Brand::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name), 'status' => '1']
        );

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully.',
            'brand'   => [
                'id'   => $brand->id,
                'name' => $brand->name,
            ],
        ]);
    }

    public function comparison(Request $request)
    {
        $ddcId      = Auth::guard('ddc')->id();
        $search     = $request->get('search');
        $filter     = $request->get('filter', 'all');
        $categoryId = $request->get('category_id');

        $query = DdcLocalProduct::where('ddc_id', $ddcId)
            ->with(['msmeProduct', 'inventories', 'category', 'brand']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('local_product_code', 'like', "%{$search}%")
                  ->orWhereHas('msmeProduct', function ($mq) use ($search) {
                      $mq->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($filter === 'linked') {
            $query->whereNotNull('msme_product_id');
        } elseif ($filter === 'unlinked') {
            $query->whereNull('msme_product_id');
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $localProducts = $query->orderBy('product_name')->get();

        $totalProducts = DdcLocalProduct::where('ddc_id', $ddcId)->count();
        $linkedCount   = DdcLocalProduct::where('ddc_id', $ddcId)->whereNotNull('msme_product_id')->count();
        $unlinkedCount = $totalProducts - $linkedCount;

        $categories = Category::active()->orderBy('name')->get();

        return view('ddc.local-products.comparison', compact(
            'localProducts',
            'totalProducts',
            'linkedCount',
            'unlinkedCount',
            'search',
            'filter',
            'categoryId',
            'categories'
        ));
    }

    /**
     * Download sample Excel (.xlsx with dropdowns) template for bulk uploading DDC Regional Products & Inventory Stock
     */
    public function sampleTemplate(Request $request)
    {
        $spreadsheet = new Spreadsheet();

        // ── Sheet 1: Main Products & Stock Template ──
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Products_Template");

        $headers = [
            "Product Name", "Category", "Barcode", "Brand", "Unit of Measure",
            "GST Rate (%)", "HSN Code", "Quantity", "MRP", "Purchase Price",
            "MSP", "CSP", "Batch Number", "Mfg Date", "Expire Date", "Local Product Code", "POS Status"
        ];

        $colIndex = 1;
        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($colIndex, 1, $h);
            $colIndex++;
        }

        // Header Styling (Professional dark navy theme with white text)
        $sheet->getStyle("A1:Q1")->getFont()->setBold(true)->getColor()->setRGB("FFFFFF");
        $sheet->getStyle("A1:Q1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB("1E293B");
        $sheet->getStyle("A1:Q1")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Pre-fill realistic sample rows where all mandatory fields are filled
        $sampleRows = [
            [
                'Fresh Farm Cow Milk 1L', 'Dairy', '8901234567890', 'Amul', 'Litre',
                0, '0401', 50, 66.00, 56.00, 58.00, 62.00, 'BATCH-MILK-01',
                date('Y-m-d'), date('Y-m-d', strtotime('+3 days')), '', 'Active'
            ],
            [
                'Desi Ghee 500g Glass Jar', 'Dairy', '8901234567891', 'Amul', 'Gram',
                12, '0405', 25, 350.00, 280.00, 300.00, 325.00, 'BATCH-GHEE-01',
                date('Y-m-d', strtotime('-15 days')), date('Y-m-d', strtotime('+180 days')), '', 'Active'
            ],
            [
                'Organic Whole Wheat Atta 10Kg', 'Grocery', '8901234567892', 'Patanjali', 'Kg',
                0, '1101', 100, 420.00, 340.00, 360.00, 390.00, 'BATCH-ATTA-01',
                date('Y-m-d', strtotime('-5 days')), date('Y-m-d', strtotime('+90 days')), '', 'Active'
            ],
            [
                'Handmade Mustard Oil 1L', 'Grocery', '8901234567893', 'Fortune', 'Litre',
                5, '1514', 10, 180.00, 140.00, 150.00, 165.00, '',
                '', '', '', 'Inactive'
            ],
        ];

        $rIdx = 2;
        foreach ($sampleRows as $sRow) {
            $sheet->fromArray($sRow, null, "A{$rIdx}");
            $rIdx++;
        }

        // ── Sheet 2: System Lists (Reference lists for native dropdowns) ──
        $listSheet = $spreadsheet->createSheet();
        $listSheet->setTitle("SystemLists");

        $categories = Category::active()->orderBy('name')->pluck('name')->toArray();
        if (empty($categories)) {
            $categories = Category::orderBy('name')->pluck('name')->toArray();
        }
        $brands = Brand::active()->orderBy('name')->pluck('name')->toArray();
        if (empty($brands)) {
            $brands = Brand::orderBy('name')->pluck('name')->toArray();
        }
        $units = \App\Models\UnitOfMeasure::getUnitsList();
        $posStatuses = ['Active', 'Inactive'];

        $listSheet->setCellValue("A1", "Categories");
        $listSheet->setCellValue("B1", "Brands");
        $listSheet->setCellValue("C1", "Units of Measure");
        $listSheet->setCellValue("D1", "POS Status");
        $listSheet->getStyle("A1:D1")->getFont()->setBold(true)->getColor()->setRGB("FFFFFF");
        $listSheet->getStyle("A1:D1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB("334155");

        $catRow = 2;
        foreach ($categories as $cat) {
            $listSheet->setCellValue("A{$catRow}", $cat);
            $catRow++;
        }
        $catMaxRow = max(2, $catRow - 1);

        $brandRow = 2;
        foreach ($brands as $brand) {
            $listSheet->setCellValue("B{$brandRow}", $brand);
            $brandRow++;
        }
        $brandMaxRow = max(2, $brandRow - 1);

        $uomRow = 2;
        foreach ($units as $u) {
            $listSheet->setCellValue("C{$uomRow}", $u);
            $uomRow++;
        }
        $uomMaxRow = max(2, $uomRow - 1);

        $listSheet->setCellValue("D2", "Active");
        $listSheet->setCellValue("D3", "Inactive");

        // ── Apply Data Validation Dropdowns to Sheet 1 (Rows 2 to 1000) ──
        $maxValidationRow = 1000;

        // 1. Category Dropdown (Column B)
        if (!empty($categories)) {
            $catValidation = new DataValidation();
            $catValidation->setType(DataValidation::TYPE_LIST);
            $catValidation->setErrorStyle(DataValidation::STYLE_STOP);
            $catValidation->setAllowBlank(false);
            $catValidation->setShowDropDown(true);
            $catValidation->setShowInputMessage(true);
            $catValidation->setPromptTitle("Select Category (Mandatory)");
            $catValidation->setPrompt("Choose an active category from the system list");
            $catValidation->setFormula1("=SystemLists!\$A\$2:\$A\${$catMaxRow}");
            for ($r = 2; $r <= $maxValidationRow; $r++) {
                $sheet->getCell("B{$r}")->setDataValidation(clone $catValidation);
            }
        }

        // 2. Brand Dropdown (Column D)
        if (!empty($brands)) {
            $brandValidation = new DataValidation();
            $brandValidation->setType(DataValidation::TYPE_LIST);
            $brandValidation->setErrorStyle(DataValidation::STYLE_STOP);
            $brandValidation->setAllowBlank(false);
            $brandValidation->setShowDropDown(true);
            $brandValidation->setShowInputMessage(true);
            $brandValidation->setPromptTitle("Select Brand (Mandatory)");
            $brandValidation->setPrompt("Choose a brand from system list or enter brand name");
            $brandValidation->setFormula1("=SystemLists!\$B\$2:\$B\${$brandMaxRow}");
            for ($r = 2; $r <= $maxValidationRow; $r++) {
                $sheet->getCell("D{$r}")->setDataValidation(clone $brandValidation);
            }
        }

        // 3. Unit of Measure Dropdown (Column E)
        if (!empty($units)) {
            $uomValidation = new DataValidation();
            $uomValidation->setType(DataValidation::TYPE_LIST);
            $uomValidation->setErrorStyle(DataValidation::STYLE_STOP);
            $uomValidation->setAllowBlank(false);
            $uomValidation->setShowDropDown(true);
            $uomValidation->setShowInputMessage(true);
            $uomValidation->setPromptTitle("Select Unit (Mandatory)");
            $uomValidation->setPrompt("Choose a unit of measure from the system list");
            $uomValidation->setFormula1("=SystemLists!\$C\$2:\$C\${$uomMaxRow}");
            for ($r = 2; $r <= $maxValidationRow; $r++) {
                $sheet->getCell("E{$r}")->setDataValidation(clone $uomValidation);
            }
        }

        // 4. POS Status Dropdown (Column Q)
        $posValidation = new DataValidation();
        $posValidation->setType(DataValidation::TYPE_LIST);
        $posValidation->setErrorStyle(DataValidation::STYLE_STOP);
        $posValidation->setAllowBlank(false);
        $posValidation->setShowDropDown(true);
        $posValidation->setShowInputMessage(true);
        $posValidation->setPromptTitle("Select POS Status (Mandatory)");
        $posValidation->setPrompt("Choose Active or Inactive for POS");
        $posValidation->setFormula1("=SystemLists!\$D\$2:\$D\$3");
        for ($r = 2; $r <= $maxValidationRow; $r++) {
            $sheet->getCell("Q{$r}")->setDataValidation(clone $posValidation);
        }

        // Set active tab back to Sheet 1
        $spreadsheet->setActiveSheetIndex(0);

        // Auto-fit column widths
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        foreach (range('A', 'D') as $col) {
            $listSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "DDC_Regional_Products_Template_" . date('Ymd_His') . ".xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    /**
     * Process Unified Bulk Upload of DDC Regional Products AND Inventory Stock (Excel .xlsx only)
     */
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'Please select an Excel file (.xlsx) to upload.',
            'file.mimes'    => 'Uploaded file must be in Excel (.xlsx, .xls) format.',
            'file.max'      => 'File size cannot exceed 10MB.',
        ]);

        $ddcId = Auth::guard('ddc')->id();
        $file = $request->file('file');

        try {
            $import = new \App\Imports\DdcLocalProductImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $file);
            $rows = $import->rows;
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to read Excel file: ' . $e->getMessage());
        }

        if (empty($rows) || count($rows) < 2) {
            return back()->with('error', 'The uploaded Excel file contains no data rows (only header or empty).');
        }

        // Header mapping
        $header = $this->normalizeHeaderArray($rows[0]);

        // Preload Categories for fast matching
        $categories = Category::all(['id', 'name']);
        $catIdMap = [];
        $catNameMap = [];
        foreach ($categories as $cat) {
            $catIdMap[$cat->id] = $cat->id;
            $cleanName = mb_strtolower(trim(preg_replace('/\s+/', ' ', $cat->name)));
            $catNameMap[$cleanName] = $cat->id;
        }

        // Preload Brands
        $brands = Brand::all(['id', 'name']);
        $brandIdMap = [];
        $brandNameMap = [];
        foreach ($brands as $b) {
            $brandIdMap[$b->id] = $b->id;
            $cleanBName = mb_strtolower(trim(preg_replace('/\s+/', ' ', $b->name)));
            $brandNameMap[$cleanBName] = $b->id;
        }

        // Preload existing regional products for this DDC (map by local_product_code and by normalized product_name)
        $existingLocalProducts = DdcLocalProduct::where('ddc_id', $ddcId)->get();
        $existingProductsByCode = [];
        $existingProductsByName = [];
        $existingBarcodes = [];

        foreach ($existingLocalProducts as $elp) {
            if ($elp->local_product_code) {
                $existingProductsByCode[strtoupper(trim($elp->local_product_code))] = $elp;
            }
            $existingProductsByName[mb_strtolower(trim($elp->product_name))] = $elp;
            if ($elp->barcode) {
                $existingBarcodes[trim((string)$elp->barcode)] = true;
            }
        }

        // Code generation prep
        $latest = DdcLocalProduct::where('local_product_code', 'REGEXP', '^LCL[0-9]+$')
            ->orderByRaw('CAST(SUBSTRING(local_product_code, 4) AS UNSIGNED) DESC')
            ->first();
        $nextSeq = $latest ? (((int) substr($latest->local_product_code, 3)) + 1) : 1;
        $usedCodesInBatch = [];

        $createdProductCount = 0;
        $updatedProductCount = 0;
        $createdInventoryCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rowNum = $i + 1; // 1-indexed row for user reference

                // Skip completely empty row
                if (empty(array_filter($row, fn($v) => !is_null($v) && trim((string)$v) !== ''))) {
                    continue;
                }

                // Pad or slice row to match header count
                if (count($header) !== count($row)) {
                    $row = array_slice(array_pad($row, count($header), null), 0, count($header));
                }
                $data = array_combine($header, $row);

                // ==========================================
                // STRICT MANDATORY CHECKS (Except Barcode, Batch, Code, Mfg, Exp)
                // ==========================================

                // 1. Product Name (Mandatory)
                $productName = trim((string)($data['product_name'] ?? ''));
                if (empty($productName)) {
                    $errors[] = "Row {$rowNum}: 'Product Name' is mandatory.";
                    continue;
                }
                $pNameNorm = mb_strtolower($productName);

                // 2. Category (Mandatory)
                $catRaw = trim((string)($data['category'] ?? ''));
                $categoryId = null;
                if (!empty($catRaw)) {
                    if (is_numeric($catRaw) && isset($catIdMap[(int)$catRaw])) {
                        $categoryId = (int)$catRaw;
                    } else {
                        $cleanCatInput = mb_strtolower(preg_replace('/\s+/', ' ', $catRaw));
                        if (isset($catNameMap[$cleanCatInput])) {
                            $categoryId = $catNameMap[$cleanCatInput];
                        }
                    }
                }
                if (!$categoryId) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'Category' is mandatory and must match a valid category in the system (given: '{$catRaw}').";
                    continue;
                }

                // 3. Brand (Mandatory)
                $brandRaw = trim((string)($data['brand'] ?? ''));
                if (empty($brandRaw)) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'Brand' is mandatory.";
                    continue;
                }
                $brandId = null;
                if (is_numeric($brandRaw) && isset($brandIdMap[(int)$brandRaw])) {
                    $brandId = (int)$brandRaw;
                } else {
                    $cleanBInput = mb_strtolower(preg_replace('/\s+/', ' ', $brandRaw));
                    if (isset($brandNameMap[$cleanBInput])) {
                        $brandId = $brandNameMap[$cleanBInput];
                    } else {
                        $newBrand = Brand::create(['name' => $brandRaw, 'status' => '1']);
                        $brandId = $newBrand->id;
                        $brandIdMap[$newBrand->id] = $newBrand->id;
                        $brandNameMap[$cleanBInput] = $newBrand->id;
                    }
                }

                // 4. Unit of Measure (Mandatory)
                $uom = trim((string)($data['unit_of_measure'] ?? ''));
                if (empty($uom)) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'Unit of Measure' is mandatory.";
                    continue;
                }

                // 5. GST Rate (%) (Mandatory)
                if (!isset($data['gst_rate']) || trim((string)$data['gst_rate']) === '' || !is_numeric(trim((string)$data['gst_rate']))) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'GST Rate (%)' is mandatory and must be a number (e.g. 0, 5, 12, 18, 28).";
                    continue;
                }
                $gstRate = (float)trim((string)$data['gst_rate']);
                if ($gstRate < 0 || $gstRate > 100) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'GST Rate (%)' must be between 0 and 100%.";
                    continue;
                }

                // 6. HSN Code (Mandatory)
                $hsnCode = trim((string)($data['hsn_code'] ?? ''));
                if (empty($hsnCode)) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'HSN Code' is mandatory.";
                    continue;
                }

                // 7. Quantity (Mandatory)
                if (!isset($data['quantity']) || trim((string)$data['quantity']) === '' || !is_numeric(trim((string)$data['quantity']))) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'Quantity' is mandatory and must be a valid number (>= 0).";
                    continue;
                }
                $qtyVal = (float)trim((string)$data['quantity']);
                if ($qtyVal < 0) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'Quantity' cannot be negative.";
                    continue;
                }

                // 8. MRP (Mandatory)
                if (!isset($data['mrp']) || trim((string)$data['mrp']) === '' || !is_numeric(trim((string)$data['mrp']))) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'MRP' is mandatory and must be a valid number greater than 0.";
                    continue;
                }
                $mrpVal = (float)trim((string)$data['mrp']);
                if ($mrpVal <= 0) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'MRP' must be greater than 0.";
                    continue;
                }

                // 9. Purchase Price (Mandatory)
                if (!isset($data['purchase_price']) || trim((string)$data['purchase_price']) === '' || !is_numeric(trim((string)$data['purchase_price']))) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'Purchase Price' is mandatory and must be a valid number greater than 0.";
                    continue;
                }
                $purchasePriceVal = (float)trim((string)$data['purchase_price']);
                if ($purchasePriceVal <= 0) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'Purchase Price' must be greater than 0.";
                    continue;
                }

                // 10. CSP (Mandatory)
                if (!isset($data['csp']) || trim((string)$data['csp']) === '' || !is_numeric(trim((string)$data['csp']))) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'CSP' (Canteen Selling Price) is mandatory and must be a valid number greater than 0.";
                    continue;
                }
                $cspVal = (float)trim((string)$data['csp']);
                if ($cspVal <= 0) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'CSP' must be greater than 0.";
                    continue;
                }
                if ($cspVal > $mrpVal) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'CSP' (₹{$cspVal}) cannot be greater than 'MRP' (₹{$mrpVal}).";
                    continue;
                }

                // 11. MSP (Wholesale / ADC Price)
                // If provided, must be > 0 and between Purchase Price and CSP. If not provided, fallback gracefully.
                if (isset($data['msp']) && trim((string)$data['msp']) !== '' && is_numeric(trim((string)$data['msp']))) {
                    $mspVal = (float)trim((string)$data['msp']);
                } else {
                    $mspVal = $purchasePriceVal;
                }
                if ($mspVal <= 0) {
                    $mspVal = $purchasePriceVal;
                }

                // 12. POS Status (Mandatory)
                $posStatusRaw = trim((string)($data['pos_status'] ?? ''));
                if (empty($posStatusRaw)) {
                    $errors[] = "Row {$rowNum} ('{$productName}'): 'POS Status' is mandatory (Select 'Active' or 'Inactive').";
                    continue;
                }
                $posStatus = (strcasecmp($posStatusRaw, 'Inactive') === 0 || $posStatusRaw === '0') ? 'Inactive' : 'Active';

                // ==========================================
                // 5 OPTIONAL FIELDS
                // ==========================================

                // Option 1: Barcode
                $barcode = trim((string)($data['barcode'] ?? ''));
                $barcode = $barcode !== '' ? $barcode : null;
                if ($barcode !== null) {
                    if (isset($existingBarcodes[$barcode]) && !isset($existingProductsByName[$pNameNorm])) {
                        $errors[] = "Row {$rowNum} ('{$productName}'): Barcode '{$barcode}' is already assigned to another regional product.";
                        continue;
                    }
                    $existingBarcodes[$barcode] = true;
                }

                // Option 2: Batch Number
                $batchNum = trim((string)($data['batch_number'] ?? ''));
                if (empty($batchNum)) {
                    $batchNum = 'BATCH-' . date('ymd') . '-' . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT);
                }

                // Option 3 & 4: Mfg Date & Expire Date
                $mfgDate    = $this->parseDateValue($data['mfg_date'] ?? null);
                $expireDate = $this->parseDateValue($data['expire_date'] ?? null);

                // Option 5: Local Product Code
                $userCode = strtoupper(trim((string)($data['local_product_code'] ?? '')));
                $localProduct = null;

                if (!empty($userCode) && isset($existingProductsByCode[$userCode])) {
                    $localProduct = $existingProductsByCode[$userCode];
                } elseif (isset($existingProductsByName[$pNameNorm])) {
                    $localProduct = $existingProductsByName[$pNameNorm];
                }

                if ($localProduct) {
                    $updates = [];
                    if (empty($localProduct->barcode) && $barcode) {
                        $updates['barcode'] = $barcode;
                    }
                    if (empty($localProduct->hsn_code) && $hsnCode) {
                        $updates['hsn_code'] = $hsnCode;
                    }
                    if ($brandId && empty($localProduct->brand_id)) {
                        $updates['brand_id'] = $brandId;
                    }
                    if (!empty($updates)) {
                        $localProduct->update($updates);
                    }
                    $updatedProductCount++;
                } else {
                    if (!empty($userCode)) {
                        if (DdcLocalProduct::where('local_product_code', $userCode)->exists() || in_array($userCode, $usedCodesInBatch)) {
                            $errors[] = "Row {$rowNum} ('{$productName}'): Product Code '{$userCode}' is already taken.";
                            continue;
                        }
                        $productCode = $userCode;
                    } else {
                        do {
                            $productCode = 'LCL' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
                            $nextSeq++;
                        } while (DdcLocalProduct::where('local_product_code', $productCode)->exists() || in_array($productCode, $usedCodesInBatch));
                    }
                    $usedCodesInBatch[] = $productCode;

                    // Create Regional Product Master
                    $localProduct = DdcLocalProduct::create([
                        'ddc_id'             => $ddcId,
                        'local_product_code' => $productCode,
                        'product_name'       => $productName,
                        'category_id'        => $categoryId,
                        'hsn_code'           => $hsnCode,
                        'gst_rate'           => $gstRate,
                        'brand_id'           => $brandId,
                        'unit_of_measure'    => $uom,
                        'barcode'            => $barcode,
                        'status'             => 'Active',
                    ]);
                    $existingProductsByCode[$productCode] = $localProduct;
                    $existingProductsByName[$pNameNorm] = $localProduct;
                    $createdProductCount++;
                }

                // ==========================================
                // CREATE INVENTORY STOCK BATCH
                // ==========================================
                $landingCost = $purchasePriceVal;
                // MSP Margin (ADC Margin) = ((MSP - Purchase Price) / MSP) * 100
                $adcMargin = ($mspVal > 0 && $landingCost > 0) ? round((($mspVal - $landingCost) / $mspVal) * 100, 2) : 0;
                // CSP Margin = ((MRP - CSP) / MRP) * 100
                $cspMargin = ($mrpVal > 0 && $cspVal > 0) ? round((($mrpVal - $cspVal) / $mrpVal) * 100, 2) : 0;

                // Expired products cannot have active POS status
                $posStatusFinal = $posStatus;
                if (!empty($expireDate) && \Carbon\Carbon::parse($expireDate)->startOfDay()->lte(now()->startOfDay())) {
                    $posStatusFinal = 'Inactive';
                }

                DdcInventory::create([
                    'ddc_id'               => $ddcId,
                    'inventory_source'     => 'LOCAL',
                    'product_id'           => null,
                    'ddc_local_product_id' => $localProduct->id,
                    'product_name'         => $localProduct->product_name,
                    'product_code'         => $localProduct->local_product_code,
                    'barcode'              => $barcode,
                    'unit_of_measure'      => $uom,
                    'quantity'             => $qtyVal,
                    'reserved_quantity'    => 0,
                    'sold_quantity'        => 0,
                    'returned_quantity'    => 0,
                    'mrp'                  => $mrpVal,
                    'purchase_price'       => $purchasePriceVal,
                    'landing_cost'         => $landingCost,
                    'msp'                  => $mspVal,
                    'csp'                  => $cspVal,
                    'margin'               => $adcMargin,
                    'adc_margin'           => $adcMargin,
                    'csp_margin'           => $cspMargin,
                    'gst_rate'             => $gstRate,
                    'batch_number'         => $batchNum,
                    'mfg_date'             => $mfgDate,
                    'expire_date'          => $expireDate,
                    'reference_type'       => 'Local Purchase',
                    'reference_id'         => null,
                    'pos_status'           => $posStatusFinal,
                ]);

                $createdInventoryCount++;
            }

            if (!empty($errors)) {
                DB::rollBack();
                $errHtml = "<strong>Bulk upload stopped due to validation errors (" . count($errors) . "):</strong><ul class='mb-0 mt-2 ps-3'>";
                foreach (array_slice($errors, 0, 10) as $err) {
                    $errHtml .= "<li>" . e($err) . "</li>";
                }
                if (count($errors) > 10) {
                    $errHtml .= "<li>...and " . (count($errors) - 10) . " more errors.</li>";
                }
                $errHtml .= "</ul><small class='text-muted mt-2 d-block'>Note: 11 fields (Product Name, Category, Brand, Unit, GST %, HSN, Quantity, MRP, Purchase Price, CSP, POS Status) are strictly mandatory. Only Barcode, Batch, Code, Mfg Date, and Expire Date can be left blank.</small>";

                return back()->with('error', $errHtml);
            }

            DB::commit();

            $msg = "Bulk upload completed successfully! {$createdProductCount} new product(s) created, {$updatedProductCount} updated, and {$createdInventoryCount} inventory stock batch(es) added directly to your DDC Stock.";
            return back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Normalize header row keys for robust matching across Excel templates
     */
    protected function normalizeHeaderArray(array $headerRow): array
    {
        $map = [
            'product_name'        => 'product_name',
            'productname'         => 'product_name',
            'name'                => 'product_name',
            'category'            => 'category',
            'category_name'       => 'category',
            'barcode'             => 'barcode',
            'bar_code'            => 'barcode',
            'brand'               => 'brand',
            'brand_name'          => 'brand',
            'unit_of_measure'     => 'unit_of_measure',
            'unit'                => 'unit_of_measure',
            'uom'                 => 'unit_of_measure',
            'gst_rate'            => 'gst_rate',
            'gst_rate_(%)'        => 'gst_rate',
            'gst_rate_%'          => 'gst_rate',
            'gst'                 => 'gst_rate',
            'tax'                 => 'gst_rate',
            'hsn_code'            => 'hsn_code',
            'hsn'                 => 'hsn_code',
            'quantity'            => 'quantity',
            'qty'                 => 'quantity',
            'stock'               => 'quantity',
            'mrp'                 => 'mrp',
            'purchase_price'      => 'purchase_price',
            'purchaseprice'       => 'purchase_price',
            'cost_price'          => 'purchase_price',
            'msp'                 => 'msp',
            'wholesale_price'     => 'msp',
            'csp'                 => 'csp',
            'selling_price'       => 'csp',
            'batch_number'        => 'batch_number',
            'batch'               => 'batch_number',
            'batch_no'            => 'batch_number',
            'mfg_date'            => 'mfg_date',
            'manufacturing_date'  => 'mfg_date',
            'expire_date'         => 'expire_date',
            'expiry_date'         => 'expire_date',
            'exp_date'            => 'expire_date',
            'local_product_code'  => 'local_product_code',
            'product_code'        => 'local_product_code',
            'code'                => 'local_product_code',
            'pos_status'          => 'pos_status',
            'status'              => 'pos_status',
        ];

        $normalized = [];
        foreach ($headerRow as $idx => $val) {
            $cleaned = mb_strtolower(trim((string)$val));
            $cleaned = str_replace([' ', '-', '(', ')', '%', '/', '\\'], '_', $cleaned);
            $cleaned = preg_replace('/_+/', '_', $cleaned);
            $cleaned = trim($cleaned, '_');

            $key = $map[$cleaned] ?? ($cleaned ?: "col_{$idx}");
            $normalized[$idx] = $key;
        }

        return $normalized;
    }

    /**
     * Safely parse Excel date serial number or text date string into Y-m-d format
     */
    protected function parseDateValue($raw): ?string
    {
        if (is_null($raw) || trim((string)$raw) === '') {
            return null;
        }

        $val = trim((string)$raw);

        // Numeric Excel timestamp
        if (is_numeric($val) && (float)$val > 1000) {
            try {
                return ExcelDate::excelToDateTimeObject((float)$val)->format('Y-m-d');
            } catch (\Exception $e) {
                // fallback to Carbon
            }
        }

        try {
            return Carbon::parse($val)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}

