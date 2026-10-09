<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\AdcInventory;
use App\Models\AdcLocalProduct;
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
        $adcId = Auth::guard('adc')->id();
        $search = trim($request->get('search', ''));
        $categoryId = $request->get('category_id');

        $query = AdcLocalProduct::where('adc_id', $adcId)
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

        return view('adc.local-products.index', compact('products', 'categories', 'search', 'categoryId'));
    }

    public function create()
    {
        $adcId = Auth::guard('adc')->id();
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::active()->orderBy('name')->get();
        $units = \App\Models\UnitOfMeasure::getUnitsList();

        $existingProducts = AdcInventory::where('adc_id', $adcId)
            ->select('id', 'product_name', 'product_code', 'barcode', 'unit_of_measure')
            ->distinct()
            ->orderBy('product_name')
            ->get();

        return view('adc.local-products.create', compact('categories', 'brands', 'units', 'existingProducts'));
    }

    public function store(Request $request)
    {
        $adcId = Auth::guard('adc')->id();

        $request->validate([
            'product_name'    => 'required|string|max:255',
            'category_id'     => 'required|exists:categories,id',
            'hsn_code'        => 'nullable|string|max:50',
            'gst_rate'        => 'required|numeric|min:0|max:100',
            'brand_id'        => 'nullable|integer',
            'unit_of_measure' => 'required|string|max:50',
            'barcode'         => 'nullable|string|max:100',
            'product_image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $code = AdcLocalProduct::generateLocalProductCode();

        $msmeProductId = $request->msme_product_id;
        $msmeProduct   = $msmeProductId ? Product::find($msmeProductId) : null;

        $imagePath = null;
        if ($request->hasFile('product_image')) {
            $file = $request->file('product_image');
            $imagePath = 'local_products/' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public', $imagePath);
        } elseif ($request->filled('fetched_image_url')) {
            $imagePath = trim($request->fetched_image_url);
        } elseif ($msmeProduct && !empty($msmeProduct->featured_image)) {
            $imagePath = $msmeProduct->featured_image;
        }

        $validatedBrandId = null;
        if ($request->filled('brand_id') && (int) $request->brand_id > 0) {
            $checkBrand = (int) $request->brand_id;
            if (Brand::where('id', $checkBrand)->exists()) {
                $validatedBrandId = $checkBrand;
            }
        }

        $localProduct = AdcLocalProduct::create([
            'adc_id'             => $adcId,
            'msme_product_id'    => $msmeProductId,
            'local_product_code' => $code,
            'product_name'       => trim($request->product_name),
            'category_id'        => $request->category_id,
            'hsn_code'           => $request->hsn_code ? trim($request->hsn_code) : null,
            'gst_rate'           => $request->gst_rate,
            'brand_id'           => $validatedBrandId,
            'unit_of_measure'    => $request->unit_of_measure,
            'barcode'            => $request->barcode ? trim($request->barcode) : null,
            'product_image'      => $imagePath,
            'status'             => 'Active',
        ]);

        return redirect()->route('adc.local-products.index')
            ->with('success', "Regional Product '{$localProduct->product_name}' ({$code}) created successfully.");
    }

    public function show($id)
    {
        $adcId = Auth::guard('adc')->id();
        $product = AdcLocalProduct::where('adc_id', $adcId)
            ->with(['category', 'brand', 'inventories'])
            ->findOrFail($id);

        return view('adc.local-products.show', compact('product'));
    }

    public function edit($id)
    {
        $adcId = Auth::guard('adc')->id();
        $product = AdcLocalProduct::where('adc_id', $adcId)->findOrFail($id);

        $categories = Category::active()->orderBy('name')->get();

        $brands = Brand::active()->orderBy('name')->get();
        $units = \App\Models\UnitOfMeasure::getUnitsList();

        return view('adc.local-products.edit', compact('product', 'categories', 'brands', 'units'));
    }

    public function update(Request $request, $id)
    {
        $adcId = Auth::guard('adc')->id();
        $product = AdcLocalProduct::where('adc_id', $adcId)->findOrFail($id);

        $request->validate([
            'product_name'    => 'required|string|max:255',
            'category_id'     => 'required|exists:categories,id',
            'hsn_code'        => 'nullable|string|max:50',
            'gst_rate'        => 'required|numeric|min:0|max:100',
            'brand_id'        => 'nullable|integer',
            'unit_of_measure' => 'required|string|max:50',
            'barcode'         => 'nullable|string|max:100',
            'product_image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = $product->product_image;
        if ($request->hasFile('product_image')) {
            $file = $request->file('product_image');
            $imagePath = 'local_products/' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public', $imagePath);
        } elseif ($request->filled('fetched_image_url')) {
            $imagePath = trim($request->fetched_image_url);
        } elseif (empty($imagePath) && $request->filled('msme_product_id')) {
            $msmeProduct = Product::find($request->msme_product_id);
            if ($msmeProduct && !empty($msmeProduct->featured_image)) {
                $imagePath = $msmeProduct->featured_image;
            }
        }

        $validatedBrandId = null;
        if ($request->filled('brand_id') && (int) $request->brand_id > 0) {
            $checkBrand = (int) $request->brand_id;
            if (Brand::where('id', $checkBrand)->exists()) {
                $validatedBrandId = $checkBrand;
            }
        }

        $product->update([
            'product_name'    => trim($request->product_name),
            'msme_product_id' => $request->msme_product_id ?: $product->msme_product_id,
            'category_id'     => $request->category_id,
            'hsn_code'        => $request->hsn_code ? trim($request->hsn_code) : null,
            'gst_rate'        => $request->gst_rate,
            'brand_id'        => $validatedBrandId,
            'unit_of_measure' => $request->unit_of_measure,
            'barcode'         => $request->barcode ? trim($request->barcode) : null,
            'product_image'   => $imagePath,
        ]);

        return redirect()->route('adc.local-products.index')
            ->with('success', "Regional Product '{$product->product_name}' updated successfully.");
    }

    public function destroy($id)
    {
        $adcId = Auth::guard('adc')->id();
        $product = AdcLocalProduct::where('adc_id', $adcId)->findOrFail($id);

        // Prevent delete if purchase history exists
        if ($product->purchaseItems()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete this local product as inward purchases exist for it.');
        }

        $product->delete();

        return redirect()->route('adc.local-products.index')
            ->with('success', 'Regional Product deleted successfully.');
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

    public function quickStoreBrand(Request $request)
    {
        $request->validate([
            'brand_name' => 'nullable|string|max:255',
            'name'       => 'nullable|string|max:255',
        ]);

        $name = trim($request->name ?: $request->brand_name);
        if (!$name) {
            return response()->json(['success' => false, 'message' => 'Brand name is required.'], 422);
        }

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
        $adcId      = Auth::guard('adc')->id();
        $search     = $request->get('search');
        $filter     = $request->get('filter', 'all');
        $categoryId = $request->get('category_id');

        $query = AdcLocalProduct::where('adc_id', $adcId)
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

        $totalProducts = AdcLocalProduct::where('adc_id', $adcId)->count();
        $linkedCount   = AdcLocalProduct::where('adc_id', $adcId)->whereNotNull('msme_product_id')->count();
        $unlinkedCount = $totalProducts - $linkedCount;

        $categories = Category::active()->orderBy('name')->get();

        return view('adc.local-products.comparison', compact(
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
