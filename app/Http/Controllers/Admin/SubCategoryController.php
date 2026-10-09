<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ImageHelper;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $categoryId = $request->get('category_id');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $query = SubCategory::with(['category', 'creator', 'updater'])->withCount('childCategories');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Export functionality
        if ($request->has('export')) {
            $selectedIds = $request->get('ids');
            if ($selectedIds) {
                $query->whereIn('id', explode(',', $selectedIds));
            }
            $records = $query->orderBy($sort, $order)->get();

            return response()->streamDownload(function () use ($records) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['ID', 'Category Name', 'Sub Category Name', 'Slug', 'Childcategories Count', 'Status', 'Created By', 'Created At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->category->name ?? '—',
                        $row->name,
                        $row->slug,
                        $row->child_categories_count,
                        $row->status === '1' ? 'Active' : 'Inactive',
                        $row->creator ? $row->creator->name : 'System',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'subcategories_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $subcategories = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();
        $categories = Category::active()->orderBy('name', 'asc')->get();

        return view('admin.sub_categories.index', compact('subcategories', 'categories', 'search', 'status', 'categoryId', 'limit', 'sort', 'order'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'status' => 'required|in:0,1',
        ]);

        $name = trim($request->name);
        $slugInput = $request->slug ? trim($request->slug) : $name;
        $slug = Str::slug($slugInput);

        // Make slug unique automatically
        $originalSlug = $slug;
        $count = 1;
        while (SubCategory::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $data = [
            'category_id' => $request->category_id,
            'name' => $name,
            'slug' => $slug,
            'status' => $request->status,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadFileToService($request->file('image'), 'sub_categories');
        }

        SubCategory::create($data);

        return back()->with('success', 'Sub Category created successfully.');
    }

    public function update(Request $request, SubCategory $subcategory)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'status' => 'required|in:0,1',
        ]);

        $name = trim($request->name);
        $slugInput = $request->slug ? trim($request->slug) : $name;
        $slug = Str::slug($slugInput);

        // Make slug unique automatically, ignoring current ID
        $originalSlug = $slug;
        $count = 1;
        while (SubCategory::where('slug', $slug)->where('id', '!=', $subcategory->id)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $data = [
            'category_id' => $request->category_id,
            'name' => $name,
            'slug' => $slug,
            'status' => $request->status,
        ];

        if ($request->hasFile('image')) {
            if ($subcategory->image) {
                ImageHelper::delete($subcategory->image);
            }
            $data['image'] = $this->uploadFileToService($request->file('image'), 'sub_categories');
        }

        $subcategory->update($data);

        return back()->with('success', 'Sub Category updated successfully.');
    }

    public function destroy(SubCategory $subcategory)
    {
        if ($subcategory->childCategories()->count() > 0) {
            return back()->with('error', 'Cannot delete sub-category — it has child-categories linked to it.');
        }

        if ($subcategory->image) {
            ImageHelper::delete($subcategory->image);
        }

        $subcategory->delete();

        return back()->with('success', 'Sub Category deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,status_active,status_inactive',
            'ids' => 'required|array',
            'ids.*' => 'exists:sub_categories,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            $restricted = SubCategory::whereIn('id', $ids)->whereHas('childCategories')->count();
            if ($restricted > 0) {
                return back()->with('error', 'Cannot delete selected sub-categories — some have child-categories linked.');
            }

            SubCategory::whereIn('id', $ids)->delete();
            return back()->with('success', 'Selected sub-categories deleted successfully.');
        } elseif ($action === 'status_active') {
            SubCategory::whereIn('id', $ids)->update(['status' => '1']);
            return back()->with('success', 'Selected sub-categories activated successfully.');
        } elseif ($action === 'status_inactive') {
            SubCategory::whereIn('id', $ids)->update(['status' => '0']);
            return back()->with('success', 'Selected sub-categories deactivated successfully.');
        }

        return back()->with('error', 'Invalid action.');
    }

    public function import(Request $request)
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(3600);
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls,txt|max:153600',
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        array_shift($rows); // Remove header row

        $total = $inserted = $skipped = $failed = 0;
        $details = [];
        $processedSubKeys = []; // 'subcategoryName|categoryId'

        $categoryNames = [];
        foreach ($rows as $row) {
            $catName = trim($row['A'] ?? '');
            if ($catName) {
                $categoryNames[] = $catName;
            }
        }
        $categoryNames = array_unique($categoryNames);
        $categoriesLookup = Category::whereIn('name', $categoryNames)->get()->keyBy('name');

        $categoryIds = $categoriesLookup->pluck('id')->toArray();
        $existingSubcategories = SubCategory::whereIn('category_id', $categoryIds)
            ->select('category_id', 'name', 'slug')
            ->get();
        
        $existingSubMap = [];
        $existingSlugMap = [];
        foreach ($existingSubcategories as $sub) {
            $key = strtolower($sub->name) . '|' . $sub->category_id;
            $existingSubMap[$key] = true;
            $existingSlugMap[$sub->slug] = true;
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $total++;

                // File columns: A=Category Name, B=Sub Category Name
                $categoryName    = trim($row['A'] ?? '');
                $subcategoryName = trim($row['B'] ?? '');

                if (empty($categoryName)) {
                    $failed++;
                    $details[] = ['row' => $rowNumber, 'data' => '-', 'status' => 'Failed', 'reason' => 'Category Name is empty'];
                    continue;
                }

                if (empty($subcategoryName)) {
                    $failed++;
                    $details[] = ['row' => $rowNumber, 'data' => '-', 'status' => 'Failed', 'reason' => 'Sub Category Name is empty'];
                    continue;
                }

                // Look up parent category
                $category = $categoriesLookup->get($categoryName);
                if (!$category) {
                    $failed++;
                    $details[] = ['row' => $rowNumber, 'data' => $subcategoryName, 'status' => 'Failed', 'reason' => "Parent Category '{$categoryName}' does not exist"];
                    continue;
                }

                $subNameLower = strtolower($subcategoryName);

                // Duplicate check within file (per category scope)
                $fileKey = $subNameLower . '|' . $category->id;
                if (in_array($fileKey, $processedSubKeys)) {
                    $skipped++;
                    $details[] = ['row' => $rowNumber, 'data' => $subcategoryName, 'status' => 'Skipped', 'reason' => 'Duplicate sub-category name under same category in uploaded file'];
                    continue;
                }

                // DB duplicate check: same sub-category name under same category
                if (isset($existingSubMap[$fileKey])) {
                    $skipped++;
                    $details[] = ['row' => $rowNumber, 'data' => $subcategoryName, 'status' => 'Skipped', 'reason' => "Sub Category '{$subcategoryName}' already exists under Category '{$category->name}'"];
                    continue;
                }

                // Auto-generate a unique slug
                $slug = Str::slug($subcategoryName);
                $originalSlug = $slug;
                $count = 1;
                while (isset($existingSlugMap[$slug])) {
                    $slug = $originalSlug . '-' . $count++;
                }

                SubCategory::create([
                    'category_id' => $category->id,
                    'name'        => $subcategoryName,
                    'slug'        => $slug,
                    'status'      => '1',
                ]);

                $processedSubKeys[] = $fileKey;
                $existingSubMap[$fileKey] = true;
                $existingSlugMap[$slug] = true;
                $inserted++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return back()->with('import_result', compact('total', 'inserted', 'skipped', 'failed', 'details'));
    }

    public function sample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Category Name');
        $sheet->setCellValue('B1', 'Sub Category Name');

        $sheet->setCellValue('A2', 'Electronics');
        $sheet->setCellValue('B2', 'Mobile Phones');

        $sheet->setCellValue('A3', 'Electronics');
        $sheet->setCellValue('B3', 'Laptops');

        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(30);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, 'sample_subcategories.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
