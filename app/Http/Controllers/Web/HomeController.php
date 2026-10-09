<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HomeBanner;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $leftBanners = HomeBanner::active()->where('position', 'left')->orderBy('sort_order')->orderBy('id', 'desc')->get();
        $rightBanners = HomeBanner::active()->where('position', 'right')->orderBy('sort_order')->orderBy('id', 'desc')->get();
        $middle3ColBanners = HomeBanner::active()->where('position', 'middle_3_col')->orderBy('sort_order')->orderBy('id', 'desc')->get();
        $aboveNewsletterBanners = HomeBanner::active()->where('position', 'above_newsletter')->orderBy('sort_order')->orderBy('id', 'desc')->get();

        $featuredProducts = Product::approved()
            ->with(['category', 'brand', 'seller'])
            ->latest()
            ->take(12)
            ->get();

        $newArrivals = Product::approved()
            ->with(['category', 'brand', 'seller'])
            ->latest()
            ->take(12)
            ->get();

        $brands = Brand::where('status', '1')->orderBy('name')->take(12)->get();
        $categories = Category::where('status', '1')->orderBy('name')->get();

        return view('web.home', compact(
            'leftBanners',
            'rightBanners',
            'middle3ColBanners',
            'aboveNewsletterBanners',
            'featuredProducts',
            'newArrivals',
            'brands',
            'categories'
        ));
    }

    public function about()
    {
        return view('web.about');
    }

    public function contact()
    {
        return view('web.contact');
    }

    public function brands()
    {
        $brands = Brand::where('status', '1')->orderBy('name')->get();
        return view('web.brands', compact('brands'));
    }

    public function vendors(Request $request)
    {
        $query = User::where('role', 'seller')
            ->where('status', '1')
            ->whereHas('sellerProfile', function ($q) {
                $q->where('kyc_status', 'APPROVED');
            });

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('sellerProfile', function ($sp) use ($search) {
                        $sp->where('company_name', 'like', '%' . $search . '%')
                            ->orWhere('seller_id', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('category')) {
            $categorySlug = $request->input('category');
            $query->whereHas('sellerProfile', function ($sp) use ($categorySlug) {
                $sp->whereHas('category', function ($cat) use ($categorySlug) {
                    $cat->where('slug', $categorySlug);
                })->orWhere('category_id', function ($subQ) use ($categorySlug) {
                    $subQ->select('id')->from('categories')->where('slug', $categorySlug);
                });
            });
        }

        $sellers = $query->paginate(18)->withQueryString();
        $categories = Category::where('status', '1')->orderBy('name')->get();

        if ($request->ajax()) {
            $html = view('web.partials.vendor-grid', compact('sellers'))->render();
            return response()->json(['html' => $html]);
        }

        return view('web.vendors', compact('sellers', 'categories'));
    }

    public function faq()
    {
        $topics = \App\Models\FaqTopic::with(['faqs' => fn($q) => $q->active()->ordered()])
            ->active()
            ->ordered()
            ->get();

        return view('web.faq', compact('topics'));
    }
}
