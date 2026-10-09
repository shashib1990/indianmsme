<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Pincode;
use App\Models\DdcPincode;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(Request $request, $id, $slug = null)
    {
        $product = Product::with([
            'category',
            'subCategory',
            'childCategory',
            'brand',
            'seller.sellerProfile',
        ])
            ->where('status', 'Approved')
            ->whereHas('seller', fn($q) => $q->where('status', '1'))
            ->whereHas('seller.sellerProfile', fn($q) => $q->where('kyc_status', 'APPROVED'))
            ->findOrFail($id);

        $correctSlug = \Illuminate\Support\Str::slug($product->product_name);
        if ($slug !== $correctSlug) {
            return redirect()->route('product.show', ['id' => $id, 'slug' => $correctSlug], 301);
        }

        // Check pincode delivery
        $pincode = $request->input('pincode') ?? session('user_pincode');
        $deliveryAvailable = false;
        $pincodeRecord = null;

        $checkSellerId = $product->seller_id;
        $subSellerId = $request->input('seller');
        if ($subSellerId) {
            $subSeller = \App\Models\User::where('role', 'seller')
                ->where(function($q) use ($subSellerId) {
                    $q->where('id', $subSellerId)
                      ->orWhereHas('sellerProfile', function($sq) use ($subSellerId) {
                          $sq->where('seller_id', $subSellerId)
                            ->orWhere('company_name', $subSellerId);
                      });
                })->first();
            if ($subSeller) {
                $checkSellerId = $subSeller->id;
            }
        }

        if ($pincode) {
            $pincodeRecord = Pincode::where('pincode', $pincode)->where('status', '1')->first();
            if ($pincodeRecord) {
                $deliveryAvailable = Pincode::isServiceableBySeller($pincode, $checkSellerId);
                if ($deliveryAvailable) {
                    session(['user_pincode' => $pincode]);
                } else {
                    session()->forget('user_pincode');
                }
            } else {
                session()->forget('user_pincode');
            }
        }

        // Related products in same category
        $relatedProducts = Product::with(['category', 'brand'])
            ->where('status', 'Approved')
            ->whereHas('seller', fn($q) => $q->where('status', '1'))
            ->whereHas('seller.sellerProfile', fn($q) => $q->where('kyc_status', 'APPROVED'))
            ->serviceableByPincode($pincode)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(8)
            ->get();

        $gallery = $product->gallery ?? [];

        return view('web.product', compact(
            'product',
            'relatedProducts',
            'gallery',
            'pincode',
            'deliveryAvailable',
            'pincodeRecord'
        ));
    }
}
