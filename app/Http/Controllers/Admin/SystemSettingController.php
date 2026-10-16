<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\Product;
use App\Http\Controllers\Vendor\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemSettingController extends Controller
{
    public function index()
    {
        $companyMargin = (float) SystemSetting::getVal('admin_margin_percentage', 8.0);
        $ddcMargin = (float) SystemSetting::getVal('ddc_margin_percentage', 5.0);
        $mbp = (float) SystemSetting::getVal('mbp_percentage', 2.0);
        $fsi = (float) SystemSetting::getVal('fsi_percentage', 0.50);

        return view('admin.settings.pricing', compact('companyMargin', 'ddcMargin', 'mbp', 'fsi'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'admin_margin_percentage' => 'required|numeric|min:0|max:100',
            'ddc_margin_percentage' => 'required|numeric|min:0|max:100',
            'mbp_percentage' => 'required|numeric|min:0|max:100',
            'fsi_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $adminMargin = (float) $request->input('admin_margin_percentage');
        $ddcMargin = (float) $request->input('ddc_margin_percentage');
        $mbp = (float) $request->input('mbp_percentage');
        $fsi = (float) $request->input('fsi_percentage');

        DB::beginTransaction();
        try {
            SystemSetting::setVal('admin_margin_percentage', $adminMargin);
            SystemSetting::setVal('company_margin_percentage', $adminMargin); // Keep legacy key in sync
            SystemSetting::setVal('ddc_margin_percentage', $ddcMargin);
            SystemSetting::setVal('mbp_percentage', $mbp);
            SystemSetting::setVal('fsi_percentage', $fsi);

            // Trigger recalculation for all products
            Product::chunk(100, function ($products) use ($adminMargin, $ddcMargin, $mbp) {
                foreach ($products as $product) {
                    $pricing = ProductController::calculatePricingData(
                        $product->seller_price_incl_gst,
                        $product->gst_rate,
                        $product->mrp,
                        $adminMargin,
                        $ddcMargin,
                        $mbp
                    );
                    $product->update($pricing);
                }
            });

            // Trigger recalculation for all sub-seller products
            \App\Models\SubSellerProduct::with('product')->chunk(100, function ($subSellerProducts) {
                foreach ($subSellerProducts as $subSellerProduct) {
                    $subSellerProduct->recalculatePricing();
                }
            });

            DB::commit();

            return redirect()->route('admin.settings.pricing.index')
                ->with('success', 'Pricing settings updated and all products recalculated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating settings: ' . $e->getMessage());
        }
    }

    public function logosIndex()
    {
        $headerLogo = SystemSetting::getVal('website_header_logo', asset('ecommerce-assets/images/logo.png'));
        $footerLogo = SystemSetting::getVal('website_footer_logo', asset('ecommerce-assets/images/logo.png'));
        $panelLogo = SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'));
        $favicon = SystemSetting::getVal('favicon', asset('admin-assets/images/favicon.ico'));

        return view('admin.settings.logos', compact('headerLogo', 'footerLogo', 'panelLogo', 'favicon'));
    }

    public function logosUpdate(Request $request)
    {
        $request->validate([
            'website_header_logo' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:5120',
            'website_footer_logo' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:5120',
            'panel_logo' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:5120',
            'favicon' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg,ico,gif|max:2048',
        ]);

        try {
            if ($request->hasFile('website_header_logo')) {
                $file = $request->file('website_header_logo');
                $url = $this->uploadLogoFile($file);
                SystemSetting::setVal('website_header_logo', $url);
                if (!$request->hasFile('panel_logo')) {
                    SystemSetting::setVal('panel_logo', $url);
                }
            }

            if ($request->hasFile('website_footer_logo')) {
                $file = $request->file('website_footer_logo');
                $url = $this->uploadLogoFile($file);
                SystemSetting::setVal('website_footer_logo', $url);
            }

            if ($request->hasFile('panel_logo')) {
                $file = $request->file('panel_logo');
                $url = $this->uploadLogoFile($file);
                SystemSetting::setVal('panel_logo', $url);
            }

            if ($request->hasFile('favicon')) {
                $file = $request->file('favicon');
                $url = $this->uploadLogoFile($file);
                SystemSetting::setVal('favicon', $url);
            }

            return redirect()->route('admin.settings.logos.index')
                ->with('success', 'System branding settings (including favicon) updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error uploading logos: ' . $e->getMessage());
        }
    }

    /**
     * Upload logo or favicon file using external upload service.
     */
    private function uploadLogoFile($file): string
    {
        return $this->uploadFileToService($file, 'logos');
    }


    public function bankIndex()
    {
        $bankName = SystemSetting::getVal('admin_bank_name', 'HDFC Bank');
        $accountName = SystemSetting::getVal('admin_account_name', 'ASC DISTRIBUTOR PVT LTD');
        $accountNumber = SystemSetting::getVal('admin_account_number', '50200089652670');
        $ifscCode = SystemSetting::getVal('admin_ifsc_code', 'HDFC0000886');
        $bankBranch = SystemSetting::getVal('admin_bank_branch', 'Naraina Plaza, Sec A, Rohini, Delhi');
        $upiId = SystemSetting::getVal('admin_upi_id', 'ascdistributor@hdfcbank');
        $qrCode = SystemSetting::getVal('admin_qr_code');

        return view('admin.settings.bank', compact(
            'bankName',
            'accountName',
            'accountNumber',
            'ifscCode',
            'bankBranch',
            'upiId',
            'qrCode'
        ));
    }

    public function bankUpdate(Request $request)
    {
        $request->validate([
            'admin_bank_name' => 'nullable|string|max:255',
            'admin_account_name' => 'nullable|string|max:255',
            'admin_account_number' => 'nullable|string|max:255',
            'admin_ifsc_code' => 'nullable|string|max:255',
            'admin_bank_branch' => 'nullable|string|max:255',
            'admin_upi_id' => 'nullable|string|max:255',
            'admin_qr_code' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:5120',
        ]);

        try {
            SystemSetting::setVal('admin_bank_name', $request->input('admin_bank_name', ''));
            SystemSetting::setVal('admin_account_name', $request->input('admin_account_name', ''));
            SystemSetting::setVal('admin_account_number', $request->input('admin_account_number', ''));
            SystemSetting::setVal('admin_ifsc_code', strtoupper($request->input('admin_ifsc_code', '')));
            SystemSetting::setVal('admin_bank_branch', $request->input('admin_bank_branch', ''));
            SystemSetting::setVal('admin_upi_id', $request->input('admin_upi_id', ''));

            if ($request->has('remove_qr_code') && $request->input('remove_qr_code') == '1') {
                SystemSetting::setVal('admin_qr_code', '');
            } elseif ($request->hasFile('admin_qr_code')) {
                $file = $request->file('admin_qr_code');
                $url = $this->uploadFileToService($file, 'bank');
                SystemSetting::setVal('admin_qr_code', $url);
            }

            return redirect()->route('admin.settings.bank.index')
                ->with('success', 'ADPL Bank Details and QR Code updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating bank details: ' . $e->getMessage());
        }
    }

    public function aswtBankIndex()
    {
        $bankName = SystemSetting::getVal('aswt_bank_name', '');
        $accountName = SystemSetting::getVal('aswt_account_name', '');
        $accountNumber = SystemSetting::getVal('aswt_account_number', '');
        $ifscCode = SystemSetting::getVal('aswt_ifsc_code', '');
        $bankBranch = SystemSetting::getVal('aswt_bank_branch', '');
        $upiId = SystemSetting::getVal('aswt_upi_id', '');
        $qrCode = SystemSetting::getVal('aswt_qr_code');

        return view('admin.settings.aswt_bank', compact(
            'bankName',
            'accountName',
            'accountNumber',
            'ifscCode',
            'bankBranch',
            'upiId',
            'qrCode'
        ));
    }

    public function aswtBankUpdate(Request $request)
    {
        $request->validate([
            'aswt_bank_name' => 'nullable|string|max:255',
            'aswt_account_name' => 'nullable|string|max:255',
            'aswt_account_number' => 'nullable|string|max:255',
            'aswt_ifsc_code' => 'nullable|string|max:255',
            'aswt_bank_branch' => 'nullable|string|max:255',
            'aswt_upi_id' => 'nullable|string|max:255',
            'aswt_qr_code' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:5120',
        ]);

        try {
            SystemSetting::setVal('aswt_bank_name', $request->input('aswt_bank_name', ''));
            SystemSetting::setVal('aswt_account_name', $request->input('aswt_account_name', ''));
            SystemSetting::setVal('aswt_account_number', $request->input('aswt_account_number', ''));
            SystemSetting::setVal('aswt_ifsc_code', strtoupper($request->input('aswt_ifsc_code', '')));
            SystemSetting::setVal('aswt_bank_branch', $request->input('aswt_bank_branch', ''));
            SystemSetting::setVal('aswt_upi_id', $request->input('aswt_upi_id', ''));

            if ($request->has('remove_qr_code') && $request->input('remove_qr_code') == '1') {
                SystemSetting::setVal('aswt_qr_code', '');
            } elseif ($request->hasFile('aswt_qr_code')) {
                $file = $request->file('aswt_qr_code');
                $url = $this->uploadFileToService($file, 'bank');
                SystemSetting::setVal('aswt_qr_code', $url);
            }

            return redirect()->route('admin.settings.aswt-bank.index')
                ->with('success', 'ASWT Bank Details and QR Code updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating ASWT bank details: ' . $e->getMessage());
        }
    }

    public function adplOfficeIndex()
    {
        $companyName = SystemSetting::getVal('adpl_company_name', '');
        $gstNumber = SystemSetting::getVal('adpl_gst_number', '');
        $stateId = SystemSetting::getVal('adpl_state_id', '');
        $districtId = SystemSetting::getVal('adpl_district_id', '');
        $pincode = SystemSetting::getVal('adpl_pincode', '');
        $address = SystemSetting::getVal('adpl_address', '');

        $states = \App\Models\State::active()->orderBy('name')->get();
        $districts = $stateId ? \App\Models\District::where('state_id', $stateId)->active()->orderBy('name')->get() : collect();

        return view('admin.settings.adpl_office', compact(
            'companyName',
            'gstNumber',
            'stateId',
            'districtId',
            'pincode',
            'address',
            'states',
            'districts'
        ));
    }

    public function adplOfficeUpdate(Request $request)
    {
        $request->validate([
            'adpl_company_name' => 'required|string|max:255',
            'adpl_gst_number'   => 'required|string|size:15',
            'adpl_state_id'     => 'required|exists:states,id',
            'adpl_district_id'  => 'required|exists:districts,id',
            'adpl_pincode'      => 'required|string|size:6',
            'adpl_address'      => 'required|string',
        ]);

        try {
            SystemSetting::setVal('adpl_company_name', trim($request->input('adpl_company_name')));
            SystemSetting::setVal('adpl_gst_number', strtoupper(trim($request->input('adpl_gst_number'))));
            SystemSetting::setVal('adpl_state_id', $request->input('adpl_state_id'));
            SystemSetting::setVal('adpl_district_id', $request->input('adpl_district_id'));
            SystemSetting::setVal('adpl_pincode', trim($request->input('adpl_pincode')));
            SystemSetting::setVal('adpl_address', trim($request->input('adpl_address')));

            return redirect()->route('admin.settings.adpl-office.index')
                ->with('success', 'ADPL Head Office Details updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating ADPL Head Office details: ' . $e->getMessage());
        }
    }

    public function cashbackIndex()
    {
        $ddcCashback = (float) SystemSetting::getVal('ddc_cashback_percentage', 0.0);
        $adcCashback = (float) SystemSetting::getVal('adc_cashback_percentage', 0.0);

        return view('admin.settings.cashback', compact('ddcCashback', 'adcCashback'));
    }

    public function cashbackUpdate(Request $request)
    {
        $request->validate([
            'ddc_cashback_percentage' => 'required|numeric|min:0|max:100',
            'adc_cashback_percentage' => 'required|numeric|min:0|max:100',
        ]);

        try {
            SystemSetting::setVal('ddc_cashback_percentage', (float) $request->input('ddc_cashback_percentage'));
            SystemSetting::setVal('adc_cashback_percentage', (float) $request->input('adc_cashback_percentage'));

            return redirect()->route('admin.settings.cashback.index')
                ->with('success', 'DDC & ADC Cashback settings updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating cashback settings: ' . $e->getMessage());
        }
    }
}
