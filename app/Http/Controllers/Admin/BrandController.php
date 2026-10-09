<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $query = Brand::withCount('products');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        // CSV Export functionality
        if ($request->has('export')) {
            $selectedIds = $request->get('ids');
            if ($selectedIds) {
                $query->whereIn('id', explode(',', $selectedIds));
            }
            $records = $query->orderBy($sort, $order)->get();

            return response()->streamDownload(function () use ($records) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['ID', 'Brand Name', 'Slug', 'Logo URL', 'Products Count', 'Status', 'Created At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->name,
                        $row->slug,
                        $row->logo,
                        $row->products_count,
                        $row->status === '1' ? 'Active' : 'Inactive',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'brands_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $brands = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.brands.index', compact('brands', 'search', 'status', 'limit', 'sort', 'order'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'status' => 'required|in:0,1',
        ]);

        $name = trim($request->name);
        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($name);

        // Make slug unique automatically
        $originalSlug = $slug;
        $count = 1;
        while (Brand::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $logoUrl = $this->uploadFileToService($request->file('logo'), 'brands');
        }

        Brand::create([
            'name' => $name,
            'slug' => $slug,
            'logo' => $logoUrl,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Brand created successfully.');
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $brand->id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'status' => 'required|in:0,1',
        ]);

        $name = trim($request->name);
        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($name);

        // Make slug unique automatically
        $originalSlug = $slug;
        $count = 1;
        while (Brand::where('slug', $slug)->where('id', '!=', $brand->id)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'status' => $request->status,
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->uploadFileToService($request->file('logo'), 'brands');
        }

        $brand->update($data);

        return back()->with('success', 'Brand updated successfully.');
    }

    public function toggleStatus($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->status = $brand->status === '1' ? '0' : '1';
        $brand->save();

        return back()->with('success', 'Brand status updated successfully.');
    }
}
