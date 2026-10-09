<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ImageHelper;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $query = Category::withCount(['subCategories', 'childCategories'])->with(['creator', 'updater']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
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
                fputcsv($file, ['ID', 'Category Name', 'Slug', 'Subcategories Count', 'Childcategories Count', 'Status', 'Created By', 'Created At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->name,
                        $row->slug,
                        $row->sub_categories_count,
                        $row->child_categories_count,
                        $row->status === '1' ? 'Active' : 'Inactive',
                        $row->creator ? $row->creator->name : 'System',
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'categories_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $categories = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.categories.index', compact('categories', 'search', 'status', 'limit', 'sort', 'order'));
    }

    public function store(Request $request)
    {
        $request->validate([
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
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'status' => $request->status,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadFileToService($request->file('image'), 'categories');
        }

        Category::create($data);

        return back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
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
        while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'status' => $request->status,
        ];

        if ($request->hasFile('image')) {
            // Safe deletion of local file if it exists
            ImageHelper::delete($category->image);
            $data['image'] = $this->uploadFileToService($request->file('image'), 'categories');
        }

        $category->update($data);

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->subCategories()->count() > 0) {
            return back()->with('error', 'Cannot delete category — it has sub-categories linked to it.');
        }

        // Delete image file if exists
        if ($category->image && File::exists(public_path($category->image))) {
            File::delete(public_path($category->image));
        }

        // Detach HSNs
        $category->hsns()->detach();

        $category->delete();

        return back()->with('success', 'Category deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,status_active,status_inactive',
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            $restricted = Category::whereIn('id', $ids)->whereHas('subCategories')->count();
            if ($restricted > 0) {
                return back()->with('error', 'Cannot delete selected categories — some have sub-categories linked.');
            }

            $categories = Category::whereIn('id', $ids)->get();
            foreach ($categories as $cat) {
                if ($cat->image && File::exists(public_path($cat->image))) {
                    File::delete(public_path($cat->image));
                }
                $cat->hsns()->detach();
                $cat->delete();
            }

            return back()->with('success', 'Selected categories deleted successfully.');
        } elseif ($action === 'status_active') {
            Category::whereIn('id', $ids)->update(['status' => '1']);
            return back()->with('success', 'Selected categories activated successfully.');
        } elseif ($action === 'status_inactive') {
            Category::whereIn('id', $ids)->update(['status' => '0']);
            return back()->with('success', 'Selected categories deactivated successfully.');
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
        $processedNames = [];
        $processedSlugs = [];

        $namesInFile = [];
        $slugsInFile = [];
        foreach ($rows as $row) {
            $name = trim($row['A'] ?? '');
            $slugInput = trim($row['B'] ?? '');
            if ($name) {
                $namesInFile[] = $name;
                $slug = !empty($slugInput) ? Str::slug($slugInput) : Str::slug($name);
                $slugsInFile[] = $slug;
            }
        }
        $namesInFile = array_unique($namesInFile);
        $slugsInFile = array_unique($slugsInFile);

        $existingCategories = Category::whereIn('name', $namesInFile)
            ->orWhereIn('slug', $slugsInFile)
            ->get(['name', 'slug']);
        
        $existingNameMap = [];
        $existingSlugMap = [];
        foreach ($existingCategories as $cat) {
            $existingNameMap[strtolower($cat->name)] = true;
            $existingSlugMap[strtolower($cat->slug)] = true;
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $total++;

                $name = trim($row['A'] ?? '');
                $slugInput = trim($row['B'] ?? '');

                if (empty($name)) {
                    $failed++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => '-',
                        'status' => 'Failed',
                        'reason' => 'Category name is empty'
                    ];
                    continue;
                }

                $slug = !empty($slugInput) ? Str::slug($slugInput) : Str::slug($name);

                $nameLower = strtolower($name);
                $slugLower = strtolower($slug);

                // Duplicate in file check
                if (in_array($nameLower, $processedNames) || in_array($slugLower, $processedSlugs)) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => $name,
                        'status' => 'Skipped',
                        'reason' => 'Duplicate name or slug in the uploaded file'
                    ];
                    continue;
                }

                // DB existence check
                if (isset($existingNameMap[$nameLower]) || isset($existingSlugMap[$slugLower])) {
                    $skipped++;
                    $details[] = [
                        'row' => $rowNumber,
                        'data' => $name,
                        'status' => 'Skipped',
                        'reason' => "Category '{$name}' or slug '{$slug}' already exists in database"
                    ];
                    continue;
                }

                Category::create([
                    'name' => $name,
                    'slug' => $slug,
                    'status' => '1',
                ]);

                $processedNames[] = $nameLower;
                $processedSlugs[] = $slugLower;
                $existingNameMap[$nameLower] = true;
                $existingSlugMap[$slugLower] = true;
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
        $sheet->setCellValue('B1', 'Slug');

        $sheet->setCellValue('A2', 'Electronics');
        $sheet->setCellValue('B2', 'electronics');

        $sheet->setCellValue('A3', 'Apparel & Clothing');
        $sheet->setCellValue('B3', 'apparel-clothing');

        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(25);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, 'sample_categories.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
