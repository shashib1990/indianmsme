<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\DDC;
use App\Models\ADC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $type    = $request->get('type', 'vendors');
        $search  = $request->get('search');
        $sort    = $request->get('sort', 'created_at');
        $order   = $request->get('order', 'desc');
        $limit   = $request->get('limit', 15);

        $query = match ($type) {
            'vendors' => Vendor::query(),
            'ddcs'    => DDC::query(),
            'adcs'    => ADC::query(),
            default   => Vendor::query(),
        };

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.users.index', compact('users', 'type', 'search', 'sort', 'order', 'limit'));
    }

    public function resetPassword(Request $request, string $type, int $id)
    {
        $request->validate([
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = match ($type) {
            'vendor' => Vendor::findOrFail($id),
            'ddc'    => DDC::findOrFail($id),
            'adc'    => ADC::findOrFail($id),
            default  => Vendor::findOrFail($id),
        };

        $user->update([
            'password'              => Hash::make($request->new_password),
            'force_change_password' => '1',
        ]);

        return back()->with('success', "Password reset for {$user->name}. They will be prompted to change it on next login.");
    }

    public function toggleStatus(string $type, int $id)
    {
        $user = match ($type) {
            'vendor' => Vendor::findOrFail($id),
            'ddc'    => DDC::findOrFail($id),
            'adc'    => ADC::findOrFail($id),
            default  => Vendor::findOrFail($id),
        };

        $newStatus = $user->is_active === '1' ? '0' : '1';
        $user->update(['is_active' => $newStatus]);

        $status = $newStatus === '1' ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully.");
    }
}
