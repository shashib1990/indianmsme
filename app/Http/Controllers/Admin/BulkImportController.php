<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Models\ADC;
use App\Models\DDC;
use App\Models\SellerProfile;
use App\Models\AdcProfile;
use App\Models\DdcProfile;
use App\Models\State;
use App\Models\District;
use App\Models\Pincode;
use App\Models\DdcPincode;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use App\Models\Brand;
use App\Models\Product;
use App\Models\DdcLocalProduct;
use App\Models\AdcLocalProduct;
use App\Models\UnitOfMeasure;
use App\Models\Hsn;
use App\Models\Department;
use App\Models\SystemSetting;
use App\Models\PickupStore;
use App\Models\PickupStoreDeliveryArea;
use App\Enums\ProductStatus;
use App\Http\Controllers\Vendor\ProductController as VendorProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class BulkImportController extends Controller
{
    // =========================================================================
    // SELLER IMPORT
    // =========================================================================

    public function sellerIndex()
    {
        return view('admin.import.seller.index');
    }

    public function sellerSample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Seller Import Template');

        $headers = [
            'A1' => 'Seller ID*',
            'B1' => 'Name*',
            'C1' => 'Email*',
            'D1' => 'Mobile*',
            'E1' => 'Company Name*',
            'F1' => 'Company Type',
            'G1' => 'Seller Type',
            'H1' => 'Brand Name',
            'I1' => 'Category Name',
            'J1' => 'Department Name',
            'K1' => 'Parent Seller ID',
            'L1' => 'GST Number',
            'M1' => 'Authorized Rep Name',
            'N1' => 'Alternate Mobile',
            'O1' => 'Alternate Email',
            'P1' => 'State*',
            'Q1' => 'District*',
            'R1' => 'Pincode',
            'S1' => 'Address',
            'T1' => 'Aadhaar No',
            'U1' => 'PAN No',
            'V1' => 'Account Holder Name',
            'W1' => 'Bank Account Number',
            'X1' => 'IFSC Code',
            'Y1' => 'Bank Name',
            'Z1' => 'Branch Name',
            'AA1' => 'Wallet Balance',
            'AB1' => 'KYC Status',
            'AC1' => 'Allow Own Products',
            'AD1' => 'Is DDC Subscribed',
            'AE1' => 'DDC Sub Status',
            'AF1' => 'DDC AMA Commission %',
            'AG1' => 'Profile Image Path/URL',
            'AH1' => 'Company Logo Path/URL',
            'AI1' => 'Company Banner Path/URL',
            'AJ1' => 'GST Certificate Path/URL',
            'AK1' => 'PAN Card Path/URL',
            'AL1' => 'Aadhaar Card Path/URL',
            'AM1' => 'Udyam Certificate Path/URL',
            'AN1' => 'Incorporation Cert Path/URL',
            'AO1' => 'Cancelled Cheque Path/URL',
            'AP1' => 'FSSAI Certificate Path/URL',
            'AQ1' => 'Trade License Path/URL',
            'AR1' => 'Status',
            'AS1' => 'Password',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerRange = 'A1:AS1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4F46E5');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sampleRow = [
            'A2' => 'IMV1001',
            'B2' => 'Rajesh Sharma',
            'C2' => 'rajesh.sharma@example.com',
            'D2' => '9876543210',
            'E2' => 'Sharma Traders Pvt Ltd',
            'F2' => 'Private Limited Company',
            'G2' => 'Manufacturer',
            'H2' => 'Sharma Craft',
            'I2' => 'Handicrafts',
            'J2' => 'Sales',
            'K2' => '',
            'L2' => '19AAAAA0000A1Z5',
            'M2' => 'Rajesh Sharma',
            'N2' => '9876543211',
            'O2' => 'info@sharmatraders.com',
            'P2' => 'West Bengal',
            'Q2' => 'Kolkata',
            'R2' => '700001',
            'S2' => '123 MG Road, Suite 4B',
            'T2' => '123456789012',
            'U2' => 'ABCDE1234F',
            'V2' => 'Sharma Traders Pvt Ltd',
            'W2' => '91827364501928',
            'X2' => 'SBIN0000001',
            'Y2' => 'State Bank of India',
            'Z2' => 'Kolkata Main Branch',
            'AA2' => '0.00',
            'AB2' => 'APPROVED',
            'AC2' => 'Yes',
            'AD2' => '0',
            'AE2' => 'none',
            'AF2' => '0.00',
            'AG2' => '',
            'AH2' => '',
            'AI2' => '',
            'AJ2' => '',
            'AK2' => '',
            'AL2' => '',
            'AM2' => '',
            'AN2' => '',
            'AO2' => '',
            'AP2' => '',
            'AQ2' => '',
            'AR2' => 'Active',
            'AS2' => 'Password123',
        ];

        foreach ($sampleRow as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Add Excel Data Validation Dropdowns for Enum / Fixed Option columns
        $this->addDropdownValidation($sheet, 'F', ['Proprietorship Firm', 'Partnership Firm', 'Private Limited Company', 'Public Limited Company', 'LLP', 'Company', 'Other']);
        $this->addDropdownValidation($sheet, 'G', ['Manufacturer', 'Wholesaler', 'Trader', 'Distributor', 'Retailer']);
        $this->addDropdownValidation($sheet, 'AB', ['APPROVED', 'PENDING', 'REJECTED']);
        $this->addDropdownValidation($sheet, 'AC', ['Yes', 'No']);
        $this->addDropdownValidation($sheet, 'AD', ['1', '0']);
        $this->addDropdownValidation($sheet, 'AE', ['none', 'pending', 'approved', 'rejected']);
        $this->addDropdownValidation($sheet, 'AR', ['Active', 'Inactive']);

        foreach (array_merge(range('A', 'Z'), ['AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS']) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Seller_Import_Template_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function sellerPreview(Request $request)
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(3600);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls,txt|max:153600',
        ]);

        $rows = $this->readExcelRows($request->file('file'));
        if (empty($rows)) {
            return response()->json(['success' => false, 'message' => 'The uploaded file is empty or missing content.'], 422);
        }

        $preview = [];
        $validCount = 0;
        $errorCount = 0;

        $processedEmails = [];
        $processedMobiles = [];
        $processedSellerIds = [];

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $sellerId     = trim($row['A'] ?? '');
            $name         = trim($row['B'] ?? '');
            $email        = trim($row['C'] ?? '');
            $mobile       = trim($row['D'] ?? '');
            $company      = trim($row['E'] ?? '');
            $compType     = $this->normalizeCompanyType(trim($row['F'] ?? ''));
            $sellerType   = trim($row['G'] ?? '');
            $brandName    = trim($row['H'] ?? '');
            $categoryName = trim($row['I'] ?? '');
            $deptName     = trim($row['J'] ?? '');
            $parentSellerId = trim($row['K'] ?? '');
            $gst          = trim($row['L'] ?? '');
            $authRep      = trim($row['M'] ?? '');
            $altMobile    = trim($row['N'] ?? '');
            $altEmail     = trim($row['O'] ?? '');
            $stateName    = trim($row['P'] ?? '');
            $distName     = trim($row['Q'] ?? '');
            $pincode      = trim($row['R'] ?? '');
            $address      = trim($row['S'] ?? '');
            $aadhaar      = trim($row['T'] ?? '');
            $pan          = trim($row['U'] ?? '');
            $accName      = trim($row['V'] ?? '');
            $accNo        = trim($row['W'] ?? '');
            $ifsc         = trim($row['X'] ?? '');
            $bankName     = trim($row['Y'] ?? '');
            $branch       = trim($row['Z'] ?? '');
            $walletBal    = trim($row['AA'] ?? '0.00');
            $kycStatus    = strtoupper(trim($row['AB'] ?? 'APPROVED'));
            $allowOwn     = trim($row['AC'] ?? 'Yes');
            $isDdcSub     = trim($row['AD'] ?? '0');
            $ddcSubStatus = strtolower(trim($row['AE'] ?? 'none'));
            $ddcAmaComm   = trim($row['AF'] ?? '0.00');
            $profileImg   = trim($row['AG'] ?? '');
            $companyLogo  = trim($row['AH'] ?? '');
            $companyBanner= trim($row['AI'] ?? '');
            $gstCert      = trim($row['AJ'] ?? '');
            $panCard      = trim($row['AK'] ?? '');
            $aadhaarCard  = trim($row['AL'] ?? '');
            $udyamCert    = trim($row['AM'] ?? '');
            $incorpCert   = trim($row['AN'] ?? '');
            $chequeDoc    = trim($row['AO'] ?? '');
            $fssaiCert    = trim($row['AP'] ?? '');
            $tradeLic     = trim($row['AQ'] ?? '');
            $status       = trim($row['AR'] ?? 'Active');
            $password     = trim($row['AS'] ?? '');

            $errors = [];

            // 1. Seller ID validation
            if (empty($sellerId)) {
                $errors[] = 'Seller ID is required.';
            } else {
                $sellerIdUpper = strtoupper($sellerId);
                if (in_array($sellerIdUpper, $processedSellerIds)) {
                    $errors[] = "Duplicate Seller ID '{$sellerId}' in uploaded file.";
                } else {
                    $processedSellerIds[] = $sellerIdUpper;
                }
                if (SellerProfile::where('seller_id', $sellerId)->exists()) {
                    $errors[] = "Seller ID '{$sellerId}' already exists in database.";
                }
            }

            // 2. Name validation
            if (empty($name)) {
                $errors[] = 'Name is required.';
            }

            // 3. Email (optional — no validation required)
            if (empty($email)) {
                $email = strtolower($sellerId ?: ($mobile ?: 'seller_' . uniqid())) . '@noemail.local';
            }

            // 4. Mobile validation
            if (empty($mobile)) {
                $errors[] = 'Mobile number is required.';
            } else {
                $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
                if (strlen($cleanMobile) < 10 || strlen($cleanMobile) > 12) {
                    $errors[] = "Invalid mobile number '{$mobile}'.";
                }
            }

            // 5. Company Name
            if (empty($company)) {
                $errors[] = 'Company Name is required.';
            }

            // 6. State & District ID Lookups
            $stateObj = null;
            $districtObj = null;
            if (empty($stateName)) {
                $errors[] = 'State is required.';
            } else {
                $stateObj = State::whereRaw('LOWER(name) = ?', [strtolower($stateName)])
                    ->orWhere('code', strtoupper($stateName))
                    ->orWhere('id', $stateName)
                    ->first();
                if (!$stateObj) {
                    $errors[] = "State '{$stateName}' not found in database.";
                }
            }

            if (empty($distName)) {
                $errors[] = 'District is required.';
            } else {
                $districtObj = District::whereRaw('LOWER(name) = ?', [strtolower($distName)])
                    ->orWhere('id', $distName)
                    ->first();
                if (!$districtObj) {
                    $errors[] = "District '{$distName}' not found in database.";
                }
            }

            // 7. Pincode (optional — no validation required)


            // 8. Category & Department Lookups (optional)
            $catObj = null;
            if (!empty($categoryName)) {
                $catObj = Category::whereRaw('LOWER(name) = ?', [strtolower($categoryName)])
                    ->orWhere('id', $categoryName)
                    ->first();
            }

            $deptObj = null;
            if (!empty($deptName)) {
                $deptObj = Department::whereRaw('LOWER(name) = ?', [strtolower($deptName)])
                    ->orWhere('id', $deptName)
                    ->first();
            }

            // 9. Parent Seller Lookup (optional)
            $parentUserObj = null;
            if (!empty($parentSellerId)) {
                $parentUserObj = User::where('role', 'seller')
                    ->where(function ($q) use ($parentSellerId) {
                        $q->where('id', $parentSellerId)
                          ->orWhereHas('sellerProfile', function ($sq) use ($parentSellerId) {
                              $sq->where('seller_id', $parentSellerId)
                                ->orWhereRaw('LOWER(seller_id) = ?', [strtolower($parentSellerId)]);
                          });
                    })
                    ->first();
                if (!$parentUserObj) {
                    $errors[] = "Parent Seller ID '{$parentSellerId}' not found in database.";
                }
            }

            // Enums Check
            if (!empty($kycStatus) && !in_array($kycStatus, ['APPROVED', 'PENDING', 'UNDER_REVIEW', 'REJECTED'])) {
                $errors[] = "Invalid KYC Status '{$kycStatus}'. Allowed: APPROVED, PENDING, UNDER_REVIEW, REJECTED.";
            }

            if (!empty($ddcSubStatus) && !in_array($ddcSubStatus, ['none', 'pending', 'approved', 'rejected'])) {
                $errors[] = "Invalid DDC Sub Status '{$ddcSubStatus}'. Allowed: none, pending, approved, rejected.";
            }

            $isValid = empty($errors);
            if ($isValid) {
                $validCount++;
            } else {
                $errorCount++;
            }

            $preview[] = [
                'row_number'    => $rowNum,
                'seller_id'     => $sellerId,
                'name'          => $name,
                'email'         => $email,
                'mobile'        => $mobile,
                'company_name'  => $company,
                'company_type'  => $compType,
                'seller_type'   => $sellerType,
                'brand_name'    => $brandName,
                'category_id'   => $catObj?->id,
                'category_name' => $categoryName,
                'department_id' => $deptObj?->id,
                'dept_name'     => $deptName,
                'parent_id'     => $parentUserObj?->id,
                'parent_seller_id' => $parentSellerId,
                'gst_number'    => $gst,
                'authorized_rep_name' => $authRep,
                'alternate_mobile'    => $altMobile,
                'alternate_email'     => $altEmail,
                'state'         => $stateObj?->name ?? $stateName,
                'state_id'      => $stateObj?->id,
                'district'      => $districtObj?->name ?? $distName,
                'district_id'   => $districtObj?->id,
                'pincode'       => $pincode,
                'address'       => $address,
                'aadhaar_no'    => $aadhaar,
                'pan_no'        => $pan,
                'account_holder_name' => $accName,
                'bank_account_number' => $accNo,
                'ifsc_code'      => $ifsc,
                'bank_name'      => $bankName,
                'branch_name'    => $branch,
                'wallet_balance' => is_numeric($walletBal) ? $walletBal : 0.00,
                'kyc_status'     => (function($s) { $map = ['UNDER_REVIEW'=>'PENDING','under_review'=>'PENDING']; $s = $map[$s] ?? $s; return in_array($s, ['APPROVED','PENDING','REJECTED']) ? $s : 'PENDING'; })($kycStatus),
                'allow_own_products' => in_array(strtolower($allowOwn), ['yes', '1', 'true']) ? 'Yes' : 'No',
                'is_ddc_subscribed'  => in_array(strtolower($isDdcSub), ['1', 'true', 'yes']) ? 1 : 0,
                'ddc_subscription_status' => in_array(strtolower($ddcSubStatus), ['approved', 'pending', 'rejected', 'none']) ? strtolower($ddcSubStatus) : 'none',
                'ddc_ama_commission_percentage' => is_numeric($ddcAmaComm) ? $ddcAmaComm : 0.00,
                'profile_image'  => $profileImg,
                'company_logo'   => $companyLogo,
                'company_banner' => $companyBanner,
                'gst_certificate' => $gstCert,
                'pan_card'       => $panCard,
                'aadhaar_card'   => $aadhaarCard,
                'udyam_certificate' => $udyamCert,
                'incorporation_certificate' => $incorpCert,
                'cancelled_cheque' => $chequeDoc,
                'fssai_certificate' => $fssaiCert,
                'trade_license'  => $tradeLic,
                'status'         => (in_array(strtolower($status), ['0', 'inactive', 'false'])) ? '0' : '1',
                'password'       => $password,
                'is_valid'       => $isValid,
                'errors'         => $errors,
            ];
        }

        return response()->json([
            'success' => true,
            'total'   => count($preview),
            'valid'   => $validCount,
            'errors'  => $errorCount,
            'records' => $preview,
        ]);
    }

    public function sellerProcess(Request $request)
    {
        @set_time_limit(600);
        ini_set('memory_limit', '512M');

        $records = $request->input('records', []);
        if (empty($records)) {
            return response()->json(['success' => false, 'message' => 'No records provided for import.'], 422);
        }

        $imported = 0;
        $skipped = 0;
        $failed = 0;
        $details = [];

        $passwordHashCache = [];

        DB::beginTransaction();
        try {
            foreach ($records as $record) {
                if (empty($record['is_valid'])) {
                    $skipped++;
                    $details[] = [
                        'row' => $record['row_number'] ?? '—',
                        'identifier' => $record['seller_id'] ?? '—',
                        'name' => $record['name'] ?? '—',
                        'status' => 'Skipped',
                        'message' => implode(' | ', $record['errors'] ?? ['Invalid record']),
                    ];
                    continue;
                }

                if (SellerProfile::where('seller_id', $record['seller_id'])->exists()) {
                    $skipped++;
                    $details[] = [
                        'row' => $record['row_number'] ?? '—',
                        'identifier' => $record['seller_id'] ?? '—',
                        'name' => $record['name'] ?? '—',
                        'status' => 'Skipped',
                        'message' => 'Record or Email already exists in database.',
                    ];
                    continue;
                }

                $pwd = !empty($record['password']) ? $record['password'] : 'Password123';
                if (!isset($passwordHashCache[$pwd])) {
                    $passwordHashCache[$pwd] = Hash::make($pwd);
                }

                $emailVal = !empty($record['email']) ? $record['email'] : strtolower(($record['seller_id'] ?? '') ?: (($record['mobile'] ?? '') ?: 'seller_' . uniqid())) . '@noemail.local';
                $user = User::create([
                    'name'     => $record['name'],
                    'email'    => $emailVal,
                    'mobile'   => $record['mobile'],
                    'role'     => 'seller',
                    'status'   => $record['status'] ?? '1',
                    'password' => $passwordHashCache[$pwd],
                ]);

                // Resolve parent_id from parent_seller_id (imvcode) if parent_id is missing
                $parentId = ($record['parent_id'] ?? null) ?: null;
                if (!$parentId && !empty($record['parent_seller_id'])) {
                    $pCode = trim($record['parent_seller_id']);
                    $pUser = User::where('role', 'seller')
                        ->where(function ($q) use ($pCode) {
                            $q->where('id', $pCode)
                              ->orWhereHas('sellerProfile', function ($sq) use ($pCode) {
                                  $sq->where('seller_id', $pCode)
                                    ->orWhereRaw('LOWER(seller_id) = ?', [strtolower($pCode)]);
                              });
                        })
                        ->first();
                    if ($pUser) {
                        $parentId = $pUser->id;
                    }
                }

                // Resolve state_id if missing
                $stateId = ($record['state_id'] ?? null) ?: null;
                if (!$stateId && !empty($record['state'])) {
                    $st = State::whereRaw('LOWER(name) = ?', [strtolower($record['state'])])
                        ->orWhere('code', strtoupper($record['state']))
                        ->orWhere('id', $record['state'])
                        ->first();
                    if ($st) $stateId = $st->id;
                }

                // Resolve district_id if missing (without state requirement)
                $districtId = ($record['district_id'] ?? null) ?: null;
                if (!$districtId && !empty($record['district'])) {
                    $dst = District::whereRaw('LOWER(name) = ?', [strtolower($record['district'])])
                        ->orWhere('id', $record['district'])
                        ->first();
                    if ($dst) $districtId = $dst->id;
                }

                $profile = SellerProfile::firstOrCreate(['user_id' => $user->id]);
                $profile->update([
                    'seller_id'             => $record['seller_id'],
                    'company_name'          => $record['company_name'],
                    'company_type'          => ($record['company_type'] ?? null) ?: null,
                    'seller_type'           => ($record['seller_type'] ?? null) ?: null,
                    'brand_name'            => ($record['brand_name'] ?? null) ?: null,
                    'category_id'           => ($record['category_id'] ?? null) ?: null,
                    'department_id'         => ($record['department_id'] ?? null) ?: null,
                    'parent_id'             => $parentId,
                    'gst_number'            => ($record['gst_number'] ?? null) ?: null,
                    'authorized_rep_name'   => ($record['authorized_rep_name'] ?? null) ?: null,
                    'alternate_mobile'      => ($record['alternate_mobile'] ?? null) ?: null,
                    'alternate_email'       => ($record['alternate_email'] ?? null) ?: null,
                    'state'                 => $record['state'],
                    'state_id'              => $stateId,
                    'district'              => $record['district'],
                    'district_id'           => $districtId,
                    'pincode'               => $record['pincode'],
                    'address'               => ($record['address'] ?? null) ?: null,
                    'aadhaar_no'            => ($record['aadhaar_no'] ?? null) ?: null,
                    'pan_no'                => ($record['pan_no'] ?? null) ?: null,
                    'account_holder_name'   => ($record['account_holder_name'] ?? null) ?: null,
                    'bank_account_number'   => ($record['bank_account_number'] ?? null) ?: null,
                    'ifsc_code'             => ($record['ifsc_code'] ?? null) ?: null,
                    'bank_name'             => ($record['bank_name'] ?? null) ?: null,
                    'branch_name'           => ($record['branch_name'] ?? null) ?: null,
                    'wallet_balance'        => $record['wallet_balance'] ?? 0.00,
                    'kyc_status'            => (function($s) { $map=['UNDER_REVIEW'=>'PENDING','under_review'=>'PENDING']; $s=$map[$s??'']??($s??''); return in_array($s,['APPROVED','PENDING','REJECTED'])?$s:'PENDING'; })($record['kyc_status'] ?? 'PENDING'),
                    'allow_own_products'    => $record['allow_own_products'] ?? 'Yes',
                    'is_ddc_subscribed'     => $record['is_ddc_subscribed'] ?? 0,
                    'ddc_subscription_status' => $record['ddc_subscription_status'] ?? 'none',
                    'ddc_ama_commission_percentage' => $record['ddc_ama_commission_percentage'] ?? 0.00,
                    'profile_image'         => ($record['profile_image'] ?? null) ?: null,
                    'company_logo'          => ($record['company_logo'] ?? null) ?: null,
                    'company_banner'        => ($record['company_banner'] ?? null) ?: null,
                    'gst_certificate'       => ($record['gst_certificate'] ?? null) ?: null,
                    'pan_card'              => ($record['pan_card'] ?? null) ?: null,
                    'udyam_certificate'     => ($record['udyam_certificate'] ?? null) ?: null,
                    'incorporation_certificate' => ($record['incorporation_certificate'] ?? null) ?: null,
                    'cancelled_cheque'      => ($record['cancelled_cheque'] ?? null) ?: null,
                ]);

                $imported++;
                $details[] = [
                    'row' => $record['row_number'] ?? '—',
                    'identifier' => "{$record['seller_id']}",
                    'name' => $record['name'],
                    'status' => 'Imported',
                    'message' => 'Successfully imported seller account.',
                ];
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'imported' => $imported,
                'skipped'  => $skipped,
                'failed'   => $failed,
                'details'  => $details,
                'message'  => "Import completed: {$imported} Sellers created, {$skipped} skipped.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed due to a system error: ' . $e->getMessage(),
            ], 500);
        }
    }


    // =========================================================================
    // ADC IMPORT
    // =========================================================================

    public function adcIndex()
    {
        return view('admin.import.adc.index');
    }

    public function adcSample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ADC Import Template');

        $headers = [
            'A1' => 'ADC Code*',
            'B1' => 'Profile Code',
            'C1' => 'Name*',
            'D1' => 'Email*',
            'E1' => 'Mobile*',
            'F1' => 'Company Name',
            'G1' => 'Trade Name',
            'H1' => 'Designation',
            'I1' => 'Type',
            'J1' => 'Advisor Code',
            'K1' => 'Abhhyam Code',
            'L1' => 'Department Name',
            'M1' => 'State*',
            'N1' => 'District*',
            'O1' => 'Pincode*',
            'P1' => 'Address',
            'Q1' => 'Aadhaar No',
            'R1' => 'PAN No',
            'S1' => 'GST Number',
            'T1' => 'Account Holder Name',
            'U1' => 'Bank Account Number',
            'V1' => 'IFSC Code',
            'W1' => 'Bank Name',
            'X1' => 'Branch Name',
            'Y1' => 'Wallet Balance',
            'Z1' => 'Per User Daily Charge',
            'AA1' => 'KYC Status',
            'AB1' => 'KYC Remarks',
            'AC1' => 'Profile Image Path/URL',
            'AD1' => 'Master Depot Image Path/URL',
            'AE1' => 'Contract Document Path/URL',
            'AF1' => 'GST Certificate Path/URL',
            'AG1' => 'Aadhaar Card Path/URL',
            'AH1' => 'PAN Card Path/URL',
            'AI1' => 'FSSAI Certificate Path/URL',
            'AJ1' => 'Cancelled Cheque Path/URL',
            'AK1' => 'Trade License Path/URL',
            'AL1' => 'ADC Agreement Path/URL',
            'AM1' => 'Franchise Deposit Receipt Path/URL',
            'AN1' => 'QR Code Path/URL',
            'AO1' => 'Status',
            'AP1' => 'Password',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerRange = 'A1:AP1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4F46E5');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sampleRow = [
            'A2' => 'ADC001',
            'B2' => 'ASC001',
            'C2' => 'Amit Kumar',
            'D2' => 'amit.adc@example.com',
            'E2' => '9812345670',
            'F2' => 'Area Development Center Kolkata',
            'G2' => 'ADC Kolkata Enterprise',
            'H2' => 'Area Coordinator',
            'I2' => 'ASC',
            'J2' => 'ADV101',
            'K2' => 'ABH202',
            'L2' => 'Operations',
            'M2' => 'West Bengal',
            'N2' => 'Kolkata',
            'O2' => '700001',
            'P2' => '45 Park Street, 2nd Floor',
            'Q2' => '987654321098',
            'R2' => 'ADCDE5678G',
            'S2' => '19AAAAA1111A1Z1',
            'T2' => 'Amit Kumar',
            'U2' => '98765432101234',
            'V2' => 'HDFC0000123',
            'W2' => 'HDFC Bank',
            'X2' => 'Park Street Branch',
            'Y2' => '0.00',
            'Z2' => '10.00',
            'AA2' => 'APPROVED',
            'AB2' => 'Verified by Admin',
            'AC2' => '',
            'AD2' => '',
            'AE2' => '',
            'AF2' => '',
            'AG2' => '',
            'AH2' => '',
            'AI2' => '',
            'AJ2' => '',
            'AK2' => '',
            'AL2' => '',
            'AM2' => '',
            'AN2' => '',
            'AO2' => 'Active',
            'AP2' => 'Password123',
        ];

        foreach ($sampleRow as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Add Excel Data Validation Dropdowns
        $this->addDropdownValidation($sheet, 'I', ['ASC', 'MVS', 'SCS']);
        $this->addDropdownValidation($sheet, 'AA', ['APPROVED', 'PENDING', 'REJECTED']);
        $this->addDropdownValidation($sheet, 'AO', ['Active', 'Inactive']);

        foreach (array_merge(range('A', 'Z'), ['AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP']) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'ADC_Import_Template_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function adcPreview(Request $request)
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(3600);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls,txt|max:153600',
        ]);

        $rows = $this->readExcelRows($request->file('file'));
        if (empty($rows)) {
            return response()->json(['success' => false, 'message' => 'The uploaded file is empty or missing content.'], 422);
        }

        // Bulk preload lookups for high performance (eliminate N+1 database queries)
        $existingAdcCodes = AdcProfile::whereNotNull('adc_code')
            ->pluck('adc_code')
            ->map(fn($c) => strtoupper(trim($c)))
            ->flip()
            ->toArray();

        $stateMap = [];
        foreach (State::all() as $s) {
            $stateMap[strtolower(trim($s->name))] = $s;
            if ($s->code) {
                $stateMap[strtoupper(trim($s->code))] = $s;
            }
            $stateMap[(string)$s->id] = $s;
        }

        $districtMap = [];
        foreach (District::all() as $d) {
            $districtMap[strtolower(trim($d->name))] = $d;
            $districtMap[(string)$d->id] = $d;
        }

        $deptMap = [];
        foreach (Department::all() as $dept) {
            $deptMap[strtolower(trim($dept->name))] = $dept;
            $deptMap[(string)$dept->id] = $dept;
        }

        $preview = [];
        $validCount = 0;
        $errorCount = 0;

        $processedEmails = [];
        $processedMobiles = [];
        $processedAdcCodes = [];

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $adcCode   = trim($row['A'] ?? '');
            $code      = trim($row['B'] ?? '');
            $name      = trim($row['C'] ?? '');
            $email     = trim($row['D'] ?? '');
            $mobile    = trim($row['E'] ?? '');
            $company   = trim($row['F'] ?? '');
            $tradeName = trim($row['G'] ?? '');
            $designation = trim($row['H'] ?? '');
            $type      = trim($row['I'] ?? 'ASC');
            $advisorCode = trim($row['J'] ?? '');
            $abhhyamCode = trim($row['K'] ?? '');
            $assignedDdcCode = ''; // Assigned DDC Code — column removed
            $deptName  = trim($row['L'] ?? '');
            $stateName = trim($row['M'] ?? '');
            $distName  = trim($row['N'] ?? '');
            $pincode   = trim($row['O'] ?? '');
            $address   = trim($row['P'] ?? '');
            $aadhaar   = trim($row['Q'] ?? '');
            $pan       = trim($row['R'] ?? '');
            $gst       = trim($row['S'] ?? '');
            $accName   = trim($row['T'] ?? '');
            $accNo     = trim($row['U'] ?? '');
            $ifsc      = trim($row['V'] ?? '');
            $bankName  = trim($row['W'] ?? '');
            $branch    = trim($row['X'] ?? '');
            $walletBal = trim($row['Y'] ?? '0.00');
            $dailyChg  = trim($row['Z'] ?? '10.00');
            $kycStatus = strtoupper(trim($row['AA'] ?? 'APPROVED'));
            $kycRemarks = trim($row['AB'] ?? '');
            $profileImg = trim($row['AC'] ?? '');
            $depotImg   = trim($row['AD'] ?? '');
            $contractDoc = trim($row['AE'] ?? '');
            $gstCert   = trim($row['AF'] ?? '');
            $aadhaarCard = trim($row['AG'] ?? '');
            $panCard   = trim($row['AH'] ?? '');
            $fssaiCert = trim($row['AI'] ?? '');
            $chequeDoc = trim($row['AJ'] ?? '');
            $tradeLic  = trim($row['AK'] ?? '');
            $adcAgreement = trim($row['AL'] ?? '');
            $franchiseReceipt = trim($row['AM'] ?? '');
            $qrCode    = trim($row['AN'] ?? '');
            $status    = trim($row['AO'] ?? 'Active');
            $password  = trim($row['AP'] ?? '');

            $errors = [];

            // 1. ADC Code validation
            if (empty($adcCode)) {
                $errors[] = 'ADC Code is required.';
            } else {
                $codeUpper = strtoupper($adcCode);
                if (in_array($codeUpper, $processedAdcCodes)) {
                    $errors[] = "Duplicate ADC Code '{$adcCode}' in uploaded file.";
                } else {
                    $processedAdcCodes[] = $codeUpper;
                }
                if (isset($existingAdcCodes[$codeUpper])) {
                    $errors[] = "ADC Code '{$adcCode}' already exists in database.";
                }
            }

            // 2. Name
            if (empty($name)) {
                $errors[] = 'Name is required.';
            }

            // 3. Email (optional — no validation required)
            if (empty($email)) {
                $email = strtolower($adcCode ?: ($mobile ?: 'adc_' . uniqid())) . '@noemail.local';
            }

            // 4. Mobile (optional — accept any value or empty without validation error)
            // Mobile is stored as received without blocking preview or process.

            // 5. State & District ID lookups (in-memory)
            $stateObj = null;
            $districtObj = null;
            if (empty($stateName)) {
                $errors[] = 'State is required.';
            } else {
                $stateObj = $stateMap[strtolower($stateName)] ?? $stateMap[strtoupper($stateName)] ?? $stateMap[(string)$stateName] ?? null;
                if (!$stateObj) {
                    $errors[] = "State '{$stateName}' not found in database.";
                }
            }

            // District (optional — if empty, 'NA', or not found in DB, import anyway with district_id = null)
            $cleanDist = strtolower(trim($distName));
            if (!empty($distName) && !in_array($cleanDist, ['na', 'n/a', 'n.a.', '-', 'null', 'none'])) {
                $districtObj = $districtMap[$cleanDist] ?? $districtMap[(string)$distName] ?? null;
            }

            // 6. Pincode (optional — no validation required)

            // 7. Assigned DDC — skipped (not imported)

            // 8. Department lookup (optional, in-memory)
            $deptObj = null;
            if (!empty($deptName)) {
                $deptObj = $deptMap[strtolower($deptName)] ?? null;
            }

            // Enums Check
            if (!empty($type) && !in_array(strtoupper($type), ['ASC', 'MVS', 'SCS'])) {
                $errors[] = "Invalid Type '{$type}'. Allowed: ASC, MVS, SCS.";
            }

            if (!empty($kycStatus) && !in_array($kycStatus, ['APPROVED', 'PENDING', 'UNDER_REVIEW', 'REJECTED'])) {
                $errors[] = "Invalid KYC Status '{$kycStatus}'. Allowed: APPROVED, PENDING, UNDER_REVIEW, REJECTED.";
            }

            $isValid = empty($errors);
            if ($isValid) {
                $validCount++;
            } else {
                $errorCount++;
            }

            $preview[] = [
                'row_number' => $rowNum,
                'adc_code'   => $adcCode,
                'code'       => $code,
                'name'       => $name,
                'email'      => $email,
                'mobile'     => $mobile,
                'company_name' => $company,
                'trade_name' => $tradeName,
                'designation' => $designation,
                'type'       => in_array(strtoupper($type), ['ASC', 'MVS', 'SCS']) ? strtoupper($type) : 'ASC',
                'advisor_code' => $advisorCode,
                'abhhyam_code' => $abhhyamCode,
                // assigned_ddc_code intentionally excluded from import
                'department_id' => $deptObj?->id,
                'dept_name'  => $deptName,
                'state'      => $stateObj?->name ?? $stateName,
                'state_id'   => $stateObj?->id,
                'district'   => $districtObj?->name ?? $distName,
                'district_id' => $districtObj?->id,
                'pincode'    => $pincode,
                'address'    => $address,
                'aadhaar_no' => $aadhaar,
                'pan_no'     => $pan,
                'gst_number' => $gst,
                'account_holder_name' => $accName,
                'bank_account_number' => $accNo,
                'ifsc_code'   => $ifsc,
                'bank_name'   => $bankName,
                'branch_name' => $branch,
                'wallet_balance' => is_numeric($walletBal) ? $walletBal : 0.00,
                'per_user_daily_charge' => is_numeric($dailyChg) ? $dailyChg : 10.00,
                'kyc_status'  => (function($s) { $map = ['UNDER_REVIEW'=>'PENDING','under_review'=>'PENDING']; $s = $map[$s] ?? $s; return in_array($s, ['APPROVED','PENDING','REJECTED']) ? $s : 'PENDING'; })($kycStatus),
                'kyc_remarks' => $kycRemarks,
                'profile_image' => $profileImg,
                'master_depot_image' => $depotImg,
                'contract_document' => $contractDoc,
                'gst_certificate' => $gstCert,
                'aadhaar_card' => $aadhaarCard,
                'pan_card'     => $panCard,
                'fssai_certificate' => $fssaiCert,
                'cancelled_cheque' => $chequeDoc,
                'trade_license' => $tradeLic,
                'adc_agreement' => $adcAgreement,
                'franchise_deposit_receipt' => $franchiseReceipt,
                'qr_code'      => $qrCode,
                'status'      => (in_array(strtolower($status), ['0', 'inactive', 'false'])) ? '0' : '1',
                'password'    => $password,
                'is_valid'    => $isValid,
                'errors'      => $errors,
            ];
        }

        return response()->json([
            'success' => true,
            'total'   => count($preview),
            'valid'   => $validCount,
            'errors'  => $errorCount,
            'records' => $preview,
        ]);
    }

    public function adcProcess(Request $request)
    {
        @set_time_limit(600);
        ini_set('memory_limit', '512M');

        $records = $request->input('records', []);
        if (empty($records)) {
            return response()->json(['success' => false, 'message' => 'No records provided for import.'], 422);
        }

        $existingAdcCodes = AdcProfile::whereNotNull('adc_code')
            ->pluck('adc_code')
            ->map(fn($c) => strtoupper(trim($c)))
            ->flip()
            ->toArray();

        $imported = 0;
        $skipped = 0;
        $failed = 0;
        $details = [];
        $passwordHashCache = [];

        DB::beginTransaction();
        try {
            foreach ($records as $record) {
                if (empty($record['is_valid'])) {
                    $skipped++;
                    $details[] = [
                        'row' => $record['row_number'] ?? '—',
                        'identifier' => $record['adc_code'] ?? '—',
                        'name' => $record['name'] ?? '—',
                        'status' => 'Skipped',
                        'message' => implode(' | ', $record['errors'] ?? ['Invalid record']),
                    ];
                    continue;
                }

                $codeUpper = strtoupper(trim($record['adc_code'] ?? ''));
                if (isset($existingAdcCodes[$codeUpper])) {
                    $skipped++;
                    $details[] = [
                        'row' => $record['row_number'] ?? '—',
                        'identifier' => $record['adc_code'] ?? '—',
                        'name' => $record['name'] ?? '—',
                        'status' => 'Skipped',
                        'message' => 'Record already exists in database.',
                    ];
                    continue;
                }

                $pwd = !empty($record['password']) ? $record['password'] : 'Password123';
                if (!isset($passwordHashCache[$pwd])) {
                    $passwordHashCache[$pwd] = Hash::make($pwd);
                }

                $now = now();
                $emailVal = !empty($record['email']) ? $record['email'] : strtolower(($record['adc_code'] ?? '') ?: (($record['mobile'] ?? '') ?: 'adc_' . uniqid())) . '@noemail.local';
                $userId = DB::table('users')->insertGetId([
                    'name'       => $record['name'],
                    'email'      => $emailVal,
                    'mobile'     => ($record['mobile'] ?? null) ?: null,
                    'role'       => 'adc',
                    'status'     => $record['status'] ?? '1',
                    'password'   => $passwordHashCache[$pwd],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Resolve state_id if missing
                $stateId = ($record['state_id'] ?? null) ?: null;
                if (!$stateId && !empty($record['state'])) {
                    $st = State::whereRaw('LOWER(name) = ?', [strtolower($record['state'])])
                        ->orWhere('code', strtoupper($record['state']))
                        ->orWhere('id', $record['state'])
                        ->first();
                    if ($st) $stateId = $st->id;
                }

                // Resolve district_id if missing (without state requirement)
                $districtId = ($record['district_id'] ?? null) ?: null;
                if (!$districtId && !empty($record['district'])) {
                    $cleanDistName = strtolower(trim($record['district']));
                    if (!in_array($cleanDistName, ['na', 'n/a', 'n.a.', '-', 'null', 'none'])) {
                        $dst = District::whereRaw('LOWER(name) = ?', [$cleanDistName])
                            ->orWhere('id', $record['district'])
                            ->first();
                        if ($dst) $districtId = $dst->id;
                    }
                }

                DB::table('adc_profiles')->insert([
                    'user_id'               => $userId,
                    'adc_code'              => $record['adc_code'],
                    'code'                  => $record['code'] ?? null,
                    'type'                  => in_array(strtoupper($record['type'] ?? ''), ['ASC', 'MVS', 'SCS']) ? strtoupper($record['type']) : 'ASC',
                    'company_name'          => ($record['company_name'] ?? null) ?: null,
                    'trade_name'            => ($record['trade_name'] ?? null) ?: null,
                    'designation'           => ($record['designation'] ?? null) ?: null,
                    'advisor_code'          => ($record['advisor_code'] ?? null) ?: null,
                    'abhhyam_code'          => ($record['abhhyam_code'] ?? null) ?: null,
                    'assigned_ddc_id'       => null, // not imported via bulk import
                    'department_id'         => ($record['department_id'] ?? null) ?: null,
                    'gst_number'            => ($record['adc_gst_number'] ?? $record['gst_number'] ?? null) ?: null,
                    'state'                 => $record['state'],
                    'state_id'              => $stateId,
                    'district'              => $record['district'],
                    'district_id'           => $districtId,
                    'pincode'               => $record['pincode'],
                    'address'               => ($record['address'] ?? null) ?: null,
                    'aadhaar_no'            => ($record['aadhaar_no'] ?? null) ?: null,
                    'pan_no'                => ($record['pan_no'] ?? null) ?: null,
                    'account_holder_name'   => ($record['account_holder_name'] ?? null) ?: null,
                    'bank_account_number'   => ($record['bank_account_number'] ?? null) ?: null,
                    'ifsc_code'             => ($record['ifsc_code'] ?? null) ?: null,
                    'bank_name'             => ($record['bank_name'] ?? null) ?: null,
                    'branch_name'           => ($record['branch_name'] ?? null) ?: null,
                    'wallet_balance'        => $record['wallet_balance'] ?? 0.00,
                    'per_user_daily_charge' => $record['per_user_daily_charge'] ?? 10.00,
                    'kyc_status'            => (function($s) { $map=['UNDER_REVIEW'=>'PENDING','under_review'=>'PENDING']; $s=$map[$s??'']??($s??''); return in_array($s,['APPROVED','PENDING','REJECTED'])?$s:'APPROVED'; })($record['kyc_status'] ?? 'APPROVED'),
                    'kyc_remarks'           => ($record['kyc_remarks'] ?? null) ?: null,
                    'profile_image'         => ($record['profile_image'] ?? null) ?: null,
                    'master_depot_image'    => ($record['master_depot_image'] ?? null) ?: null,
                    'contract_document'     => ($record['contract_document'] ?? null) ?: null,
                    'gst_certificate'       => ($record['gst_certificate'] ?? null) ?: null,
                    'aadhaar_card'          => ($record['aadhaar_card'] ?? null) ?: null,
                    'pan_card'              => ($record['pan_card'] ?? null) ?: null,
                    'fssai_certificate'     => ($record['fssai_certificate'] ?? null) ?: null,
                    'cancelled_cheque'      => ($record['cancelled_cheque'] ?? null) ?: null,
                    'trade_license'         => ($record['trade_license'] ?? null) ?: null,
                    'adc_agreement'         => ($record['adc_agreement'] ?? null) ?: null,
                    'franchise_deposit_receipt' => ($record['franchise_deposit_receipt'] ?? null) ?: null,
                    'qr_code'               => ($record['qr_code'] ?? null) ?: null,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ]);

                $existingAdcCodes[$codeUpper] = true;
                $imported++;
                $details[] = [
                    'row' => $record['row_number'] ?? '—',
                    'identifier' => "{$record['adc_code']}",
                    'name' => $record['name'],
                    'status' => 'Imported',
                    'message' => 'Successfully imported ADC account.',
                ];
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'imported' => $imported,
                'skipped'  => $skipped,
                'failed'   => $failed,
                'details'  => $details,
                'message'  => "Import completed: {$imported} ADCs created, {$skipped} skipped.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed due to a system error: ' . $e->getMessage(),
            ], 500);
        }
    }


    // =========================================================================
    // DDC IMPORT
    // =========================================================================

    public function ddcIndex()
    {
        return view('admin.import.ddc.index');
    }

    public function ddcSample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DDC Import Template');

        $headers = [
            'A1' => 'DDC Code*',
            'B1' => 'Name*',
            'C1' => 'Email*',
            'D1' => 'Mobile*',
            'E1' => 'Type',
            'F1' => 'Company Name*',
            'G1' => 'Trade Name',
            'H1' => 'DDC GST Number',
            'I1' => 'Official Phone',
            'J1' => 'Official Email',
            'K1' => 'Department Name',
            'L1' => 'State*',
            'M1' => 'District*',
            'N1' => 'Pincode*',
            'O1' => 'Address',
            'P1' => 'Assigned Pincodes',
            'Q1' => 'Aadhaar No',
            'R1' => 'PAN No',
            'S1' => 'Account Holder Name',
            'T1' => 'Bank Account Number',
            'U1' => 'IFSC Code',
            'V1' => 'Bank Name',
            'W1' => 'Branch Name',
            'X1' => 'Wallet Balance',
            'Y1' => 'Per User Daily Charge',
            'Z1' => 'KYC Status',
            'AA1' => 'KYC Remarks',
            'AB1' => 'Profile Image Path/URL',
            'AC1' => 'Master Depot Image Path/URL',
            'AD1' => 'Contract Document Path/URL',
            'AE1' => 'GST Certificate Path/URL',
            'AF1' => 'Aadhaar Card Path/URL',
            'AG1' => 'FSSAI Certificate Path/URL',
            'AH1' => 'Cancelled Cheque Path/URL',
            'AI1' => 'Trade License Path/URL',
            'AJ1' => 'DDC Agreement Path/URL',
            'AK1' => 'Franchise Deposit Receipt Path/URL',
            'AL1' => 'QR Code Path/URL',
            'AM1' => 'Status',
            'AN1' => 'Password',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerRange = 'A1:AN1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4F46E5');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sampleRow = [
            'A2' => 'DDB001',
            'B2' => 'Sunil Verma',
            'C2' => 'sunil.ddc@example.com',
            'D2' => '9765432109',
            'E2' => 'Physical',
            'F2' => 'District Development Center Kolkata',
            'G2' => 'DDC Enterprise',
            'H2' => '19AAAAA2222A1Z2',
            'I2' => '9765432109',
            'J2' => 'sunil.ddc@example.com',
            'K2' => 'Logistics',
            'L2' => 'West Bengal',
            'M2' => 'Kolkata',
            'N2' => '700001',
            'O2' => '10 Strand Road, 3rd Floor',
            'P2' => '700001, 700002, 700003',
            'Q2' => '876543210987',
            'R2' => 'DDCDE4321H',
            'S2' => 'Sunil Verma',
            'T2' => '98761234509876',
            'U2' => 'ICIC0000456',
            'V2' => 'ICICI Bank',
            'W2' => 'Dalhousie Square Branch',
            'X2' => '0.00',
            'Y2' => '100.00',
            'Z2' => 'APPROVED',
            'AA2' => 'Verified by Admin',
            'AB2' => '',
            'AC2' => '',
            'AD2' => '',
            'AE2' => '',
            'AF2' => '',
            'AG2' => '',
            'AH2' => '',
            'AI2' => '',
            'AJ2' => '',
            'AK2' => '',
            'AL2' => '',
            'AM2' => 'Active',
            'AN2' => 'Password123',
        ];

        foreach ($sampleRow as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Add Excel Data Validation Dropdowns for Enum / Fixed Option columns
        $this->addDropdownValidation($sheet, 'E', ['Physical', 'Virtual']);
        $this->addDropdownValidation($sheet, 'Z', ['APPROVED', 'PENDING', 'UNDER_REVIEW', 'REJECTED']);
        $this->addDropdownValidation($sheet, 'AM', ['Active', 'Inactive']);

        foreach (array_merge(range('A', 'Z'), ['AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN']) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'DDC_Import_Template_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function ddcPreview(Request $request)
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(3600);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls,txt|max:153600',
        ]);

        $rows = $this->readExcelRows($request->file('file'));
        if (empty($rows)) {
            return response()->json(['success' => false, 'message' => 'The uploaded file is empty or missing content.'], 422);
        }

        $adplCompName = SystemSetting::getVal('adpl_company_name', '');
        $adplGstNo    = SystemSetting::getVal('adpl_gst_number', '');
        $adplStateId  = SystemSetting::getVal('adpl_state_id', '');
        $adplStateName = '';
        if ($adplStateId) {
            $stObj = State::find($adplStateId);
            if ($stObj) $adplStateName = $stObj->name;
        }
        $adplDistId   = SystemSetting::getVal('adpl_district_id', '');
        $adplDistName = '';
        if ($adplDistId) {
            $dstObj = District::find($adplDistId);
            if ($dstObj) $adplDistName = $dstObj->name;
        }
        $adplPincode  = SystemSetting::getVal('adpl_pincode', '');
        $adplAddress  = SystemSetting::getVal('adpl_address', '');

        $preview = [];
        $validCount = 0;
        $errorCount = 0;

        $processedEmails = [];
        $processedMobiles = [];
        $processedDdcCodes = [];

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $ddcCode   = trim($row['A'] ?? '');
            $name      = trim($row['B'] ?? '');
            $email     = trim($row['C'] ?? '');
            $mobile    = trim($row['D'] ?? '');
            $type      = trim($row['E'] ?? 'Physical');
            $company   = trim($row['F'] ?? '');
            $tradeName = trim($row['G'] ?? '');
            $ddcGst    = trim($row['H'] ?? '');
            $offPhone  = trim($row['I'] ?? '');
            $offEmail  = trim($row['J'] ?? '');
            $deptName  = trim($row['K'] ?? '');
            $stateName = trim($row['L'] ?? '');
            $distName  = trim($row['M'] ?? '');
            $pincode   = trim($row['N'] ?? '');
            $address   = trim($row['O'] ?? '');
            $assignedPincodesStr = trim($row['P'] ?? '');
            $aadhaar   = trim($row['Q'] ?? '');
            $pan       = trim($row['R'] ?? '');
            $accName   = trim($row['S'] ?? '');
            $accNo     = trim($row['T'] ?? '');
            $ifsc      = trim($row['U'] ?? '');
            $bankName  = trim($row['V'] ?? '');
            $branch    = trim($row['W'] ?? '');
            $walletBal = trim($row['X'] ?? '0.00');
            $dailyChg  = trim($row['Y'] ?? '100.00');
            $kycStatus = strtoupper(trim($row['Z'] ?? 'APPROVED'));
            $kycRemarks= trim($row['AA'] ?? '');
            $profileImg= trim($row['AB'] ?? '');
            $depotImg  = trim($row['AC'] ?? '');
            $contractDoc= trim($row['AD'] ?? '');
            $gstCert   = trim($row['AE'] ?? '');
            $aadhaarCard= trim($row['AF'] ?? '');
            $fssaiCert = trim($row['AG'] ?? '');
            $chequeDoc = trim($row['AH'] ?? '');
            $tradeLic  = trim($row['AI'] ?? '');
            $ddcAgreement= trim($row['AJ'] ?? '');
            $franchiseReceipt= trim($row['AK'] ?? '');
            $qrCode    = trim($row['AL'] ?? '');
            $status    = trim($row['AM'] ?? 'Active');
            $password  = trim($row['AN'] ?? '');

            $errors = [];

            // 1. DDC Code validation
            if (empty($ddcCode)) {
                $errors[] = 'DDC Code is required.';
            } else {
                $codeUpper = strtoupper($ddcCode);
                if (in_array($codeUpper, $processedDdcCodes)) {
                    $errors[] = "Duplicate DDC Code '{$ddcCode}' in uploaded file.";
                } else {
                    $processedDdcCodes[] = $codeUpper;
                }
                if (DdcProfile::where('ddc_code', $ddcCode)->exists()) {
                    $errors[] = "DDC Code '{$ddcCode}' already exists in database.";
                }
            }

            // 2. Name
            if (empty($name)) {
                $errors[] = 'Name is required.';
            }

            // 3. Email (optional — no validation required)
            if (empty($email)) {
                $email = strtolower($ddcCode ?: ($mobile ?: 'ddc_' . uniqid())) . '@noemail.local';
            }

            // 4. Mobile
            if (empty($mobile)) {
                $errors[] = 'Mobile number is required.';
            } else {
                $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
                if (strlen($cleanMobile) < 10 || strlen($cleanMobile) > 12) {
                    $errors[] = "Invalid mobile number '{$mobile}'.";
                }
            }

            // 5. Company Name
            if (empty($company)) {
                $errors[] = 'Company Name is required.';
            }

            // 6. State & District ID lookups
            $stateObj = null;
            $districtObj = null;
            if (empty($stateName)) {
                $errors[] = 'State is required.';
            } else {
                $stateObj = State::whereRaw('LOWER(name) = ?', [strtolower($stateName)])
                    ->orWhere('code', strtoupper($stateName))
                    ->orWhere('id', $stateName)
                    ->first();
                if (!$stateObj) {
                    $errors[] = "State '{$stateName}' not found in database.";
                }
            }

            if (empty($distName)) {
                $errors[] = 'District is required.';
            } else {
                $districtObj = District::whereRaw('LOWER(name) = ?', [strtolower($distName)])
                    ->orWhere('id', $distName)
                    ->first();
                if (!$districtObj) {
                    $errors[] = "District '{$distName}' not found in database.";
                }
            }

            // 7. Pincode (optional — no validation required)

            // 8. Department lookup (optional)
            $deptObj = null;
            if (!empty($deptName)) {
                $deptObj = Department::whereRaw('LOWER(name) = ?', [strtolower($deptName)])
                    ->orWhere('id', $deptName)
                    ->first();
            }

            // 9. Assigned Pincodes parse & lookup (optional)
            $validPincodeIds = [];
            if (!empty($assignedPincodesStr)) {
                $rawPincodes = array_filter(array_map('trim', explode(',', $assignedPincodesStr)));
                foreach ($rawPincodes as $pin) {
                    $pinRecord = Pincode::where('pincode', $pin)->first();
                    if ($pinRecord) {
                        $validPincodeIds[] = $pinRecord->id;
                    }
                }
            }

            // Enums Check
            if (!empty($type) && !in_array(ucfirst(strtolower($type)), ['Physical', 'Virtual'])) {
                $errors[] = "Invalid Type '{$type}'. Allowed: Physical, Virtual.";
            }

            if (!empty($kycStatus) && !in_array($kycStatus, ['APPROVED', 'PENDING', 'UNDER_REVIEW', 'REJECTED'])) {
                $errors[] = "Invalid KYC Status '{$kycStatus}'. Allowed: APPROVED, PENDING, UNDER_REVIEW, REJECTED.";
            }

            $isValid = empty($errors);
            if ($isValid) {
                $validCount++;
            } else {
                $errorCount++;
            }

            $preview[] = [
                'row_number' => $rowNum,
                'ddc_code'   => $ddcCode,
                'name'       => $name,
                'email'      => $email,
                'mobile'     => $mobile,
                'type'       => ucfirst(strtolower($type)),
                'company_name' => $company,
                'trade_name' => $tradeName,
                'ddc_gst_number' => $ddcGst,
                'official_phone' => $offPhone,
                'official_email' => $offEmail,
                'department_id'  => $deptObj?->id,
                'dept_name'  => $deptName,
                'state'      => $stateObj?->name ?? $stateName,
                'state_id'   => $stateObj?->id,
                'district'   => $districtObj?->name ?? $distName,
                'district_id' => $districtObj?->id,
                'pincode'    => $pincode,
                'address'    => $address,
                'adpl_company_name' => $adplCompName,
                'adpl_gst_number'   => $adplGstNo,
                'adpl_state'        => $adplStateName,
                'adpl_state_id'     => $adplStateId,
                'adpl_district'     => $adplDistName,
                'adpl_district_id'  => $adplDistId,
                'adpl_pincode'      => $adplPincode,
                'adpl_address'      => $adplAddress,
                'assigned_pincodes_str' => $assignedPincodesStr,
                'assigned_pincode_ids'  => $validPincodeIds,
                'aadhaar_no' => $aadhaar,
                'pan_no'     => $pan,
                'account_holder_name' => $accName,
                'bank_account_number' => $accNo,
                'ifsc_code'   => $ifsc,
                'bank_name'   => $bankName,
                'branch_name' => $branch,
                'wallet_balance' => is_numeric($walletBal) ? $walletBal : 0.00,
                'per_user_daily_charge' => is_numeric($dailyChg) ? $dailyChg : 100.00,
                'kyc_status'  => (function($s) { $map = ['UNDER_REVIEW'=>'PENDING','under_review'=>'PENDING']; $s = $map[$s] ?? $s; return in_array($s, ['APPROVED','PENDING','REJECTED']) ? $s : 'PENDING'; })($kycStatus),
                'kyc_remarks' => $kycRemarks,
                'profile_image' => $profileImg,
                'master_depot_image' => $depotImg,
                'contract_document' => $contractDoc,
                'gst_certificate' => $gstCert,
                'aadhaar_card' => $aadhaarCard,
                'fssai_certificate' => $fssaiCert,
                'cancelled_cheque' => $chequeDoc,
                'trade_license' => $tradeLic,
                'ddc_agreement' => $ddcAgreement,
                'franchise_deposit_receipt' => $franchiseReceipt,
                'qr_code'      => $qrCode,
                'status'      => (in_array(strtolower($status), ['0', 'inactive', 'false'])) ? '0' : '1',
                'password'    => $password,
                'is_valid'    => $isValid,
                'errors'      => $errors,
            ];
        }

        return response()->json([
            'success' => true,
            'total'   => count($preview),
            'valid'   => $validCount,
            'errors'  => $errorCount,
            'records' => $preview,
        ]);
    }

    public function ddcProcess(Request $request)
    {
        @set_time_limit(600);
        ini_set('memory_limit', '512M');

        $records = $request->input('records', []);
        if (empty($records)) {
            return response()->json(['success' => false, 'message' => 'No records provided for import.'], 422);
        }

        $adplCompName = SystemSetting::getVal('adpl_company_name', '');
        $adplGstNo    = SystemSetting::getVal('adpl_gst_number', '');
        $adplStateId  = SystemSetting::getVal('adpl_state_id', '');
        $adplStateName = '';
        if ($adplStateId) {
            $stObj = State::find($adplStateId);
            if ($stObj) $adplStateName = $stObj->name;
        }
        $adplDistId   = SystemSetting::getVal('adpl_district_id', '');
        $adplDistName = '';
        if ($adplDistId) {
            $dstObj = District::find($adplDistId);
            if ($dstObj) $adplDistName = $dstObj->name;
        }
        $adplPincode  = SystemSetting::getVal('adpl_pincode', '');
        $adplAddress  = SystemSetting::getVal('adpl_address', '');

        $imported = 0;
        $skipped = 0;
        $failed = 0;
        $details = [];
        $passwordHashCache = [];

        DB::beginTransaction();
        try {
            foreach ($records as $record) {
                if (empty($record['is_valid'])) {
                    $skipped++;
                    $details[] = [
                        'row' => $record['row_number'] ?? '—',
                        'identifier' => $record['ddc_code'] ?? '—',
                        'name' => $record['name'] ?? '—',
                        'status' => 'Skipped',
                        'message' => implode(' | ', $record['errors'] ?? ['Invalid record']),
                    ];
                    continue;
                }

                if (DdcProfile::where('ddc_code', $record['ddc_code'])->exists()) {
                    $skipped++;
                    $details[] = [
                        'row' => $record['row_number'] ?? '—',
                        'identifier' => $record['ddc_code'] ?? '—',
                        'name' => $record['name'] ?? '—',
                        'status' => 'Skipped',
                        'message' => 'Record or Email already exists in database.',
                    ];
                    continue;
                }

                $pwd = !empty($record['password']) ? $record['password'] : 'Password123';
                if (!isset($passwordHashCache[$pwd])) {
                    $passwordHashCache[$pwd] = Hash::make($pwd);
                }

                $emailVal = !empty($record['email']) ? $record['email'] : strtolower(($record['ddc_code'] ?? '') ?: (($record['mobile'] ?? '') ?: 'ddc_' . uniqid())) . '@noemail.local';
                $user = User::create([
                    'name'     => $record['name'],
                    'email'    => $emailVal,
                    'mobile'   => $record['mobile'],
                    'role'     => 'ddc',
                    'status'   => $record['status'] ?? '1',
                    'password' => $passwordHashCache[$pwd],
                ]);

                // Resolve state_id if missing
                $stateId = ($record['state_id'] ?? null) ?: null;
                if (!$stateId && !empty($record['state'])) {
                    $st = State::whereRaw('LOWER(name) = ?', [strtolower($record['state'])])
                        ->orWhere('code', strtoupper($record['state']))
                        ->orWhere('id', $record['state'])
                        ->first();
                    if ($st) $stateId = $st->id;
                }

                // Resolve district_id if missing (without state requirement)
                $districtId = ($record['district_id'] ?? null) ?: null;
                if (!$districtId && !empty($record['district'])) {
                    $dst = District::whereRaw('LOWER(name) = ?', [strtolower($record['district'])])
                        ->orWhere('id', $record['district'])
                        ->first();
                    if ($dst) $districtId = $dst->id;
                }

                $profile = DdcProfile::firstOrCreate(['user_id' => $user->id]);
                $profile->update([
                    'ddc_code'              => $record['ddc_code'],
                    'ddc_owner_name'        => $record['name'],
                    'type'                  => ($record['type'] ?? '') ?: 'Physical',
                    'company_name'          => $record['company_name'],
                    'trade_name'            => ($record['trade_name'] ?? null) ?: null,
                    'ddc_gst_number'        => ($record['ddc_gst_number'] ?? null) ?: null,
                    'phone'                 => $record['mobile'],
                    'email'                 => $record['email'],
                    'official_phone'        => ($record['official_phone'] ?? null) ?: $record['mobile'],
                    'official_email'        => ($record['official_email'] ?? null) ?: $record['email'],
                    'department_id'         => ($record['department_id'] ?? null) ?: null,
                    'state'                 => $record['state'],
                    'state_id'              => $stateId,
                    'district'              => $record['district'],
                    'district_id'           => $districtId,
                    'pincode'               => $record['pincode'],
                    'ddc_pincode'           => $record['pincode'],
                    'address'               => ($record['address'] ?? null) ?: null,
                    'ddc_address'           => ($record['address'] ?? null) ?: null,
                    'adpl_company_name'     => ($record['adpl_company_name'] ?? null) ?: $adplCompName,
                    'adpl_gst_number'       => ($record['adpl_gst_number'] ?? null) ?: $adplGstNo,
                    'adpl_state'            => ($record['adpl_state'] ?? null) ?: $adplStateName,
                    'adpl_state_id'         => ($record['adpl_state_id'] ?? null) ?: ($adplStateId ?: null),
                    'adpl_district'         => ($record['adpl_district'] ?? null) ?: $adplDistName,
                    'adpl_district_id'      => ($record['adpl_district_id'] ?? null) ?: ($adplDistId ?: null),
                    'adpl_pincode'          => ($record['adpl_pincode'] ?? null) ?: $adplPincode,
                    'adpl_address'          => ($record['adpl_address'] ?? null) ?: $adplAddress,
                    'aadhaar_no'            => ($record['aadhaar_no'] ?? null) ?: null,
                    'pan_no'                => ($record['pan_no'] ?? null) ?: null,
                    'account_holder_name'   => ($record['account_holder_name'] ?? null) ?: null,
                    'bank_account_number'   => ($record['bank_account_number'] ?? null) ?: null,
                    'ifsc_code'             => ($record['ifsc_code'] ?? null) ?: null,
                    'bank_name'             => ($record['bank_name'] ?? null) ?: null,
                    'branch_name'           => ($record['branch_name'] ?? null) ?: null,
                    'wallet_balance'        => $record['wallet_balance'] ?? 0.00,
                    'per_user_daily_charge' => $record['per_user_daily_charge'] ?? 100.00,
                    'kyc_status'            => (function($s) { $map=['UNDER_REVIEW'=>'PENDING','under_review'=>'PENDING']; $s=$map[$s??'']??($s??''); return in_array($s,['APPROVED','PENDING','REJECTED'])?$s:'APPROVED'; })($record['kyc_status'] ?? 'APPROVED'),
                    'kyc_remarks'           => ($record['kyc_remarks'] ?? null) ?: null,
                    'profile_image'         => ($record['profile_image'] ?? null) ?: null,
                    'master_depot_image'    => ($record['master_depot_image'] ?? null) ?: null,
                    'contract_document'     => ($record['contract_document'] ?? null) ?: null,
                    'gst_certificate'       => ($record['gst_certificate'] ?? null) ?: null,
                    'aadhaar_card'          => ($record['aadhaar_card'] ?? null) ?: null,
                    'fssai_certificate'     => ($record['fssai_certificate'] ?? null) ?: null,
                    'cancelled_cheque'      => ($record['cancelled_cheque'] ?? null) ?: null,
                    'trade_license'         => ($record['trade_license'] ?? null) ?: null,
                    'ddc_agreement'         => ($record['ddc_agreement'] ?? null) ?: null,
                    'franchise_deposit_receipt' => ($record['franchise_deposit_receipt'] ?? null) ?: null,
                    'qr_code'               => ($record['qr_code'] ?? null) ?: null,
                ]);

                // Map assigned pincodes if provided
                if (!empty($record['assigned_pincode_ids']) && is_array($record['assigned_pincode_ids'])) {
                    foreach ($record['assigned_pincode_ids'] as $pincodeId) {
                        DdcPincode::firstOrCreate([
                            'ddc_id'     => $user->id,
                            'pincode_id' => $pincodeId,
                        ]);
                    }
                }

                $imported++;
                $details[] = [
                    'row' => $record['row_number'] ?? '—',
                    'identifier' => "{$record['ddc_code']}",
                    'name' => $record['name'],
                    'status' => 'Imported',
                    'message' => 'Successfully imported DDC account.',
                ];
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'imported' => $imported,
                'skipped'  => $skipped,
                'failed'   => $failed,
                'details'  => $details,
                'message'  => "Import completed: {$imported} DDCs created, {$skipped} skipped.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed due to a system error: ' . $e->getMessage(),
            ], 500);
        }
    }


    // =========================================================================
    // PRODUCT IMPORT
    // =========================================================================

    public function productIndex()
    {
        return view('admin.import.product.index');
    }

    public function productSample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Product Import Template');

        $headers = [
            'A1'  => 'Seller ID*',
            'B1'  => 'Product Code*',
            'C1'  => 'Product Name*',
            'D1'  => 'Category*',
            'E1'  => 'Sub Category',
            'F1'  => 'Child Category',
            'G1'  => 'Brand*',
            'H1'  => 'HSN Code*',
            'I1'  => 'GST Rate (%)',
            'J1'  => 'MRP*',
            'K1'  => 'Seller Price (Incl GST)*',
            'L1'  => 'Admin Margin (%)',
            'M1'  => 'DDC Margin (%)',
            'N1'  => 'MBP (%)',
            'O1'  => 'Barcode',
            'P1'  => 'Unit of Measure',
            'Q1'  => 'Case Pack Quantity',
            'R1'  => 'Search Keywords',
            'S1'  => 'Description',
            'T1'  => 'Product Specification',
            'U1'  => 'Storage Instructions',
            'V1'  => 'Shelf Life',
            'W1'  => 'Country of Origin',
            'X1'  => 'Height',
            'Y1'  => 'Width',
            'Z1'  => 'Length',
            'AA1' => 'Weight',
            'AB1' => 'Dimension Unit',
            'AC1' => 'Weight Unit',
            'AD1' => 'Featured Image Path/URL',
            'AE1' => 'Status',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerRange = 'A1:AE1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('059669');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sampleRow = [
            'A2'  => 'IMV1009',
            'B2'  => 'PRD1001',
            'C2'  => 'Organic Herbal Tea 250g',
            'D2'  => 'Beverages',
            'E2'  => 'Tea',
            'F2'  => '',
            'G2'  => 'Himalayan Organic',
            'H2'  => '0902',
            'I2'  => '5',
            'J2'  => '350.00',
            'K2'  => '280.00',
            'L2'  => '8.00',
            'M2'  => '5.00',
            'N2'  => '2.00',
            'O2'  => '8901234567890',
            'P2'  => 'Piece',
            'Q2'  => '1',
            'R2'  => 'herbal tea, organic, green tea',
            'S2'  => 'Pure organic herbal tea sourced from Himalayan tea gardens.',
            'T2'  => '100% Organic, No added preservatives',
            'U2'  => 'Store in a cool dry place',
            'V2'  => '12 Months',
            'W2'  => 'India',
            'X2'  => '15',
            'Y2'  => '10',
            'Z2'  => '5',
            'AA2' => '0.25',
            'AB2' => 'cm',
            'AC2' => 'kg',
            'AD2' => '',
            'AE2' => 'Approved',
        ];

        foreach ($sampleRow as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Dropdown validations
        $this->addDropdownValidation($sheet, 'P', ['Piece', 'Box', 'Kg', 'Liter', 'Pack', 'Set', 'Gram', 'Meter']);
        $this->addDropdownValidation($sheet, 'AE', ['Approved', 'Draft', 'Pending Approval', 'Inactive']);

        foreach (array_merge(range('A', 'Z'), ['AA', 'AB', 'AC', 'AD', 'AE']) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Product_Import_Template_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function productPreview(Request $request)
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(3600);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls,txt|max:153600',
        ]);

        $rawRows = $this->readRawExcelRows($request->file('file'));
        if (empty($rawRows)) {
            return response()->json(['success' => false, 'message' => 'The uploaded file is empty or missing content.'], 422);
        }

        $firstRow = $rawRows[0] ?? [];
        $isSellerImportHeader = false;
        foreach ($firstRow as $cellVal) {
            $t = strtolower(trim((string)$cellVal));
            if (str_contains($t, 'company type') || str_contains($t, 'seller type') || str_contains($t, 'aadhaar') || str_contains($t, 'bank account') || str_contains($t, 'ifsc code')) {
                $isSellerImportHeader = true;
                break;
            }
        }

        $headerMap = $this->normalizeProductHeaders($firstRow);
        $hasProductHeader = isset($headerMap['product_name']) || isset($headerMap['mrp']) || isset($headerMap['category']) || isset($headerMap['brand']) || isset($headerMap['hsn_code']);

        if ($isSellerImportHeader && !$hasProductHeader) {
            return response()->json([
                'success' => false,
                'message' => 'The uploaded file appears to be a Seller Import spreadsheet (containing seller/company fields), not a Product Bulk Import file. Please select the correct Product Bulk Import template.'
            ], 422);
        }

        $rows = $rawRows;
        if ($hasProductHeader || $isSellerImportHeader) {
            array_shift($rows); // Remove header row
        }

        $preview = [];
        $validCount = 0;
        $errorCount = 0;

        $processedCodes = [];
        $processedBarcodes = [];

        // Lookup caches to prevent database query slowdowns/timeouts on large files
        $sellerCache = [];
        $catCache = [];
        $subCatCache = [];
        $childCatCache = [];
        $brandCache = [];
        $hsnCache = [];

        // Pre-extract all product codes in the file for super-fast bulk database validation
        $rawFileCodes = [];
        foreach ($rows as $r) {
            $c = $hasProductHeader && isset($headerMap['product_code'])
                ? trim((string)($r[$headerMap['product_code']] ?? ''))
                : trim((string)($r['B'] ?? ''));
            if (!empty($c)) {
                $u = strtoupper($c);
                $rawFileCodes[] = $u;
                $rawFileCodes[] = 'PROD-' . $u;
            }
        }
        $rawFileCodes = array_unique(array_filter($rawFileCodes));
        $existingDbCodesMap = [];
        if (!empty($rawFileCodes)) {
            foreach (array_chunk($rawFileCodes, 1000) as $chunkCodes) {
                $found = Product::whereIn('product_code', $chunkCodes)
                    ->pluck('product_code')
                    ->map(fn($c) => strtoupper($c))
                    ->toArray();
                foreach ($found as $fc) {
                    $existingDbCodesMap[$fc] = true;
                }
            }
        }

        foreach ($rows as $idx => $row) {
            $rowNum = ($hasProductHeader || $isSellerImportHeader) ? ($idx + 2) : ($idx + 1);

            $getVal = function ($key, $defaultColLetter, $fallbackDefault = '') use ($row, $headerMap, $hasProductHeader) {
                if ($hasProductHeader && isset($headerMap[$key])) {
                    $val = $row[$headerMap[$key]] ?? $fallbackDefault;
                    return trim((string)$val);
                }
                $val = $row[$defaultColLetter] ?? $fallbackDefault;
                return trim((string)$val);
            };

            $sellerCode        = $getVal('seller_code', 'A');
            $productCode       = $getVal('product_code', 'B');
            $productName       = $getVal('product_name', 'C');
            $catInput          = $getVal('category', 'D');
            $subCatInput       = $getVal('sub_category', 'E');
            $childCatInput     = $getVal('child_category', 'F');
            $brandInput        = $getVal('brand', 'G');
            $hsnInput          = $getVal('hsn_code', 'H');
            $userGst           = $getVal('gst_rate', 'I');
            $mrpInput          = $getVal('mrp', 'J', '0');
            $sellerPriceInput = $getVal('seller_price', 'K', '0');
            $adminMarginInput = $getVal('admin_margin', 'L');
            $ddcMarginInput   = $getVal('ddc_margin', 'M');
            $mbpInput         = $getVal('mbp', 'N');
            $rawBarcode        = $getVal('barcode', 'O');
            $uom               = $getVal('unit_of_measure', 'P', 'Piece');
            $casePackQty       = $getVal('case_pack_quantity', 'Q', '1');
            $keywordsStr       = $getVal('search_keywords', 'R');
            $description       = $getVal('description', 'S');
            $specification     = $getVal('product_specification', 'T');
            $storageInst       = $getVal('storage_instructions', 'U');
            $shelfLife         = $getVal('shelf_life', 'V');
            $country           = $getVal('country_of_origin', 'W', 'India');
            $height            = $getVal('height', 'X');
            $width             = $getVal('width', 'Y');
            $length            = $getVal('length', 'Z');
            $weight            = $getVal('weight', 'AA');
            $dimUnit           = $getVal('dimension_unit', 'AB', 'cm');
            $weightUnit        = $getVal('weight_unit', 'AC', 'kg');
            $featuredImg       = $getVal('featured_image', 'AD');
            $status            = $this->normalizeProductStatus($getVal('status', 'AE', 'Approved'));

            // Format barcode if provided in scientific notation
            $barcode = $rawBarcode;
            if (!empty($barcode) && (stripos($barcode, 'e+') !== false || stripos($barcode, 'e-') !== false)) {
                $barcode = sprintf('%.0f', (float)$barcode);
            }

            $errors = [];

            // 1. Seller lookup (Cached)
            $sellerUser = null;
            if (empty($sellerCode)) {
                $errors[] = 'Seller ID is required.';
            } else {
                $cleanSellerCode = trim($sellerCode);
                if (array_key_exists($cleanSellerCode, $sellerCache)) {
                    $sellerUser = $sellerCache[$cleanSellerCode];
                } else {
                    if (is_numeric($cleanSellerCode)) {
                        $sellerUser = User::find($cleanSellerCode) ?? \App\Models\Vendor::find($cleanSellerCode);
                    }
                    if (!$sellerUser) {
                        $sellerProfile = SellerProfile::whereRaw('LOWER(seller_id) = ?', [strtolower($cleanSellerCode)])->first();
                        if ($sellerProfile) {
                            $sellerUser = $sellerProfile->user ?? User::find($sellerProfile->user_id) ?? \App\Models\Vendor::find($sellerProfile->user_id);
                        }
                    }
                    if (!$sellerUser) {
                        $sellerUser = User::where(function ($q) use ($cleanSellerCode) {
                            $q->whereRaw('LOWER(email) = ?', [strtolower($cleanSellerCode)])
                              ->orWhere('mobile', $cleanSellerCode);
                        })->first() ?? \App\Models\Vendor::where(function ($q) use ($cleanSellerCode) {
                            $q->whereRaw('LOWER(email) = ?', [strtolower($cleanSellerCode)])
                              ->orWhere('mobile', $cleanSellerCode);
                        })->first();
                    }
                    $sellerCache[$cleanSellerCode] = $sellerUser;
                }

                if (!$sellerUser) {
                    $errors[] = "Seller Code '{$sellerCode}' not found in database.";
                }
            }

            // 2. Product Code (optional, allowed to be duplicate)
            if (!empty($productCode)) {
                $processedCodes[] = strtoupper($productCode);
            }

            // 3. Product Name
            if (empty($productName)) {
                $errors[] = 'Product Name is required.';
            }

            // 4. Category lookup (Optional)
            $catObj = null;
            if (!empty($catInput)) {
                $catKey = strtolower(trim((string)$catInput));
                if (array_key_exists($catKey, $catCache)) {
                    $catObj = $catCache[$catKey];
                } else {
                    $catObj = Category::where('id', $catInput)
                        ->orWhereRaw('LOWER(name) = ?', [$catKey])
                        ->first();
                    $catCache[$catKey] = $catObj;
                }
            }

            // SubCategory & ChildCategory lookups (Optional)
            $subCatObj = null;
            if (!empty($subCatInput)) {
                $subKey = ($catObj ? $catObj->id : '0') . '_' . strtolower(trim((string)$subCatInput));
                if (array_key_exists($subKey, $subCatCache)) {
                    $subCatObj = $subCatCache[$subKey];
                } else {
                    $subCatQuery = SubCategory::query();
                    if ($catObj) {
                        $subCatQuery->where('category_id', $catObj->id);
                    }
                    $subCatObj = $subCatQuery->where(function ($q) use ($subCatInput) {
                        $q->where('id', $subCatInput)
                          ->orWhereRaw('LOWER(name) = ?', [strtolower($subCatInput)]);
                    })->first();
                    $subCatCache[$subKey] = $subCatObj;
                }
            }

            $childCatObj = null;
            if (!empty($childCatInput)) {
                $childKey = ($subCatObj ? $subCatObj->id : '0') . '_' . strtolower(trim((string)$childCatInput));
                if (array_key_exists($childKey, $childCatCache)) {
                    $childCatObj = $childCatCache[$childKey];
                } else {
                    $childCatQuery = ChildCategory::query();
                    if ($subCatObj) {
                        $childCatQuery->where('sub_category_id', $subCatObj->id);
                    }
                    $childCatObj = $childCatQuery->where(function ($q) use ($childCatInput) {
                        $q->where('id', $childCatInput)
                          ->orWhereRaw('LOWER(name) = ?', [strtolower($childCatInput)]);
                    })->first();
                    $childCatCache[$childKey] = $childCatObj;
                }
            }

            // 5. Brand lookup (Cached with Auto-Creation if missing)
            $brandObj = null;
            if (empty($brandInput)) {
                $errors[] = 'Brand is required.';
            } else {
                $brandKey = strtolower(trim((string)$brandInput));
                if (array_key_exists($brandKey, $brandCache)) {
                    $brandObj = $brandCache[$brandKey];
                } else {
                    $brandObj = Brand::where('id', $brandInput)
                        ->orWhereRaw('LOWER(name) = ?', [$brandKey])
                        ->first();
                    if (!$brandObj) {
                        $brandObj = Brand::create([
                            'name'   => trim((string)$brandInput),
                            'status' => '1',
                        ]);
                    }
                    $brandCache[$brandKey] = $brandObj;
                }
                if (!$brandObj) {
                    $errors[] = "Brand '{$brandInput}' could not be created.";
                }
            }

            // 6. HSN Code lookup & validation (Cached)
            $hsnObj = null;
            $gstRate = 0.00;
            $cleanHsn = trim((string)$hsnInput);
            if (empty($cleanHsn)) {
                $errors[] = 'HSN Code is required.';
            } else {
                if (array_key_exists($cleanHsn, $hsnCache)) {
                    $hsnObj = $hsnCache[$cleanHsn];
                } else {
                    $padded4 = str_pad($cleanHsn, 4, '0', STR_PAD_LEFT);
                    $padded8 = str_pad($cleanHsn, 8, '0', STR_PAD_LEFT);

                    $hsnObj = Hsn::where('hsn_code', $cleanHsn)
                        ->orWhere('hsn_code', $padded4)
                        ->orWhere('hsn_code', $padded8)
                        ->first();
                    $hsnCache[$cleanHsn] = $hsnObj;
                }

                if (!$hsnObj) {
                    $errors[] = "HSN Code '{$hsnInput}' not found in master database.";
                } else {
                    $gstRate = (float)$hsnObj->gst_percentage;
                }
            }

            if ($userGst !== '' && is_numeric($userGst)) {
                $gstRate = (float)$userGst;
            }

            // 7. MRP & Seller Price validation
            $parseNum = function($v) {
                if (is_null($v) || $v === '') return 0.0;
                $clean = preg_replace('/[^\d.]/', '', str_replace(',', '', (string)$v));
                return is_numeric($clean) ? (float)$clean : 0.0;
            };

            $mrp = $parseNum($mrpInput);
            $sellerPrice = $parseNum($sellerPriceInput);

            if ($mrp <= 0) {
                $errors[] = "MRP must be greater than 0.";
            }
            if ($sellerPrice <= 0) {
                $errors[] = "Seller Price must be greater than 0.";
            }
            if ($mrp > 0 && $sellerPrice > 0 && $mrp < $sellerPrice) {
                $errors[] = "MRP (₹{$mrp}) cannot be less than Seller Price (₹{$sellerPrice}).";
            }

            // Parse custom margin percentages from Excel if provided
            $adminMarginPercent = (is_numeric($adminMarginInput) && $adminMarginInput !== '') ? (float)$adminMarginInput : null;
            $ddcMarginPercent   = (is_numeric($ddcMarginInput) && $ddcMarginInput !== '') ? (float)$ddcMarginInput : null;
            $mbpPercent         = (is_numeric($mbpInput) && $mbpInput !== '') ? (float)$mbpInput : null;

            // 8. Barcode formatting (optional)
            if (empty($barcode)) {
                $barcode = null;
            }

            // Keywords array
            $keywordsArray = [];
            if (!empty($keywordsStr)) {
                $keywordsArray = array_values(array_filter(array_map('trim', explode(',', $keywordsStr))));
            }

            // Calculate pricing margins for preview based on picked Excel margins or defaults
            $pricing = null;
            if ($mrp > 0 && $sellerPrice > 0 && $mrp >= $sellerPrice) {
                $pricing = VendorProductController::calculatePricingData(
                    $sellerPrice,
                    $gstRate,
                    $mrp,
                    $adminMarginPercent,
                    $ddcMarginPercent,
                    $mbpPercent
                );
            }

            $isValid = empty($errors);
            if ($isValid) {
                $validCount++;
            } else {
                $errorCount++;
            }

            $preview[] = [
                'row_number'                   => $rowNum,
                'seller_code'                  => $sellerCode,
                'seller_user_id'               => $sellerUser?->id,
                'product_code'                 => $productCode,
                'product_name'                 => $productName,
                'category_name'                => $catObj?->name ?? $catInput,
                'category_id'                  => $catObj?->id,
                'sub_category_name'            => $subCatObj?->name ?? $subCatInput,
                'sub_category_id'              => $subCatObj?->id,
                'child_category_name'          => $childCatObj?->name ?? $childCatInput,
                'child_category_id'            => $childCatObj?->id,
                'brand_name'                   => $brandObj?->name ?? $brandInput,
                'brand_id'                     => $brandObj?->id,
                'hsn_code'                     => $hsnInput,
                'gst_rate'                     => $gstRate,
                'mrp'                          => $mrp,
                'seller_price_incl_gst'        => $sellerPrice,
                'custom_admin_margin_percentage' => $adminMarginPercent,
                'custom_ddc_margin_percentage'   => $ddcMarginPercent,
                'custom_mbp_percentage'          => $mbpPercent,
                'barcode'                      => $barcode,
                'unit_of_measure'              => $uom ?: 'Piece',
                'net_quantity'                 => null,
                'case_pack_quantity'           => is_numeric($casePackQty) ? (int)$casePackQty : 1,
                'product_keywords'             => $keywordsArray,
                'description'                  => $description ?: $productName,
                'product_specification'        => $specification,
                'storage_instructions'         => $storageInst,
                'shelf_life'                   => $shelfLife,
                'country_of_origin'            => $country ?: 'India',
                'height'                       => is_numeric($height) ? $height : null,
                'width'                        => is_numeric($width) ? $width : null,
                'length'                       => is_numeric($length) ? $length : null,
                'weight'                       => is_numeric($weight) ? $weight : null,
                'dimension_unit'               => $dimUnit ?: 'cm',
                'weight_unit'                  => $weightUnit ?: 'kg',
                'featured_image'               => $featuredImg,
                'status'                       => $status,
                'is_valid'                     => $isValid,
                'errors'                       => $errors,

                // Calculated Pricing & Margins
                'base_cost'                    => $pricing['base_cost'] ?? 0.00,
                'company_gst_amount'           => $pricing['company_gst_amount'] ?? 0.00,
                'admin_margin_percentage'      => $pricing['admin_margin_percentage'] ?? 0.00,
                'admin_margin_amount'          => $pricing['admin_margin_amount'] ?? 0.00,
                'ddc_purchase_price'           => $pricing['ddc_purchase_price'] ?? 0.00,
                'ddc_margin_percentage'        => $pricing['ddc_margin_percentage'] ?? 0.00,
                'ddc_margin_amount'            => $pricing['ddc_margin_amount'] ?? 0.00,
                'price_before_gst'             => $pricing['price_before_gst'] ?? 0.00,
                'seller_gst_amount'            => $pricing['seller_gst_amount'] ?? 0.00,
                'adc_purchase_price'           => $pricing['adc_purchase_price'] ?? 0.00,
                'mbp_percentage'               => $pricing['mbp_percentage'] ?? 0.00,
                'mbp_amount'                   => $pricing['mbp_amount'] ?? 0.00,
                'mbp_90_share'                 => $pricing['mbp_90_share'] ?? 0.00,
                'mbp_10_share'                 => $pricing['mbp_10_share'] ?? 0.00,
                'mbp_38_share'                 => $pricing['mbp_38_share'] ?? 0.00,
                'mbp_62_share'                 => $pricing['mbp_62_share'] ?? 0.00,
                'available_margin'             => $pricing['available_margin'] ?? 0.00,
                'adc_final_margin'             => $pricing['adc_final_margin'] ?? 0.00,
                'pricing'                      => $pricing,
            ];
        }

        return response()->json($this->cleanUtf8([
            'success' => true,
            'total'   => count($preview),
            'valid'   => $validCount,
            'errors'  => $errorCount,
            'records' => $preview,
        ]), 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function productProcess(Request $request)
    {
        @set_time_limit(600);

        $records = $request->input('records', []);
        if (empty($records)) {
            return response()->json(['success' => false, 'message' => 'No records provided for import.'], 422);
        }

        $imported = 0;
        $skipped = 0;
        $failed = 0;
        $details = [];

        // Pre-query existing product codes in bulk (checking both raw codes and PROD- prefixed codes)
        $incomingCodes = [];
        $incomingBarcodes = [];
        foreach ($records as $r) {
            if (!empty($r['product_code'])) {
                $rawC = strtoupper(trim((string)$r['product_code']));
                $incomingCodes[] = $rawC;
                if (!str_starts_with($rawC, 'PROD-')) {
                    $incomingCodes[] = 'PROD-' . $rawC;
                }
            }
            if (!empty($r['barcode'])) {
                $incomingBarcodes[] = trim((string)$r['barcode']);
            }
        }

        $existingCodeMap = [];
        if (!empty($incomingCodes)) {
            foreach (array_chunk(array_unique($incomingCodes), 1000) as $chunkCodes) {
                $foundDbCodes = Product::whereIn('product_code', $chunkCodes)
                    ->pluck('product_code')
                    ->map(fn($c) => strtoupper($c))
                    ->toArray();
                foreach ($foundDbCodes as $fc) {
                    $existingCodeMap[$fc] = true;
                }
            }
        }

        $existingBarcodeMap = [];
        if (!empty($incomingBarcodes)) {
            foreach (array_chunk(array_unique($incomingBarcodes), 1000) as $chunkBc) {
                $foundDbBc = Product::whereIn('barcode', $chunkBc)
                    ->pluck('barcode')
                    ->toArray();
                foreach ($foundDbBc as $fbc) {
                    $existingBarcodeMap[$fbc] = true;
                }
            }
        }

        foreach ($records as $record) {
            if (empty($record['is_valid'])) {
                $skipped++;
                $details[] = [
                    'row' => $record['row_number'] ?? '—',
                    'identifier' => $record['product_code'] ?? '—',
                    'name' => $record['product_name'] ?? '—',
                    'status' => 'Skipped',
                    'message' => implode(' | ', $record['errors'] ?? ['Invalid record']),
                ];
                continue;
            }

            $sellerUserId = $record['seller_user_id'] ?? null;
            if (empty($sellerUserId)) {
                $failed++;
                $details[] = [
                    'row' => $record['row_number'] ?? '—',
                    'identifier' => $record['product_code'] ?? '—',
                    'name' => $record['product_name'] ?? '—',
                    'status' => 'Failed',
                    'message' => 'Seller ID could not be found or resolved in database.',
                ];
                continue;
            }



            // Pick custom margin percentages if passed from Excel row preview
            $adminMarginPercent = (isset($record['custom_admin_margin_percentage']) && is_numeric($record['custom_admin_margin_percentage']))
                ? (float)$record['custom_admin_margin_percentage']
                : ((isset($record['admin_margin_percentage']) && is_numeric($record['admin_margin_percentage'])) ? (float)$record['admin_margin_percentage'] : null);

            $ddcMarginPercent = (isset($record['custom_ddc_margin_percentage']) && is_numeric($record['custom_ddc_margin_percentage']))
                ? (float)$record['custom_ddc_margin_percentage']
                : ((isset($record['ddc_margin_percentage']) && is_numeric($record['ddc_margin_percentage'])) ? (float)$record['ddc_margin_percentage'] : null);

            $mbpPercent = (isset($record['custom_mbp_percentage']) && is_numeric($record['custom_mbp_percentage']))
                ? (float)$record['custom_mbp_percentage']
                : ((isset($record['mbp_percentage']) && is_numeric($record['mbp_percentage'])) ? (float)$record['mbp_percentage'] : null);

            // Calculate pricing engine fields (Base Cost, Margins, GSTs, MBP Shares)
            $pricing = VendorProductController::calculatePricingData(
                $record['seller_price_incl_gst'] ?? 0,
                $record['gst_rate'] ?? 0,
                $record['mrp'] ?? 0,
                $adminMarginPercent,
                $ddcMarginPercent,
                $mbpPercent
            );

            $productData = [
                'seller_id'             => $sellerUserId,
                'product_code'          => !empty($record['product_code']) ? trim((string)$record['product_code']) : null,
                'product_name'          => $record['product_name'],
                'barcode'               => !empty($record['barcode']) ? trim((string)$record['barcode']) : null,
                'brand_id'              => $record['brand_id'] ?? null,
                'category_id'           => $record['category_id'] ?? null,
                'sub_category_id'       => $record['sub_category_id'] ?: null,
                'child_category_id'     => $record['child_category_id'] ?: null,
                'hsn_code'              => $record['hsn_code'] ?? null,
                'gst_rate'              => $record['gst_rate'] ?? 0,
                'mrp'                   => $record['mrp'] ?? 0,
                'seller_price_incl_gst' => $record['seller_price_incl_gst'] ?? 0,
                'unit_of_measure'       => $record['unit_of_measure'] ?: 'Piece',
                'net_quantity'          => null,
                'case_pack_quantity'    => $record['case_pack_quantity'] ?: 1,
                'product_keywords'      => $record['product_keywords'] ?: [],
                'description'           => $record['description'] ?: $record['product_name'],
                'product_specification' => $record['product_specification'] ?: null,
                'storage_instructions'  => $record['storage_instructions'] ?: null,
                'shelf_life'            => $record['shelf_life'] ?: null,
                'country_of_origin'     => $record['country_of_origin'] ?: 'India',
                'height'                => $record['height'] ?: null,
                'width'                 => $record['width'] ?: null,
                'length'                => $record['length'] ?: null,
                'weight'                => $record['weight'] ?: null,
                'dimension_unit'        => $record['dimension_unit'] ?: 'cm',
                'weight_unit'           => $record['weight_unit'] ?: 'kg',
                'featured_image'        => !empty($record['featured_image']) ? $record['featured_image'] : '',
                'status'                => $this->normalizeProductStatus($record['status'] ?? 'Approved'),
                'created_by'            => auth()->id() ?? $sellerUserId,
            ];

            $productData = array_merge($productData, $pricing);

            try {
                $product = DB::transaction(function () use ($productData) {
                    return Product::create($productData);
                });

                if (!empty($product->product_code)) {
                    $existingCodeMap[strtoupper($product->product_code)] = true;
                }
                if (!empty($product->barcode)) {
                    $existingBarcodeMap[$product->barcode] = true;
                }

                $imported++;
                $details[] = [
                    'row' => $record['row_number'] ?? '—',
                    'identifier' => $product->product_code ?? ($record['product_code'] ?? '—'),
                    'name' => $record['product_name'],
                    'status' => 'Imported',
                    'message' => 'Successfully imported product.',
                ];
            } catch (\Throwable $e) {
                $failed++;
                $details[] = [
                    'row' => $record['row_number'] ?? '—',
                    'identifier' => $record['product_code'] ?? '—',
                    'name' => $record['product_name'] ?? '—',
                    'status' => 'Failed',
                    'message' => 'Import failed: ' . $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success'  => true,
            'imported' => $imported,
            'skipped'  => $skipped,
            'failed'   => $failed,
            'details'  => $details,
            'message'  => "Import completed: {$imported} Products created, {$skipped} skipped, {$failed} failed.",
        ]);
    }

    // =========================================================================
    // HELPER FUNCTIONS
    // =========================================================================

    private function addDropdownValidation($sheet, string $column, array $options, int $maxRows = 100)
    {
        $formula = '"' . implode(',', $options) . '"';
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Invalid Option');
        $validation->setError('Please select a valid option from the dropdown list.');
        $validation->setPromptTitle('Select Option');
        $validation->setPrompt('Choose an option from the dropdown list.');
        $validation->setFormula1($formula);

        for ($row = 2; $row <= $maxRows; $row++) {
            $sheet->getCell("{$column}{$row}")->setDataValidation(clone $validation);
        }
    }

    private function readExcelRows($file): array
    {
        $rows = $this->readRawExcelRows($file);
        if (!empty($rows)) {
            $firstRowLower = array_map(fn($v) => strtolower(trim((string)$v)), $rows[0]);
            $isHeader = false;
            foreach ($firstRowLower as $val) {
                if (str_contains($val, 'name') || str_contains($val, 'code') || str_contains($val, 'email') || str_contains($val, 'mobile') || str_contains($val, 'id') || str_contains($val, 'address') || str_contains($val, 'price') || str_contains($val, 'title')) {
                    $isHeader = true;
                    break;
                }
            }
            if ($isHeader) {
                array_shift($rows);
            }
        }
        return $rows;
    }

    private function parseExcelDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim($value);

        // Handle Excel numeric serial dates (e.g. 45902, 45902.0)
        if (is_numeric($value)) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value);
                return $dt->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Try standard date formats
        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d', 'm/d/Y', 'Y-m-d H:i:s', 'd-m-Y H:i:s'];
        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $value);
            if ($dt && $dt->format($fmt) === $value) {
                return $dt->format('Y-m-d');
            }
        }

        // Fallback for English text dates like "02 Sep 2025" or "2025/09/02"
        $ts = strtotime($value);
        if ($ts !== false && $ts > 0) {
            return date('Y-m-d', $ts);
        }

        return null;
    }

    private function normalizeUom(?string $uom, ?string $fallback = 'Piece'): string
    {
        $val = trim((string)$uom);
        if ($val === '') {
            return $fallback ?: 'Piece';
        }

        $lower = strtolower($val);
        $map = [
            'pcs'      => 'Piece',
            'pc'       => 'Piece',
            'piece'    => 'Piece',
            'pieces'   => 'Piece',
            'box'      => 'Box',
            'boxes'    => 'Box',
            'pack'     => 'Pack',
            'packs'    => 'Pack',
            'pkt'      => 'Pack',
            'packet'   => 'Pack',
            'bottle'   => 'Bottle',
            'bottles'  => 'Bottle',
            'kg'       => 'Kg',
            'kgs'      => 'Kg',
            'kilogram' => 'Kg',
            'gm'       => 'Gram',
            'g'        => 'Gram',
            'gram'     => 'Gram',
            'grams'    => 'Gram',
            'ltr'      => 'Litre',
            'liter'    => 'Litre',
            'litre'    => 'Litre',
            'ml'       => 'ML',
            'pair'     => 'Pair',
            'set'      => 'Set',
            'meter'    => 'Meter',
            'dozen'    => 'Dozen',
        ];

        return $map[$lower] ?? $val;
    }

    private function cleanUtf8(mixed $data): mixed
    {
        if (is_string($data)) {
            if ($data === '') {
                return '';
            }
            if (!mb_check_encoding($data, 'UTF-8')) {
                $converted = @mb_convert_encoding($data, 'UTF-8', 'Windows-1252');
                if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
                    $data = $converted;
                } else {
                    $data = iconv('UTF-8', 'UTF-8//IGNORE', $data);
                }
            }
            return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string)$data);
        }

        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleanKey = is_string($key) ? $this->cleanUtf8($key) : $key;
                $cleaned[$cleanKey] = $this->cleanUtf8($value);
            }
            return $cleaned;
        }

        return $data;
    }

    private function readRawExcelRows($file): array
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(3600);

        try {
            $pathname = $file->getPathname();
            $origName = $file->getClientOriginalName();
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (in_array($ext, ['csv', 'txt', 'tsv'])) {
                $rows = [];
                if (($handle = fopen($pathname, 'r')) !== false) {
                    $bom = fread($handle, 3);
                    if ($bom !== "\xEF\xBB\xBF") {
                        rewind($handle);
                    }
                    $emptyConsecutive = 0;
                    while (($data = fgetcsv($handle, 0, ",")) !== false) {
                        $rowData = [];
                        $hasContent = false;
                        foreach ($data as $idx => $val) {
                            $valStr = $this->cleanUtf8(trim(str_replace(["\r", "\n", "\xC2\xA0", "\xA0"], ' ', (string)($val ?? ''))));
                            if ($valStr !== '') {
                                $hasContent = true;
                            }
                            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
                            $rowData[$colLetter] = $valStr;
                        }
                        if ($hasContent) {
                            $emptyConsecutive = 0;
                            $rows[] = $rowData;
                        } else {
                            $emptyConsecutive++;
                            if ($emptyConsecutive >= 25) {
                                break;
                            }
                        }
                        if (count($rows) >= 200000) {
                            break;
                        }
                    }
                    fclose($handle);
                    return $this->cleanUtf8($rows);
                }
            }

            $reader = IOFactory::createReaderForFile($pathname);
            $reader->setReadDataOnly(true);
            if (method_exists($reader, 'setReadEmptyCells')) {
                $reader->setReadEmptyCells(true);
            }

            $spreadsheet = $reader->load($pathname);
            $sheet = $spreadsheet->getActiveSheet();

            $rows = [];
            $emptyConsecutive = 0;

            foreach ($sheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $rowData = [];
                $hasContent = false;

                foreach ($cellIterator as $cell) {
                    $col = $cell->getColumn();
                    $val = $this->cleanUtf8(trim(str_replace(["\r", "\n", "\xC2\xA0", "\xA0"], ' ', (string)($cell->getValue() ?? ''))));
                    if ($val !== '') {
                        $hasContent = true;
                    }
                    $rowData[$col] = $val;
                }

                if ($hasContent) {
                    $emptyConsecutive = 0;
                    $rows[] = $rowData;
                } else {
                    $emptyConsecutive++;
                    if ($emptyConsecutive >= 25) {
                        break;
                    }
                }

                if (count($rows) >= 200000) {
                    break;
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $sheet);

            return $this->cleanUtf8($rows);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('readRawExcelRows error: ' . $e->getMessage());
            throw new \InvalidArgumentException('Unable to read uploaded file: ' . $e->getMessage());
        }
    }

    private function normalizeProductHeaders(array $firstRow): array
    {
        $aliasMap = [
            'seller_id' => 'seller_code',
            'seller_code' => 'seller_code',
            'seller' => 'seller_code',
            'vendor_code' => 'seller_code',
            'vendor_id' => 'seller_code',
            'vendor' => 'seller_code',

            'product_code' => 'product_code',
            'prod_code' => 'product_code',
            'code' => 'product_code',
            'sku' => 'product_code',

            'product_name' => 'product_name',
            'product_title' => 'product_name',
            'product' => 'product_name',
            'name' => 'product_name',
            'item_name' => 'product_name',

            'category' => 'category',
            'category_name' => 'category',
            'cat' => 'category',
            'category_id' => 'category',

            'sub_category' => 'sub_category',
            'sub_category_name' => 'sub_category',
            'subcategory' => 'sub_category',
            'sub_cat' => 'sub_category',

            'child_category' => 'child_category',
            'child_category_name' => 'child_category',
            'childcategory' => 'child_category',
            'child_cat' => 'child_category',

            'brand' => 'brand',
            'brand_name' => 'brand',
            'brand_id' => 'brand',

            'hsn_code' => 'hsn_code',
            'hsn' => 'hsn_code',
            'hsn_number' => 'hsn_code',

            'gst_rate' => 'gst_rate',
            'gst' => 'gst_rate',
            'gst_percentage' => 'gst_rate',
            'gst_rate_percentage' => 'gst_rate',

            'mrp' => 'mrp',
            'mrp_price' => 'mrp',
            'mrp_incl_gst' => 'mrp',
            'mrp_rs' => 'mrp',

            'seller_price' => 'seller_price',
            'seller_price_incl_gst' => 'seller_price',
            'selling_price' => 'seller_price',
            'vendor_price' => 'seller_price',
            'price' => 'seller_price',

            'admin_margin' => 'admin_margin',
            'admin_margin_percentage' => 'admin_margin',
            'ddc_margin' => 'ddc_margin',
            'ddc_margin_percentage' => 'ddc_margin',
            'mbp' => 'mbp',
            'mbp_percentage' => 'mbp',

            'barcode' => 'barcode',
            'ean' => 'barcode',
            'upc' => 'barcode',

            'unit_of_measure' => 'unit_of_measure',
            'uom' => 'unit_of_measure',
            'unit' => 'unit_of_measure',

            'case_pack_quantity' => 'case_pack_quantity',
            'case_pack_qty' => 'case_pack_quantity',
            'pack_quantity' => 'case_pack_quantity',
            'case_pack' => 'case_pack_quantity',

            'search_keywords' => 'search_keywords',
            'product_keywords' => 'search_keywords',
            'keywords' => 'search_keywords',
            'tags' => 'search_keywords',

            'description' => 'description',
            'prod_description' => 'description',

            'product_specification' => 'product_specification',
            'specification' => 'product_specification',
            'specs' => 'product_specification',

            'storage_instructions' => 'storage_instructions',
            'storage' => 'storage_instructions',

            'shelf_life' => 'shelf_life',

            'country_of_origin' => 'country_of_origin',
            'country' => 'country_of_origin',

            'height' => 'height',
            'width' => 'width',
            'length' => 'length',
            'weight' => 'weight',
            'dimension_unit' => 'dimension_unit',
            'weight_unit' => 'weight_unit',

            'featured_image' => 'featured_image',
            'featured_image_path_url' => 'featured_image',
            'image' => 'featured_image',

            'status' => 'status',
        ];

        $headerMap = [];
        foreach ($firstRow as $colLetter => $cellVal) {
            $rawStr = strtolower(trim((string)$cellVal));
            $cleanKey = preg_replace('/[^a-z0-9]+/', '_', $rawStr);
            $cleanKey = trim(preg_replace('/_+/', '_', $cleanKey), '_');
            if (isset($aliasMap[$cleanKey])) {
                $headerMap[$aliasMap[$cleanKey]] = $colLetter;
            }
        }
        return $headerMap;
    }

    private function normalizeCompanyType(?string $type): ?string
    {
        if (empty($type)) return null;
        $validEnums = ['Proprietorship Firm', 'Partnership Firm', 'Private Limited Company', 'Public Limited Company', 'LLP', 'Company', 'Other'];
        if (in_array($type, $validEnums)) {
            return $type;
        }
        $t = strtolower(trim($type));
        if (str_contains($t, 'private') || str_contains($t, 'pvt')) return 'Private Limited Company';
        if (str_contains($t, 'public')) return 'Public Limited Company';
        if (str_contains($t, 'proprietor') || str_contains($t, 'proprietary')) return 'Proprietorship Firm';
        if (str_contains($t, 'partner')) return 'Partnership Firm';
        if (str_contains($t, 'llp')) return 'LLP';
        if (str_contains($t, 'company')) return 'Company';
        return 'Other';
    }

    private function normalizeProductStatus(?string $status): string
    {
        if (empty($status)) {
            return ProductStatus::APPROVED->value;
        }

        $trimmed = trim($status);
        foreach (ProductStatus::cases() as $case) {
            if (strcasecmp($case->value, $trimmed) === 0 || strcasecmp($case->name, $trimmed) === 0) {
                return $case->value;
            }
        }

        if (strcasecmp($trimmed, 'active') === 0) {
            return ProductStatus::APPROVED->value;
        }

        return ProductStatus::APPROVED->value;
    }

    // =========================================================================
    // PICKUP STORE IMPORT
    // =========================================================================

    public function pickupStoreIndex()
    {
        return view('admin.import.pickup_store.index');
    }

    public function pickupStoreSample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pickup Store Import Template');

        $headers = [
            'A1' => 'Seller Code*',
            'B1' => 'PSC Code*',
            'C1' => 'Pickup Store Name*',
            'D1' => 'Contact Person*',
            'E1' => 'Mobile',
            'F1' => 'Email',
            'G1' => 'GST Number',
            'H1' => 'Address*',
            'I1' => 'Landmark',
            'J1' => 'State*',
            'K1' => 'District*',
            'L1' => 'Pincode*',
            'M1' => 'Can Dispatch & Receive Payment',
            'N1' => 'Account Holder Name',
            'O1' => 'Bank Account Number',
            'P1' => 'IFSC Code',
            'Q1' => 'Bank Name',
            'R1' => 'Branch Name',
            'S1' => 'Status',
            'T1' => 'Password',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerRange = 'A1:T1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('059669');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sampleRow = [
            'A2' => 'IMV1001',
            'B2' => 'PSC1001',
            'C2' => 'Aravind Handlooms Store Pune',
            'D2' => 'Rajesh Sharma',
            'E2' => '9822012345',
            'F2' => 'store.pune@aravindmsme.org',
            'G2' => '27AAACA12341Z1',
            'H2' => 'Shop 12, MG Road',
            'I2' => 'Near Central Mall',
            'J2' => 'Maharashtra',
            'K2' => 'Mumbai',
            'L2' => '400001',
            'M2' => 'Yes',
            'N2' => 'Aravind Handlooms',
            'O2' => '91802001234567',
            'P2' => 'SBIN0001234',
            'Q2' => 'State Bank of India',
            'R2' => 'Fort Branch',
            'S2' => 'Active',
            'T2' => 'Password123',
        ];

        foreach ($sampleRow as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Dropdown validations
        $this->addDropdownValidation($sheet, 'M', ['Yes', 'No']);
        $this->addDropdownValidation($sheet, 'S', ['Active', 'Inactive']);

        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Pickup_Store_Import_Template_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function pickupStorePreview(Request $request)
    {
        @set_time_limit(600);
        ini_set('memory_limit', '512M');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls|max:20480',
        ]);

        $rawRows = $this->readRawExcelRows($request->file('file'));
        if (empty($rawRows)) {
            return response()->json(['success' => false, 'message' => 'The uploaded file is empty or missing content.'], 422);
        }

        // Detect if first row is header
        $firstRow = $rawRows[0] ?? [];
        $firstRowLower = array_map(fn($v) => strtolower(trim((string)$v)), $firstRow);

        $hasPscCodeHeader = false;
        foreach ($firstRowLower as $val) {
            if (str_contains($val, 'psc') || str_contains($val, 'store code')) {
                $hasPscCodeHeader = true;
                break;
            }
        }

        $rows = $rawRows;
        // If first row looks like header row, strip it
        if (!empty($firstRowLower['A']) && (str_contains($firstRowLower['A'], 'seller') || str_contains($firstRowLower['A'], 'code'))) {
            array_shift($rows);
        }

        // -------------------------------------------------------------
        // PRE-FETCH REFERENCE DATA INTO IN-MEMORY MAPS FOR O(1) LOOKUPS
        // -------------------------------------------------------------

        // 1. Existing PickupStores sets (PSC code is scoped per owner_id)
        $existingMobiles  = array_change_key_case(array_flip(PickupStore::whereNotNull('mobile')->pluck('mobile')->toArray()), CASE_LOWER);
        $existingEmails   = array_change_key_case(array_flip(PickupStore::whereNotNull('email')->pluck('email')->toArray()), CASE_LOWER);

        $existingPscCodes = [];
        $existingStores = PickupStore::select('owner_id', 'pickup_store_code')->whereNotNull('pickup_store_code')->get();
        $sellerStoreCounts = [];
        foreach ($existingStores as $ps) {
            $ownerId = (int)$ps->owner_id;
            $codeUpper = strtoupper(trim($ps->pickup_store_code));
            $existingPscCodes[$ownerId . '_' . $codeUpper] = true;

            if (preg_match('/^PSC(\d+)$/i', $codeUpper, $m)) {
                $num = (int)$m[1];
                if (!isset($sellerStoreCounts[$ownerId]) || $num > $sellerStoreCounts[$ownerId]) {
                    $sellerStoreCounts[$ownerId] = $num;
                }
            }
        }

        // 2. States Map
        $states = State::all();
        $stateMapById   = [];
        $stateMapByCode = [];
        $stateMapByName = [];
        foreach ($states as $st) {
            $stateMapById[$st->id] = $st;
            if ($st->code) $stateMapByCode[strtoupper($st->code)] = $st;
            if ($st->name) $stateMapByName[strtolower($st->name)] = $st;
        }

        // 3. Districts Map
        $districts = District::all();
        $districtMapById = [];
        $districtMapByName = [];
        foreach ($districts as $dst) {
            $districtMapById[$dst->id] = $dst;
            $districtMapByName[strtolower(trim($dst->name))] = $dst;
        }

        // 4. Sellers Map
        $sellerUsers = User::whereIn('role', ['seller', 'sub_seller'])->get();
        $sellerProfiles = SellerProfile::all();

        $sellerMapBySellerId = [];
        foreach ($sellerProfiles as $sp) {
            if ($sp->seller_id) {
                $user = $sellerUsers->firstWhere('id', $sp->user_id);
                if ($user) {
                    $sellerMapBySellerId[strtolower(trim($sp->seller_id))] = $user;
                }
            }
        }
        $sellerMapByEmail  = [];
        $sellerMapByMobile = [];
        foreach ($sellerUsers as $u) {
            if ($u->email) $sellerMapByEmail[strtolower(trim($u->email))] = $u;
            if ($u->mobile) $sellerMapByMobile[trim($u->mobile)] = $u;
        }

        // Auto generator counter
        $latestStoreCode = PickupStore::orderByRaw('CAST(SUBSTRING(pickup_store_code, 4) AS UNSIGNED) DESC')->value('pickup_store_code');
        $autoCodeNum = 1001;
        if ($latestStoreCode && preg_match('/^PSC(\d+)$/', $latestStoreCode, $m)) {
            $autoCodeNum = max(1001, ((int)$m[1]) + 1);
        }

        $preview = [];
        $validCount = 0;
        $errorCount = 0;

        $processedMobiles  = [];
        $processedEmails   = [];
        $processedPscCodes = [];

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;

            if ($hasPscCodeHeader || isset($row['T'])) {
                // Template WITH PSC Code in Column B
                $sellerCode         = trim($row['A'] ?? '');
                $pscCode            = trim($row['B'] ?? '');
                $storeName          = trim($row['C'] ?? '');
                $contactPerson      = trim($row['D'] ?? '');
                $mobile             = trim($row['E'] ?? '');
                $email              = trim($row['F'] ?? '');
                $gstNumber          = trim($row['G'] ?? '');
                $address            = trim($row['H'] ?? '');
                $landmark           = trim($row['I'] ?? '');
                $stateInput         = trim($row['J'] ?? '');
                $districtInput      = trim($row['K'] ?? '');
                $pincode            = trim($row['L'] ?? '');
                $canDispatchInput   = trim($row['M'] ?? 'Yes');
                $accountHolderName  = trim($row['N'] ?? '');
                $bankAccountNumber  = trim($row['O'] ?? '');
                $ifscCode           = trim($row['P'] ?? '');
                $bankName           = trim($row['Q'] ?? '');
                $branchName         = trim($row['R'] ?? '');
                $statusInput        = trim($row['S'] ?? 'Active');
                $password           = trim($row['T'] ?? '');
            } else {
                // Legacy Template WITHOUT PSC Code column (Seller Code in A, Store Name in B)
                $sellerCode         = trim($row['A'] ?? '');
                $pscCode            = '';
                $storeName          = trim($row['B'] ?? '');
                $contactPerson      = trim($row['C'] ?? '');
                $mobile             = trim($row['D'] ?? '');
                $email              = trim($row['E'] ?? '');
                $gstNumber          = trim($row['F'] ?? '');
                $address            = trim($row['G'] ?? '');
                $landmark           = trim($row['H'] ?? '');
                $stateInput         = trim($row['I'] ?? '');
                $districtInput      = trim($row['J'] ?? '');
                $pincode            = trim($row['K'] ?? '');
                $canDispatchInput   = trim($row['L'] ?? 'Yes');
                $accountHolderName  = trim($row['M'] ?? '');
                $bankAccountNumber  = trim($row['N'] ?? '');
                $ifscCode           = trim($row['O'] ?? '');
                $bankName           = trim($row['P'] ?? '');
                $branchName         = trim($row['Q'] ?? '');
                $statusInput        = trim($row['R'] ?? 'Active');
                $password           = trim($row['S'] ?? '');
            }

            $errors = [];

            // 1. Seller lookup (In-Memory Map)
            $sellerUser = null;
            $ownerType = 'Seller';
            if (empty($sellerCode)) {
                $errors[] = 'Seller Code is required.';
            } else {
                $cleanSellerCode = strtolower(trim($sellerCode));
                $sellerUser = $sellerMapBySellerId[$cleanSellerCode]
                    ?? $sellerMapByEmail[$cleanSellerCode]
                    ?? $sellerMapByMobile[$cleanSellerCode]
                    ?? null;

                if (!$sellerUser) {
                    $errors[] = "Seller Code '{$sellerCode}' not found in database.";
                } else {
                    $ownerType = $sellerUser->parent_id === null ? 'Seller' : 'Sub Seller';
                }
            }

            // 2. PSC Code validation (Scoped per Seller)
            $ownerId = $sellerUser?->id ?? 0;
            if (!empty($pscCode)) {
                $pscCodeUpper = strtoupper($pscCode);
                $pscKey = $ownerId . '_' . $pscCodeUpper;
                if (isset($processedPscCodes[$pscKey])) {
                    $errors[] = "Duplicate PSC Code '{$pscCode}' for seller '{$sellerCode}' in uploaded file.";
                } else if (isset($existingPscCodes[$pscKey])) {
                    $errors[] = "PSC Code '{$pscCode}' is already registered for this seller in database.";
                } else {
                    $processedPscCodes[$pscKey] = true;
                }
            } else {
                $currentCount = max(1001, ($sellerStoreCounts[$ownerId] ?? 1000) + 1);
                do {
                    $genCandidate = 'PSC' . $currentCount++;
                    $candKey = $ownerId . '_' . strtoupper($genCandidate);
                } while (isset($existingPscCodes[$candKey]) || isset($processedPscCodes[$candKey]));

                $sellerStoreCounts[$ownerId] = $currentCount - 1;
                $pscCode = $genCandidate;
                $processedPscCodes[$ownerId . '_' . strtoupper($pscCode)] = true;
            }

            // 3. Mandatory fields & Mobile/Email validation (In-Memory Maps)
            if (empty($storeName)) {
                $errors[] = 'Pickup Store Name is required.';
            }
            if (empty($contactPerson)) {
                $errors[] = 'Contact Person is required.';
            }

            // GST Number is optional
            // Mobile and email can be empty or duplicate

            if (empty($address)) {
                $errors[] = 'Address is required.';
            }

            // 4. State lookup (Optional/Flexible)
            $stateObj = null;
            if (!empty($stateInput)) {
                $stateInputTrim = trim($stateInput);
                $stateObj = $stateMapById[$stateInputTrim]
                    ?? $stateMapByCode[strtoupper($stateInputTrim)]
                    ?? $stateMapByName[strtolower($stateInputTrim)]
                    ?? null;
            }

            // 5. District lookup (Direct & Independent of state)
            $districtObj = null;
            $cleanDistrict = trim((string)$districtInput);
            if (!empty($cleanDistrict) && strtolower($cleanDistrict) !== 'na' && strtolower($cleanDistrict) !== 'n/a') {
                $districtObj = $districtMapById[$cleanDistrict]
                    ?? $districtMapByName[strtolower($cleanDistrict)]
                    ?? null;
            }

            // Pincode validation
            if (empty($pincode)) {
                $errors[] = 'Pincode is required.';
            } else if (!preg_match('/^[0-9]{6}$/', $pincode)) {
                $errors[] = "Pincode '{$pincode}' must be exactly 6 digits.";
            }

            // Can receive payment and dispatch
            $canDispatch = in_array(strtolower($canDispatchInput), ['1', 'yes', 'true', 'active']);

            if ($canDispatch) {
                if (empty($accountHolderName)) $errors[] = 'Account Holder Name is required when dispatch & payment option is Yes.';
                if (empty($bankAccountNumber)) $errors[] = 'Bank Account Number is required when dispatch & payment option is Yes.';
                if (empty($ifscCode)) $errors[] = 'IFSC Code is required when dispatch & payment option is Yes.';
                if (empty($bankName)) $errors[] = 'Bank Name is required when dispatch & payment option is Yes.';
                if (empty($branchName)) $errors[] = 'Branch Name is required when dispatch & payment option is Yes.';
            }

            $status = in_array(strtolower($statusInput), ['inactive', '0', 'disabled']) ? 'Inactive' : 'Active';

            $isValid = empty($errors);
            if ($isValid) {
                $validCount++;
            } else {
                $errorCount++;
            }

            $preview[] = [
                'row_number'                        => $rowNum,
                'seller_code'                       => $sellerCode,
                'seller_name'                       => $sellerUser?->name ?? '—',
                'seller_user_id'                    => $sellerUser?->id,
                'owner_type'                        => $ownerType,
                'pickup_store_code'                 => $pscCode,
                'pickup_store_name'                 => $storeName,
                'contact_person'                    => $contactPerson,
                'mobile'                            => $mobile,
                'email'                             => $email,
                'gst_number'                        => $gstNumber,
                'address'                           => $address,
                'landmark'                          => $landmark,
                'state_id'                          => $stateObj?->id,
                'state_name'                        => $stateObj?->name ?? $stateInput,
                'district_id'                       => $districtObj?->id,
                'district_name'                     => $districtObj?->name ?? $districtInput,
                'pincode'                           => $pincode,
                'can_receive_payment_and_dispatch'  => $canDispatch,
                'account_holder_name'               => $accountHolderName,
                'bank_account_number'               => $bankAccountNumber,
                'ifsc_code'                         => $ifscCode,
                'bank_name'                         => $bankName,
                'branch_name'                       => $branchName,
                'status'                            => $status,
                'password'                          => $password ?: 'Password123',
                'is_valid'                          => $isValid,
                'errors'                            => $errors,
            ];
        }

        return response()->json([
            'success' => true,
            'total'   => count($preview),
            'valid'   => $validCount,
            'errors'  => $errorCount,
            'records' => $preview,
        ]);
    }

    public function pickupStoreProcess(Request $request)
    {
        @set_time_limit(600);
        ini_set('memory_limit', '512M');

        $records = $request->input('records', []);
        if (empty($records)) {
            return response()->json(['success' => false, 'message' => 'No records provided for import.'], 422);
        }

        $imported = 0;
        $skipped = 0;
        $failed = 0;
        $details = [];

        // Pre-fetch existing records to avoid DB queries per row
        $existingMobiles  = array_change_key_case(array_flip(PickupStore::whereNotNull('mobile')->pluck('mobile')->toArray()), CASE_LOWER);
        $existingEmails   = array_change_key_case(array_flip(PickupStore::whereNotNull('email')->pluck('email')->toArray()), CASE_LOWER);

        $existingPscCodes = [];
        $existingStores = PickupStore::select('owner_id', 'pickup_store_code')->whereNotNull('pickup_store_code')->get();
        foreach ($existingStores as $ps) {
            $existingPscCodes[$ps->owner_id . '_' . strtoupper(trim($ps->pickup_store_code))] = true;
        }

        // Password Hash Caching (crucial for high speed: bcrypt hash cost is heavy)
        $passwordHashCache = [];

        $insertBatch = [];
        $now = now();

        DB::beginTransaction();
        try {
            foreach ($records as $record) {
                if (empty($record['is_valid'])) {
                    $skipped++;
                    $details[] = [
                        'row' => $record['row_number'] ?? '—',
                        'identifier' => $record['pickup_store_name'] ?? '—',
                        'name' => $record['seller_code'] ?? '—',
                        'status' => 'Skipped',
                        'message' => implode(' | ', $record['errors'] ?? ['Invalid record']),
                    ];
                    continue;
                }

                $ownerId = $record['seller_user_id'] ?? 0;
                $code = !empty($record['pickup_store_code']) ? $record['pickup_store_code'] : PickupStore::generatePickupStoreCode($ownerId);
                $codeUpper = strtoupper($code);
                $pscKey = $ownerId . '_' . $codeUpper;

                if (isset($existingPscCodes[$pscKey])) {
                    $skipped++;
                    $details[] = [
                        'row' => $record['row_number'] ?? '—',
                        'identifier' => $code,
                        'name' => $record['pickup_store_name'],
                        'status' => 'Skipped',
                        'message' => "PSC Code '{$code}' is already registered for this seller.",
                    ];
                    continue;
                }

                // Mark in set to prevent intra-batch duplicates for same seller
                $existingPscCodes[$pscKey] = true;

                $rawPass = $record['password'] ?? 'Password123';
                if (!isset($passwordHashCache[$rawPass])) {
                    $passwordHashCache[$rawPass] = Hash::make($rawPass);
                }

                $canDispatch = !empty($record['can_receive_payment_and_dispatch']);

                $store = PickupStore::create([
                    'owner_type'                        => $record['owner_type'] ?? 'Seller',
                    'owner_id'                          => $record['seller_user_id'] ?? null,
                    'pickup_store_code'                 => $code,
                    'pickup_store_name'                 => $record['pickup_store_name'] ?? '',
                    'contact_person'                    => $record['contact_person'] ?? '',
                    'mobile'                            => $record['mobile'] ?? null,
                    'email'                             => $record['email'] ?? null,
                    'password'                          => $passwordHashCache[$rawPass],
                    'gst_number'                        => $record['gst_number'] ?? null,
                    'address'                           => $record['address'],
                    'landmark'                          => $record['landmark'] ?: null,
                    'state_id'                          => $record['state_id'],
                    'district_id'                       => $record['district_id'],
                    'pincode'                           => $record['pincode'],
                    'delivery_coverage_type'            => 'district',
                    'status'                            => ($record['status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active',
                    'can_receive_payment_and_dispatch'  => $canDispatch ? 1 : 0,
                    'account_holder_name'               => $canDispatch ? ($record['account_holder_name'] ?: null) : null,
                    'bank_account_number'               => $canDispatch ? ($record['bank_account_number'] ?: null) : null,
                    'ifsc_code'                         => $canDispatch ? ($record['ifsc_code'] ?: null) : null,
                    'bank_name'                         => $canDispatch ? ($record['bank_name'] ?: null) : null,
                    'branch_name'                       => $canDispatch ? ($record['branch_name'] ?: null) : null,
                ]);

                // Create default District Delivery Area mapping for the store
                if (!empty($record['state_id']) && !empty($record['district_id'])) {
                    PickupStoreDeliveryArea::firstOrCreate([
                        'pickup_store_id' => $store->id,
                        'coverage_type'   => 'district',
                        'state_id'        => $record['state_id'],
                        'district_id'     => $record['district_id'],
                    ], [
                        'status'          => 'Active',
                    ]);
                }

                $imported++;
                $details[] = [
                    'row' => $record['row_number'] ?? '—',
                    'identifier' => $code,
                    'name' => $record['pickup_store_name'],
                    'status' => 'Imported',
                    'message' => "Successfully created Pickup Store {$code} for Seller {$record['seller_code']} (Default Coverage: District).",
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped,
                'failed' => $failed,
                'details' => $details,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pickup Store Import Error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to import pickup stores: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // DELIVERY AREA IMPORT
    // =========================================================================

    public function deliveryAreaIndex()
    {
        return view('admin.import.delivery_area.index');
    }

    public function deliveryAreaSample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Delivery Area Import Template');

        $headers = [
            'A1' => 'Seller Code*',
            'B1' => 'PSC Code*',
            'C1' => 'District Name*',
            'D1' => 'State Name',
            'E1' => 'Status',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerRange = 'A1:E1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0ea5e9');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sampleRows = [
            ['A2' => 'IMV1001', 'B2' => 'PSC1001', 'C2' => 'Mumbai',  'D2' => 'Maharashtra', 'E2' => 'Active'],
            ['A3' => 'IMV1001', 'B3' => 'PSC1001', 'C3' => 'Pune',    'D3' => 'Maharashtra', 'E3' => 'Active'],
            ['A4' => 'IMV1002', 'B4' => 'PSC1002', 'C4' => 'Nashik',  'D4' => 'Maharashtra', 'E4' => 'Active'],
        ];

        foreach ($sampleRows as $rowData) {
            foreach ($rowData as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
        }

        // Dropdown for Status column
        $this->addDropdownValidation($sheet, 'E', ['Active', 'Inactive']);

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Delivery_Area_Import_Template_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function deliveryAreaPreview(Request $request)
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '-1');

        try {
            $request->validate([
                'file' => 'required|file|max:153600',
            ], [
                'file.required' => 'Please select an Excel or CSV file to upload.',
                'file.file' => 'The uploaded item must be a valid file.',
                'file.max' => 'The uploaded file size must not exceed 150 MB.',
            ]);

            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['xlsx', 'xls', 'csv', 'txt'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file format (.' . $ext . '). Please upload an Excel (.xlsx, .xls) or CSV (.csv) file.',
                ], 422);
            }

            $rawRows = $this->readRawExcelRows($file);
            if (empty($rawRows)) {
                return response()->json(['success' => false, 'message' => 'The uploaded file is empty or missing content.'], 422);
            }

            // Clean cell values function
            $cleanCell = fn($v) => trim(str_replace(["\r", "\n", "\xC2\xA0", "\xA0"], ' ', (string)($v ?? '')));

            // Detect Header Row & Map Column Positions Dynamically
            $sellerCol   = 'A';
            $pscCol      = 'B';
            $districtCol = 'C';
            $stateCol    = 'D';
            $statusCol   = 'E';
            $rows        = $rawRows;

            if (!empty($rawRows[0])) {
                $headerCells = array_map(fn($v) => strtolower($cleanCell($v)), $rawRows[0]);
                $foundHeader = false;

                foreach ($headerCells as $colKey => $cellVal) {
                    $cleanVal = preg_replace('/[^a-z0-9_]/', '', str_replace('*', '', $cellVal));
                    if (in_array($cleanVal, ['sellercode', 'seller', 'sellerid', 'vendorcode', 'imvcode', 'vendorid', 'vendor', 'sellernumber', 'sellername', 'subsellercode', 'user', 'sellercodemobileemail', 'seller_code', 'vendor_code'])) {
                        $sellerCol = $colKey;
                        $foundHeader = true;
                    } elseif (in_array($cleanVal, ['psccode', 'psc', 'storecode', 'pickupstorecode', 'pickupstore', 'store', 'storename', 'pscname', 'storeid', 'pickupstoreid', 'psc_code', 'store_code'])) {
                        $pscCol = $colKey;
                        $foundHeader = true;
                    } elseif (in_array($cleanVal, ['districtname', 'district', 'districts', 'districtid', 'district_name', 'district_id', 'city'])) {
                        $districtCol = $colKey;
                        $foundHeader = true;
                    } elseif (in_array($cleanVal, ['statename', 'state', 'states', 'stateid', 'state_name', 'state_id'])) {
                        $stateCol = $colKey;
                        $foundHeader = true;
                    } elseif (in_array($cleanVal, ['status', 'statestatus', 'active'])) {
                        $statusCol = $colKey;
                        $foundHeader = true;
                    }
                }

                if (!$foundHeader) {
                    foreach ($headerCells as $cellVal) {
                        $c = preg_replace('/[^a-z0-9]/', '', str_replace('*', '', $cellVal));
                        if (in_array($c, ['sellercode', 'seller', 'vendor', 'psccode', 'psc', 'storecode', 'districtname', 'district', 'statename', 'state', 'status'])) {
                            $foundHeader = true;
                            break;
                        }
                    }
                }

                if ($foundHeader) {
                    array_shift($rows);
                }
            }

            if (empty($rows)) {
                return response()->json(['success' => false, 'message' => 'No data rows found after stripping header row.'], 422);
            }

            // -------------------------------------------------------
            // PRE-FETCH REFERENCE DATA
            // -------------------------------------------------------

            // 1. Sellers map (seller_id, email, mobile, id => user)
            $sellerProfiles = SellerProfile::all();
            $sellerUsers    = User::whereIn('role', ['seller', 'sub_seller', 'vendor'])->get();
            $sellerUsersById = $sellerUsers->keyBy('id');

            $sellerMapBySellerId = [];
            foreach ($sellerProfiles as $sp) {
                if ($sp->seller_id && isset($sellerUsersById[$sp->user_id])) {
                    $sellerMapBySellerId[strtolower(trim($sp->seller_id))] = $sellerUsersById[$sp->user_id];
                }
            }

            $sellerMapByEmail  = [];
            $sellerMapByMobile = [];
            foreach ($sellerUsers as $u) {
                if ($u->email)  $sellerMapByEmail[strtolower(trim($u->email))] = $u;
                if ($u->mobile) $sellerMapByMobile[trim($u->mobile)]           = $u;
            }

            // 2. Pickup Stores map
            $storeMapByOwnerPsc = [];
            $storeMapByPsc      = [];
            foreach (PickupStore::all() as $ps) {
                if ($ps->pickup_store_code) {
                    $pscUpper = strtoupper(trim($ps->pickup_store_code));
                    $storeMapByOwnerPsc[$ps->owner_id . '_' . $pscUpper] = $ps;
                    $storeMapByPsc[$pscUpper] = $ps;
                }
            }

            // 3. States map (id => state) + Districts map (name => district[])
            $statesById = State::all()->keyBy('id');
            $districts  = District::all();
            $districtMapByName = [];
            foreach ($districts as $dst) {
                $districtMapByName[strtolower(trim($dst->name))][] = $dst;
            }

            // 4. Existing delivery areas (to detect duplicates)
            $existingAreas = PickupStoreDeliveryArea::where('coverage_type', 'district')
                ->select('pickup_store_id', 'district_id')
                ->get()
                ->map(fn($a) => $a->pickup_store_id . '_' . $a->district_id)
                ->flip()
                ->all();

            $preview     = [];
            $validCount  = 0;
            $errorCount  = 0;
            $processed   = []; // track intra-file duplicates: storeId_districtId

            foreach ($rows as $idx => $row) {
                $rowNum        = $idx + 2;
                $rawSellerVal  = $cleanCell($row[$sellerCol] ?? '');
                $sellerCode    = strtolower($rawSellerVal);
                $pscCode       = strtoupper($cleanCell($row[$pscCol] ?? ''));
                $districtInput = $cleanCell($row[$districtCol] ?? '');
                $stateInput    = $stateCol ? $cleanCell($row[$stateCol] ?? '') : '';
                $statusInput   = $cleanCell($row[$statusCol] ?? 'Active');

                // Explicit guard to skip header line if present
                $cleanSellerCheck   = preg_replace('/[^a-z0-9]/', '', strtolower($rawSellerVal));
                $cleanPscCheck      = preg_replace('/[^a-z0-9]/', '', strtolower($pscCode));
                $cleanDistrictCheck = preg_replace('/[^a-z0-9]/', '', strtolower($districtInput));

                if (in_array($cleanSellerCheck, ['sellercode', 'seller', 'sellerid', 'vendorcode', 'vendor'])
                    || in_array($cleanPscCheck, ['psccode', 'psc', 'storecode', 'pickupstorecode', 'store'])
                    || in_array($cleanDistrictCheck, ['districtname', 'district', 'districts'])) {
                    continue;
                }

                $errors = [];

                // 1. Seller lookup
                $sellerUser = null;
                if (empty($rawSellerVal)) {
                    $errors[] = 'Seller Code is required.';
                } else {
                    $sellerUser = $sellerMapBySellerId[$sellerCode]
                        ?? $sellerMapByEmail[$sellerCode]
                        ?? $sellerMapByMobile[$rawSellerVal]
                        ?? (is_numeric($rawSellerVal) ? ($sellerUsersById[(int)$rawSellerVal] ?? null) : null);

                    if (!$sellerUser) {
                        $errors[] = "Seller Code '{$rawSellerVal}' not found in database.";
                    }
                }

                // 2. Pickup Store lookup (seller + PSC code)
                $storeObj = null;
                if (empty($pscCode)) {
                    $errors[] = 'PSC Code is required.';
                } elseif ($sellerUser) {
                    // Primary: match by owner_id + PSC code
                    $storeObj = $storeMapByOwnerPsc[$sellerUser->id . '_' . $pscCode] ?? null;
                    if (!$storeObj && !empty($sellerUser->parent_id)) {
                        $storeObj = $storeMapByOwnerPsc[$sellerUser->parent_id . '_' . $pscCode] ?? null;
                    }
                    if (!$storeObj) {
                        // Fallback: match by PSC code alone
                        $storeObj = $storeMapByPsc[$pscCode] ?? null;
                    }
                    if (!$storeObj) {
                        $errors[] = "PSC Code '{$pscCode}' not found for Seller '{$rawSellerVal}'.";
                    }
                } elseif (!empty($pscCode)) {
                    // Seller was invalid but still try PSC lookup so we can show store name in preview
                    $storeObj = $storeMapByPsc[$pscCode] ?? null;
                }

                // 3. District lookup
                $districtObj = null;
                if (empty($districtInput)) {
                    $errors[] = 'District Name is required.';
                } else {
                    $matches = $districtMapByName[strtolower($districtInput)] ?? [];
                    if (count($matches) === 0) {
                        $errors[] = "District '{$districtInput}' not found in database.";
                    } elseif (count($matches) === 1) {
                        $districtObj = $matches[0];
                    } else {
                        // Multiple districts with same name — check state match first
                        if (!empty($stateInput)) {
                            foreach ($matches as $d) {
                                $stName = strtolower($statesById[$d->state_id]->name ?? '');
                                $stCode = strtolower($statesById[$d->state_id]->code ?? '');
                                if ($stName === strtolower($stateInput) || $stCode === strtolower($stateInput)) {
                                    $districtObj = $d;
                                    break;
                                }
                            }
                        }
                        if (!$districtObj && $storeObj) {
                            foreach ($matches as $d) {
                                if ($d->state_id === $storeObj->state_id) {
                                    $districtObj = $d;
                                    break;
                                }
                            }
                        }
                        if (!$districtObj) {
                            $districtObj = $matches[0]; // fallback: first match
                        }
                    }
                }

                // 4. Duplicate check
                if ($storeObj && $districtObj) {
                    $uniqueKey = $storeObj->id . '_' . $districtObj->id;
                    if (isset($existingAreas[$uniqueKey])) {
                        $errors[] = "Delivery area for district '{$districtInput}' already exists for store '{$pscCode}'.";
                    } elseif (isset($processed[$uniqueKey])) {
                        $errors[] = "Duplicate row in uploaded file: '{$pscCode}' + '{$districtInput}'.";
                    } else {
                        $processed[$uniqueKey] = true;
                    }
                }

                $status  = in_array(strtolower($statusInput), ['inactive', '0', 'disabled']) ? 'Inactive' : 'Active';
                $isValid = empty($errors);
                $isValid ? $validCount++ : $errorCount++;

                $preview[] = [
                    'row_number'    => $rowNum,
                    'seller_code'   => $rawSellerVal,
                    'seller_name'   => $sellerUser?->name ?? '—',
                    'psc_code'      => $pscCode,
                    'store_name'    => $storeObj?->pickup_store_name ?? '—',
                    'store_id'      => $storeObj?->id,
                    'district_name' => $districtObj?->name ?? $districtInput,
                    'district_id'   => $districtObj?->id,
                    'state_name'    => $districtObj ? ($statesById[$districtObj->state_id]->name ?? '—') : '—',
                    'state_id'      => $districtObj?->state_id,
                    'coverage_type' => 'district',
                    'status'        => $status,
                    'is_valid'      => $isValid,
                    'errors'        => $errors,
                ];
            }

            return response()->json([
                'success' => true,
                'total'   => count($preview),
                'valid'   => $validCount,
                'errors'  => $errorCount,
                'records' => $preview,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', array_map(fn($msgs) => implode(', ', $msgs), $e->errors())),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('deliveryAreaPreview error: ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error processing file: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function deliveryAreaProcess(Request $request)
    {
        @set_time_limit(600);
        ini_set('memory_limit', '512M');

        $records = $request->input('records', []);
        if (empty($records)) {
            return response()->json(['success' => false, 'message' => 'No records provided for import.'], 422);
        }

        $imported = 0;
        $skipped  = 0;
        $failed   = 0;
        $details  = [];
        $affectedStoreIds = [];

        // Re-fetch existing areas to avoid second-chance duplicates
        $existingAreas = PickupStoreDeliveryArea::where('coverage_type', 'district')
            ->select('pickup_store_id', 'district_id')
            ->get()
            ->map(fn($a) => $a->pickup_store_id . '_' . $a->district_id)
            ->flip()
            ->all();

        $insertBatch = [];
        $now = now();

        DB::beginTransaction();
        try {
            foreach ($records as $record) {
                if (empty($record['is_valid'])) {
                    $skipped++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => $record['psc_code']   ?? '—',
                        'name'       => $record['district_name'] ?? '—',
                        'status'     => 'Skipped',
                        'message'    => implode(' | ', $record['errors'] ?? ['Invalid record']),
                    ];
                    continue;
                }

                $uniqueKey = $record['store_id'] . '_' . $record['district_id'];
                if (isset($existingAreas[$uniqueKey])) {
                    $skipped++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => $record['psc_code']   ?? '—',
                        'name'       => $record['district_name'] ?? '—',
                        'status'     => 'Skipped',
                        'message'    => 'Delivery area already exists in database.',
                    ];
                    continue;
                }

                $existingAreas[$uniqueKey] = true;
                $affectedStoreIds[] = $record['store_id'];

                $insertBatch[] = [
                    'pickup_store_id' => $record['store_id'],
                    'coverage_type'   => 'district',
                    'state_id'        => $record['state_id'],
                    'district_id'     => $record['district_id'],
                    'pincode'         => null,
                    'status'          => ($record['status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];

                $imported++;
                $details[] = [
                    'row'        => $record['row_number'] ?? '—',
                    'identifier' => $record['psc_code']   ?? '—',
                    'name'       => $record['district_name'] ?? '—',
                    'status'     => 'Imported',
                    'message'    => "Delivery area '{$record['district_name']}' linked to store '{$record['psc_code']}'.",
                ];

                if (count($insertBatch) >= 500) {
                    PickupStoreDeliveryArea::insert($insertBatch);
                    $insertBatch = [];
                }
            }

            if (!empty($insertBatch)) {
                PickupStoreDeliveryArea::insert($insertBatch);
            }

            if (!empty($affectedStoreIds)) {
                PickupStore::whereIn('id', array_unique($affectedStoreIds))
                    ->update(['delivery_coverage_type' => 'district']);
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'imported' => $imported,
                'skipped'  => $skipped,
                'failed'   => $failed,
                'details'  => $details,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to import delivery areas: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // SUB SELLER IMPORT
    // =========================================================================

    public function subSellerIndex()
    {
        return view('admin.import.sub_seller.index');
    }

    public function subSellerSample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sub Seller Import Template');

        $headers = [
            'A1' => 'Seller ID (Old System)*',
            'B1' => 'Parent Seller ID*',
            'C1' => 'Name*',
            'D1' => 'Email*',
            'E1' => 'Mobile*',
            'F1' => 'Company Name*',
            'G1' => 'Company Type',
            'H1' => 'GST Number',
            'I1' => 'Authorized Rep Name',
            'J1' => 'Alternate Mobile',
            'K1' => 'Alternate Email',
            'L1' => 'State*',
            'M1' => 'District*',
            'N1' => 'Pincode*',
            'O1' => 'Address',
            'P1' => 'Aadhaar No',
            'Q1' => 'PAN No',
            'R1' => 'Account Holder Name',
            'S1' => 'Bank Account Number',
            'T1' => 'IFSC Code',
            'U1' => 'Bank Name',
            'V1' => 'Branch Name',
            'W1' => 'Wallet Balance',
            'X1' => 'KYC Status',
            'Y1' => 'Allow Own Products',
            'Z1' => 'Status',
            'AA1' => 'Password',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerRange = 'A1:AA1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('7C3AED');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sampleRow = [
            'A2' => 'IMV2001',
            'B2' => 'IMV1001',
            'C2' => 'Priya Sharma',
            'D2' => 'priya.sharma@example.com',
            'E2' => '9876543210',
            'F2' => 'Priya Handicrafts',
            'G2' => 'Proprietorship Firm',
            'H2' => '19AAAAA0000A1Z5',
            'I2' => 'Priya Sharma',
            'J2' => '9876543211',
            'K2' => 'info@priyahandicraft.com',
            'L2' => 'West Bengal',
            'M2' => 'Kolkata',
            'N2' => '700001',
            'O2' => '45 Park Street',
            'P2' => '123456789012',
            'Q2' => 'ABCDE1234F',
            'R2' => 'Priya Sharma',
            'S2' => '9182736450192',
            'T2' => 'SBIN0000001',
            'U2' => 'State Bank of India',
            'V2' => 'Park Street Branch',
            'W2' => '0.00',
            'X2' => 'APPROVED',
            'Y2' => 'Yes',
            'Z2' => 'Active',
            'AA2' => 'Password123',
        ];

        foreach ($sampleRow as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $this->addDropdownValidation($sheet, 'G', ['Proprietorship Firm', 'Partnership Firm', 'Private Limited Company', 'Public Limited Company', 'LLP', 'Company', 'Other']);
        $this->addDropdownValidation($sheet, 'X', ['APPROVED', 'PENDING', 'REJECTED']);
        $this->addDropdownValidation($sheet, 'Y', ['Yes', 'No']);
        $this->addDropdownValidation($sheet, 'Z', ['Active', 'Inactive']);

        foreach (array_merge(range('A', 'Z'), ['AA']) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Sub_Seller_Import_Template_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function subSellerPreview(Request $request)
    {
        @set_time_limit(600);
        ini_set('memory_limit', '512M');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls|max:20480',
        ]);

        $rawRows = $this->readRawExcelRows($request->file('file'));
        if (empty($rawRows)) {
            return response()->json(['success' => false, 'message' => 'The uploaded file is empty or missing content.'], 422);
        }

        // Strip header row if present
        $rows = $rawRows;
        $firstRowLower = array_map(fn($v) => strtolower(trim((string)$v)), $rawRows[0] ?? []);
        if (!empty($firstRowLower) && (
            in_array('sub seller id', $firstRowLower)   ||
            in_array('sub seller id*', $firstRowLower)  ||
            in_array('parent seller id', $firstRowLower)||
            in_array('parent seller id*', $firstRowLower)
        )) {
            array_shift($rows);
        }

        // -------------------------------------------------------
        // PRE-FETCH REFERENCE DATA
        // -------------------------------------------------------

        // 1. Parent Sellers map (seller_id => user)
        $parentSellerProfiles = SellerProfile::whereNotNull('seller_id')->get();
        $parentSellerUsers    = User::where('role', 'seller')->get()->keyBy('id');
        $sellerMapBySellerId  = [];
        foreach ($parentSellerProfiles as $sp) {
            if (isset($parentSellerUsers[$sp->user_id])) {
                $sellerMapBySellerId[strtolower(trim($sp->seller_id))] = [
                    'user'    => $parentSellerUsers[$sp->user_id],
                    'profile' => $sp,
                ];
            }
        }

        // 2. All existing seller IDs (all sellers) for duplicate/uniqueness check
        $existingSellerIds = SellerProfile::whereNotNull('seller_id')->pluck('seller_id')
            ->map(fn($id) => strtolower($id))->flip()->all();

        // 3. All existing emails / mobiles (role=seller covers both parent and sub-sellers)
        $existingEmails  = User::where('role', 'seller')->whereNotNull('email')
            ->pluck('email')->map(fn($e) => strtolower($e))->flip()->all();
        $existingMobiles = User::where('role', 'seller')->whereNotNull('mobile')
            ->pluck('mobile')->flip()->all();

        // 4. States + Districts in-memory
        $statesById   = State::all()->keyBy('id');
        $stateMapByName = [];
        foreach ($statesById as $st) {
            $stateMapByName[strtolower($st->name)] = $st;
            if ($st->code) $stateMapByName[strtolower($st->code)] = $st;
            $stateMapByName[(string)$st->id] = $st;
        }
        $districts = District::all();
        $districtMapByName = []; // lowerName => district
        foreach ($districts as $dst) {
            $districtMapByName[strtolower($dst->name)] = $dst;
            $districtMapByName[(string)$dst->id] = $dst;
        }

        $preview       = [];
        $validCount    = 0;
        $errorCount    = 0;
        $processedIds     = [];
        $processedEmails  = [];
        $processedMobiles = [];

        foreach ($rows as $idx => $row) {
            $rowNum         = $idx + 2;
            $subSellerId    = trim($row['A'] ?? '');
            $parentSellerId = trim($row['B'] ?? '');
            $name           = trim($row['C'] ?? '');
            $email          = strtolower(trim($row['D'] ?? ''));
            $mobile         = trim($row['E'] ?? '');
            $company        = trim($row['F'] ?? '');
            $compType       = $this->normalizeCompanyType(trim($row['G'] ?? ''));
            $gst            = trim($row['H'] ?? '');
            $authRep        = trim($row['I'] ?? '');
            $altMobile      = trim($row['J'] ?? '');
            $altEmail       = trim($row['K'] ?? '');
            $stateName      = trim($row['L'] ?? '');
            $distName       = trim($row['M'] ?? '');
            $pincode        = trim($row['N'] ?? '');
            $address        = trim($row['O'] ?? '');
            $aadhaar        = trim($row['P'] ?? '');
            $pan            = trim($row['Q'] ?? '');
            $accName        = trim($row['R'] ?? '');
            $accNo          = trim($row['S'] ?? '');
            $ifsc           = trim($row['T'] ?? '');
            $bankName       = trim($row['U'] ?? '');
            $branch         = trim($row['V'] ?? '');
            $walletBal      = trim($row['W'] ?? '0.00');
            $kycStatus      = strtoupper(trim($row['X'] ?? 'APPROVED'));
            $allowOwn       = trim($row['Y'] ?? 'Yes');
            $statusInput    = trim($row['Z'] ?? 'Active');
            $password       = trim($row['AA'] ?? '');

            $errors = [];

            // 1. Sub Seller ID
            if (empty($subSellerId)) {
                $errors[] = 'Sub Seller ID is required.';
            } else {
                $idLower = strtolower($subSellerId);
                if (isset($existingSellerIds[$idLower])) {
                    $errors[] = "Sub Seller ID '{$subSellerId}' already exists in database.";
                } elseif (isset($processedIds[$idLower])) {
                    $errors[] = "Duplicate Sub Seller ID '{$subSellerId}' in uploaded file.";
                } else {
                    $processedIds[$idLower] = true;
                }
            }

            // 2. Parent Seller lookup
            $parentData = null;
            $parentUser = null;
            if (empty($parentSellerId)) {
                $errors[] = 'Parent Seller ID is required.';
            } else {
                $parentData = $sellerMapBySellerId[strtolower($parentSellerId)] ?? null;
                if (!$parentData) {
                    $parentUserObj = User::where('role', 'seller')
                        ->where(function ($q) use ($parentSellerId) {
                            $q->where('id', $parentSellerId)
                              ->orWhereHas('sellerProfile', function ($sq) use ($parentSellerId) {
                                  $sq->where('seller_id', $parentSellerId)
                                    ->orWhereRaw('LOWER(seller_id) = ?', [strtolower($parentSellerId)]);
                              });
                        })
                        ->first();
                    if ($parentUserObj) {
                        $parentUser = $parentUserObj;
                    } else {
                        $errors[] = "Parent Seller ID '{$parentSellerId}' not found in database.";
                    }
                } else {
                    $parentUser = $parentData['user'];
                }
            }

            // 3. Name
            if (empty($name)) $errors[] = 'Name is required.';

            // 4. Email (optional — no validation required)
            if (empty($email)) {
                $email = strtolower($subSellerId ?: ($mobile ?: 'sub_' . uniqid())) . '@noemail.local';
            }

            // 5. Mobile (optional — no required or format validation, duplicate accounts allowed)
            // Mobile can be empty or duplicate

            // 6. Company Name
            if (empty($company)) $errors[] = 'Company Name is required.';

            // 7. State
            $stateObj = null;
            if (empty($stateName)) {
                $errors[] = 'State is required.';
            } else {
                $stateObj = $stateMapByName[strtolower($stateName)] ?? null;
                if (!$stateObj) $errors[] = "State '{$stateName}' not found in database.";
            }

            // 8. District
            $districtObj = null;
            if (empty($distName)) {
                $errors[] = 'District is required.';
            } else {
                $districtObj = $districtMapByName[strtolower($distName)] ?? null;
                if (!$districtObj) $errors[] = "District '{$distName}' not found in database.";
            }

            // 9. Pincode (optional — no validation required)

            // 10. KYC status
            if (!in_array($kycStatus, ['APPROVED', 'PENDING', 'UNDER_REVIEW', 'REJECTED'])) {
                $kycStatus = 'APPROVED';
            }

            $status  = in_array(strtolower($statusInput), ['inactive', '0', 'false']) ? '0' : '1';
            $isValid = empty($errors);
            $isValid ? $validCount++ : $errorCount++;

            $preview[] = [
                'row_number'          => $rowNum,
                'sub_seller_id'       => $subSellerId,
                'parent_seller_id'    => $parentSellerId,
                'parent_user_id'      => $parentUser?->id,
                'parent_name'         => $parentUser?->name ?? '—',
                'name'                => $name,
                'email'               => $email,
                'mobile'              => $mobile,
                'company_name'        => $company,
                'company_type'        => $compType,
                'gst_number'          => $gst,
                'authorized_rep_name' => $authRep,
                'alternate_mobile'    => $altMobile,
                'alternate_email'     => $altEmail,
                'state'               => $stateObj?->name ?? $stateName,
                'state_id'            => $stateObj?->id,
                'district'            => $districtObj?->name ?? $distName,
                'district_id'         => $districtObj?->id,
                'pincode'             => $pincode,
                'address'             => $address,
                'aadhaar_no'          => $aadhaar,
                'pan_no'              => $pan,
                'account_holder_name' => $accName,
                'bank_account_number' => $accNo,
                'ifsc_code'           => $ifsc,
                'bank_name'           => $bankName,
                'branch_name'         => $branch,
                'wallet_balance'      => is_numeric($walletBal) ? $walletBal : 0.00,
                'kyc_status'          => $kycStatus,
                'allow_own_products'  => in_array(strtolower($allowOwn), ['yes', '1', 'true']) ? 'Yes' : 'No',
                'status'              => $status,
                'password'            => $password,
                'is_valid'            => $isValid,
                'errors'              => $errors,
            ];
        }

        return response()->json([
            'success' => true,
            'total'   => count($preview),
            'valid'   => $validCount,
            'errors'  => $errorCount,
            'records' => $preview,
        ]);
    }

    public function subSellerProcess(Request $request)
    {
        @set_time_limit(600);
        ini_set('memory_limit', '512M');

        $records = $request->input('records', []);
        if (empty($records)) {
            return response()->json(['success' => false, 'message' => 'No records provided for import.'], 422);
        }

        $imported = 0;
        $skipped  = 0;
        $failed   = 0;
        $details  = [];

        $passwordHashCache = [];

        // Re-check existing IDs/emails/mobiles at process time
        $existingSellerIds = SellerProfile::whereNotNull('seller_id')->pluck('seller_id')
            ->map(fn($id) => strtolower($id))->flip()->all();
        $existingEmails    = User::where('role', 'seller')->whereNotNull('email')
            ->pluck('email')->map(fn($e) => strtolower($e))->flip()->all();
        $existingMobiles   = User::where('role', 'seller')->whereNotNull('mobile')
            ->pluck('mobile')->flip()->all();

        DB::beginTransaction();
        try {
            foreach ($records as $record) {
                if (empty($record['is_valid'])) {
                    $skipped++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => $record['sub_seller_id'] ?? '—',
                        'name'       => $record['name'] ?? '—',
                        'status'     => 'Skipped',
                        'message'    => implode(' | ', $record['errors'] ?? ['Invalid record']),
                    ];
                    continue;
                }

                $idLower    = strtolower($record['sub_seller_id']);
                $emailLower = strtolower($record['email']);
                $mobile     = $record['mobile'];

                // Second-chance duplicate guard
                if (isset($existingSellerIds[$idLower])) {
                    $skipped++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => $record['sub_seller_id'] ?? '—',
                        'name'       => $record['name'] ?? '—',
                        'status'     => 'Skipped',
                        'message'    => 'ID already exists in database.',
                    ];
                    continue;
                }

                $pwd = !empty($record['password']) ? $record['password'] : 'Password123';
                if (!isset($passwordHashCache[$pwd])) {
                    $passwordHashCache[$pwd] = Hash::make($pwd);
                }

                $emailVal = !empty($record['email']) ? $record['email'] : strtolower(($record['sub_seller_id'] ?? '') ?: (($record['mobile'] ?? '') ?: 'sub_' . uniqid())) . '@noemail.local';
                $user = User::create([
                    'name'     => $record['name'],
                    'email'    => $emailVal,
                    'mobile'   => $record['mobile'],
                    'role'     => 'seller',
                    'status'   => $record['status'] ?? '1',
                    'password' => $passwordHashCache[$pwd],
                ]);

                // Resolve parent_user_id from parent_seller_id (imvcode) if parent_user_id is missing
                $parentUserId = ($record['parent_user_id'] ?? null) ?: null;
                if (!$parentUserId && !empty($record['parent_seller_id'])) {
                    $pCode = trim($record['parent_seller_id']);
                    $pUser = User::where('role', 'seller')
                        ->where(function ($q) use ($pCode) {
                            $q->where('id', $pCode)
                              ->orWhereHas('sellerProfile', function ($sq) use ($pCode) {
                                  $sq->where('seller_id', $pCode)
                                    ->orWhereRaw('LOWER(seller_id) = ?', [strtolower($pCode)]);
                              });
                        })
                        ->first();
                    if ($pUser) {
                        $parentUserId = $pUser->id;
                    }
                }

                // Resolve state_id if missing
                $stateId = ($record['state_id'] ?? null) ?: null;
                if (!$stateId && !empty($record['state'])) {
                    $st = State::whereRaw('LOWER(name) = ?', [strtolower($record['state'])])
                        ->orWhere('code', strtoupper($record['state']))
                        ->orWhere('id', $record['state'])
                        ->first();
                    if ($st) $stateId = $st->id;
                }

                // Resolve district_id if missing (without state requirement)
                $districtId = ($record['district_id'] ?? null) ?: null;
                if (!$districtId && !empty($record['district'])) {
                    $dst = District::whereRaw('LOWER(name) = ?', [strtolower($record['district'])])
                        ->orWhere('id', $record['district'])
                        ->first();
                    if ($dst) $districtId = $dst->id;
                }

                // Set parent_id + seller profile fields via User model hook
                $user->seller_id           = $record['sub_seller_id'];
                $user->parent_id           = $parentUserId;
                $user->company_name        = $record['company_name'];
                $user->company_type        = ($record['company_type'] ?? null) ?: null;
                $user->gst_number          = ($record['gst_number'] ?? null) ?: null;
                $user->authorized_rep_name = ($record['authorized_rep_name'] ?? null) ?: null;
                $user->alternate_mobile    = ($record['alternate_mobile'] ?? null) ?: null;
                $user->state_id            = $stateId;
                $user->district_id         = $districtId;
                $user->state               = $record['state'];
                $user->district            = $record['district'];
                $user->pincode             = $record['pincode'];
                $user->address             = ($record['address'] ?? null) ?: null;
                $user->aadhaar_no          = ($record['aadhaar_no'] ?? null) ?: null;
                $user->pan_no              = ($record['pan_no'] ?? null) ?: null;
                $user->account_holder_name = ($record['account_holder_name'] ?? null) ?: null;
                $user->bank_account_number = ($record['bank_account_number'] ?? null) ?: null;
                $user->ifsc_code           = ($record['ifsc_code'] ?? null) ?: null;
                $user->bank_name           = ($record['bank_name'] ?? null) ?: null;
                $user->branch_name         = ($record['branch_name'] ?? null) ?: null;
                $user->wallet_balance      = $record['wallet_balance'] ?? 0.00;
                $user->kyc_status          = $record['kyc_status'] ?? 'APPROVED';
                $user->allow_own_products  = $record['allow_own_products'] ?? 'No';
                $user->save();

                // Mark as used to prevent intra-batch duplicates
                $existingSellerIds[$idLower]    = true;
                $existingEmails[$emailLower]     = true;
                $existingMobiles[$mobile]        = true;

                $imported++;
                $details[] = [
                    'row'        => $record['row_number'] ?? '—',
                    'identifier' => $record['sub_seller_id'],
                    'name'       => $record['name'],
                    'status'     => 'Imported',
                    'message'    => "Sub Seller '{$record['sub_seller_id']}' created under parent '{$record['parent_seller_id']}'.",
                ];
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'imported' => $imported,
                'skipped'  => $skipped,
                'failed'   => $failed,
                'details'  => $details,
                'message'  => "Import completed: {$imported} Sub Sellers created, {$skipped} skipped.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // DDC CUSTOMER IMPORT
    // =========================================================================

    public function ddcCustomerIndex()
    {
        return view('admin.import.ddc_customer.index');
    }

    public function ddcCustomerSample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DDC Customer Import');

        $headers = [
            'A1' => 'DDC Code*',
            'B1' => 'Customer Name*',
            'C1' => 'Mobile*',
            'D1' => 'Email',
            'E1' => 'Address',
            'F1' => 'Pincode',
            'G1' => 'GST Number',
            'H1' => 'Abhhyam Code',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style header row
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e40af']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Sample row
        $sheet->setCellValue('A2', 'DDC001');
        $sheet->setCellValue('B2', 'Ramesh Kumar');
        $sheet->setCellValue('C2', '9876543210');
        $sheet->setCellValue('D2', 'ramesh@example.com');
        $sheet->setCellValue('E2', '12 MG Road, Delhi');
        $sheet->setCellValue('F2', '110001');
        $sheet->setCellValue('G2', '07AABCU9603R1ZX');
        $sheet->setCellValue('H2', 'ABH123456');

        $writer = new Xlsx($spreadsheet);
        $filename = 'DDC_Customer_Import_Sample.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function ddcCustomerPreview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not parse Excel file: ' . $e->getMessage()]);
        }

        // Build DDC lookup: ddc_code => user_id (from ddc_profiles)
        $ddcMap = DB::table('ddc_profiles')
            ->whereNotNull('ddc_code')
            ->join('users', 'users.id', '=', 'ddc_profiles.user_id')
            ->select('ddc_profiles.ddc_code', 'ddc_profiles.user_id', 'users.name as ddc_name_col')
            ->get()
            ->mapWithKeys(fn($row) => [strtoupper(trim($row->ddc_code)) => [
                'user_id'  => $row->user_id,
                'ddc_name' => $row->ddc_name_col,
            ]])
            ->toArray();

        $records  = [];
        $rowIndex = 0;

        foreach ($rows as $lineNum => $row) {
            $firstCol = strtoupper(trim($row['A'] ?? ''));
            if ($lineNum === 0 || in_array($firstCol, ['DDC CODE', 'DDC CODE*']) || str_contains($firstCol, 'DDC CODE') || str_starts_with($firstCol, '--')) continue; // skip header/notes
            $rowIndex++;

            $ddcCode   = trim($row['A'] ?? '');
            $name      = trim($row['B'] ?? '');
            $mobile    = trim($row['C'] ?? '');
            $email     = trim($row['D'] ?? '');
            $address   = trim($row['E'] ?? '');
            $pincode   = trim($row['F'] ?? '');
            $gst       = trim($row['G'] ?? '');
            $abhhyam   = trim($row['H'] ?? '');

            // Skip fully empty rows
            if ($ddcCode === '' && $name === '' && $mobile === '') continue;

            $errors  = [];
            $ddcId   = null;
            $ddcName = null;

            // Validate DDC code
            if ($ddcCode === '') {
                $errors[] = 'DDC Code is required.';
            } else {
                $ddcEntry = $ddcMap[strtoupper($ddcCode)] ?? null;
                if (!$ddcEntry) {
                    $errors[] = "DDC Code '{$ddcCode}' not found in system.";
                } else {
                    $ddcId   = $ddcEntry['user_id'];
                    $ddcName = $ddcEntry['ddc_name'];
                }
            }

            // Validate Name
            if ($name === '') {
                $errors[] = 'Customer Name is required.';
            }

            // Validate Mobile
            if ($mobile === '') {
                $errors[] = 'Mobile is required.';
            } elseif (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
                $errors[] = "Mobile '{$mobile}' is invalid (must be 10-digit Indian number).";
            }

            // Validate Email
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email '{$email}' is invalid.";
            }

            // Validate Pincode
            if ($pincode !== '' && !preg_match('/^\d{6}$/', $pincode)) {
                $errors[] = "Pincode '{$pincode}' must be 6 digits.";
            }

            // GST format check
            if ($gst !== '' && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', strtoupper($gst))) {
                $errors[] = "GST Number '{$gst}' format is invalid.";
            }

            $records[] = [
                'row_number'   => $rowIndex,
                'ddc_code'     => $ddcCode,
                'ddc_id'       => $ddcId,
                'ddc_name'     => $ddcName,
                'name'         => $name,
                'mobile'       => $mobile,
                'email'        => $email ?: null,
                'address'      => $address ?: null,
                'pincode'      => $pincode ?: null,
                'gst_number'   => $gst ? strtoupper($gst) : null,
                'abhhyam_code' => $abhhyam ?: null,
                'is_valid'     => count($errors) === 0,
                'errors'       => $errors,
            ];
        }

        $valid  = count(array_filter($records, fn($r) => $r['is_valid']));
        $errors = count(array_filter($records, fn($r) => !$r['is_valid']));

        return response()->json([
            'success' => true,
            'total'   => count($records),
            'valid'   => $valid,
            'errors'  => $errors,
            'records' => $records,
        ]);
    }

    public function ddcCustomerProcess(Request $request)
    {
        $records = $request->input('records', []);
        if (empty($records)) {
            return response()->json(['success' => false, 'message' => 'No records to process.']);
        }

        $imported = 0;
        $skipped  = 0;
        $failed   = 0;
        $details  = [];

        DB::beginTransaction();
        try {
            foreach ($records as $record) {
                if (!($record['is_valid'] ?? false)) {
                    $skipped++;
                    $errMsg = !empty($record['errors']) ? 'Validation Error: ' . implode(' | ', $record['errors']) : 'Row has validation errors — skipped.';
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => $record['mobile'] ?? '—',
                        'name'       => $record['name'] ?? '—',
                        'status'     => 'Skipped',
                        'message'    => $errMsg,
                    ];
                    continue;
                }

                $ddcId  = $record['ddc_id'];
                $mobile = $record['mobile'];

                // Check for existing customer with same mobile under same DDC
                $existing = DB::table('ddc_pos_customers')
                    ->where('ddc_id', $ddcId)
                    ->where('mobile', $mobile)
                    ->first();

                if ($existing) {
                    // Update existing record
                    DB::table('ddc_pos_customers')->where('id', $existing->id)->update([
                        'name'         => $record['name'],
                        'email'        => $record['email'] ?? null,
                        'address'      => $record['address'] ?? null,
                        'pincode'      => $record['pincode'] ?? null,
                        'gst_number'   => $record['gst_number'] ?? null,
                        'abhhyam_code' => $record['abhhyam_code'] ?? null,
                        'updated_at'   => now(),
                    ]);
                    $skipped++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => $mobile,
                        'name'       => $record['name'],
                        'status'     => 'Skipped',
                        'message'    => "Customer with mobile '{$mobile}' already exists under this DDC — record updated.",
                    ];
                } else {
                    // Create new customer (bypass model observer for bulk import)
                    DB::table('ddc_pos_customers')->insert([
                        'ddc_id'       => $ddcId,
                        'name'         => $record['name'],
                        'mobile'       => $mobile,
                        'email'        => $record['email'] ?? null,
                        'address'      => $record['address'] ?? null,
                        'pincode'      => $record['pincode'] ?? null,
                        'gst_number'   => $record['gst_number'] ?? null,
                        'abhhyam_code' => $record['abhhyam_code'] ?? null,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                    $imported++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => $mobile,
                        'name'       => $record['name'],
                        'status'     => 'Imported',
                        'message'    => "Customer '{$record['name']}' created under DDC '{$record['ddc_code']}'.",
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'imported' => $imported,
                'skipped'  => $skipped,
                'failed'   => $failed,
                'details'  => $details,
                'message'  => "Import completed: {$imported} customers created, {$skipped} skipped/updated.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // ADC CUSTOMER IMPORT
    // =========================================================================

    public function adcCustomerIndex()
    {
        return view('admin.import.adc_customer.index');
    }

    public function adcCustomerSample()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ADC Customer Import');

        $headers = [
            'A1' => 'ADC Code*',
            'B1' => 'Customer Name*',
            'C1' => 'Mobile*',
            'D1' => 'Email',
            'E1' => 'Address',
            'F1' => 'Pincode',
            'G1' => 'GST Number',
            'H1' => 'Abhhyam Code',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065f46']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Sample row
        $sheet->setCellValue('A2', 'ADC001');
        $sheet->setCellValue('B2', 'Suresh Yadav');
        $sheet->setCellValue('C2', '9988776655');
        $sheet->setCellValue('D2', 'suresh@example.com');
        $sheet->setCellValue('E2', '45 Ring Road, Mumbai');
        $sheet->setCellValue('F2', '400001');
        $sheet->setCellValue('G2', '27AABCU9603R1ZX');
        $sheet->setCellValue('H2', 'ABH654321');

        $writer = new Xlsx($spreadsheet);
        $filename = 'ADC_Customer_Import_Sample.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function adcCustomerPreview(Request $request)
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '-1');

        $request->validate([
            'file' => 'required|file|max:153600',
        ], [
            'file.required' => 'Please select an Excel or CSV file to upload.',
            'file.file'     => 'The uploaded item must be a valid file.',
            'file.max'      => 'The uploaded file size must not exceed 150 MB.',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv', 'txt'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file format (.' . $ext . '). Please upload an Excel (.xlsx, .xls) or CSV (.csv) file.',
            ], 422);
        }

        try {
            $rawRows = $this->readRawExcelRows($file);
            if (empty($rawRows)) {
                return response()->json(['success' => false, 'message' => 'The uploaded file is empty or missing content.'], 422);
            }
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Could not parse file: ' . $e->getMessage()], 422);
        }

        // Clean cell function
        $cleanCell = fn($v) => trim(str_replace(["\r", "\n", "\xC2\xA0", "\xA0"], ' ', (string)($v ?? '')));

        // Dynamic Header Matching
        $adcCol     = 'A';
        $nameCol    = 'B';
        $mobileCol  = 'C';
        $emailCol   = 'D';
        $addressCol = 'E';
        $pincodeCol = 'F';
        $gstCol     = 'G';
        $abhhyamCol = 'H';
        $rows       = $rawRows;

        if (!empty($rawRows[0])) {
            $headerCells = array_map(fn($v) => strtolower($cleanCell($v)), $rawRows[0]);
            $foundHeader = false;

            foreach ($headerCells as $colKey => $cellVal) {
                $cleanVal = preg_replace('/[^a-z0-9_]/', '', str_replace('*', '', $cellVal));
                if (in_array($cleanVal, ['adccode', 'adc', 'adcid'])) {
                    $adcCol = $colKey; $foundHeader = true;
                } elseif (in_array($cleanVal, ['customername', 'name', 'customer'])) {
                    $nameCol = $colKey; $foundHeader = true;
                } elseif (in_array($cleanVal, ['mobile', 'mobilecode', 'phone', 'contact'])) {
                    $mobileCol = $colKey; $foundHeader = true;
                } elseif (in_array($cleanVal, ['email', 'emailaddress'])) {
                    $emailCol = $colKey;
                } elseif (in_array($cleanVal, ['address', 'street'])) {
                    $addressCol = $colKey;
                } elseif (in_array($cleanVal, ['pincode', 'pin', 'zip', 'postal'])) {
                    $pincodeCol = $colKey;
                } elseif (in_array($cleanVal, ['gstnumber', 'gst', 'gstin'])) {
                    $gstCol = $colKey;
                } elseif (in_array($cleanVal, ['abhhyamcode', 'abhhyam'])) {
                    $abhhyamCol = $colKey;
                }
            }

            if ($foundHeader) {
                array_shift($rows);
            }
        }

        // Build ADC lookup: adc_code => user_id (from adc_profiles)
        $adcMap = DB::table('adc_profiles')
            ->whereNotNull('adc_code')
            ->join('users', 'users.id', '=', 'adc_profiles.user_id')
            ->select('adc_profiles.adc_code', 'adc_profiles.user_id', 'users.name as adc_name_col')
            ->get()
            ->mapWithKeys(fn($row) => [strtoupper(trim($row->adc_code)) => [
                'user_id'  => $row->user_id,
                'adc_name' => $row->adc_name_col,
            ]])
            ->toArray();

        $records  = [];

        foreach ($rows as $idx => $row) {
            $rowIndex = $idx + 2;

            $adcCode = $cleanCell($row[$adcCol] ?? '');
            $name    = $cleanCell($row[$nameCol] ?? '');
            $mobile  = $cleanCell($row[$mobileCol] ?? '');
            $email   = $cleanCell($row[$emailCol] ?? '');
            $address = $cleanCell($row[$addressCol] ?? '');
            $pincode = $cleanCell($row[$pincodeCol] ?? '');
            $gst     = $cleanCell($row[$gstCol] ?? '');
            $abhhyam = $cleanCell($row[$abhhyamCol] ?? '');

            // Skip fully empty rows
            if ($adcCode === '' && $name === '' && $mobile === '') continue;

            $errors  = [];
            $adcId   = null;
            $adcName = null;

            // Validate ADC code
            if ($adcCode === '') {
                $errors[] = 'ADC Code is required.';
            } else {
                $adcEntry = $adcMap[strtoupper($adcCode)] ?? null;
                if (!$adcEntry) {
                    $errors[] = "ADC Code '{$adcCode}' not found in system.";
                } else {
                    $adcId   = $adcEntry['user_id'];
                    $adcName = $adcEntry['adc_name'];
                }
            }

            // Validate Name
            if ($name === '') {
                $errors[] = 'Customer Name is required.';
            }

            // Validate Mobile
            if ($mobile === '') {
                $errors[] = 'Mobile is required.';
            } elseif (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
                $errors[] = "Mobile '{$mobile}' is invalid (must be 10-digit Indian number).";
            }

            // Validate Email
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email '{$email}' is invalid.";
            }

            // Validate Pincode
            if ($pincode !== '' && !preg_match('/^\d{6}$/', $pincode)) {
                $errors[] = "Pincode '{$pincode}' must be 6 digits.";
            }

            // GST format check
            if ($gst !== '' && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', strtoupper($gst))) {
                $errors[] = "GST Number '{$gst}' format is invalid.";
            }

            $records[] = [
                'row_number'   => $rowIndex,
                'adc_code'     => $adcCode,
                'adc_id'       => $adcId,
                'adc_name'     => $adcName,
                'name'         => $name,
                'mobile'       => $mobile,
                'email'        => $email ?: null,
                'address'      => $address ?: null,
                'pincode'      => $pincode ?: null,
                'gst_number'   => $gst ? strtoupper($gst) : null,
                'abhhyam_code' => $abhhyam ?: null,
                'is_valid'     => count($errors) === 0,
                'errors'       => $errors,
            ];
        }

        $valid  = count(array_filter($records, fn($r) => $r['is_valid']));
        $errors = count(array_filter($records, fn($r) => !$r['is_valid']));

        return response()->json([
            'success' => true,
            'total'   => count($records),
            'valid'   => $valid,
            'errors'  => $errors,
            'records' => $records,
        ]);
    }

    public function adcCustomerProcess(Request $request)
    {
        @set_time_limit(600);
        ini_set('memory_limit', '512M');

        $records = $request->input('records', []);
        if (empty($records)) {
            return response()->json(['success' => false, 'message' => 'No records to process.']);
        }

        $imported = 0;
        $skipped  = 0;
        $failed   = 0;
        $details  = [];

        // Pre-fetch all existing customers for the involved ADC IDs into a map to eliminate N+1 DB queries
        $validRecords = array_filter($records, fn($r) => !empty($r['is_valid']));
        $adcIds = array_unique(array_filter(array_column($validRecords, 'adc_id')));

        $existingMap = [];
        if (!empty($adcIds)) {
            $existingCustomers = DB::table('adc_pos_customers')
                ->whereIn('adc_id', $adcIds)
                ->get(['id', 'adc_id', 'mobile']);
            foreach ($existingCustomers as $ec) {
                $existingMap["{$ec->adc_id}:{$ec->mobile}"] = $ec->id;
            }
        }

        DB::beginTransaction();
        try {
            foreach ($records as $record) {
                if (!($record['is_valid'] ?? false)) {
                    $skipped++;
                    $errMsg = !empty($record['errors']) ? 'Validation Error: ' . implode(' | ', $record['errors']) : 'Row has validation errors — skipped.';
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => $record['mobile'] ?? '—',
                        'name'       => $record['name'] ?? '—',
                        'status'     => 'Skipped',
                        'message'    => $errMsg,
                    ];
                    continue;
                }

                $adcId  = $record['adc_id'];
                $mobile = $record['mobile'];
                $mapKey = "{$adcId}:{$mobile}";

                $existingId = $existingMap[$mapKey] ?? null;

                if ($existingId) {
                    // Update existing record
                    DB::table('adc_pos_customers')->where('id', $existingId)->update([
                        'name'         => $record['name'],
                        'email'        => $record['email'] ?? null,
                        'address'      => $record['address'] ?? null,
                        'pincode'      => $record['pincode'] ?? null,
                        'gst_number'   => $record['gst_number'] ?? null,
                        'abhhyam_code' => $record['abhhyam_code'] ?? null,
                        'updated_at'   => now(),
                    ]);
                    $skipped++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => $mobile,
                        'name'       => $record['name'],
                        'status'     => 'Skipped',
                        'message'    => "Customer with mobile '{$mobile}' already exists under this ADC — record updated.",
                    ];
                } else {
                    // Create new customer (bypass model observer for bulk import)
                    $newId = DB::table('adc_pos_customers')->insertGetId([
                        'adc_id'                 => $adcId,
                        'name'                   => $record['name'],
                        'mobile'                 => $mobile,
                        'email'                  => $record['email'] ?? null,
                        'address'                => $record['address'] ?? null,
                        'pincode'                => $record['pincode'] ?? null,
                        'gst_number'             => $record['gst_number'] ?? null,
                        'abhhyam_code'           => $record['abhhyam_code'] ?? null,
                        'total_credit_given'     => 0,
                        'total_payment_received' => 0,
                        'outstanding_balance'    => 0,
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ]);

                    // Store in map so any duplicate row in the same batch updates instead of failing
                    $existingMap[$mapKey] = $newId;

                    $imported++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => $mobile,
                        'name'       => $record['name'],
                        'status'     => 'Imported',
                        'message'    => "Customer '{$record['name']}' created under ADC '{$record['adc_code']}'.",
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'imported' => $imported,
                'skipped'  => $skipped,
                'failed'   => $failed,
                'details'  => $details,
                'message'  => "Import completed: {$imported} customers created, {$skipped} skipped/updated.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // DDC MSME STOCK INVENTORY IMPORT
    // =========================================================================

    public function ddcMsmeInventoryIndex()
    {
        $categories = Category::orderBy('name')->get();
        $units      = UnitOfMeasure::getUnitsList();
        $products   = Product::whereNotNull('product_code')->select('product_code', 'product_name', 'mrp')->orderBy('product_code')->take(500)->get();
        $ddcs       = DB::table('ddc_profiles')
            ->join('users', 'users.id', '=', 'ddc_profiles.user_id')
            ->select('ddc_profiles.ddc_code', 'users.name')
            ->whereNotNull('ddc_profiles.ddc_code')
            ->orderBy('ddc_profiles.ddc_code')
            ->get();

        return view('admin.import.ddc_msme_inventory.index', compact('categories', 'units', 'products', 'ddcs'));
    }

    public function ddcMsmeInventorySample()
    {
        $spreadsheet = new Spreadsheet();

        // Fetch master data for dropdowns & reference tab
        $categories  = Category::orderBy('name')->pluck('name')->toArray();
        $units       = UnitOfMeasure::getUnitsList();
        $products    = Product::whereNotNull('product_code')->select('product_code', 'product_name')->orderBy('product_code')->take(300)->get();
        $ddcProfiles = DB::table('ddc_profiles')
            ->join('users', 'users.id', '=', 'ddc_profiles.user_id')
            ->select('ddc_profiles.ddc_code', 'users.name')
            ->whereNotNull('ddc_profiles.ddc_code')
            ->orderBy('ddc_profiles.ddc_code')
            ->get();

        $ddcCodes     = $ddcProfiles->pluck('ddc_code')->toArray();
        $productCodes = $products->pluck('product_code')->toArray();

        // ---------------------------------------------------------------------
        // Sheet 1: Main DDC MSME Inventory Import Sheet
        // ---------------------------------------------------------------------
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DDC MSME Inventory Import');

        // All 23 columns covering every ddc_inventories field
        $headers = [
            'A1' => 'DDC Code*',
            'B1' => 'Product Code*',
            'C1' => 'Product Name (override)',
            'D1' => 'Barcode',
            'E1' => 'Unit of Measure',
            'F1' => 'Quantity*',
            'G1' => 'Reserved Qty',
            'H1' => 'Sold Qty',
            'I1' => 'Returned Qty',
            'J1' => 'MRP*',
            'K1' => 'Purchase Price',
            'L1' => 'Landing Cost',
            'M1' => 'MSP',
            'N1' => 'CSP',
            'O1' => 'Margin',
            'P1' => 'ADC Margin %',
            'Q1' => 'CSP Margin %',
            'R1' => 'GST Rate %',
            'S1' => 'Mfg Date (YYYY-MM-DD)',
            'T1' => 'Expire Date (YYYY-MM-DD)',
            'U1' => 'Batch Number',
            'V1' => 'Reference Type',
            'W1' => 'POS Status',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '92400e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:W1')->applyFromArray($headerStyle);

        foreach (range('A', 'W') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Sample row with all fields
        $sampleDdc  = !empty($ddcCodes) ? $ddcCodes[0] : 'DDB001';
        $sampleProd = !empty($productCodes) ? $productCodes[0] : 'IMV-PRD-0001';
        $sampleUom  = !empty($units) ? $units[0] : 'Piece';

        $sheet->setCellValue('A2', $sampleDdc);
        $sheet->setCellValue('B2', $sampleProd);
        $sheet->setCellValue('C2', '');              // product name override (leave blank to use catalog name)
        $sheet->setCellValue('D2', '8901234567890'); // barcode
        $sheet->setCellValue('E2', $sampleUom);      // unit_of_measure dropdown
        $sheet->setCellValue('F2', 100);             // quantity
        $sheet->setCellValue('G2', 0);               // reserved_quantity
        $sheet->setCellValue('H2', 0);               // sold_quantity
        $sheet->setCellValue('I2', 0);               // returned_quantity
        $sheet->setCellValue('J2', 250.00);          // mrp
        $sheet->setCellValue('K2', 200.00);          // purchase_price
        $sheet->setCellValue('L2', 200.00);          // landing_cost
        $sheet->setCellValue('M2', 210.00);          // msp
        $sheet->setCellValue('N2', 220.00);          // csp
        $sheet->setCellValue('O2', 50.00);           // margin
        $sheet->setCellValue('P2', 5);               // adc_margin %
        $sheet->setCellValue('Q2', 3);               // csp_margin %
        $sheet->setCellValue('R2', 18);              // gst_rate %
        $sheet->setCellValue('S2', '2026-01-01');    // mfg_date
        $sheet->setCellValue('T2', '2027-12-31');    // expire_date
        $sheet->setCellValue('U2', 'BATCH-2026-01'); // batch_number
        $sheet->setCellValue('V2', 'MSME PO');       // reference_type
        $sheet->setCellValue('W2', 'active');        // pos_status

        // Notes row
        $sheet->setCellValue('A3', '-- Notes --');
        $sheet->setCellValue('B3', 'Select from Product Code dropdown or Reference Data sheet');
        $sheet->setCellValue('C3', 'If blank, auto-filled from catalog');
        $sheet->setCellValue('E3', 'Select from UOM dropdown (PCS, KG, LTR, Box, etc.)');
        $sheet->setCellValue('V3', 'MSME PO or Local Purchase');
        $sheet->setCellValue('W3', 'active or inactive');

        $noteStyle = [
            'font' => ['italic' => true, 'color' => ['rgb' => '6b7280']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f9fafb']],
        ];
        $sheet->getStyle('A3:W3')->applyFromArray($noteStyle);

        // ---------------------------------------------------------------------
        // Sheet 2: Reference Data Master Tab
        // ---------------------------------------------------------------------
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Reference Data');
        $refSheet->setCellValue('A1', 'DDC Code');
        $refSheet->setCellValue('B1', 'Product Code');
        $refSheet->setCellValue('C1', 'Product Name');
        $refSheet->setCellValue('D1', 'Units of Measure (UOM)');
        $refSheet->setCellValue('E1', 'Categories List');

        $refSheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '92400e']],
        ]);

        foreach ($ddcCodes as $idx => $code) {
            $refSheet->setCellValue('A' . ($idx + 2), $code);
        }
        foreach ($products as $idx => $p) {
            $refSheet->setCellValue('B' . ($idx + 2), $p->product_code);
            $refSheet->setCellValue('C' . ($idx + 2), $p->product_name);
        }
        foreach ($units as $idx => $uName) {
            $refSheet->setCellValue('D' . ($idx + 2), $uName);
        }
        foreach ($categories as $idx => $cName) {
            $refSheet->setCellValue('E' . ($idx + 2), $cName);
        }

        foreach (range('A', 'E') as $col) {
            $refSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Return to main sheet
        $spreadsheet->setActiveSheetIndex(0);

        // ---------------------------------------------------------------------
        // Add Data Validation Dropdowns to Main Sheet (Rows 2 to 200)
        // ---------------------------------------------------------------------
        $ddcEndRow  = max(count($ddcCodes) + 1, 2);
        $prodEndRow = max(count($productCodes) + 1, 2);
        $uomEndRow  = max(count($units) + 1, 2);

        for ($r = 2; $r <= 200; $r++) {
            // DDC Code Dropdown (Col A)
            if (!empty($ddcCodes)) {
                $ddcValidation = $sheet->getCell("A{$r}")->getDataValidation();
                $ddcValidation->setType(DataValidation::TYPE_LIST);
                $ddcValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $ddcValidation->setAllowBlank(true);
                $ddcValidation->setShowInputMessage(true);
                $ddcValidation->setShowErrorMessage(true);
                $ddcValidation->setShowDropDown(true);
                $ddcValidation->setFormula1("'Reference Data'!\$A\$2:\$A\${$ddcEndRow}");
            }

            // Product Code Dropdown (Col B)
            if (!empty($productCodes)) {
                $prodValidation = $sheet->getCell("B{$r}")->getDataValidation();
                $prodValidation->setType(DataValidation::TYPE_LIST);
                $prodValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $prodValidation->setAllowBlank(true);
                $prodValidation->setShowInputMessage(true);
                $prodValidation->setShowErrorMessage(true);
                $prodValidation->setShowDropDown(true);
                $prodValidation->setFormula1("'Reference Data'!\$B\$2:\$B\${$prodEndRow}");
            }

            // UOM Dropdown (Col E)
            $uomValidation = $sheet->getCell("E{$r}")->getDataValidation();
            $uomValidation->setType(DataValidation::TYPE_LIST);
            $uomValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $uomValidation->setAllowBlank(true);
            $uomValidation->setShowInputMessage(true);
            $uomValidation->setShowErrorMessage(true);
            $uomValidation->setShowDropDown(true);
            $uomValidation->setFormula1("'Reference Data'!\$D\$2:\$D\${$uomEndRow}");

            // POS Status Dropdown (Col W)
            $posValidation = $sheet->getCell("W{$r}")->getDataValidation();
            $posValidation->setType(DataValidation::TYPE_LIST);
            $posValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $posValidation->setAllowBlank(true);
            $posValidation->setShowInputMessage(true);
            $posValidation->setShowErrorMessage(true);
            $posValidation->setShowDropDown(true);
            $posValidation->setFormula1('"active,inactive"');
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'DDC_MSME_Inventory_Import_Sample.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function ddcMsmeInventoryPreview(Request $request)
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(3600);
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv,txt|max:153600']);

        try {
            $rows = $this->readRawExcelRows($request->file('file'));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not parse Excel file: ' . $e->getMessage()]);
        }

        // Build DDC lookup: ddc_code => {user_id, ddc_name}
        $ddcMap = DB::table('ddc_profiles')
            ->whereNotNull('ddc_code')
            ->join('users', 'users.id', '=', 'ddc_profiles.user_id')
            ->select('ddc_profiles.ddc_code', 'ddc_profiles.user_id', 'users.name as ddc_name_col')
            ->get()
            ->mapWithKeys(fn($row) => [strtoupper(trim($row->ddc_code)) => [
                'user_id'  => $row->user_id,
                'ddc_name' => $row->ddc_name_col,
            ]])
            ->toArray();

        // Build Product lookup: product_code => product object
        $productMap = DB::table('products')
            ->whereNotNull('product_code')
            ->select('id', 'product_code', 'product_name', 'mrp', 'ddc_purchase_price', 'adc_purchase_price', 'gst_rate')
            ->get()
            ->mapWithKeys(fn($p) => [strtoupper(trim($p->product_code)) => $p])
            ->toArray();

        $records  = [];
        $rowIndex = 0;

        foreach ($rows as $lineNum => $row) {
            $firstCol = strtoupper(trim($row['A'] ?? ''));
            if ($lineNum === 0 || in_array($firstCol, ['DDC CODE', 'DDC CODE*']) || str_contains($firstCol, 'DDC CODE') || str_starts_with($firstCol, '--')) continue; // skip header/notes
            $rowIndex++;

            // Parse all 23 columns
            $ddcCode      = trim($row['A'] ?? '');
            $productCode  = trim($row['B'] ?? '');
            $productNameOverride = trim($row['C'] ?? '');
            $barcode      = trim($row['D'] ?? '');
            $uom          = trim($row['E'] ?? '');
            $quantity     = trim($row['F'] ?? '');
            $reservedQty  = trim($row['G'] ?? '');
            $soldQty      = trim($row['H'] ?? '');
            $returnedQty  = trim($row['I'] ?? '');
            $mrp          = trim($row['J'] ?? '');
            $purchasePrice = trim($row['K'] ?? '');
            $landingCost  = trim($row['L'] ?? '');
            $msp          = trim($row['M'] ?? '');
            $csp          = trim($row['N'] ?? '');
            $margin       = trim($row['O'] ?? '');
            $adcMargin    = trim($row['P'] ?? '');
            $cspMargin    = trim($row['Q'] ?? '');
            $gstRate      = trim($row['R'] ?? '');
            $mfgDate      = trim($row['S'] ?? '');
            $expireDate   = trim($row['T'] ?? '');
            $batchNumber  = trim($row['U'] ?? '');
            $referenceType = trim($row['V'] ?? '');
            $posStatus    = trim($row['W'] ?? '');

            // Skip fully empty rows
            if ($ddcCode === '' && $productCode === '' && $quantity === '') continue;

            $errors      = [];
            $ddcId       = null;
            $ddcName     = null;
            $productId   = null;
            $productName = null;

            // Validate DDC Code
            if ($ddcCode === '') {
                $defaultDdcEntry = !empty($ddcMap) ? reset($ddcMap) : null;
                $ddcId   = $defaultDdcEntry['user_id'] ?? 1;
                $ddcName = $defaultDdcEntry['ddc_name'] ?? 'System DDC';
                $ddcCode = !empty($ddcMap) ? (string)array_key_first($ddcMap) : 'DDC001';
            } else {
                $ddcEntry = $ddcMap[strtoupper($ddcCode)] ?? null;
                if (!$ddcEntry) {
                    $defaultDdcEntry = !empty($ddcMap) ? reset($ddcMap) : null;
                    $ddcId   = $defaultDdcEntry['user_id'] ?? 1;
                    $ddcName = $defaultDdcEntry['ddc_name'] ?? 'System DDC';
                } else {
                    $ddcId   = $ddcEntry['user_id'];
                    $ddcName = $ddcEntry['ddc_name'];
                }
            }

            // Validate Product Code (Optional catalog match)
            if ($productCode === '' && $productNameOverride === '') {
                $productId   = null;
                $productName = 'Item Row ' . $rowIndex;
            } else {
                $prod = $productCode !== '' ? ($productMap[strtoupper($productCode)] ?? null) : null;
                if ($prod) {
                    $productId   = $prod->id;
                    $productName = $productNameOverride !== '' ? $productNameOverride : ($prod->product_name ?? $productCode);
                } else {
                    // No match required: product can be imported even if not in central catalog
                    $productId   = null;
                    $productName = $productNameOverride !== '' ? $productNameOverride : $productCode;
                }
            }

            // Graceful parsing with fallbacks (no blocking validation errors)
            $quantityFinal = (is_numeric($quantity) && (float)$quantity >= 0) ? (float)$quantity : 0.0;

            $mrpFinal = 0.0;
            if (is_numeric($mrp) && (float)$mrp > 0) {
                $mrpFinal = (float)$mrp;
            } elseif (is_numeric($purchasePrice) && (float)$purchasePrice > 0) {
                $mrpFinal = (float)$purchasePrice;
            } elseif (is_numeric($landingCost) && (float)$landingCost > 0) {
                $mrpFinal = (float)$landingCost;
            }

            $mfgDateFinal = $mfgDate !== '' ? $this->parseExcelDate($mfgDate) : null;
            $expireDateFinal = $expireDate !== '' ? $this->parseExcelDate($expireDate) : null;

            $posStatusFinal = ($posStatus !== '' && in_array(strtolower($posStatus), ['active', 'inactive'])) ? strtolower($posStatus) : 'active';

            // Validate reference_type
            $referenceTypeFinal = 'MSME PO';
            if ($referenceType !== '') {
                if (!in_array($referenceType, ['MSME PO', 'Local Purchase'])) {
                    $errors[] = "Reference Type '{$referenceType}' must be 'MSME PO' or 'Local Purchase'.";
                } else {
                    $referenceTypeFinal = $referenceType;
                }
            }

            $marginFinal = $margin !== '' ? (float)$margin : null;
            $landingCostFinal = $landingCost !== '' ? (float)$landingCost : null;

            $records[] = [
                'row_number'      => $rowIndex,
                'ddc_code'        => $ddcCode,
                'ddc_id'          => $ddcId,
                'ddc_name'        => $ddcName,
                'product_code'    => $productCode,
                'product_id'      => $productId,
                'product_name'    => $productName,
                'barcode'         => $barcode ?: null,
                'unit_of_measure' => $this->normalizeUom($uom, 'Piece'),
                'quantity'        => $quantity !== '' ? (float)$quantity : 0,
                'reserved_qty'    => $reservedQty !== '' ? (float)$reservedQty : 0,
                'sold_qty'        => $soldQty !== '' ? (float)$soldQty : 0,
                'returned_qty'    => $returnedQty !== '' ? (float)$returnedQty : 0,
                'mrp'             => $mrp !== '' ? (float)$mrp : null,
                'purchase_price'  => $purchasePrice !== '' ? (float)$purchasePrice : null,
                'landing_cost'    => $landingCostFinal,
                'msp'             => $msp !== '' ? (float)$msp : null,
                'csp'             => $csp !== '' ? (float)$csp : null,
                'margin'          => $marginFinal,
                'adc_margin'      => $adcMargin !== '' ? (float)$adcMargin : 0,
                'csp_margin'      => $cspMargin !== '' ? (float)$cspMargin : 0,
                'gst_rate'        => $gstRate !== '' ? (float)$gstRate : 0,
                'mfg_date'        => $mfgDateFinal,
                'expire_date'     => $expireDateFinal,
                'batch_number'    => $batchNumber ?: null,
                'reference_type'  => $referenceTypeFinal,
                'pos_status'      => $posStatusFinal,
                'is_valid'        => count($errors) === 0,
                'errors'          => $errors,
            ];
        }

        $valid      = count(array_filter($records, fn($r) => $r['is_valid']));
        $errorCount = count(array_filter($records, fn($r) => !$r['is_valid']));

        return response()->json([
            'success' => true,
            'total'   => count($records),
            'valid'   => $valid,
            'errors'  => $errorCount,
            'records' => $records,
        ]);
    }

    public function ddcMsmeInventoryProcess(Request $request)
    {
        $records = $request->input('records', []);
        if (empty($records)) {
            return response()->json(['success' => false, 'message' => 'No records to process.']);
        }

        $imported = 0;
        $skipped  = 0;
        $failed   = 0;
        $details  = [];

        DB::beginTransaction();
        try {
            foreach ($records as $record) {
                if (!($record['is_valid'] ?? false)) {
                    $skipped++;
                    $errMsg = !empty($record['errors']) ? 'Validation Error: ' . implode(' | ', $record['errors']) : 'Row has validation errors — skipped.';
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => ($record['ddc_code'] ?? '—') . ' / ' . ($record['product_code'] ?? '—'),
                        'name'       => $record['product_name'] ?? '—',
                        'status'     => 'Skipped',
                        'message'    => $errMsg,
                    ];
                    continue;
                }

                try {
                    DB::table('ddc_inventories')->insert([
                        'ddc_id'                 => $record['ddc_id'],
                        'inventory_source'       => 'MSME',
                        'product_id'             => $record['product_id'],
                        'ddc_local_product_id'   => null,
                        'product_name'           => $record['product_name'],
                        'product_code'           => $record['product_code'],
                        'barcode'                => $record['barcode'] ?? null,
                        'unit_of_measure'        => $this->normalizeUom($record['unit_of_measure'] ?? null, 'Piece'),
                        'quantity'               => (float)($record['quantity'] ?? 0),
                        'reserved_quantity'      => (float)($record['reserved_qty'] ?? 0),
                        'sold_quantity'          => (float)($record['sold_qty'] ?? 0),
                        'returned_quantity'      => (float)($record['returned_qty'] ?? 0),
                        'mrp'                    => (float)($record['mrp'] ?? 0),
                        'purchase_price'         => (float)($record['purchase_price'] ?? 0),
                        'landing_cost'           => (float)($record['landing_cost'] ?? 0),
                        'msp'                    => (float)($record['msp'] ?? 0),
                        'csp'                    => (float)($record['csp'] ?? 0),
                        'margin'                 => (float)($record['margin'] ?? 0),
                        'adc_margin'             => (float)($record['adc_margin'] ?? 0),
                        'csp_margin'             => (float)($record['csp_margin'] ?? 0),
                        'gst_rate'               => (float)($record['gst_rate'] ?? 0),
                        'mfg_date'               => $record['mfg_date'] ?: null,
                        'expire_date'            => $record['expire_date'] ?: null,
                        'batch_number'           => $record['batch_number'] ?? null,
                        'purchase_order_id'      => null,
                        'purchase_order_item_id' => null,
                        'reference_type'         => $record['reference_type'] ?? 'MSME PO',
                        'reference_id'           => null,
                        'pos_status'             => $record['pos_status'] ?? 'active',
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ]);

                    $imported++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => ($record['ddc_code'] ?? '—') . ' / ' . ($record['product_code'] ?? '—'),
                        'name'       => $record['product_name'],
                        'status'     => 'Imported',
                        'message'    => "Stock of {$record['quantity']} {$record['unit_of_measure']} for '{$record['product_name']}' added to DDC '{$record['ddc_code']}' inventory.",
                    ];
                } catch (\Exception $rowEx) {
                    $failed++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => ($record['ddc_code'] ?? '—') . ' / ' . ($record['product_code'] ?? '—'),
                        'name'       => $record['product_name'] ?? '—',
                        'status'     => 'Failed',
                        'message'    => 'DB Error: ' . $rowEx->getMessage(),
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'imported' => $imported,
                'skipped'  => $skipped,
                'failed'   => $failed,
                'details'  => $details,
                'message'  => "Import completed: {$imported} inventory rows added, {$skipped} skipped, {$failed} failed.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // DDC LOCAL / REGIONAL INVENTORY IMPORT
    // =========================================================================

    public function ddcLocalInventoryIndex()
    {
        $categories = Category::orderBy('name')->get();
        $units      = UnitOfMeasure::getUnitsList();
        $brands     = Brand::orderBy('name')->get();
        $ddcs       = DB::table('ddc_profiles')
            ->join('users', 'users.id', '=', 'ddc_profiles.user_id')
            ->select('ddc_profiles.ddc_code', 'users.name')
            ->whereNotNull('ddc_profiles.ddc_code')
            ->orderBy('ddc_profiles.ddc_code')
            ->get();

        return view('admin.import.ddc_local_inventory.index', compact('categories', 'units', 'brands', 'ddcs'));
    }

    public function ddcLocalInventorySample()
    {
        $spreadsheet = new Spreadsheet();

        // Fetch master data for dropdowns & reference tab
        $categories = Category::orderBy('name')->pluck('name')->toArray();
        $units      = UnitOfMeasure::getUnitsList();
        $brands     = Brand::orderBy('name')->pluck('name')->toArray();

        // ---------------------------------------------------------------------
        // Sheet 1: Main Import Sheet
        // ---------------------------------------------------------------------
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DDC Local Inventory Import');

        // All 22 columns covering DDC Local Product & ddc_inventories fields
        $headers = [
            'A1' => 'DDC Code*',
            'B1' => 'Product Name*',
            'C1' => 'Local Product Code',
            'D1' => 'Category (Name or ID)',
            'E1' => 'HSN Code',
            'F1' => 'Barcode',
            'G1' => 'Unit of Measure',
            'H1' => 'Quantity*',
            'I1' => 'MRP*',
            'J1' => 'Purchase Price',
            'K1' => 'Landing Cost',
            'L1' => 'MSP',
            'M1' => 'CSP',
            'N1' => 'Margin',
            'O1' => 'ADC Margin %',
            'P1' => 'CSP Margin %',
            'Q1' => 'GST Rate %',
            'R1' => 'Mfg Date (YYYY-MM-DD)',
            'S1' => 'Expire Date (YYYY-MM-DD)',
            'T1' => 'Batch Number',
            'U1' => 'Reference Type',
            'V1' => 'POS Status',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0f766e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:V1')->applyFromArray($headerStyle);

        foreach (range('A', 'V') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Sample row values
        $sampleCat = !empty($categories) ? $categories[0] : 'Grocery';
        $sampleUom = !empty($units) ? $units[0] : 'LTR';

        $sheet->setCellValue('A2', 'DDB001');
        $sheet->setCellValue('B2', 'Local Organic Mustard Oil 1L');
        $sheet->setCellValue('C2', 'LCL000001');     // local product code
        $sheet->setCellValue('D2', $sampleCat);      // category dropdown value
        $sheet->setCellValue('E2', '1514');           // hsn code
        $sheet->setCellValue('F2', '8901234567890'); // barcode
        $sheet->setCellValue('G2', $sampleUom);      // unit_of_measure dropdown value
        $sheet->setCellValue('H2', 50);              // quantity
        $sheet->setCellValue('I2', 180.00);          // mrp
        $sheet->setCellValue('J2', 140.00);          // purchase_price
        $sheet->setCellValue('K2', 140.00);          // landing_cost
        $sheet->setCellValue('L2', 150.00);          // msp
        $sheet->setCellValue('M2', 160.00);          // csp
        $sheet->setCellValue('N2', 40.00);           // margin
        $sheet->setCellValue('O2', 5);               // adc_margin %
        $sheet->setCellValue('P2', 3);               // csp_margin %
        $sheet->setCellValue('Q2', 5);               // gst_rate %
        $sheet->setCellValue('R2', '2026-01-01');    // mfg_date
        $sheet->setCellValue('S2', '2027-12-31');    // expire_date
        $sheet->setCellValue('T2', 'BATCH-LCL-01');  // batch_number
        $sheet->setCellValue('U2', 'Local Purchase'); // reference_type
        $sheet->setCellValue('V2', 'active');        // pos_status

        // Notes row
        $sheet->setCellValue('A3', '-- Notes --');
        $sheet->setCellValue('B3', 'Name of regional/local product');
        $sheet->setCellValue('C3', 'Optional. If blank, auto-generated (LCL000001...)');
        $sheet->setCellValue('D3', 'Select from Category dropdown or Reference Data sheet');
        $sheet->setCellValue('G3', 'Select from UOM dropdown (PCS, KG, LTR, Box, etc.)');
        $sheet->setCellValue('U3', 'Local Purchase or Direct Stock');
        $sheet->setCellValue('V3', 'active or inactive');

        $noteStyle = [
            'font' => ['italic' => true, 'color' => ['rgb' => '6b7280']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f9fafb']],
        ];
        $sheet->getStyle('A3:V3')->applyFromArray($noteStyle);

        // ---------------------------------------------------------------------
        // Sheet 2: Reference Data Master Tab
        // ---------------------------------------------------------------------
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Reference Data');
        $refSheet->setCellValue('A1', 'Categories List');
        $refSheet->setCellValue('B1', 'Units of Measure (UOM)');
        $refSheet->setCellValue('C1', 'Brands List');

        $refSheet->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']],
        ]);

        foreach ($categories as $idx => $cName) {
            $refSheet->setCellValue('A' . ($idx + 2), $cName);
        }
        foreach ($units as $idx => $uName) {
            $refSheet->setCellValue('B' . ($idx + 2), $uName);
        }
        foreach ($brands as $idx => $bName) {
            $refSheet->setCellValue('C' . ($idx + 2), $bName);
        }

        foreach (range('A', 'C') as $col) {
            $refSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Return to main sheet
        $spreadsheet->setActiveSheetIndex(0);

        // ---------------------------------------------------------------------
        // Add Data Validation Dropdowns to Main Sheet (Rows 2 to 200)
        // ---------------------------------------------------------------------
        $catEndRow = max(count($categories) + 1, 2);
        $uomEndRow = max(count($units) + 1, 2);

        for ($r = 2; $r <= 200; $r++) {
            // Category Dropdown (Col D)
            $catValidation = $sheet->getCell("D{$r}")->getDataValidation();
            $catValidation->setType(DataValidation::TYPE_LIST);
            $catValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $catValidation->setAllowBlank(true);
            $catValidation->setShowInputMessage(true);
            $catValidation->setShowErrorMessage(true);
            $catValidation->setShowDropDown(true);
            $catValidation->setFormula1("'Reference Data'!\$A\$2:\$A\${$catEndRow}");

            // UOM Dropdown (Col G)
            $uomValidation = $sheet->getCell("G{$r}")->getDataValidation();
            $uomValidation->setType(DataValidation::TYPE_LIST);
            $uomValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $uomValidation->setAllowBlank(true);
            $uomValidation->setShowInputMessage(true);
            $uomValidation->setShowErrorMessage(true);
            $uomValidation->setShowDropDown(true);
            $uomValidation->setFormula1("'Reference Data'!\$B\$2:\$B\${$uomEndRow}");

            // POS Status Dropdown (Col V)
            $posValidation = $sheet->getCell("V{$r}")->getDataValidation();
            $posValidation->setType(DataValidation::TYPE_LIST);
            $posValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $posValidation->setAllowBlank(true);
            $posValidation->setShowInputMessage(true);
            $posValidation->setShowErrorMessage(true);
            $posValidation->setShowDropDown(true);
            $posValidation->setFormula1('"active,inactive"');
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'DDC_Local_Inventory_Import_Sample.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function ddcLocalInventoryPreview(Request $request)
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(3600);
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv,txt|max:153600']);

        try {
            $rows = $this->readRawExcelRows($request->file('file'));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not parse Excel file: ' . $e->getMessage()]);
        }

        // Build DDC lookup: ddc_code => {user_id, ddc_name}
        $ddcMap = DB::table('ddc_profiles')
            ->whereNotNull('ddc_code')
            ->join('users', 'users.id', '=', 'ddc_profiles.user_id')
            ->select('ddc_profiles.ddc_code', 'ddc_profiles.user_id', 'users.name as ddc_name_col')
            ->get()
            ->mapWithKeys(fn($row) => [strtoupper(trim($row->ddc_code)) => [
                'user_id'  => $row->user_id,
                'ddc_name' => $row->ddc_name_col,
            ]])
            ->toArray();

        // Build Category lookup
        $categories = Category::select('id', 'name')->get();
        $catMapByName = $categories->mapWithKeys(fn($c) => [strtolower(trim($c->name)) => $c->id])->toArray();
        $catMapById   = $categories->pluck('id', 'id')->toArray();
        $defaultCatId = $categories->first()?->id ?: 1;

        $records  = [];
        $rowIndex = 0;

        foreach ($rows as $lineNum => $row) {
            $firstCol = strtoupper(trim($row['A'] ?? ''));
            if ($lineNum === 0 || in_array($firstCol, ['DDC CODE', 'DDC CODE*']) || str_contains($firstCol, 'DDC CODE') || str_starts_with($firstCol, '--')) continue; // skip header/notes
            $rowIndex++;

            // Parse columns A to V
            $ddcCode          = trim($row['A'] ?? '');
            $productName      = trim($row['B'] ?? '');
            $localProductCode = trim($row['C'] ?? '');
            $categoryInput    = trim($row['D'] ?? '');
            $hsnCode          = trim($row['E'] ?? '');
            $barcode          = trim($row['F'] ?? '');
            $uom              = trim($row['G'] ?? '');
            $quantity         = trim($row['H'] ?? '');
            $mrp              = trim($row['I'] ?? '');
            $purchasePrice    = trim($row['J'] ?? '');
            $landingCost      = trim($row['K'] ?? '');
            $msp              = trim($row['L'] ?? '');
            $csp              = trim($row['M'] ?? '');
            $margin           = trim($row['N'] ?? '');
            $adcMargin        = trim($row['O'] ?? '');
            $cspMargin        = trim($row['P'] ?? '');
            $gstRate          = trim($row['Q'] ?? '');
            $mfgDate          = trim($row['R'] ?? '');
            $expireDate       = trim($row['S'] ?? '');
            $batchNumber      = trim($row['T'] ?? '');
            $referenceType    = trim($row['U'] ?? '');
            $posStatus        = trim($row['V'] ?? '');

            // Skip fully empty rows
            if ($ddcCode === '' && $productName === '' && $quantity === '') continue;

            $errors  = [];
            $ddcId   = null;
            $ddcName = null;

            // Validate DDC Code
            if ($ddcCode === '') {
                $defaultDdcEntry = !empty($ddcMap) ? reset($ddcMap) : null;
                $ddcId   = $defaultDdcEntry['user_id'] ?? 1;
                $ddcName = $defaultDdcEntry['ddc_name'] ?? 'System DDC';
                $ddcCode = !empty($ddcMap) ? (string)array_key_first($ddcMap) : 'DDC001';
            } else {
                $ddcEntry = $ddcMap[strtoupper($ddcCode)] ?? null;
                if (!$ddcEntry) {
                    $defaultDdcEntry = !empty($ddcMap) ? reset($ddcMap) : null;
                    $ddcId   = $defaultDdcEntry['user_id'] ?? 1;
                    $ddcName = $defaultDdcEntry['ddc_name'] ?? 'System DDC';
                } else {
                    $ddcId   = $ddcEntry['user_id'];
                    $ddcName = $ddcEntry['ddc_name'];
                }
            }

            // Validate Product Name
            if ($productName === '') {
                $productName = 'Local Item Row ' . $rowIndex;
            }

            // Resolve Category ID
            $categoryId = $defaultCatId;
            if ($categoryInput !== '') {
                if (isset($catMapById[(int)$categoryInput])) {
                    $categoryId = (int)$categoryInput;
                } elseif (isset($catMapByName[strtolower($categoryInput)])) {
                    $categoryId = $catMapByName[strtolower($categoryInput)];
                }
            }

            // Graceful parsing with fallbacks (no blocking validation errors)
            $quantityFinal = (is_numeric($quantity) && (float)$quantity >= 0) ? (float)$quantity : 0.0;

            $mrpFinal = 0.0;
            if (is_numeric($mrp) && (float)$mrp > 0) {
                $mrpFinal = (float)$mrp;
            } elseif (is_numeric($purchasePrice) && (float)$purchasePrice > 0) {
                $mrpFinal = (float)$purchasePrice;
            } elseif (is_numeric($landingCost) && (float)$landingCost > 0) {
                $mrpFinal = (float)$landingCost;
            }

            $mfgDateFinal = $mfgDate !== '' ? $this->parseExcelDate($mfgDate) : null;
            $expireDateFinal = $expireDate !== '' ? $this->parseExcelDate($expireDate) : null;

            $posStatusFinal = ($posStatus !== '' && in_array(strtolower($posStatus), ['active', 'inactive'])) ? strtolower($posStatus) : 'active';

            // Validate reference_type
            $referenceTypeFinal = 'Local Purchase';
            if ($referenceType !== '') {
                $referenceTypeFinal = $referenceType;
            }

            // Auto-calculate margin if not provided but purchase_price & mrp are present
            $marginFinal = $margin !== '' ? (float)$margin : null;
            if ($marginFinal === null && $purchasePrice !== '' && $mrp !== '') {
                $marginFinal = round((float)$mrp - (float)$purchasePrice, 2);
            }

            // landing_cost defaults to purchase_price if blank
            $landingCostFinal = $landingCost !== '' ? (float)$landingCost
                : ($purchasePrice !== '' ? (float)$purchasePrice : null);

            $records[] = [
                'row_number'         => $rowIndex,
                'ddc_code'           => $ddcCode,
                'ddc_id'             => $ddcId,
                'ddc_name'           => $ddcName,
                'product_name'       => $productName,
                'local_product_code' => $localProductCode,
                'category_id'        => $categoryId,
                'category_input'     => $categoryInput,
                'hsn_code'           => $hsnCode ?: null,
                'barcode'            => $barcode ?: null,
                'unit_of_measure'    => $this->normalizeUom($uom, 'Piece'),
                'quantity'           => $quantity !== '' ? (float)$quantity : 0,
                'reserved_qty'       => 0,
                'sold_qty'           => 0,
                'returned_qty'       => 0,
                'mrp'                => $mrp !== '' ? (float)$mrp : null,
                'purchase_price'     => $purchasePrice !== '' ? (float)$purchasePrice : null,
                'landing_cost'       => $landingCostFinal,
                'msp'                => $msp !== '' ? (float)$msp : null,
                'csp'                => $csp !== '' ? (float)$csp : null,
                'margin'             => $marginFinal,
                'adc_margin'         => $adcMargin !== '' ? (float)$adcMargin : 0,
                'csp_margin'         => $cspMargin !== '' ? (float)$cspMargin : 0,
                'gst_rate'           => $gstRate !== '' ? (float)$gstRate : 0,
                'mfg_date'           => $mfgDateFinal,
                'expire_date'        => $expireDateFinal,
                'batch_number'       => $batchNumber ?: null,
                'reference_type'     => $referenceTypeFinal,
                'pos_status'         => $posStatusFinal,
                'is_valid'           => count($errors) === 0,
                'errors'             => $errors,
            ];
        }

        $valid      = count(array_filter($records, fn($r) => $r['is_valid']));
        $errorCount = count(array_filter($records, fn($r) => !$r['is_valid']));

        return response()->json([
            'success' => true,
            'total'   => count($records),
            'valid'   => $valid,
            'errors'  => $errorCount,
            'records' => $records,
        ]);
    }

    public function ddcLocalInventoryProcess(Request $request)
    {
        $records = $request->input('records', []);
        if (empty($records)) {
            return response()->json(['success' => false, 'message' => 'No records to process.']);
        }

        $imported = 0;
        $skipped  = 0;
        $failed   = 0;
        $details  = [];

        $localProductMap = [];

        DB::beginTransaction();
        try {
            foreach ($records as $record) {
                if (!($record['is_valid'] ?? false)) {
                    $skipped++;
                    $errMsg = !empty($record['errors']) ? 'Validation Error: ' . implode(' | ', $record['errors']) : 'Row has validation errors — skipped.';
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => ($record['ddc_code'] ?? '—') . ' / ' . ($record['product_name'] ?? '—'),
                        'name'       => $record['product_name'] ?? '—',
                        'status'     => 'Skipped',
                        'message'    => $errMsg,
                    ];
                    continue;
                }

                try {
                    $ddcId = $record['ddc_id'];
                    $pNameNorm = strtolower(trim($record['product_name']));
                    $pCodeNorm = !empty($record['local_product_code']) ? strtoupper(trim($record['local_product_code'])) : null;

                    $keyByName = "{$ddcId}:name:{$pNameNorm}";
                    $keyByCode = $pCodeNorm ? "{$ddcId}:code:{$pCodeNorm}" : null;

                    // 1. Find or create DdcLocalProduct (Cache + DB lookup)
                    $localProduct = null;
                    if ($keyByCode && isset($localProductMap[$keyByCode])) {
                        $localProduct = $localProductMap[$keyByCode];
                    } elseif (isset($localProductMap[$keyByName])) {
                        $localProduct = $localProductMap[$keyByName];
                    }

                    if (!$localProduct && $pCodeNorm) {
                        $localProduct = DdcLocalProduct::where('local_product_code', $pCodeNorm)->first();
                    }

                    if (!$localProduct) {
                        $localProduct = DdcLocalProduct::where('ddc_id', $ddcId)
                            ->whereRaw('LOWER(product_name) = ?', [$pNameNorm])
                            ->first();
                    }

                    if ($localProduct) {
                        // Update existing Local Product attributes if missing
                        $updateData = [];
                        if (empty($localProduct->barcode) && !empty($record['barcode'])) {
                            $updateData['barcode'] = $record['barcode'];
                        }
                        if (empty($localProduct->hsn_code) && !empty($record['hsn_code'])) {
                            $updateData['hsn_code'] = $record['hsn_code'];
                        }
                        if (!empty($record['gst_rate']) && (float)$localProduct->gst_rate === 0.0) {
                            $updateData['gst_rate'] = $record['gst_rate'];
                        }
                        if (!empty($updateData)) {
                            $localProduct->update($updateData);
                        }
                    } else {
                        // Create new DdcLocalProduct (guarantee unique code)
                        $code = $pCodeNorm;
                        if (empty($code) || DdcLocalProduct::where('local_product_code', $code)->exists()) {
                            $code = DdcLocalProduct::generateLocalProductCode();
                        }

                        $localProduct = DdcLocalProduct::create([
                            'ddc_id'             => $ddcId,
                            'local_product_code' => $code,
                            'product_name'       => $record['product_name'],
                            'category_id'        => $record['category_id'],
                            'hsn_code'           => $record['hsn_code'] ?: null,
                            'gst_rate'           => $record['gst_rate'] ?? 0,
                            'unit_of_measure'    => $this->normalizeUom($record['unit_of_measure'] ?? null, 'Piece'),
                            'barcode'            => $record['barcode'] ?: null,
                            'status'             => 'Active',
                        ]);
                    }

                    // Save into in-memory batch map
                    $localProductMap[$keyByName] = $localProduct;
                    if ($localProduct->local_product_code) {
                        $localProductMap["{$ddcId}:code:" . strtoupper($localProduct->local_product_code)] = $localProduct;
                    }

                    // 2. Insert separate Stock entry into ddc_inventories for every row
                    DB::table('ddc_inventories')->insert([
                        'ddc_id'                 => $ddcId,
                        'inventory_source'       => 'LOCAL',
                        'product_id'             => null,
                        'ddc_local_product_id'   => $localProduct->id,
                        'product_name'           => $localProduct->product_name,
                        'product_code'           => $localProduct->local_product_code,
                        'barcode'                => $record['barcode'] ?? $localProduct->barcode,
                        'unit_of_measure'        => $this->normalizeUom($record['unit_of_measure'] ?? $localProduct->unit_of_measure ?? null, 'Piece'),
                        'quantity'               => (float)($record['quantity'] ?? 0),
                        'reserved_quantity'      => 0,
                        'sold_quantity'          => 0,
                        'returned_quantity'      => 0,
                        'mrp'                    => (float)($record['mrp'] ?? 0),
                        'purchase_price'         => (float)($record['purchase_price'] ?? 0),
                        'landing_cost'           => (float)($record['landing_cost'] ?? 0),
                        'msp'                    => (float)($record['msp'] ?? 0),
                        'csp'                    => (float)($record['csp'] ?? 0),
                        'margin'                 => (float)($record['margin'] ?? 0),
                        'adc_margin'             => (float)($record['adc_margin'] ?? 0),
                        'csp_margin'             => (float)($record['csp_margin'] ?? 0),
                        'gst_rate'               => (float)($record['gst_rate'] ?? $localProduct->gst_rate ?? 0),
                        'mfg_date'               => $record['mfg_date'] ?: null,
                        'expire_date'            => $record['expire_date'] ?: null,
                        'batch_number'           => $record['batch_number'] ?? null,
                        'purchase_order_id'      => null,
                        'purchase_order_item_id' => null,
                        'reference_type'         => $record['reference_type'] ?? 'LOCAL_PURCHASE',
                        'reference_id'           => null,
                        'pos_status'             => $record['pos_status'] ?? 'active',
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ]);

                    $imported++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => ($record['ddc_code'] ?? '—') . ' / ' . $localProduct->local_product_code,
                        'name'       => $localProduct->product_name,
                        'status'     => 'Imported',
                        'message'    => "Created/linked Local Product '{$localProduct->product_name}' ({$localProduct->local_product_code}) & added stock of {$record['quantity']} {$record['unit_of_measure']} into DDC '{$record['ddc_code']}' inventory.",
                    ];
                } catch (\Exception $rowEx) {
                    $failed++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => ($record['ddc_code'] ?? '—') . ' / ' . ($record['product_name'] ?? '—'),
                        'name'       => $record['product_name'] ?? '—',
                        'status'     => 'Failed',
                        'message'    => 'DB Error: ' . $rowEx->getMessage(),
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'imported' => $imported,
                'skipped'  => $skipped,
                'failed'   => $failed,
                'details'  => $details,
                'message'  => "Import completed: {$imported} Local Inventory rows added, {$skipped} skipped, {$failed} failed.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // ADC MSME STOCK INVENTORY IMPORT
    // =========================================================================

    public function adcMsmeInventoryIndex()
    {
        return view('admin.import.adc_msme_inventory.index');
    }

    public function adcMsmeInventorySample()
    {
        $spreadsheet = new Spreadsheet();

        // Fetch master data for dropdowns & reference tab
        $categories  = Category::orderBy('name')->pluck('name')->toArray();
        $units       = UnitOfMeasure::getUnitsList();
        $products    = Product::whereNotNull('product_code')->select('product_code', 'product_name')->orderBy('product_code')->take(300)->get();
        $adcProfiles = DB::table('adc_profiles')
            ->join('users', 'users.id', '=', 'adc_profiles.user_id')
            ->select('adc_profiles.adc_code', 'users.name')
            ->whereNotNull('adc_profiles.adc_code')
            ->orderBy('adc_profiles.adc_code')
            ->get();

        $adcCodes     = $adcProfiles->pluck('adc_code')->toArray();
        $productCodes = $products->pluck('product_code')->toArray();

        // ---------------------------------------------------------------------
        // Sheet 1: Main ADC MSME Inventory Import Sheet
        // ---------------------------------------------------------------------
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ADC MSME Inventory Import');

        // All 23 columns covering every adc_inventories field
        $headers = [
            'A1' => 'ADC Code*',
            'B1' => 'Product Code*',
            'C1' => 'Product Name (override)',
            'D1' => 'Barcode',
            'E1' => 'Unit of Measure',
            'F1' => 'Quantity*',
            'G1' => 'Reserved Qty',
            'H1' => 'Sold Qty',
            'I1' => 'Returned Qty',
            'J1' => 'MRP*',
            'K1' => 'Purchase Price',
            'L1' => 'Landing Cost',
            'M1' => 'MSP',
            'N1' => 'CSP',
            'O1' => 'Margin',
            'P1' => 'ADC Margin %',
            'Q1' => 'CSP Margin %',
            'R1' => 'GST Rate %',
            'S1' => 'Mfg Date (YYYY-MM-DD)',
            'T1' => 'Expire Date (YYYY-MM-DD)',
            'U1' => 'Batch Number',
            'V1' => 'Reference Type',
            'W1' => 'POS Status',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3730a3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:W1')->applyFromArray($headerStyle);

        foreach (range('A', 'W') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Sample row with all fields
        $sampleAdc  = !empty($adcCodes) ? $adcCodes[0] : 'ADC001';
        $sampleProd = !empty($productCodes) ? $productCodes[0] : 'IMV-PRD-0001';
        $sampleUom  = !empty($units) ? $units[0] : 'Piece';

        $sheet->setCellValue('A2', $sampleAdc);
        $sheet->setCellValue('B2', $sampleProd);
        $sheet->setCellValue('C2', '');              // product name override (leave blank to use catalog name)
        $sheet->setCellValue('D2', '8901234567890'); // barcode
        $sheet->setCellValue('E2', $sampleUom);      // unit_of_measure dropdown
        $sheet->setCellValue('F2', 100);             // quantity
        $sheet->setCellValue('G2', 0);               // reserved_quantity
        $sheet->setCellValue('H2', 0);               // sold_quantity
        $sheet->setCellValue('I2', 0);               // returned_quantity
        $sheet->setCellValue('J2', 250.00);          // mrp
        $sheet->setCellValue('K2', 200.00);          // purchase_price
        $sheet->setCellValue('L2', 200.00);          // landing_cost
        $sheet->setCellValue('M2', 210.00);          // msp
        $sheet->setCellValue('N2', 220.00);          // csp
        $sheet->setCellValue('O2', 50.00);           // margin
        $sheet->setCellValue('P2', 5);               // adc_margin %
        $sheet->setCellValue('Q2', 3);               // csp_margin %
        $sheet->setCellValue('R2', 18);              // gst_rate %
        $sheet->setCellValue('S2', '2026-01-01');    // mfg_date
        $sheet->setCellValue('T2', '2027-12-31');    // expire_date
        $sheet->setCellValue('U2', 'BATCH-2026-01'); // batch_number
        $sheet->setCellValue('V2', 'MSME PO');       // reference_type
        $sheet->setCellValue('W2', 'active');        // pos_status

        // Notes row
        $sheet->setCellValue('A3', '-- Notes --');
        $sheet->setCellValue('B3', 'Select from Product Code dropdown or Reference Data sheet');
        $sheet->setCellValue('C3', 'If blank, auto-filled from catalog');
        $sheet->setCellValue('E3', 'Select from UOM dropdown (PCS, KG, LTR, Box, etc.)');
        $sheet->setCellValue('V3', 'MSME PO or Local Purchase');
        $sheet->setCellValue('W3', 'active or inactive');

        $noteStyle = [
            'font' => ['italic' => true, 'color' => ['rgb' => '6b7280']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f9fafb']],
        ];
        $sheet->getStyle('A3:W3')->applyFromArray($noteStyle);

        // ---------------------------------------------------------------------
        // Sheet 2: Reference Data Master Tab
        // ---------------------------------------------------------------------
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Reference Data');
        $refSheet->setCellValue('A1', 'ADC Code');
        $refSheet->setCellValue('B1', 'Product Code');
        $refSheet->setCellValue('C1', 'Product Name');
        $refSheet->setCellValue('D1', 'Units of Measure (UOM)');
        $refSheet->setCellValue('E1', 'Categories List');

        $refSheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3730a3']],
        ]);

        foreach ($adcCodes as $idx => $code) {
            $refSheet->setCellValue('A' . ($idx + 2), $code);
        }
        foreach ($products as $idx => $p) {
            $refSheet->setCellValue('B' . ($idx + 2), $p->product_code);
            $refSheet->setCellValue('C' . ($idx + 2), $p->product_name);
        }
        foreach ($units as $idx => $uName) {
            $refSheet->setCellValue('D' . ($idx + 2), $uName);
        }
        foreach ($categories as $idx => $cName) {
            $refSheet->setCellValue('E' . ($idx + 2), $cName);
        }

        foreach (range('A', 'E') as $col) {
            $refSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Return to main sheet
        $spreadsheet->setActiveSheetIndex(0);

        // ---------------------------------------------------------------------
        // Add Data Validation Dropdowns to Main Sheet (Rows 2 to 200)
        // ---------------------------------------------------------------------
        $adcEndRow  = max(count($adcCodes) + 1, 2);
        $prodEndRow = max(count($productCodes) + 1, 2);
        $uomEndRow  = max(count($units) + 1, 2);

        for ($r = 2; $r <= 200; $r++) {
            // ADC Code Dropdown (Col A)
            if (!empty($adcCodes)) {
                $adcValidation = $sheet->getCell("A{$r}")->getDataValidation();
                $adcValidation->setType(DataValidation::TYPE_LIST);
                $adcValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $adcValidation->setAllowBlank(true);
                $adcValidation->setShowInputMessage(true);
                $adcValidation->setShowErrorMessage(true);
                $adcValidation->setShowDropDown(true);
                $adcValidation->setFormula1("'Reference Data'!\$A\$2:\$A\${$adcEndRow}");
            }

            // Product Code Dropdown (Col B)
            if (!empty($productCodes)) {
                $prodValidation = $sheet->getCell("B{$r}")->getDataValidation();
                $prodValidation->setType(DataValidation::TYPE_LIST);
                $prodValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $prodValidation->setAllowBlank(true);
                $prodValidation->setShowInputMessage(true);
                $prodValidation->setShowErrorMessage(true);
                $prodValidation->setShowDropDown(true);
                $prodValidation->setFormula1("'Reference Data'!\$B\$2:\$B\${$prodEndRow}");
            }

            // UOM Dropdown (Col E)
            $uomValidation = $sheet->getCell("E{$r}")->getDataValidation();
            $uomValidation->setType(DataValidation::TYPE_LIST);
            $uomValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $uomValidation->setAllowBlank(true);
            $uomValidation->setShowInputMessage(true);
            $uomValidation->setShowErrorMessage(true);
            $uomValidation->setShowDropDown(true);
            $uomValidation->setFormula1("'Reference Data'!\$D\$2:\$D\${$uomEndRow}");

            // POS Status Dropdown (Col W)
            $posValidation = $sheet->getCell("W{$r}")->getDataValidation();
            $posValidation->setType(DataValidation::TYPE_LIST);
            $posValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $posValidation->setAllowBlank(true);
            $posValidation->setShowInputMessage(true);
            $posValidation->setShowErrorMessage(true);
            $posValidation->setShowDropDown(true);
            $posValidation->setFormula1('"active,inactive"');
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'ADC_MSME_Inventory_Import_Sample.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function adcMsmeInventoryPreview(Request $request)
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(3600);
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv,txt|max:153600']);

        try {
            $rows = $this->readRawExcelRows($request->file('file'));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not parse Excel file: ' . $e->getMessage()]);
        }

        // Build ADC lookup: adc_code => {user_id, adc_name}
        $adcMap = DB::table('adc_profiles')
            ->whereNotNull('adc_code')
            ->join('users', 'users.id', '=', 'adc_profiles.user_id')
            ->select('adc_profiles.adc_code', 'adc_profiles.user_id', 'users.name as adc_name_col')
            ->get()
            ->mapWithKeys(fn($row) => [strtoupper(trim($row->adc_code)) => [
                'user_id'  => $row->user_id,
                'adc_name' => $row->adc_name_col,
            ]])
            ->toArray();

        // Build Product lookup: product_code => product object
        $productMap = DB::table('products')
            ->whereNotNull('product_code')
            ->select('id', 'product_code', 'product_name', 'mrp', 'ddc_purchase_price', 'adc_purchase_price', 'gst_rate')
            ->get()
            ->mapWithKeys(fn($p) => [strtoupper(trim($p->product_code)) => $p])
            ->toArray();

        $records  = [];
        $rowIndex = 0;

        foreach ($rows as $lineNum => $row) {
            $firstCol = strtoupper(trim($row['A'] ?? ''));
            if ($lineNum === 0 || in_array($firstCol, ['ADC CODE', 'ADC CODE*']) || str_contains($firstCol, 'ADC CODE') || str_starts_with($firstCol, '--')) continue; // skip header/notes
            $rowIndex++;

            // Parse all 23 columns
            $adcCode             = trim($row['A'] ?? '');
            $productCode         = trim($row['B'] ?? '');
            $productNameOverride = trim($row['C'] ?? '');
            $barcode             = trim($row['D'] ?? '');
            $uom                 = trim($row['E'] ?? '');
            $quantity            = trim($row['F'] ?? '');
            $reservedQty         = trim($row['G'] ?? '');
            $soldQty             = trim($row['H'] ?? '');
            $returnedQty         = trim($row['I'] ?? '');
            $mrp                 = trim($row['J'] ?? '');
            $purchasePrice       = trim($row['K'] ?? '');
            $landingCost         = trim($row['L'] ?? '');
            $msp                 = trim($row['M'] ?? '');
            $csp                 = trim($row['N'] ?? '');
            $margin              = trim($row['O'] ?? '');
            $adcMargin           = trim($row['P'] ?? '');
            $cspMargin           = trim($row['Q'] ?? '');
            $gstRate             = trim($row['R'] ?? '');
            $mfgDate             = trim($row['S'] ?? '');
            $expireDate          = trim($row['T'] ?? '');
            $batchNumber         = trim($row['U'] ?? '');
            $referenceType       = trim($row['V'] ?? '');
            $posStatus           = trim($row['W'] ?? '');

            // Skip fully empty rows
            if ($adcCode === '' && $productCode === '' && $quantity === '') continue;

            $errors      = [];
            $adcId       = null;
            $adcName     = null;
            $productId   = null;
            $productName = null;

            // Validate ADC Code
            if ($adcCode === '') {
                $defaultAdcEntry = !empty($adcMap) ? reset($adcMap) : null;
                $adcId   = $defaultAdcEntry['user_id'] ?? 1;
                $adcName = $defaultAdcEntry['adc_name'] ?? 'System ADC';
                $adcCode = !empty($adcMap) ? (string)array_key_first($adcMap) : 'ADC001';
            } else {
                $adcEntry = $adcMap[strtoupper($adcCode)] ?? null;
                if (!$adcEntry) {
                    $defaultAdcEntry = !empty($adcMap) ? reset($adcMap) : null;
                    $adcId   = $defaultAdcEntry['user_id'] ?? 1;
                    $adcName = $defaultAdcEntry['adc_name'] ?? 'System ADC';
                } else {
                    $adcId   = $adcEntry['user_id'];
                    $adcName = $adcEntry['adc_name'];
                }
            }

            // Validate Product Code (Optional catalog match)
            if ($productCode === '' && $productNameOverride === '') {
                $productId   = null;
                $productName = 'Item Row ' . $rowIndex;
            } else {
                $prod = $productCode !== '' ? ($productMap[strtoupper($productCode)] ?? null) : null;
                if ($prod) {
                    $productId   = $prod->id;
                    $productName = $productNameOverride !== '' ? $productNameOverride : ($prod->product_name ?? $productCode);
                } else {
                    // No match required: product can be imported even if not in central catalog
                    $productId   = null;
                    $productName = $productNameOverride !== '' ? $productNameOverride : $productCode;
                }
            }

            // Graceful parsing with fallbacks (no blocking validation errors)
            $quantityFinal = (is_numeric($quantity) && (float)$quantity >= 0) ? (float)$quantity : 0.0;

            $mrpFinal = 0.0;
            if (is_numeric($mrp) && (float)$mrp > 0) {
                $mrpFinal = (float)$mrp;
            } elseif (is_numeric($purchasePrice) && (float)$purchasePrice > 0) {
                $mrpFinal = (float)$purchasePrice;
            } elseif (is_numeric($landingCost) && (float)$landingCost > 0) {
                $mrpFinal = (float)$landingCost;
            }

            $mfgDateFinal = $mfgDate !== '' ? $this->parseExcelDate($mfgDate) : null;
            $expireDateFinal = $expireDate !== '' ? $this->parseExcelDate($expireDate) : null;

            $posStatusFinal = ($posStatus !== '' && in_array(strtolower($posStatus), ['active', 'inactive'])) ? strtolower($posStatus) : 'active';

            // Validate reference_type (ADC DB ENUM: 'MSME_PO', 'LOCAL_PURCHASE')
            $refUpper = strtoupper(str_replace([' ', '-'], '_', $referenceType));
            if ($refUpper === 'LOCAL_PURCHASE' || $refUpper === 'LOCAL') {
                $referenceTypeFinal = 'LOCAL_PURCHASE';
            } else {
                $referenceTypeFinal = 'MSME_PO';
            }

            $marginFinal = $margin !== '' ? (float)$margin : null;
            $landingCostFinal = $landingCost !== '' ? (float)$landingCost : null;

            $records[] = [
                'row_number'      => $rowIndex,
                'adc_code'        => $adcCode,
                'adc_id'          => $adcId,
                'adc_name'        => $adcName,
                'product_code'    => $productCode,
                'product_id'      => $productId,
                'product_name'    => $productName,
                'barcode'         => $barcode ?: null,
                'unit_of_measure' => $this->normalizeUom($uom, 'Piece'),
                'quantity'        => $quantity !== '' ? (float)$quantity : 0,
                'reserved_qty'    => $reservedQty !== '' ? (float)$reservedQty : 0,
                'sold_qty'        => $soldQty !== '' ? (float)$soldQty : 0,
                'returned_qty'    => $returnedQty !== '' ? (float)$returnedQty : 0,
                'mrp'             => $mrp !== '' ? (float)$mrp : null,
                'purchase_price'  => $purchasePrice !== '' ? (float)$purchasePrice : null,
                'landing_cost'    => $landingCostFinal,
                'msp'             => $msp !== '' ? (float)$msp : null,
                'csp'             => $csp !== '' ? (float)$csp : null,
                'margin'          => $marginFinal,
                'adc_margin'      => $adcMargin !== '' ? (float)$adcMargin : 0,
                'csp_margin'      => $cspMargin !== '' ? (float)$cspMargin : 0,
                'gst_rate'        => $gstRate !== '' ? (float)$gstRate : 0,
                'mfg_date'        => $mfgDateFinal,
                'expire_date'     => $expireDateFinal,
                'batch_number'    => $batchNumber ?: null,
                'reference_type'  => $referenceTypeFinal,
                'pos_status'      => $posStatusFinal,
                'is_valid'        => count($errors) === 0,
                'errors'          => $errors,
            ];
        }

        $valid      = count(array_filter($records, fn($r) => $r['is_valid']));
        $errorCount = count(array_filter($records, fn($r) => !$r['is_valid']));

        return response()->json([
            'success' => true,
            'total'   => count($records),
            'valid'   => $valid,
            'errors'  => $errorCount,
            'records' => $records,
        ]);
    }

    public function adcMsmeInventoryProcess(Request $request)
    {
        $records = $request->input('records', []);
        if (empty($records)) {
            return response()->json(['success' => false, 'message' => 'No records to process.']);
        }

        $imported = 0;
        $skipped  = 0;
        $failed   = 0;
        $details  = [];

        DB::beginTransaction();
        try {
            foreach ($records as $record) {
                if (!($record['is_valid'] ?? false)) {
                    $skipped++;
                    $errMsg = !empty($record['errors']) ? 'Validation Error: ' . implode(' | ', $record['errors']) : 'Row has validation errors — skipped.';
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => ($record['adc_code'] ?? '—') . ' / ' . ($record['product_code'] ?? '—'),
                        'name'       => $record['product_name'] ?? '—',
                        'status'     => 'Skipped',
                        'message'    => $errMsg,
                    ];
                    continue;
                }

                try {
                    DB::table('adc_inventories')->insert([
                        'adc_id'                 => $record['adc_id'],
                        'inventory_source'       => 'MSME',
                        'product_id'             => $record['product_id'],
                        'adc_local_product_id'   => null,
                        'product_name'           => $record['product_name'],
                        'product_code'           => $record['product_code'],
                        'barcode'                => $record['barcode'] ?? null,
                        'unit_of_measure'        => $this->normalizeUom($record['unit_of_measure'] ?? null, 'Piece'),
                        'quantity'               => (float)($record['quantity'] ?? 0),
                        'reserved_quantity'      => (float)($record['reserved_qty'] ?? 0),
                        'sold_quantity'          => (float)($record['sold_qty'] ?? 0),
                        'returned_quantity'      => (float)($record['returned_qty'] ?? 0),
                        'mrp'                    => (float)($record['mrp'] ?? 0),
                        'purchase_price'         => (float)($record['purchase_price'] ?? 0),
                        'landing_cost'           => (float)($record['landing_cost'] ?? 0),
                        'msp'                    => (float)($record['msp'] ?? 0),
                        'adc_price'              => (float)($record['msp'] ?? 0),
                        'csp'                    => (float)($record['csp'] ?? 0),
                        'adc_margin'             => (float)($record['adc_margin'] ?? 0),
                        'csp_margin'             => (float)($record['csp_margin'] ?? 0),
                        'gst_rate'               => (float)($record['gst_rate'] ?? 0),
                        'mfg_date'               => $record['mfg_date'] ?: null,
                        'expire_date'            => $record['expire_date'] ?: null,
                        'batch_number'           => $record['batch_number'] ?? null,
                        'adc_order_id'           => null,
                        'adc_order_item_id'      => null,
                        'reference_type'         => $record['reference_type'] ?? 'MSME_PO',
                        'reference_id'           => null,
                        'pos_status'             => $record['pos_status'] ?? 'active',
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ]);

                    $imported++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => ($record['adc_code'] ?? '—') . ' / ' . ($record['product_code'] ?? '—'),
                        'name'       => $record['product_name'],
                        'status'     => 'Imported',
                        'message'    => "Stock of {$record['quantity']} {$record['unit_of_measure']} for '{$record['product_name']}' added to ADC '{$record['adc_code']}' inventory.",
                    ];
                } catch (\Exception $rowEx) {
                    $failed++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => ($record['adc_code'] ?? '—') . ' / ' . ($record['product_code'] ?? '—'),
                        'name'       => $record['product_name'] ?? '—',
                        'status'     => 'Failed',
                        'message'    => 'DB Error: ' . $rowEx->getMessage(),
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'imported' => $imported,
                'skipped'  => $skipped,
                'failed'   => $failed,
                'details'  => $details,
                'message'  => "Import completed: {$imported} ADC inventory rows added, {$skipped} skipped, {$failed} failed.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // ADC LOCAL / REGIONAL INVENTORY IMPORT
    // =========================================================================

    public function adcLocalInventoryIndex()
    {
        return view('admin.import.adc_local_inventory.index');
    }

    public function adcLocalInventorySample()
    {
        $spreadsheet = new Spreadsheet();

        // Fetch master data for dropdowns & reference tab
        $categories  = Category::orderBy('name')->pluck('name')->toArray();
        $units       = UnitOfMeasure::getUnitsList();
        $brands      = Brand::orderBy('name')->pluck('name')->toArray();
        $adcProfiles = DB::table('adc_profiles')
            ->join('users', 'users.id', '=', 'adc_profiles.user_id')
            ->select('adc_profiles.adc_code', 'users.name')
            ->whereNotNull('adc_profiles.adc_code')
            ->orderBy('adc_profiles.adc_code')
            ->get();

        $adcCodes = $adcProfiles->pluck('adc_code')->toArray();

        // ---------------------------------------------------------------------
        // Sheet 1: Main ADC Local Inventory Import Sheet
        // ---------------------------------------------------------------------
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ADC Local Inventory Import');

        // All 22 columns covering ADC Local Product & adc_inventories fields
        $headers = [
            'A1' => 'ADC Code*',
            'B1' => 'Product Name*',
            'C1' => 'Local Product Code',
            'D1' => 'Category (Name or ID)',
            'E1' => 'HSN Code',
            'F1' => 'Barcode',
            'G1' => 'Unit of Measure',
            'H1' => 'Quantity*',
            'I1' => 'MRP*',
            'J1' => 'Purchase Price',
            'K1' => 'Landing Cost',
            'L1' => 'MSP',
            'M1' => 'CSP',
            'N1' => 'Margin',
            'O1' => 'ADC Margin %',
            'P1' => 'CSP Margin %',
            'Q1' => 'GST Rate %',
            'R1' => 'Mfg Date (YYYY-MM-DD)',
            'S1' => 'Expire Date (YYYY-MM-DD)',
            'T1' => 'Batch Number',
            'U1' => 'Reference Type',
            'V1' => 'POS Status',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0284c7']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:V1')->applyFromArray($headerStyle);

        foreach (range('A', 'V') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Sample row values
        $sampleAdc = !empty($adcCodes) ? $adcCodes[0] : 'ADC001';
        $sampleCat = !empty($categories) ? $categories[0] : 'Grocery';
        $sampleUom = !empty($units) ? $units[0] : 'LTR';

        $sheet->setCellValue('A2', $sampleAdc);
        $sheet->setCellValue('B2', 'Local Organic Mustard Oil 1L');
        $sheet->setCellValue('C2', 'LCL000001');     // local product code
        $sheet->setCellValue('D2', $sampleCat);      // category dropdown value
        $sheet->setCellValue('E2', '1514');           // hsn code
        $sheet->setCellValue('F2', '8901234567890'); // barcode
        $sheet->setCellValue('G2', $sampleUom);      // unit_of_measure dropdown value
        $sheet->setCellValue('H2', 50);              // quantity
        $sheet->setCellValue('I2', 180.00);          // mrp
        $sheet->setCellValue('J2', 140.00);          // purchase_price
        $sheet->setCellValue('K2', 140.00);          // landing_cost
        $sheet->setCellValue('L2', 150.00);          // msp
        $sheet->setCellValue('M2', 160.00);          // csp
        $sheet->setCellValue('N2', 40.00);           // margin
        $sheet->setCellValue('O2', 5);               // adc_margin %
        $sheet->setCellValue('P2', 3);               // csp_margin %
        $sheet->setCellValue('Q2', 5);               // gst_rate %
        $sheet->setCellValue('R2', '2026-01-01');    // mfg_date
        $sheet->setCellValue('S2', '2027-12-31');    // expire_date
        $sheet->setCellValue('T2', 'BATCH-LCL-01');  // batch_number
        $sheet->setCellValue('U2', 'Local Purchase'); // reference_type
        $sheet->setCellValue('V2', 'active');        // pos_status

        // Notes row
        $sheet->setCellValue('A3', '-- Notes --');
        $sheet->setCellValue('B3', 'Name of regional/local product');
        $sheet->setCellValue('C3', 'Optional. If blank, auto-generated (LCL000001...)');
        $sheet->setCellValue('D3', 'Select from Category dropdown or Reference Data sheet');
        $sheet->setCellValue('G3', 'Select from UOM dropdown (PCS, KG, LTR, Box, etc.)');
        $sheet->setCellValue('U3', 'Local Purchase or Direct Stock');
        $sheet->setCellValue('V3', 'active or inactive');

        $noteStyle = [
            'font' => ['italic' => true, 'color' => ['rgb' => '6b7280']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f9fafb']],
        ];
        $sheet->getStyle('A3:V3')->applyFromArray($noteStyle);

        // ---------------------------------------------------------------------
        // Sheet 2: Reference Data Master Tab
        // ---------------------------------------------------------------------
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Reference Data');
        $refSheet->setCellValue('A1', 'ADC Code');
        $refSheet->setCellValue('B1', 'Categories List');
        $refSheet->setCellValue('C1', 'Units of Measure (UOM)');
        $refSheet->setCellValue('D1', 'Brands List');

        $refSheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0369a1']],
        ]);

        foreach ($adcCodes as $idx => $code) {
            $refSheet->setCellValue('A' . ($idx + 2), $code);
        }
        foreach ($categories as $idx => $cName) {
            $refSheet->setCellValue('B' . ($idx + 2), $cName);
        }
        foreach ($units as $idx => $uName) {
            $refSheet->setCellValue('C' . ($idx + 2), $uName);
        }
        foreach ($brands as $idx => $bName) {
            $refSheet->setCellValue('D' . ($idx + 2), $bName);
        }

        foreach (range('A', 'D') as $col) {
            $refSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Return to main sheet
        $spreadsheet->setActiveSheetIndex(0);

        // ---------------------------------------------------------------------
        // Add Data Validation Dropdowns to Main Sheet (Rows 2 to 200)
        // ---------------------------------------------------------------------
        $adcEndRow = max(count($adcCodes) + 1, 2);
        $catEndRow = max(count($categories) + 1, 2);
        $uomEndRow = max(count($units) + 1, 2);

        for ($r = 2; $r <= 200; $r++) {
            // ADC Code Dropdown (Col A)
            if (!empty($adcCodes)) {
                $adcValidation = $sheet->getCell("A{$r}")->getDataValidation();
                $adcValidation->setType(DataValidation::TYPE_LIST);
                $adcValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $adcValidation->setAllowBlank(true);
                $adcValidation->setShowInputMessage(true);
                $adcValidation->setShowErrorMessage(true);
                $adcValidation->setShowDropDown(true);
                $adcValidation->setFormula1("'Reference Data'!\$A\$2:\$A\${$adcEndRow}");
            }

            // Category Dropdown (Col D)
            $catValidation = $sheet->getCell("D{$r}")->getDataValidation();
            $catValidation->setType(DataValidation::TYPE_LIST);
            $catValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $catValidation->setAllowBlank(true);
            $catValidation->setShowInputMessage(true);
            $catValidation->setShowErrorMessage(true);
            $catValidation->setShowDropDown(true);
            $catValidation->setFormula1("'Reference Data'!\$B\$2:\$B\${$catEndRow}");

            // UOM Dropdown (Col G)
            $uomValidation = $sheet->getCell("G{$r}")->getDataValidation();
            $uomValidation->setType(DataValidation::TYPE_LIST);
            $uomValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $uomValidation->setAllowBlank(true);
            $uomValidation->setShowInputMessage(true);
            $uomValidation->setShowErrorMessage(true);
            $uomValidation->setShowDropDown(true);
            $uomValidation->setFormula1("'Reference Data'!\$C\$2:\$C\${$uomEndRow}");

            // POS Status Dropdown (Col V)
            $posValidation = $sheet->getCell("V{$r}")->getDataValidation();
            $posValidation->setType(DataValidation::TYPE_LIST);
            $posValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $posValidation->setAllowBlank(true);
            $posValidation->setShowInputMessage(true);
            $posValidation->setShowErrorMessage(true);
            $posValidation->setShowDropDown(true);
            $posValidation->setFormula1('"active,inactive"');
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'ADC_Local_Inventory_Import_Sample.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function adcLocalInventoryPreview(Request $request)
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(3600);
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv,txt|max:307200']);

        $importToken = 'adc_local_' . uniqid() . '_' . time();
        $tempDir = storage_path('app/temp_imports');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $csvPath = "{$tempDir}/{$importToken}.csv";

        try {
            $this->convertUploadedFileToCsv($request->file('file'), $csvPath);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not parse uploaded file: ' . $e->getMessage()]);
        }

        // Build ADC lookup: adc_code => {user_id, adc_name}
        $adcMap = DB::table('adc_profiles')
            ->whereNotNull('adc_code')
            ->join('users', 'users.id', '=', 'adc_profiles.user_id')
            ->select('adc_profiles.adc_code', 'adc_profiles.user_id', 'users.name as adc_name_col')
            ->get()
            ->mapWithKeys(fn($row) => [strtoupper(trim($row->adc_code)) => [
                'user_id'  => $row->user_id,
                'adc_name' => $row->adc_name_col,
            ]])
            ->toArray();

        // Build Category lookup
        $categories = Category::select('id', 'name')->get();
        $catMapByName = $categories->mapWithKeys(fn($c) => [strtolower(trim($c->name)) => $c->id])->toArray();
        $catMapById   = $categories->pluck('id', 'id')->toArray();
        $defaultCatId = $categories->first()?->id ?: 1;

        $previewRecords = [];
        $totalRows  = 0;
        $validCount = 0;
        $errorCount = 0;

        $previewErrorCsvPath = "{$tempDir}/{$importToken}_preview_errors.csv";
        $errOut = fopen($previewErrorCsvPath, 'w');
        if ($errOut) {
            fputcsv($errOut, [
                'Row Number',
                'ADC Code',
                'Product Name',
                'Local Product Code',
                'Category',
                'HSN Code',
                'Barcode',
                'Unit of Measure',
                'Quantity',
                'MRP',
                'Purchase Price',
                'Landing Cost',
                'MSP',
                'CSP',
                'Margin',
                'ADC Margin %',
                'CSP Margin %',
                'GST Rate %',
                'Mfg Date',
                'Expire Date',
                'Batch Number',
                'Reference Type',
                'POS Status',
                'Error Details / Validation Remarks'
            ]);
        }

        if (($handle = fopen($csvPath, 'r')) !== false) {
            $lineNum = 0;
            $emptyConsecutive = 0;

            while (($row = fgetcsv($handle, 0, ",")) !== false) {
                $lineNum++;
                if ($lineNum === 1) continue; // header
                $colA = trim($row[0] ?? '');
                if ($lineNum === 2 && str_starts_with($colA, '--')) continue; // notes row

                $isEmpty = true;
                foreach ($row as $cell) {
                    if (trim((string)$cell) !== '') {
                        $isEmpty = false;
                        break;
                    }
                }
                if ($isEmpty) {
                    $emptyConsecutive++;
                    if ($emptyConsecutive >= 25) break;
                    continue;
                }
                $emptyConsecutive = 0;
                $totalRows++;

                $parsed = $this->validateAdcLocalInventoryRow($row, $totalRows, $adcMap, $catMapByName, $catMapById, $defaultCatId);

                if ($parsed['is_valid']) {
                    $validCount++;
                } else {
                    $errorCount++;
                    if ($errOut) {
                        $rowFields = array_map(fn($idx) => trim($row[$idx] ?? ''), range(0, 21));
                        fputcsv($errOut, array_merge(
                            [$totalRows],
                            $rowFields,
                            [implode(' | ', $parsed['errors'] ?? ['Validation Error'])]
                        ));
                    }
                }

                if (count($previewRecords) < 100) {
                    $previewRecords[] = $parsed;
                }
            }
            fclose($handle);
        }

        if ($errOut) {
            fclose($errOut);
        }

        // Save meta JSON
        $metaPath = "{$tempDir}/{$importToken}.json";
        file_put_contents($metaPath, json_encode([
            'token'      => $importToken,
            'total'      => $totalRows,
            'valid'      => $validCount,
            'errors'     => $errorCount,
            'created_at' => time(),
        ]));

        return response()->json([
            'success'      => true,
            'import_token' => $importToken,
            'total'        => $totalRows,
            'valid'        => $validCount,
            'errors'       => $errorCount,
            'records'      => $previewRecords,
            'message'      => "File validated successfully. {$totalRows} records ready for processing.",
        ]);
    }

    public function adcLocalInventoryExportPreviewErrors(Request $request)
    {
        $importToken = $request->input('token') ?? $request->input('import_token');
        $exportAll   = $request->boolean('all', false);

        if (empty($importToken)) {
            return redirect()->back()->with('error', 'Import session expired or token missing.');
        }

        $tempDir = storage_path('app/temp_imports');

        if ($exportAll) {
            $originalCsv = "{$tempDir}/{$importToken}.csv";
            if (!file_exists($originalCsv)) {
                return redirect()->back()->with('error', 'Original preview file not found. Please re-upload.');
            }
            $filename = 'ADC_Local_Inventory_All_Preview_Records_' . date('Ymd_His') . '.csv';
            return response()->download($originalCsv, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        $errorCsvPath = "{$tempDir}/{$importToken}_preview_errors.csv";

        if (!file_exists($errorCsvPath)) {
            return redirect()->back()->with('error', 'No preview error log found for this session.');
        }

        // Count lines to decide stream method
        $lineCount = 0;
        if (($h = fopen($errorCsvPath, 'r')) !== false) {
            while (fgets($h) !== false) $lineCount++;
            fclose($h);
        }

        // For large datasets (> 5000 rows), stream CSV directly for instant download & zero memory overhead
        if ($lineCount > 5000 || $request->boolean('csv', false)) {
            $filename = 'ADC_Local_Inventory_Preview_Errors_' . date('Ymd_His') . '.csv';
            return response()->streamDownload(function () use ($errorCsvPath) {
                $out = fopen('php://output', 'w');
                // UTF-8 BOM for Excel compatibility
                fwrite($out, "\xEF\xBB\xBF");
                if (($in = fopen($errorCsvPath, 'r')) !== false) {
                    while (($row = fgetcsv($in)) !== false) {
                        fputcsv($out, $row);
                    }
                    fclose($in);
                }
                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'max-age=0',
            ]);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Preview Errors');

        $headers = [
            'A1' => 'Row #',
            'B1' => 'ADC Code',
            'C1' => 'Product Name',
            'D1' => 'Local Product Code',
            'E1' => 'Category',
            'F1' => 'HSN Code',
            'G1' => 'Barcode',
            'H1' => 'Unit of Measure',
            'I1' => 'Quantity',
            'J1' => 'MRP',
            'K1' => 'Purchase Price',
            'L1' => 'Landing Cost',
            'M1' => 'MSP',
            'N1' => 'CSP',
            'O1' => 'Margin',
            'P1' => 'ADC Margin %',
            'Q1' => 'CSP Margin %',
            'R1' => 'GST Rate %',
            'S1' => 'Mfg Date',
            'T1' => 'Expire Date',
            'U1' => 'Batch Number',
            'V1' => 'Reference Type',
            'W1' => 'POS Status',
            'X1' => 'Error Details / Validation Remarks',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerRange = 'A1:X1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ef4444');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowIdx = 2;
        if (($handle = fopen($errorCsvPath, 'r')) !== false) {
            $line = 0;
            while (($data = fgetcsv($handle, 0, ",")) !== false) {
                $line++;
                if ($line === 1) continue;
                for ($col = 0; $col < 24; $col++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                    $sheet->setCellValue($colLetter . $rowIdx, $data[$col] ?? '');
                }
                $rowIdx++;
            }
            fclose($handle);
        }

        for ($col = 1; $col <= 24; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'ADC_Local_Inventory_Preview_Errors_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function adcLocalInventoryExportImportLogs(Request $request)
    {
        $importToken = $request->input('token') ?? $request->input('import_token');
        $type = $request->input('type', 'all');

        if (empty($importToken)) {
            return redirect()->back()->with('error', 'Import session expired or token missing.');
        }

        $tempDir = storage_path('app/temp_imports');
        $logCsvPath = "{$tempDir}/{$importToken}_import_logs.csv";

        if (!file_exists($logCsvPath)) {
            return redirect()->back()->with('error', 'No import logs found for this session.');
        }

        $lineCount = 0;
        if (($h = fopen($logCsvPath, 'r')) !== false) {
            while (fgets($h) !== false) $lineCount++;
            fclose($h);
        }

        if ($lineCount > 5000 || $request->boolean('csv', false)) {
            $filename = ($type === 'failed_skipped' ? 'ADC_Local_Inventory_Skipped_Failed_All_' : 'ADC_Local_Inventory_All_Logs_') . date('Ymd_His') . '.csv';
            return response()->streamDownload(function () use ($logCsvPath, $type) {
                $out = fopen('php://output', 'w');
                fwrite($out, "\xEF\xBB\xBF");
                if (($in = fopen($logCsvPath, 'r')) !== false) {
                    $line = 0;
                    while (($row = fgetcsv($in)) !== false) {
                        $line++;
                        if ($line > 1 && $type === 'failed_skipped') {
                            $status = $row[3] ?? '';
                            if ($status === 'Imported' || $status === 'Success' || $status === 'Inserted') {
                                continue;
                            }
                        }
                        fputcsv($out, $row);
                    }
                    fclose($in);
                }
                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'max-age=0',
            ]);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($type === 'failed_skipped' ? 'Skipped & Failed Logs' : 'All Import Logs');

        $headers = [
            'A1' => 'Row #',
            'B1' => 'Identifier (ADC / Code)',
            'C1' => 'Product Name',
            'D1' => 'Status',
            'E1' => 'Details / Message',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerRange = 'A1:E1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($type === 'failed_skipped' ? 'f59e0b' : '0ea5e9');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowIdx = 2;
        if (($handle = fopen($logCsvPath, 'r')) !== false) {
            $line = 0;
            while (($data = fgetcsv($handle, 0, ",")) !== false) {
                $line++;
                if ($line === 1) continue;
                $status = $data[3] ?? '';
                if ($type === 'failed_skipped' && ($status === 'Imported' || $status === 'Success' || $status === 'Inserted')) {
                    continue;
                }
                $sheet->setCellValue('A' . $rowIdx, $data[0] ?? '');
                $sheet->setCellValue('B' . $rowIdx, $data[1] ?? '');
                $sheet->setCellValue('C' . $rowIdx, $data[2] ?? '');
                $sheet->setCellValue('D' . $rowIdx, $data[3] ?? '');
                $sheet->setCellValue('E' . $rowIdx, $data[4] ?? '');
                $rowIdx++;
            }
            fclose($handle);
        }

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = ($type === 'failed_skipped' ? 'ADC_Local_Inventory_Skipped_Failed_All_' : 'ADC_Local_Inventory_All_Logs_') . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function adcLocalInventoryPreviewPage(Request $request)
    {
        $importToken = $request->input('import_token');
        if (empty($importToken)) {
            return response()->json(['success' => false, 'message' => 'Import session expired or token missing.']);
        }

        $tempDir = storage_path('app/temp_imports');
        $csvPath = "{$tempDir}/{$importToken}.csv";
        $metaPath = "{$tempDir}/{$importToken}.json";

        if (!file_exists($csvPath)) {
            return response()->json(['success' => false, 'message' => 'Preview session expired. Please re-upload file.']);
        }

        $meta = file_exists($metaPath) ? json_decode(file_get_contents($metaPath), true) : [];
        $totalRows = $meta['total'] ?? 0;
        $validCount = $meta['valid'] ?? 0;
        $errorCount = $meta['errors'] ?? 0;

        $page = max(1, (int)$request->input('page', 1));
        $perPage = max(10, min(500, (int)$request->input('per_page', 50)));

        $totalPages = (int)ceil($totalRows / max(1, $perPage));
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;

        $adcMap = DB::table('adc_profiles')
            ->whereNotNull('adc_code')
            ->join('users', 'users.id', '=', 'adc_profiles.user_id')
            ->select('adc_profiles.adc_code', 'adc_profiles.user_id', 'users.name as adc_name_col')
            ->get()
            ->mapWithKeys(fn($row) => [strtoupper(trim($row->adc_code)) => [
                'user_id'  => $row->user_id,
                'adc_name' => $row->adc_name_col,
            ]])
            ->toArray();

        $categories = Category::select('id', 'name')->get();
        $catMapByName = $categories->mapWithKeys(fn($c) => [strtolower(trim($c->name)) => $c->id])->toArray();
        $catMapById   = $categories->pluck('id', 'id')->toArray();
        $defaultCatId = $categories->first()?->id ?: 1;

        $pageRecords = [];
        $currentDataRowIndex = 0;

        if (($handle = fopen($csvPath, 'r')) !== false) {
            $lineNum = 0;
            while (($row = fgetcsv($handle, 0, ",")) !== false) {
                $lineNum++;
                if ($lineNum === 1) continue;
                $colA = trim($row[0] ?? '');
                if ($lineNum === 2 && str_starts_with($colA, '--')) continue;

                $isEmpty = true;
                foreach ($row as $cell) {
                    if (trim((string)$cell) !== '') {
                        $isEmpty = false;
                        break;
                    }
                }
                if ($isEmpty) continue;

                if ($currentDataRowIndex < $offset) {
                    $currentDataRowIndex++;
                    continue;
                }

                $currentDataRowIndex++;
                $parsed = $this->validateAdcLocalInventoryRow($row, $currentDataRowIndex, $adcMap, $catMapByName, $catMapById, $defaultCatId);
                $pageRecords[] = $parsed;

                if (count($pageRecords) >= $perPage) {
                    break;
                }
            }
            fclose($handle);
        }

        return response()->json([
            'success'      => true,
            'import_token' => $importToken,
            'page'         => $page,
            'per_page'     => $perPage,
            'total_pages'  => $totalPages,
            'total'        => $totalRows,
            'valid'        => $validCount,
            'errors'       => $errorCount,
            'records'      => $pageRecords,
        ]);
    }

    public function adcLocalInventoryProcess(Request $request)
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(3600);

        $importToken = $request->input('import_token');

        if (empty($importToken)) {
            $records = $request->input('records', []);
            if (empty($records)) {
                return response()->json(['success' => false, 'message' => 'No records or import session provided.']);
            }
            $batchResult = $this->executeBatchDbInsert($records, '');
            return response()->json([
                'success'  => true,
                'imported' => $batchResult['imported'],
                'skipped'  => $batchResult['skipped'],
                'failed'   => $batchResult['failed'],
                'details'  => $batchResult['details'],
            ]);
        }

        $tempDir = storage_path('app/temp_imports');
        $csvPath = "{$tempDir}/{$importToken}.csv";
        $metaPath = "{$tempDir}/{$importToken}.json";

        if (!file_exists($csvPath)) {
            return response()->json(['success' => false, 'message' => 'Import session expired or file not found. Please upload file again.']);
        }

        $meta = file_exists($metaPath) ? json_decode(file_get_contents($metaPath), true) : [];
        $totalRows = $meta['total'] ?? 0;

        $offset = (int)$request->input('offset', 0);
        $batchSize = (int)$request->input('batch_size', 5000);

        $adcMap = DB::table('adc_profiles')
            ->whereNotNull('adc_code')
            ->join('users', 'users.id', '=', 'adc_profiles.user_id')
            ->select('adc_profiles.adc_code', 'adc_profiles.user_id', 'users.name as adc_name_col')
            ->get()
            ->mapWithKeys(fn($row) => [strtoupper(trim($row->adc_code)) => [
                'user_id'  => $row->user_id,
                'adc_name' => $row->adc_name_col,
            ]])
            ->toArray();

        $categories = Category::select('id', 'name')->get();
        $catMapByName = $categories->mapWithKeys(fn($c) => [strtolower(trim($c->name)) => $c->id])->toArray();
        $catMapById   = $categories->pluck('id', 'id')->toArray();
        $defaultCatId = $categories->first()?->id ?: 1;

        $batchRecords = [];
        $rowsRead = 0;
        $currentDataRowIndex = 0;

        if (($handle = fopen($csvPath, 'r')) !== false) {
            $lineNum = 0;
            $emptyConsecutive = 0;

            while (($row = fgetcsv($handle, 0, ",")) !== false) {
                $lineNum++;
                if ($lineNum === 1) continue;
                $colA = trim($row[0] ?? '');
                if ($lineNum === 2 && str_starts_with($colA, '--')) continue;

                $isEmpty = true;
                foreach ($row as $cell) {
                    if (trim((string)$cell) !== '') {
                        $isEmpty = false;
                        break;
                    }
                }
                if ($isEmpty) {
                    $emptyConsecutive++;
                    if ($emptyConsecutive >= 25) break;
                    continue;
                }
                $emptyConsecutive = 0;

                if ($currentDataRowIndex < $offset) {
                    $currentDataRowIndex++;
                    continue;
                }

                $currentDataRowIndex++;
                $parsed = $this->validateAdcLocalInventoryRow($row, $currentDataRowIndex, $adcMap, $catMapByName, $catMapById, $defaultCatId);
                $batchRecords[] = $parsed;
                $rowsRead++;

                if ($rowsRead >= $batchSize) {
                    break;
                }
            }
            fclose($handle);
        }

        $isFinished = ($currentDataRowIndex >= $totalRows) || ($rowsRead === 0);

        $batchResult = $this->executeBatchDbInsert($batchRecords, $importToken);

        return response()->json([
            'success'        => true,
            'import_token'   => $importToken,
            'batch_imported' => $batchResult['imported'],
            'batch_skipped'  => $batchResult['skipped'],
            'batch_failed'   => $batchResult['failed'],
            'offset'         => $offset,
            'next_offset'    => $currentDataRowIndex,
            'is_finished'    => $isFinished,
            'total'          => $totalRows,
            'details'        => array_slice($batchResult['details'], 0, 50),
        ]);
    }

    private function convertUploadedFileToCsv($file, string $targetCsvPath): void
    {
        $pathname = $file->getPathname();
        $origName = $file->getClientOriginalName();
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (in_array($ext, ['csv', 'txt', 'tsv'])) {
            $delimiter = ($ext === 'tsv') ? "\t" : ",";
            $in = fopen($pathname, 'r');
            $out = fopen($targetCsvPath, 'w');
            if ($in && $out) {
                $bom = fread($in, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($in);
                }
                while (($data = fgetcsv($in, 0, $delimiter)) !== false) {
                    fputcsv($out, $data);
                }
                fclose($in);
                fclose($out);
                return;
            }
        }

        // Try ultra-fast streaming XMLReader for XLSX files
        if ($ext === 'xlsx') {
            try {
                if ($this->streamXlsxToCsv($pathname, $targetCsvPath)) {
                    return;
                }
            } catch (\Throwable $e) {
                \Log::warning("XMLReader XLSX streaming fallback to PhpSpreadsheet: " . $e->getMessage());
            }
        }

        $reader = IOFactory::createReaderForFile($pathname);
        $reader->setReadDataOnly(true);
        if (method_exists($reader, 'setReadEmptyCells')) {
            $reader->setReadEmptyCells(false);
        }
        $spreadsheet = $reader->load($pathname);
        $sheet = $spreadsheet->getActiveSheet();

        $out = fopen($targetCsvPath, 'w');
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $val = trim(str_replace(["\r", "\n", "\xC2\xA0", "\xA0"], ' ', (string)($cell->getValue() ?? '')));
                $rowData[] = $val;
            }
            fputcsv($out, $rowData);
        }
        fclose($out);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $sheet);
    }

    private function streamXlsxToCsv(string $xlsxPath, string $targetCsvPath): bool
    {
        if (!class_exists('\ZipArchive')) {
            return false;
        }

        $zip = new \ZipArchive();
        if ($zip->open($xlsxPath) !== true) {
            return false;
        }

        $sharedStrings = [];
        $sharedStringsIndex = $zip->locateName('xl/sharedStrings.xml');
        if ($sharedStringsIndex !== false) {
            $stream = $zip->getStream('xl/sharedStrings.xml');
            if ($stream) {
                $xml = new \XMLReader();
                $xml->open('zip://' . $xlsxPath . '#xl/sharedStrings.xml');
                while ($xml->read()) {
                    if ($xml->nodeType == \XMLReader::ELEMENT && $xml->name == 't') {
                        $sharedStrings[] = trim($xml->readString());
                    }
                }
                $xml->close();
                fclose($stream);
            }
        }

        $sheetPath = 'xl/worksheets/sheet1.xml';
        if ($zip->locateName($sheetPath) === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (str_starts_with($stat['name'], 'xl/worksheets/sheet') && str_ends_with($stat['name'], '.xml')) {
                    $sheetPath = $stat['name'];
                    break;
                }
            }
        }

        if ($zip->locateName($sheetPath) === false) {
            $zip->close();
            return false;
        }

        $out = fopen($targetCsvPath, 'w');
        if (!$out) {
            $zip->close();
            return false;
        }

        $xml = new \XMLReader();
        $xml->open('zip://' . $xlsxPath . '#' . $sheetPath);

        $currentRow = [];
        $cellType = '';

        while ($xml->read()) {
            if ($xml->nodeType == \XMLReader::ELEMENT) {
                if ($xml->name == 'row') {
                    $currentRow = [];
                } elseif ($xml->name == 'c') {
                    $cellType = $xml->getAttribute('t') ?? '';
                    $cellRef  = $xml->getAttribute('r') ?? '';
                    if ($cellRef) {
                        preg_match('/([A-Z]+)(\d+)/', $cellRef, $matches);
                        if (!empty($matches[1])) {
                            $colLetters = $matches[1];
                            $colIdx = 0;
                            for ($len = strlen($colLetters), $i = 0; $i < $len; $i++) {
                                $colIdx = $colIdx * 26 + (ord($colLetters[$i]) - ord('A') + 1);
                            }
                            $targetColIdx = $colIdx - 1;
                            while (count($currentRow) < $targetColIdx) {
                                $currentRow[] = '';
                            }
                        }
                    }
                } elseif ($xml->name == 'v' || $xml->name == 't') {
                    $val = trim($xml->readString());
                    if ($cellType === 's' && is_numeric($val)) {
                        $val = $sharedStrings[(int)$val] ?? '';
                    }
                    $currentRow[] = str_replace(["\r", "\n", "\xC2\xA0", "\xA0"], ' ', $val);
                }
            } elseif ($xml->nodeType == \XMLReader::END_ELEMENT) {
                if ($xml->name == 'row') {
                    if (!empty($currentRow)) {
                        fputcsv($out, $currentRow);
                    }
                    $currentRow = [];
                }
            }
        }

        $xml->close();
        fclose($out);
        $zip->close();

        return true;
    }

    private function validateAdcLocalInventoryRow(array $row, int $rowIndex, array $adcMap, array $catMapByName, array $catMapById, int $defaultCatId): array
    {
        $adcCode          = trim($row[0] ?? '');
        $productName      = trim($row[1] ?? '');
        $localProductCode = trim($row[2] ?? '');
        $categoryInput    = trim($row[3] ?? '');
        $hsnCode          = trim($row[4] ?? '');
        $barcode          = trim($row[5] ?? '');
        $uom              = trim($row[6] ?? '');
        $quantity         = trim($row[7] ?? '');
        $mrp              = trim($row[8] ?? '');
        $purchasePrice    = trim($row[9] ?? '');
        $landingCost      = trim($row[10] ?? '');
        $msp              = trim($row[11] ?? '');
        $csp              = trim($row[12] ?? '');
        $margin           = trim($row[13] ?? '');
        $adcMargin        = trim($row[14] ?? '');
        $cspMargin        = trim($row[15] ?? '');
        $gstRate          = trim($row[16] ?? '');
        $mfgDate          = trim($row[17] ?? '');
        $expireDate       = trim($row[18] ?? '');
        $batchNumber      = trim($row[19] ?? '');
        $referenceType    = trim($row[20] ?? '');
        $posStatus        = trim($row[21] ?? '');

        // Resolve ADC ID & Name with automatic fallback to first available ADC if missing/not found
        $defaultAdcEntry = !empty($adcMap) ? reset($adcMap) : null;
        $defaultAdcId    = $defaultAdcEntry['user_id'] ?? 1;
        $defaultAdcName  = $defaultAdcEntry['adc_name'] ?? 'System ADC';

        if ($adcCode === '') {
            $adcId   = $defaultAdcId;
            $adcName = $defaultAdcName;
            $adcCode = !empty($adcMap) ? (string)array_key_first($adcMap) : 'ADCB001';
        } else {
            $adcEntry = $adcMap[strtoupper($adcCode)] ?? null;
            if (!$adcEntry) {
                $adcId   = $defaultAdcId;
                $adcName = $defaultAdcName;
            } else {
                $adcId   = $adcEntry['user_id'];
                $adcName = $adcEntry['adc_name'];
            }
        }

        if ($productName === '') {
            $productName = 'Local Item Row ' . $rowIndex;
        }

        $categoryId = $defaultCatId;
        if ($categoryInput !== '') {
            if (isset($catMapById[(int)$categoryInput])) {
                $categoryId = (int)$categoryInput;
            } elseif (isset($catMapByName[strtolower($categoryInput)])) {
                $categoryId = $catMapByName[strtolower($categoryInput)];
            }
        }

        $qtyNum = 0.0;
        if ($quantity !== '' && is_numeric($quantity)) {
            $qtyNum = max(0.0, (float)$quantity);
        }

        $mrpNum = 0.0;
        if ($mrp !== '' && is_numeric($mrp) && (float)$mrp > 0) {
            $mrpNum = (float)$mrp;
        } elseif ($purchasePrice !== '' && is_numeric($purchasePrice) && (float)$purchasePrice > 0) {
            $mrpNum = (float)$purchasePrice;
        } elseif ($landingCost !== '' && is_numeric($landingCost) && (float)$landingCost > 0) {
            $mrpNum = (float)$landingCost;
        } elseif ($msp !== '' && is_numeric($msp) && (float)$msp > 0) {
            $mrpNum = (float)$msp;
        } elseif ($csp !== '' && is_numeric($csp) && (float)$csp > 0) {
            $mrpNum = (float)$csp;
        }

        $mfgDateFinal = null;
        if ($mfgDate !== '') {
            $mfgDateFinal = $this->parseExcelDate($mfgDate);
        }

        $expireDateFinal = null;
        if ($expireDate !== '') {
            $expireDateFinal = $this->parseExcelDate($expireDate);
        }

        $posStatusFinal = 'active';
        if ($posStatus !== '' && in_array(strtolower($posStatus), ['active', 'inactive'])) {
            $posStatusFinal = strtolower($posStatus);
        }

        $refUpper = strtoupper(str_replace([' ', '-'], '_', $referenceType));
        if ($refUpper === 'MSME_PO' || $refUpper === 'MSME') {
            $referenceTypeFinal = 'MSME_PO';
        } else {
            $referenceTypeFinal = 'LOCAL_PURCHASE';
        }

        $purchasePriceFinal = ($purchasePrice !== '' && is_numeric($purchasePrice)) ? (float)$purchasePrice : $mrpNum;
        $landingCostFinal   = ($landingCost !== '' && is_numeric($landingCost)) ? (float)$landingCost : $purchasePriceFinal;

        $marginFinal = ($margin !== '' && is_numeric($margin)) ? (float)$margin : null;
        if ($marginFinal === null && $mrpNum > 0) {
            $marginFinal = round($mrpNum - $purchasePriceFinal, 2);
        }

        return [
            'row_number'         => $rowIndex,
            'adc_code'           => $adcCode,
            'adc_id'             => $adcId,
            'adc_name'           => $adcName,
            'product_name'       => $productName,
            'local_product_code' => $localProductCode,
            'category_id'        => $categoryId,
            'category_input'     => $categoryInput,
            'hsn_code'           => $hsnCode ?: null,
            'barcode'            => $barcode ?: null,
            'unit_of_measure'    => $this->normalizeUom($uom, 'Piece'),
            'quantity'           => $qtyNum,
            'reserved_qty'       => 0,
            'sold_qty'           => 0,
            'returned_qty'       => 0,
            'mrp'                => $mrpNum,
            'purchase_price'     => $purchasePriceFinal,
            'landing_cost'       => $landingCostFinal,
            'msp'                => ($msp !== '' && is_numeric($msp)) ? (float)$msp : null,
            'csp'                => ($csp !== '' && is_numeric($csp)) ? (float)$csp : null,
            'margin'             => $marginFinal,
            'adc_margin'         => ($adcMargin !== '' && is_numeric($adcMargin)) ? (float)$adcMargin : 0,
            'csp_margin'         => ($cspMargin !== '' && is_numeric($cspMargin)) ? (float)$cspMargin : 0,
            'gst_rate'           => ($gstRate !== '' && is_numeric($gstRate)) ? (float)$gstRate : 0,
            'mfg_date'           => $mfgDateFinal,
            'expire_date'        => $expireDateFinal,
            'batch_number'       => $batchNumber ?: null,
            'reference_type'     => $referenceTypeFinal,
            'pos_status'         => $posStatusFinal,
            'is_valid'           => true,
            'errors'             => [],
        ];
    }

    private function executeBatchDbInsert(array $records, string $importToken = ''): array
    {
        $imported = 0;
        $skipped  = 0;
        $failed   = 0;
        $details  = [];

        if (empty($records)) {
            return compact('imported', 'skipped', 'failed', 'details');
        }

        $validRecords = [];
        foreach ($records as $r) {
            $r['is_valid'] = true;
            $validRecords[] = $r;
        }

        if (empty($validRecords)) {
            $this->appendLogsToCsv($importToken, $details);
            return compact('imported', 'skipped', 'failed', 'details');
        }

        // -------------------------------------------------------
        // PRE-FETCH & IN-MEMORY MAPS FOR EXTREME SPEED & ZERO FAILS
        // -------------------------------------------------------
        $adcIdsInBatch = array_unique(array_filter(array_column($validRecords, 'adc_id')));
        $codesInBatch  = array_unique(array_filter(array_map(fn($r) => !empty($r['local_product_code']) ? strtoupper(trim($r['local_product_code'])) : null, $validRecords)));
        $namesInBatch  = array_unique(array_filter(array_map(fn($r) => strtolower(trim($r['product_name'])), $validRecords)));

        $existingProducts = AdcLocalProduct::where(function ($q) use ($codesInBatch, $namesInBatch, $adcIdsInBatch) {
            if (!empty($codesInBatch)) {
                $q->whereIn('local_product_code', $codesInBatch);
            }
            if (!empty($namesInBatch)) {
                $q->orWhere(function ($sq) use ($adcIdsInBatch, $namesInBatch) {
                    $sq->whereIn('adc_id', $adcIdsInBatch)
                       ->whereIn(DB::raw('LOWER(product_name)'), $namesInBatch);
                });
            }
        })->get();

        $globalProductMapByCode = [];
        $adcProductMapByName   = [];
        $usedCodesMap          = [];

        foreach ($existingProducts as $p) {
            if ($p->local_product_code) {
                $cUpper = strtoupper(trim($p->local_product_code));
                $globalProductMapByCode[$cUpper] = $p;
                $usedCodesMap[$cUpper] = true;
            }
            $pNameLow = strtolower(trim($p->product_name));
            $adcProductMapByName["{$p->adc_id}:{$pNameLow}"] = $p;
        }

        $latest = AdcLocalProduct::where('local_product_code', 'LIKE', 'LCL%')
            ->orderByRaw('CAST(SUBSTRING(local_product_code, 4) AS UNSIGNED) DESC')
            ->first();
        $nextProductSeq = $latest ? (((int) substr($latest->local_product_code, 3)) + 1) : 1;

        $inventoriesToInsert = [];
        $now = now();

        DB::beginTransaction();
        try {
            foreach ($validRecords as $record) {
                try {
                    $adcId     = $record['adc_id'];
                    $pNameNorm = strtolower(trim($record['product_name']));
                    $pCodeNorm = !empty($record['local_product_code']) ? strtoupper(trim($record['local_product_code'])) : null;

                    $keyByName = "{$adcId}:{$pNameNorm}";
                    $localProduct = null;

                    // 1. Try match by Code
                    if ($pCodeNorm && isset($globalProductMapByCode[$pCodeNorm])) {
                        $localProduct = $globalProductMapByCode[$pCodeNorm];
                    }
                    // 2. Try match by Name for this ADC
                    elseif (isset($adcProductMapByName[$keyByName])) {
                        $localProduct = $adcProductMapByName[$keyByName];
                    }

                    if ($localProduct) {
                        $updateData = [];
                        if (empty($localProduct->barcode) && !empty($record['barcode'])) {
                            $updateData['barcode'] = $record['barcode'];
                        }
                        if (empty($localProduct->hsn_code) && !empty($record['hsn_code'])) {
                            $updateData['hsn_code'] = $record['hsn_code'];
                        }
                        if (!empty($record['gst_rate']) && (float)$localProduct->gst_rate === 0.0) {
                            $updateData['gst_rate'] = $record['gst_rate'];
                        }
                        if (!empty($updateData)) {
                            $localProduct->update($updateData);
                        }
                    } else {
                        // Determine unique code
                        $code = $pCodeNorm;
                        if (empty($code) || isset($usedCodesMap[$code])) {
                            do {
                                $code = 'LCL' . str_pad((string)$nextProductSeq++, 8, '0', STR_PAD_LEFT);
                            } while (isset($usedCodesMap[$code]));
                        }

                        $localProduct = AdcLocalProduct::create([
                            'adc_id'             => $adcId,
                            'local_product_code' => $code,
                            'product_name'       => $record['product_name'],
                            'category_id'        => $record['category_id'],
                            'hsn_code'           => $record['hsn_code'] ?: null,
                            'gst_rate'           => $record['gst_rate'] ?? 0,
                            'unit_of_measure'    => $this->normalizeUom($record['unit_of_measure'] ?? null, 'Piece'),
                            'barcode'            => $record['barcode'] ?: null,
                            'status'             => 'Active',
                        ]);

                        $usedCodesMap[$code] = true;
                        $globalProductMapByCode[$code] = $localProduct;
                        $adcProductMapByName[$keyByName] = $localProduct;
                    }

                    DB::table('adc_inventories')->insert([
                        'adc_id'                 => $adcId,
                        'inventory_source'       => 'LOCAL',
                        'product_id'             => null,
                        'adc_local_product_id'   => $localProduct->id,
                        'product_name'           => $localProduct->product_name,
                        'product_code'           => $localProduct->local_product_code,
                        'barcode'                => $record['barcode'] ?? $localProduct->barcode,
                        'unit_of_measure'        => $this->normalizeUom($record['unit_of_measure'] ?? $localProduct->unit_of_measure ?? null, 'Piece'),
                        'quantity'               => (float)($record['quantity'] ?? 0),
                        'reserved_quantity'      => 0,
                        'sold_quantity'          => 0,
                        'returned_quantity'      => 0,
                        'mrp'                    => (float)($record['mrp'] ?? 0),
                        'purchase_price'         => (float)($record['purchase_price'] ?? 0),
                        'landing_cost'           => (float)($record['landing_cost'] ?? 0),
                        'msp'                    => (float)($record['msp'] ?? 0),
                        'adc_price'              => (float)($record['adc_price'] ?? $record['msp'] ?? 0),
                        'csp'                    => (float)($record['csp'] ?? 0),
                        'adc_margin'             => (float)($record['adc_margin'] ?? 0),
                        'csp_margin'             => (float)($record['csp_margin'] ?? 0),
                        'gst_rate'               => (float)($record['gst_rate'] ?? $localProduct->gst_rate ?? 0),
                        'mfg_date'               => $record['mfg_date'] ?: null,
                        'expire_date'            => $record['expire_date'] ?: null,
                        'batch_number'           => $record['batch_number'] ?? null,
                        'adc_order_id'           => null,
                        'adc_order_item_id'      => null,
                        'reference_type'         => $record['reference_type'] ?? 'LOCAL_PURCHASE',
                        'reference_id'           => null,
                        'pos_status'             => $record['pos_status'] ?? 'active',
                        'created_at'             => $now,
                        'updated_at'             => $now,
                    ]);

                    $imported++;
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => ($record['adc_code'] ?? '—') . ' / ' . $localProduct->local_product_code,
                        'name'       => $localProduct->product_name,
                        'status'     => 'Imported',
                        'message'    => "Linked Local Product '{$localProduct->product_name}' ({$localProduct->local_product_code}) & added stock of {$record['quantity']}.",
                    ];
                } catch (\Exception $ex) {
                    $failed++;
                    $cleanMsg = $ex->getMessage();
                    if (str_contains($cleanMsg, 'Column') && str_contains($cleanMsg, 'cannot be null')) {
                        preg_match("/Column '([^']+)'/", $cleanMsg, $matches);
                        $colName = $matches[1] ?? 'field';
                        $cleanMsg = "Value missing for mandatory database column '{$colName}'.";
                    }
                    $details[] = [
                        'row'        => $record['row_number'] ?? '—',
                        'identifier' => ($record['adc_code'] ?? '—') . ' / ' . ($record['product_name'] ?? '—'),
                        'name'       => $record['product_name'] ?? '—',
                        'status'     => 'Failed',
                        'message'    => "Row " . ($record['row_number'] ?? '—') . " [" . ($record['product_name'] ?? 'Item') . "]: " . $cleanMsg,
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $imported = 0;
            $failed += count($validRecords);
            $details[] = [
                'row'        => '—',
                'identifier' => 'Batch Transaction',
                'name'       => 'Batch Error',
                'status'     => 'Failed',
                'message'    => 'Transaction failed: ' . $e->getMessage(),
            ];
        }

        $this->appendLogsToCsv($importToken, $details);

        return compact('imported', 'skipped', 'failed', 'details');
    }

    private function appendLogsToCsv(string $importToken, array $details): void
    {
        if (empty($importToken) || empty($details)) return;
        $tempDir = storage_path('app/temp_imports');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $logCsvPath = "{$tempDir}/{$importToken}_import_logs.csv";
        $isNew = !file_exists($logCsvPath);

        $out = fopen($logCsvPath, 'a');
        if ($out) {
            if ($isNew) {
                fputcsv($out, ['Row Number', 'Identifier', 'Name', 'Status', 'Message']);
            }
            foreach ($details as $d) {
                fputcsv($out, [
                    $d['row'] ?? '—',
                    $d['identifier'] ?? '—',
                    $d['name'] ?? '—',
                    $d['status'] ?? '—',
                    $d['message'] ?? '—',
                ]);
            }
            fclose($out);
        }
    }

    // =========================================================================
    // CLEAR / WIPE IMPORTED DATA METHODS
    // =========================================================================

    public function sellerClear()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $userIds = SellerProfile::whereNull('parent_id')->pluck('user_id')->filter();
            SellerProfile::whereNull('parent_id')->delete();
            if ($userIds->isNotEmpty()) {
                User::whereIn('id', $userIds)->delete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => true, 'message' => 'All imported sellers cleared successfully.']);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => false, 'message' => 'Failed to clear seller data: ' . $e->getMessage()], 500);
        }
    }

    public function subSellerClear()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $userIds = SellerProfile::whereNotNull('parent_id')->pluck('user_id')->filter();
            SellerProfile::whereNotNull('parent_id')->delete();
            if ($userIds->isNotEmpty()) {
                User::whereIn('id', $userIds)->delete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => true, 'message' => 'All imported sub-sellers cleared successfully.']);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => false, 'message' => 'Failed to clear sub-seller data: ' . $e->getMessage()], 500);
        }
    }

    public function adcClear()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $userIds = DB::table('adc_profiles')->pluck('user_id')->filter();
            DB::table('adc_profiles')->delete();
            if ($userIds->isNotEmpty()) {
                User::whereIn('id', $userIds)->delete();
            }
            User::where('role', 'adc')->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => true, 'message' => 'All imported ADCs cleared successfully.']);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => false, 'message' => 'Failed to clear ADC data: ' . $e->getMessage()], 500);
        }
    }

    public function ddcClear()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $userIds = DB::table('ddc_profiles')->pluck('user_id')->filter();
            DB::table('ddc_profiles')->delete();
            if ($userIds->isNotEmpty()) {
                User::whereIn('id', $userIds)->delete();
            }
            User::where('role', 'ddc')->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => true, 'message' => 'All imported DDCs cleared successfully.']);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => false, 'message' => 'Failed to clear DDC data: ' . $e->getMessage()], 500);
        }
    }

    public function productClear()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Product::query()->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => true, 'message' => 'All imported products cleared successfully.']);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => false, 'message' => 'Failed to clear product data: ' . $e->getMessage()], 500);
        }
    }

    public function pickupStoreClear()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            PickupStore::query()->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => true, 'message' => 'All imported pickup stores cleared successfully.']);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => false, 'message' => 'Failed to clear pickup store data: ' . $e->getMessage()], 500);
        }
    }

    public function deliveryAreaClear()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            PickupStoreDeliveryArea::query()->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => true, 'message' => 'All imported delivery areas cleared successfully.']);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => false, 'message' => 'Failed to clear delivery area data: ' . $e->getMessage()], 500);
        }
    }

    public function ddcCustomerClear()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('ddc_pos_customers')->delete();
            User::where('role', 'ddc_customer')->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => true, 'message' => 'All imported DDC customers cleared successfully.']);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => false, 'message' => 'Failed to clear DDC customer data: ' . $e->getMessage()], 500);
        }
    }

    public function adcCustomerClear()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('adc_pos_customers')->delete();
            User::where('role', 'adc_customer')->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => true, 'message' => 'All imported ADC customers cleared successfully.']);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => false, 'message' => 'Failed to clear ADC customer data: ' . $e->getMessage()], 500);
        }
    }

    public function ddcMsmeInventoryClear()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('ddc_inventories')->where('inventory_source', 'MSME')->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => true, 'message' => 'All DDC MSME inventory records cleared successfully.']);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => false, 'message' => 'Failed to clear DDC MSME inventory data: ' . $e->getMessage()], 500);
        }
    }

    public function ddcLocalInventoryClear()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('ddc_inventories')->where('inventory_source', 'LOCAL')->delete();
            DdcLocalProduct::query()->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => true, 'message' => 'All DDC Local inventory & products cleared successfully.']);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => false, 'message' => 'Failed to clear DDC Local inventory data: ' . $e->getMessage()], 500);
        }
    }

    public function adcMsmeInventoryClear()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('adc_inventories')->where('inventory_source', 'MSME')->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => true, 'message' => 'All ADC MSME inventory records cleared successfully.']);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => false, 'message' => 'Failed to clear ADC MSME inventory data: ' . $e->getMessage()], 500);
        }
    }

    public function adcLocalInventoryClear()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('adc_inventories')->where('inventory_source', 'LOCAL')->delete();
            AdcLocalProduct::query()->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => true, 'message' => 'All ADC Local inventory & products cleared successfully.']);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return response()->json(['success' => false, 'message' => 'Failed to clear ADC Local inventory data: ' . $e->getMessage()], 500);
        }
    }
}

