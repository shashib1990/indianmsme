<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\State;
use App\Models\District;
use App\Services\TheekHaiService;
use App\Services\NotificationService;

class AuthController extends Controller
{
    public function showLogin()
    {
        // If already logged in as ADC or web, redirect to correct dashboard
        foreach (['adc', 'web'] as $guard) {
            if (Auth::guard($guard)->check()) {
                $redirectRoute = match ($guard) {
                    'adc' => route('home'),
                    default => route('home'),
                };
                return redirect($redirectRoute);
            }
        }

        return view('web.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = trim($request->email);
        $password = $request->password;

        $user = User::where('role', 'adc')
            ->whereHas('adcProfile', function ($q) use ($loginInput) {
                $q->where('adc_code', $loginInput);
            })->first();

        if ($user && Auth::guard('adc')->attempt(['id' => $user->id, 'password' => $password], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('adc.dashboard'))->with('success', 'Logged in as ADC successfully.');
        }

        return back()->withErrors(['email' => 'Invalid credentials. Please check your ADC Code and Password.'])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'type' => ['required', 'string', 'in:ASC,MVS,SCS'],
            'code' => [
                'required_if:type,ASC,SCS',
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->type, ['ASC', 'SCS']) && !empty($value)) {
                        $exists = \App\Models\AdcProfile::where('type', $request->type)
                            ->where('code', $value)
                            ->exists();
                        if ($exists) {
                            $fail("This {$request->type} code has already been registered.");
                        }
                    }
                }
            ],
            'advisor_code' => ['required', 'string', 'max:255'],
            'state_id' => ['required', 'exists:states,id'],
            'district_id' => ['required', 'exists:districts,id'],
            'pincode' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
            'address' => ['required', 'string'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'gst_number' => ['required', 'string', 'min:15', 'max:15'],
        ], [
            'mobile.regex' => 'The mobile number must be exactly 10 digits.',
            'pincode.regex' => 'The pincode must be exactly 6 digits.',
            'code.required_if' => 'The Code field is required when ASC or SCS type is selected.',
            'gst_number.required' => 'The GST Number field is required.',
            'gst_number.min' => 'The GST Number must be exactly 15 characters.',
            'gst_number.max' => 'The GST Number must be exactly 15 characters.',
        ]);

        // 1. Call TheekHai Register API to validate mobile number (do not save to DB yet)
        $theekHaiResult = TheekHaiService::registerUser($request->name, $request->mobile);

        if (!$theekHaiResult['success']) {
            return back()->withErrors(['mobile' => $theekHaiResult['message']])->withInput();
        }

        // 2. Generate random 6-digit numeric OTP valid for 5 minutes
        $otp = sprintf('%06d', rand(100000, 999999));
        $expiresAt = now()->addMinutes(5)->timestamp;

        // 3. Store form data & OTP in Session temporarily
        $request->session()->put('adc_reg_pending', [
            'inputs' => $request->only([
                'name',
                'email',
                'mobile',
                'password',
                'type',
                'code',
                'advisor_code',
                'state_id',
                'district_id',
                'pincode',
                'address',
                'company_name',
                'gst_number'
            ]),
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);

        // 4. Send OTP via TheekHai Message API
        TheekHaiService::sendMessage(
            $request->mobile,
            "Your OTP for ADC Registration on Indian MSME is: {$otp}. Valid for 5 minutes."
        );

        // 5. Redirect to OTP Verification screen
        return redirect()->route('adc.verify-otp');
    }

    public function showVerifyOtp(Request $request)
    {
        if (!$request->session()->has('adc_reg_pending')) {
            return redirect()->route('login')->with('error', 'No pending registration session found. Please register again.');
        }

        $pending = $request->session()->get('adc_reg_pending');
        $mobile = $pending['inputs']['mobile'] ?? '';
        $expiresAt = $pending['expires_at'] ?? (now()->addMinutes(5)->timestamp);
        $remainingSeconds = max(0, $expiresAt - now()->timestamp);

        return view('web.auth.verify-otp', compact('mobile', 'expiresAt', 'remainingSeconds'));
    }

    public function resendOtp(Request $request)
    {
        if (!$request->session()->has('adc_reg_pending')) {
            return redirect()->route('login')->with('error', 'Registration session expired. Please register again.');
        }

        $pending = $request->session()->get('adc_reg_pending');

        // Generate new 6-digit OTP and reset validity to 5 minutes
        $newOtp = sprintf('%06d', rand(100000, 999999));
        $pending['otp'] = $newOtp;
        $pending['expires_at'] = now()->addMinutes(5)->timestamp;

        $request->session()->put('adc_reg_pending', $pending);

        $mobile = $pending['inputs']['mobile'];

        // Send new OTP via TheekHai Message API
        TheekHaiService::sendMessage(
            $mobile,
            "A new OTP for your ADC Registration on Indian MSME is: {$newOtp}. Valid for 5 minutes."
        );

        return redirect()->route('adc.verify-otp')->with('resend_success', true);
    }

    public function verifyOtp(Request $request)
    {
        if (!$request->session()->has('adc_reg_pending')) {
            return redirect()->route('login')->with('error', 'Registration session expired. Please register again.');
        }

        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ], [
            'otp.required' => 'Please enter the 6-digit OTP.',
            'otp.digits' => 'The OTP must be exactly 6 digits.',
        ]);

        $pending = $request->session()->get('adc_reg_pending');

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

        $state = State::find($inputs['state_id']);
        $district = District::find($inputs['district_id']);

        if (in_array($inputs['type'], ['ASC', 'SCS'])) {
            $code = $inputs['code'];
        } else {
            // MVS: generate incremental APC code (e.g. APC0001, APC0002, ...)
            $lastApcNum = \App\Models\AdcProfile::where('code', 'like', 'APC%')
                ->pluck('code')
                ->map(function ($c) {
                    return preg_match('/APC(\d+)/i', (string) $c, $m) ? (int) $m[1] : 0;
                })
                ->max();

            $nextApcNum = ($lastApcNum ? $lastApcNum : 0) + 1;
            $code = 'APC' . str_pad($nextApcNum, 4, '0', STR_PAD_LEFT);
        }

        // Generate incremental ADC code (e.g. ADC001, ADC002, ...) for every registration
        $lastAdcNum = \App\Models\AdcProfile::where('adc_code', 'like', 'ADC%')
            ->pluck('adc_code')
            ->map(function ($c) {
                return preg_match('/ADC(\d+)/i', (string) $c, $m) ? (int) $m[1] : 0;
            })
            ->max();

        $nextAdcNum = ($lastAdcNum ? $lastAdcNum : 0) + 1;
        $adcCode = 'ADC' . str_pad($nextAdcNum, 3, '0', STR_PAD_LEFT);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $user = User::create([
                'name'           => $inputs['name'],
                'email'          => $inputs['email'],
                'mobile'         => $inputs['mobile'],
                'password'       => bcrypt($inputs['password']),
                'role'           => 'adc',
                'status'         => '1',
                'type'           => $inputs['type'],
                'code'           => $code,
                'adc_code'       => $adcCode,
                'advisor_code'   => $inputs['advisor_code'],
                'state_id'       => $inputs['state_id'],
                'district_id'    => $inputs['district_id'],
                'state'          => $state ? $state->name : null,
                'district'       => $district ? $district->name : null,
                'pincode'        => $inputs['pincode'],
                'address'        => $inputs['address'],
                'company_name'   => $inputs['company_name'],
                'gst_number'     => $inputs['gst_number'],
                'official_phone' => $inputs['mobile'] ?? null,
                'official_email' => $inputs['email'] ?? null,
                'kyc_status'     => 'Pending',
            ]);

            // Explicitly save ALL profile fields to adc_profiles.
            // This is the authoritative save — ensures adc_code, code, gst_number,
            // advisor_code etc. are always persisted regardless of boot event timing.
            \App\Models\AdcProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'type'           => $inputs['type'],
                    'code'           => $code,
                    'adc_code'       => $adcCode,
                    'advisor_code'   => $inputs['advisor_code'] ?? null,
                    'company_name'   => $inputs['company_name'] ?? null,
                    'official_phone' => $inputs['mobile'] ?? null,
                    'official_email' => $inputs['email'] ?? null,
                    'gst_number'     => $inputs['gst_number'] ?? null,
                    'address'        => $inputs['address'] ?? null,
                    'state'          => $state ? $state->name : null,
                    'district'       => $district ? $district->name : null,
                    'state_id'       => $inputs['state_id'] ?? null,
                    'district_id'    => $inputs['district_id'] ?? null,
                    'pincode'        => $inputs['pincode'] ?? null,
                    'kyc_status'     => 'Pending',
                ]
            );

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('ADC Registration DB failed', [
                'error'  => $e->getMessage(),
                'mobile' => $inputs['mobile'] ?? null,
                'type'   => $inputs['type'] ?? null,
            ]);
            return back()->withErrors(['otp' => 'Registration failed due to a server error. Please try again or contact support.']);
        }

        // Reload fresh user from DB so adcProfile relation reflects fully-saved data
        $user = User::find($user->id);

        // Register ADC on external Abhhyam API (called AFTER profile is fully populated)
        \App\Services\AbhhyamService::registerAdc($user);

        // Send confirmation message via TheekHai Message API (with Username & Password)
        $plainPassword = $inputs['password'] ?? '';
        TheekHaiService::sendMessage(
            $user->mobile,
            "Welcome to Indian MSME! Your ADC Registration ({$adcCode}) is completed successfully. Username: {$adcCode}, Password: {$plainPassword} for login."
        );

        NotificationService::notifyAdmin(
            "New ADC Registration",
            "New ADC registered: {$user->name} (ADC Code: {$adcCode}, Email: {$user->email}, Pincode: {$user->pincode}).",
            route('admin.external.adc.index')
        );

        Auth::guard('adc')->login($user);

        // Clear temporary session data
        $request->session()->forget('adc_reg_pending');

        return redirect()->route('home')->with('success', "ADC Account registered & verified successfully. Your ADC Code / Username is: {$adcCode}");
    }

    public function logout(Request $request)
    {
        foreach (['adc', 'web'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }

    public function getRunningStoreCodes($type)
    {
        if (!in_array($type, ['ASC', 'MVS', 'SCS'])) {
            return response()->json([], 400);
        }

        try {
            if (strtoupper($type) === 'SCS') {
                $url = "https://ardhsainikmall.com/api/scs-store-code/scs";
            } else {
                $url = "https://ardhsainikcanteen.in/api/running-store-code/{$type}";
            }

            $response = Http::timeout(10)->get($url);
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                \Illuminate\Support\Facades\Log::warning("Store codes API non-200: " . $response->status() . " Body: " . $response->body());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error loading store codes: " . $e->getMessage());
        }
        return response()->json([]);
    }
}
