<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\State;
use App\Models\Vendor;
use App\Services\TheekHaiService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('vendor')->check()) {
            return redirect()->route('vendor.dashboard');
        }
        return view('vendor.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_id' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $vendorCode = trim($request->input('login_id', $request->input('email')));

        // Find vendor user by Vendor Code (seller_id in sellerProfile), email, or mobile
        $vendor = Vendor::where(function ($query) use ($vendorCode) {
            $query->where('email', $vendorCode)
                ->orWhere('mobile', $vendorCode)
                ->orWhereHas('sellerProfile', function ($q) use ($vendorCode) {
                    $q->where('seller_id', $vendorCode);
                });
        })->first();

        if ($vendor && Hash::check($request->password, $vendor->password)) {
            if (!$vendor->status || $vendor->status === '0') {
                return back()->withErrors(['login_id' => 'Your account has been deactivated. Please contact admin.'])->onlyInput('login_id');
            }

            Auth::guard('vendor')->login($vendor, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('vendor.dashboard'));
        }

        return back()->withErrors(['login_id' => 'Invalid credentials. Please enter a valid Vendor Code and Password.'])->onlyInput('login_id');
    }

    public function showRegister()
    {
        if (Auth::guard('vendor')->check()) {
            return redirect()->route('vendor.dashboard');
        }

        $categories = Category::active()->orderBy('name')->get();
        $states = State::active()->orderBy('name')->get();

        return view('web.vendor-account', compact('categories', 'states'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'company_type' => ['required', 'in:Company,LLP,Proprietorship Firm,Other'],
            'seller_type' => ['required', 'in:Trader,Manufacturer,Distributor'],
            'brand_name' => ['required_if:seller_type,Manufacturer', 'nullable', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'authorized_rep_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'state' => ['required', 'exists:states,id'],
            'district' => ['required', 'exists:districts,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'brand_name.required_if' => 'Brand Name is required when Seller Type is Manufacturer.',
            'mobile.regex' => 'The mobile number must be exactly 10 numeric digits.',
        ]);

        $brandName = null;
        if ($request->seller_type === 'Manufacturer') {
            $brandNameInput = $request->input('brand_name');
            if (is_array($brandNameInput)) {
                $brandName = implode(', ', array_filter(array_map('trim', $brandNameInput)));
            } else {
                $brandName = trim((string) $brandNameInput);
            }
        }

        // Validate mobile number with TheekHai Register API
        $theekHaiResult = TheekHaiService::registerUser($request->authorized_rep_name, $request->mobile);
        if (!$theekHaiResult['success']) {
            return back()->withErrors(['mobile' => $theekHaiResult['message']])->withInput();
        }

        // Generate 6-digit numeric OTP valid for 5 minutes
        $otp = sprintf('%06d', rand(100000, 999999));
        $expiresAt = now()->addMinutes(5)->timestamp;

        // Save form inputs and OTP into session (do NOT create DB record yet)
        $request->session()->put('vendor_reg_pending', [
            'inputs' => [
                'category_id' => $request->category_id,
                'company_type' => $request->company_type,
                'seller_type' => $request->seller_type,
                'brand_name' => $brandName,
                'company_name' => $request->company_name,
                'authorized_rep_name' => $request->authorized_rep_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'state' => $request->state,
                'district' => $request->district,
                'password' => $request->password,
            ],
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);

        // Send OTP via TheekHai Message API
        TheekHaiService::sendMessage(
            $request->mobile,
            "Your OTP for Seller Registration on Indian MSME is: {$otp}. Valid for 5 minutes."
        );

        return redirect()->route('vendor.verify-otp');
    }

    public function showVerifyOtp(Request $request)
    {
        if (!$request->session()->has('vendor_reg_pending')) {
            return redirect()->route('vendor.login')->with('error', 'No pending registration session found. Please register again.');
        }

        $pending = $request->session()->get('vendor_reg_pending');
        $mobile = $pending['inputs']['mobile'] ?? '';
        $expiresAt = $pending['expires_at'] ?? (now()->addMinutes(5)->timestamp);
        $remainingSeconds = max(0, $expiresAt - now()->timestamp);

        return view('vendor.auth.verify-otp', compact('mobile', 'expiresAt', 'remainingSeconds'));
    }

    public function resendOtp(Request $request)
    {
        if (!$request->session()->has('vendor_reg_pending')) {
            return redirect()->route('vendor.login')->with('error', 'Registration session expired. Please register again.');
        }

        $pending = $request->session()->get('vendor_reg_pending');

        $newOtp = sprintf('%06d', rand(100000, 999999));
        $pending['otp'] = $newOtp;
        $pending['expires_at'] = now()->addMinutes(5)->timestamp;

        $request->session()->put('vendor_reg_pending', $pending);

        $mobile = $pending['inputs']['mobile'];

        TheekHaiService::sendMessage(
            $mobile,
            "A new OTP for your Seller Registration on Indian MSME is: {$newOtp}. Valid for 5 minutes."
        );

        return redirect()->route('vendor.verify-otp')->with('resend_success', true);
    }

    public function verifyOtp(Request $request)
    {
        if (!$request->session()->has('vendor_reg_pending')) {
            return redirect()->route('vendor.login')->with('error', 'Registration session expired. Please register again.');
        }

        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ], [
            'otp.required' => 'Please enter the 6-digit OTP.',
            'otp.digits'   => 'The OTP must be exactly 6 digits.',
        ]);

        $pending = $request->session()->get('vendor_reg_pending');

        // Check 5-minute validity
        if (now()->timestamp > $pending['expires_at']) {
            return back()->withErrors(['otp' => 'OTP has expired (validity is 5 minutes). Please click Resend OTP.']);
        }

        // Verify OTP match
        if (trim($request->otp) !== (string) $pending['otp']) {
            return back()->withErrors(['otp' => 'Invalid OTP entered. Please check the TheekHai App and enter the latest OTP.']);
        }

        // --- OTP Verified: Complete Registration in Database ---
        $inputs = $pending['inputs'];

        // Generate unique Seller ID with IMV prefix (e.g. IMV1001)
        $sellerId = \App\Models\SellerProfile::generateSellerId();

        $vendor = new Vendor();
        $vendor->name = $inputs['company_name'];
        $vendor->email = $inputs['email'];
        $vendor->mobile = $inputs['mobile'];
        $vendor->password = Hash::make($inputs['password']);
        $vendor->role = 'seller';
        $vendor->status = '1'; // active account

        $vendor->company_name = $inputs['company_name'];
        $vendor->company_type = $inputs['company_type'];
        $vendor->seller_type = $inputs['seller_type'];
        $vendor->brand_name = $inputs['brand_name'];
        $vendor->authorized_rep_name = $inputs['authorized_rep_name'];
        $vendor->state_id = $inputs['state'];
        $vendor->district_id = $inputs['district'];
        $vendor->state = null;
        $vendor->district = null;
        $vendor->category_id = $inputs['category_id'];
        $vendor->seller_id = $sellerId;
        $vendor->save();

        // Share login details on TheekHai App
        TheekHaiService::sendMessage(
            $vendor->mobile,
            "Welcome to Indian MSME! Your Seller Registration is successful. Your Seller Code (Login ID): {$sellerId}, Password: {$inputs['password']}. Login at: " . route('vendor.login')
        );

        NotificationService::notifyAdmin(
            "New Vendor Registration",
            "New Vendor registered: {$vendor->company_name} (Seller ID: {$sellerId}, Email: {$vendor->email}, Contact: {$vendor->authorized_rep_name}).",
            route('admin.external.vendors.index')
        );

        // Clear session data
        $request->session()->forget('vendor_reg_pending');

        // Log the user in
        Auth::guard('vendor')->login($vendor);

        return redirect()->route('vendor.dashboard')->with('success', "Registration & OTP verification successful! Your Seller Code is {$sellerId}. Welcome to your seller dashboard.");
    }

    public function logout(Request $request)
    {
        Auth::guard('vendor')->logout();
        
        return redirect()->route('vendor.login');
    }
}
