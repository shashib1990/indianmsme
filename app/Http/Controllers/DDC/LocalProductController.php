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
}
