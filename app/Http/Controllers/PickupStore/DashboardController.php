<?php

namespace App\Http\Controllers\PickupStore;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $store = Auth::guard('pickup_store')->user();
        $store->load('owner');

        return view('pickup-store.dashboard', compact('store'));
    }
}
