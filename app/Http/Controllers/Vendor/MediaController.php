<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\SellerMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $type = $request->get('type');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $query = SellerMedia::where('seller_id', auth('vendor')->id());

        if ($search) {
            $query->where('file_name', 'like', "%{$search}%");
        }

        if ($type) {
            if ($type === 'image') {
                $query->where('file_type', 'like', 'image/%');
            } elseif ($type === 'document') {
                $query->where('file_type', 'not like', 'image/%');
            }
        }

        $media = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('vendor.media.index', compact('media', 'search', 'type', 'limit', 'sort', 'order'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('file');
            $fileUrl = $this->uploadFileToService($file, 'seller_media_' . auth('vendor')->id());

            SellerMedia::create([
                'seller_id' => auth('vendor')->id(),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $fileUrl,
                'file_url' => $fileUrl,
                'file_size' => $file->getSize(),
                'file_type' => $file->getClientMimeType(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => 'File uploaded successfully!',
                    'file_url' => $fileUrl
                ]);
            }

            return back()->with('success', 'File uploaded successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $media = SellerMedia::where('seller_id', auth('vendor')->id())->findOrFail($id);
        $media->delete();

        return back()->with('success', 'File deleted from library successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:seller_media,id',
        ]);

        SellerMedia::where('seller_id', auth('vendor')->id())
            ->whereIn('id', $request->ids)
            ->delete();

        return back()->with('success', 'Selected files deleted successfully.');
    }

    public function apiList(Request $request)
    {
        $search = $request->get('search');
        $type = $request->get('type'); // 'image' or 'pdf'

        $query = SellerMedia::where('seller_id', auth('vendor')->id());

        if ($search) {
            $query->where('file_name', 'like', "%{$search}%");
        }

        if ($type === 'image') {
            $query->where('file_type', 'like', 'image/%');
        } elseif ($type === 'pdf') {
            $query->where('file_type', 'application/pdf');
        }

        $files = $query->orderBy('created_at', 'desc')->take(20)->get();

        return response()->json($files);
    }
}
