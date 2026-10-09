<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeBanner;
use Illuminate\Http\Request;
use App\Helpers\ImageHelper;
use Illuminate\Http\UploadedFile;

class HomePageBannerController extends Controller
{
    /**
     * Show the Home Page Banners management page.
     */
    public function index()
    {
        $leftBanners = HomeBanner::where('position', 'left')
            ->orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->get();

        $rightBanners = HomeBanner::where('position', 'right')
            ->orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->get();

        $middle3ColBanners = HomeBanner::where('position', 'middle_3_col')
            ->orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->get();

        $aboveNewsletterBanners = HomeBanner::where('position', 'above_newsletter')
            ->orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.banners.index', compact('leftBanners', 'rightBanners', 'middle3ColBanners', 'aboveNewsletterBanners'));
    }

    /**
     * Store a newly created banner.
     */
    public function store(Request $request)
    {
        $request->validate([
            'position' => 'required|in:left,right,middle_3_col,above_newsletter',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'link_url' => 'nullable|string|max:2048',
            'is_active' => 'nullable|in:0,1',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $imageUrl = $this->processAndUploadImage($request->file('image'), 'banners');

            HomeBanner::create([
                'position' => $request->position,
                'image_url' => $imageUrl,
                'link_url' => $request->input('link_url'),
                'is_active' => $request->has('is_active'),
                'sort_order' => $request->input('sort_order', 0),
            ]);

            return redirect()->route('admin.banners.index')->with('success', 'Banner added successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload banner: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified banner.
     */
    public function edit(int $id)
    {
        $banner = HomeBanner::findOrFail($id);
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Update the specified banner in storage.
     */
    public function update(Request $request, int $id)
    {
        $banner = HomeBanner::findOrFail($id);

        $request->validate([
            'position' => 'required|in:left,right,middle_3_col,above_newsletter',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'link_url' => 'nullable|string|max:2048',
            'is_active' => 'nullable|in:0,1',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            if ($request->hasFile('image')) {
                $banner->image_url = $this->processAndUploadImage($request->file('image'), 'banners');
            }

            $banner->position = $request->position;
            $banner->link_url = $request->input('link_url');
            $banner->is_active = $request->has('is_active');
            $banner->sort_order = $request->input('sort_order', 0);
            $banner->save();

            return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update banner: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status of a banner.
     */
    public function toggleStatus(int $id)
    {
        $banner = HomeBanner::findOrFail($id);
        $banner->update(['is_active' => !$banner->is_active]);

        $status = $banner->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.banners.index')->with('success', "Banner has been successfully {$status}.");
    }

    /**
     * Remove/Delete a banner.
     */
    public function destroy(int $id)
    {
        $banner = HomeBanner::findOrFail($id);
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted successfully.');
    }

    /**
     * Internal helper to compress and upload image.
     */
    private function processAndUploadImage(UploadedFile $file, string $folderName): string
    {
        // 1. Compress the file using the project's existing ImageHelper.
        $tempRelPath = ImageHelper::compressAndSave($file, 'uploads/temp_banners');
        $localPath = public_path($tempRelPath);

        // 2. Create a new UploadedFile instance from the compressed local file.
        $uploadedFile = new UploadedFile(
            $localPath,
            basename($localPath),
            mime_content_type($localPath),
            null,
            true // test mode allows using files not uploaded via HTTP POST
        );

        // 3. Upload using the base Controller's uploadFileToService.
        $fileUrl = $this->uploadFileToService($uploadedFile, $folderName);

        // 4. Delete the temporary compressed local file to avoid clutter.
        ImageHelper::delete($tempRelPath);

        return $fileUrl;
    }
}
