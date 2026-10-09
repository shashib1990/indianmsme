<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ChildCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $categoryId = $request->get('category_id');
        $subCategoryId = $request->get('sub_category_id');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $query = ChildCategory::with(['category', 'subCategory', 'creator', 'updater']);

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

        if ($subCategoryId) {
            $query->where('sub_category_id', $subCategoryId);
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
                fputcsv($file, ['ID', 'Category Name', 'Sub Category Name', 'Child Category Name', 'Slug', 'Status', 'Created By', 'Created At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->category->name ?? '—',
                        $row->subCategory->name ?? '—',
                        $row->name,
                        $row->slug,
                        $row->status === '1' ? 'Active' : 'Inactive',
                        $row->creator ? $row->creator->name : 'System',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'childcategories_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $childcategories = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();
        $categories = Category::active()->orderBy('name', 'asc')->get();
        $subcategories = $categoryId ? SubCategory::where('category_id', $categoryId)->active()->orderBy('name', 'asc')->get() : collect();

        return view('admin.child_categories.index', compact('childcategories', 'categories', 'subcategories', 'search', 'status', 'categoryId', 'subCategoryId', 'limit', 'sort', 'order'));
    }

    public function getSubcategories(Request $request)
    {
        $categoryId = $request->get('category_id');
        $subcategories = SubCategory::where('category_id', $categoryId)->active()->orderBy('name', 'asc')->get(['id', 'name']);
        return response()->json($subcategories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        $name = trim($request->name);
        $slugInput = $request->slug ? trim($request->slug) : $name;
        $slug = Str::slug($slugInput);

        // Make slug unique automatically
        $originalSlug = $slug;
        $count = 1;
        while (ChildCategory::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        ChildCategory::create([
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'name' => $name,
            'slug' => $slug,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Child Category created successfully.');
    }

    public function update(Request $request, ChildCategory $childcategory)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        $name = trim($request->name);
        $slugInput = $request->slug ? trim($request->slug) : $name;
        $slug = Str::slug($slugInput);

        // Make slug unique automatically, ignoring current ID
        $originalSlug = $slug;
        $count = 1;
        while (ChildCategory::where('slug', $slug)->where('id', '!=', $childcategory->id)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $childcategory->update([
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'name' => $name,
            'slug' => $slug,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Child Category updated successfully.');
    }

    public function destroy(ChildCategory $childcategory)
    {
        $childcategory->delete();

        return back()->with('success', 'Child Category deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,status_active,status_inactive',
            'ids' => 'required|array',
            'ids.*' => 'exists:child_categories,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            ChildCategory::whereIn('id', $ids)->delete();
            return back()->with('success', 'Selected child categories deleted successfully.');
        } elseif ($action === 'status_active') {
            ChildCategory::whereIn('id', $ids)->update(['status' => '1']);
            return back()->with('success', 'Selected child categories activated successfully.');
        } elseif ($action === 'status_inactive') {
            ChildCategory::whereIn('id', $ids)->update(['status' => '0']);
            return back()->with('success', 'Selected child categories deactivated successfully.');
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
        array_shift($rows);

        $total = $inserted = $skipped = $failed = 0;
        $details = [];
        $processedChildKeys = []; // 'childcategoryName|subCategoryId'

        $categoryNames = [];
        $subcategoryNames = [];
        foreach ($rows as $row) {
            $catName = trim($row['A'] ?? '');
            $subName = trim($row['B'] ?? '');
            if ($catName) {
                $categoryNames[] = $catName;
            }
            if ($subName) {
                $subcategoryNames[] = $subName;
            }
        }
        $categoryNames = array_unique($categoryNames);
        $subcategoryNames = array_unique($subcategoryNames);

        $categoriesLookup = Category::whereIn('name', $categoryNames)->get()->keyBy('name');
        
        $categoryIds = $categoriesLookup->pluck('id')->toArray();
        $subcategoriesLookup = SubCategory::whereIn('name', $subcategoryNames)
            ->whereIn('category_id', $categoryIds)
            ->get()
            ->keyBy(function ($item) {
                return $item->name . '|' . $item->category_id;
            });

        $subcategoryIds = $subcategoriesLookup->pluck('id')->toArray();
        $existingChildCategories = ChildCategory::whereIn('sub_category_id', $subcategoryIds)
            ->select('sub_category_id', 'name', 'slug')
            ->get();
        
        $existingChildMap = [];
        $existingSlugMap = [];
        foreach ($existingChildCategories as $child) {
            $key = strtolower($child->name) . '|' . $child->sub_category_id;
            $existingChildMap[$key] = true;
            $existingSlugMap[$child->slug] = true;
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $total++;

                // File columns: A=Category Name, B=Sub Category Name, C=Child Category Name
                $categoryName      = trim($row['A'] ?? '');
                $subcategoryName   = trim($row['B'] ?? '');
                $childcategoryName = trim($row['C'] ?? '');

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

                if (empty($childcategoryName)) {
                    $failed++;
                    $details[] = ['row' => $rowNumber, 'data' => '-', 'status' => 'Failed', 'reason' => 'Child Category Name is empty'];
                    continue;
                }

                // Look up parent category
                $category = $categoriesLookup->get($categoryName);
                if (!$category) {
                    $failed++;
                    $details[] = ['row' => $rowNumber, 'data' => $childcategoryName, 'status' => 'Failed', 'reason' => "Parent Category '{$categoryName}' does not exist"];
                    continue;
                }

                // Look up subcategory under the category
                $subcategory = $subcategoriesLookup->get($subcategoryName . '|' . $category->id);
                if (!$subcategory) {
                    $failed++;
                    $details[] = ['row' => $rowNumber, 'data' => $childcategoryName, 'status' => 'Failed', 'reason' => "Sub Category '{$subcategoryName}' does not exist under Category '{$categoryName}'"];
                    continue;
                }

                $childNameLower = strtolower($childcategoryName);

                // Duplicate check in file (scoped to sub-category)
                $fileKey = $childNameLower . '|' . $subcategory->id;
                if (in_array($fileKey, $processedChildKeys)) {
                    $skipped++;
                    $details[] = ['row' => $rowNumber, 'data' => $childcategoryName, 'status' => 'Skipped', 'reason' => 'Duplicate child category name under same sub-category in uploaded file'];
                    continue;
                }

                // DB duplicate check: same child name under same sub-category
                if (isset($existingChildMap[$fileKey])) {
                    $skipped++;
                    $details[] = ['row' => $rowNumber, 'data' => $childcategoryName, 'status' => 'Skipped', 'reason' => "Child Category '{$childcategoryName}' already exists under Sub Category '{$subcategory->name}'"];
                    continue;
                }

                // Auto-generate a unique slug
                $slug = Str::slug($childcategoryName);
                $originalSlug = $slug;
                $count = 1;
                while (isset($existingSlugMap[$slug])) {
                    $slug = $originalSlug . '-' . $count++;
                }

                ChildCategory::create([
                    'category_id'     => $category->id,
                    'sub_category_id' => $subcategory->id,
                    'name'            => $childcategoryName,
                    'slug'            => $slug,
                    'status'          => '1',
                ]);

                $processedChildKeys[] = $fileKey;
                $existingChildMap[$fileKey] = true;
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
        $sheet->setCellValue('C1', 'Child Category Name');

        $sheet->setCellValue('A2', 'Electronics');
        $sheet->setCellValue('B2', 'Mobile Phones');
        $sheet->setCellValue('C2', 'iPhones');

        $sheet->setCellValue('A3', 'Electronics');
        $sheet->setCellValue('B3', 'Laptops');
        $sheet->setCellValue('C3', 'Gaming Laptops');

        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(30);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, 'sample_childcategories.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
