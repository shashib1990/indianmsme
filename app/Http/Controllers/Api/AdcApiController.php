<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdcProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdcApiController extends Controller
{
    /**
     * Public Unauthenticated API to update abhhyam_code in adc_profiles table matching by adc_code.
     * Method: POST
     * Parameters: adc_code (required), abhhyam_code (required)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAbhhyamCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'adc_code'     => ['required', 'string'],
            'abhhyam_code' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $adcCode     = trim($request->adc_code);
        $abhhyamCode = trim($request->abhhyam_code);

        // 1. Find profile by adc_code in adc_profiles table
        $adcProfile = AdcProfile::where('adc_code', $adcCode)->first();

        // 2. If not found in adc_profiles directly, check users table by adc_code or code
        if (!$adcProfile) {
            $user = User::where('role', 'adc')
                ->where(function ($q) use ($adcCode) {
                    $q->where('adc_code', $adcCode)
                      ->orWhere('code', $adcCode);
                })
                ->first();

            if ($user) {
                $adcProfile = $user->adcProfile()->firstOrCreate([]);
            }
        }

        if (!$adcProfile) {
            return response()->json([
                'success' => false,
                'message' => "ADC profile not found for adc_code: '{$adcCode}'.",
            ], 404);
        }

        // Update abhhyam_code in adc_profiles
        $adcProfile->abhhyam_code = $abhhyamCode;
        $adcProfile->save();

        // Also update linked User model if exists
        $user = $adcProfile->user ?: User::find($adcProfile->user_id);
        if ($user) {
            $user->abhhyam_code = $abhhyamCode;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Abhhyam code updated successfully.',
            'data'    => [
                'adc_code'       => $adcCode,
                'abhhyam_code'   => $abhhyamCode,
                'adc_profile_id' => $adcProfile->id,
                'user_id'        => $adcProfile->user_id,
            ],
        ], 200);
    }

    /**
     * Public Unauthenticated API to update abhhyam_code in POS Customer tables matching by mobile/phone number.
     * Method: POST
     * Parameters: mobile or phone (required), abhhyam_code (required)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCustomerAbhhyamCode(Request $request)
    {
        $mobile = trim((string) $request->input('mobile', $request->input('phone')));

        $validator = Validator::make([
            'mobile'       => $mobile,
            'abhhyam_code' => $request->input('abhhyam_code'),
        ], [
            'mobile'       => ['required', 'string'],
            'abhhyam_code' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $abhhyamCode = trim((string) $request->input('abhhyam_code'));

        // Search and update matching POS customers
        $adcCustomers = \App\Models\AdcPosCustomer::where('mobile', $mobile)->get();
        $ddcCustomers = \App\Models\DdcPosCustomer::where('mobile', $mobile)->get();

        if ($adcCustomers->isEmpty() && $ddcCustomers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "POS Customer not found with mobile/phone number: '{$mobile}'.",
            ], 404);
        }

        $updatedAdcCount = 0;
        foreach ($adcCustomers as $cust) {
            $cust->abhhyam_code = $abhhyamCode;
            $cust->save();
            $updatedAdcCount++;
        }

        $updatedDdcCount = 0;
        foreach ($ddcCustomers as $cust) {
            $cust->abhhyam_code = $abhhyamCode;
            $cust->save();
            $updatedDdcCount++;
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer Abhhyam code updated successfully.',
            'data'    => [
                'mobile'                => $mobile,
                'abhhyam_code'          => $abhhyamCode,
                'adc_customers_updated' => $updatedAdcCount,
                'ddc_customers_updated' => $updatedDdcCount,
            ],
        ], 200);
    }

    /**
     * Public Unauthenticated API to update abhhyam_code in ddc_pos_customers table matching by mobile/phone number.
     * Method: POST
     * Parameters: mobile or phone (required), abhhyam_code (required)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDdcCustomerAbhhyamCode(Request $request)
    {
        $mobile = trim((string) $request->input('mobile', $request->input('phone')));

        $validator = Validator::make([
            'mobile'       => $mobile,
            'abhhyam_code' => $request->input('abhhyam_code'),
        ], [
            'mobile'       => ['required', 'string'],
            'abhhyam_code' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $abhhyamCode = trim((string) $request->input('abhhyam_code'));

        // Search and update matching DDC POS customers
        $ddcCustomers = \App\Models\DdcPosCustomer::where('mobile', $mobile)->get();

        if ($ddcCustomers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "DDC POS Customer not found with mobile/phone number: '{$mobile}'.",
            ], 404);
        }

        $updatedDdcCount = 0;
        foreach ($ddcCustomers as $cust) {
            $cust->abhhyam_code = $abhhyamCode;
            $cust->save();
            $updatedDdcCount++;
        }

        return response()->json([
            'success' => true,
            'message' => 'DDC Customer Abhhyam code updated successfully.',
            'data'    => [
                'mobile'                => $mobile,
                'abhhyam_code'          => $abhhyamCode,
                'ddc_customers_updated' => $updatedDdcCount,
            ],
        ], 200);
    }

    /**
     * POST API: Fetch complete ADC data including details, canteen name, stock summary, and products list with price and stock.
     * Method: POST
     * Endpoint: /api/adc/details
     * Parameters: adc_id (optional), adc_code (optional), mobile (optional), email (optional)
     * Optional filters: search, category, in_stock_only, page, per_page
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdcDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'adc_id'        => ['nullable'],
            'adc_code'      => ['nullable', 'string'],
            'mobile'        => ['nullable', 'string'],
            'email'         => ['nullable', 'string'],
            'search'        => ['nullable', 'string'],
            'category'      => ['nullable', 'string'],
            'in_stock_only' => ['nullable'],
            'page'          => ['nullable', 'integer', 'min:1'],
            'per_page'      => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $adcId    = $request->input('adc_id');
        $adcCode  = trim((string) $request->input('adc_code'));
        $mobile   = trim((string) $request->input('mobile'));
        $email    = trim((string) $request->input('email'));

        // If no identifier provided, check if user is authenticated via Sanctum or session guard
        if (empty($adcId) && empty($adcCode) && empty($mobile) && empty($email)) {
            $currentUser = auth('sanctum')->user() ?: auth('adc')->user();
            if ($currentUser) {
                $adcId = $currentUser->id;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide at least one identifier parameter: adc_id, adc_code, mobile, or email.',
                ], 422);
            }
        }

        // Query User with role 'adc'
        $query = User::where('role', 'adc')
            ->with(['adcProfile.assignedDdc.ddcProfile', 'department']);

        if (!empty($adcId)) {
            $query->where('id', $adcId);
        } elseif (!empty($adcCode)) {
            $query->whereHas('adcProfile', function ($pq) use ($adcCode) {
                $pq->where('adc_code', $adcCode)
                   ->orWhere('code', $adcCode);
            });
        } elseif (!empty($mobile)) {
            $query->where(function ($q) use ($mobile) {
                $q->where('mobile', $mobile)
                  ->orWhereHas('adcProfile', function ($pq) use ($mobile) {
                      $pq->where('official_phone', $mobile);
                  });
            });
        } elseif (!empty($email)) {
            $query->where(function ($q) use ($email) {
                $q->where('email', $email)
                  ->orWhereHas('adcProfile', function ($pq) use ($email) {
                      $pq->where('official_email', $email);
                  });
            });
        }

        $adcUser = $query->first();

        if (!$adcUser) {
            return response()->json([
                'success' => false,
                'message' => 'ADC not found with provided identifier.',
            ], 404);
        }

        $adcProfile = $adcUser->adcProfile;

        // Resolve Canteen Name with consistent hierarchy across the project
        $canteenName = $adcProfile?->company_name
            ?: ($adcProfile?->trade_name
                ?: ($adcUser->company_name
                    ?: ($adcUser->name ?: 'ADC Canteen')));

        // Resolve Display ADC Code
        $resolvedAdcCode = $adcProfile?->adc_code
            ?: ($adcUser->adc_code
                ?: ($adcProfile?->code
                    ?: ($adcUser->code ?: 'ADC' . str_pad((string) $adcUser->id, 3, '0', STR_PAD_LEFT))));

        // Build Inventory Query for this ADC
        $invQuery = \App\Models\AdcInventory::where('adc_id', $adcUser->id)
            ->with([
                'product.category',
                'product.subCategory',
                'product.childCategory',
                'product.brand',
                'localProduct.category',
                'localProduct.brand',
            ]);

        // Search filter (Product Name, Product Code, Barcode, Batch Number)
        $search = trim((string) $request->input('search'));
        if (!empty($search)) {
            $invQuery->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('batch_number', 'like', "%{$search}%");
            });
        }

        // Category filter
        $category = trim((string) $request->input('category'));
        if (!empty($category) && strtolower($category) !== 'all') {
            $invQuery->where(function ($q) use ($category) {
                $q->whereHas('product.category', function ($cq) use ($category) {
                    $cq->where('name', 'like', "%{$category}%");
                })->orWhereHas('localProduct.category', function ($cq) use ($category) {
                    $cq->where('name', 'like', "%{$category}%");
                });
            });
        }

        // In-stock only filter
        $inStockOnly = filter_var($request->input('in_stock_only', false), FILTER_VALIDATE_BOOLEAN);
        if ($inStockOnly) {
            $invQuery->whereRaw('(quantity - COALESCE(reserved_quantity, 0)) > 0');
        }

        // Calculate Stock & Inventory Summary before pagination
        $totalProductsCount = (clone $invQuery)->count();
        $inStockProductsCount = (clone $invQuery)->whereRaw('(quantity - COALESCE(reserved_quantity, 0)) > 0')->count();
        $outOfStockProductsCount = (clone $invQuery)->whereRaw('(quantity - COALESCE(reserved_quantity, 0)) <= 0')->count();
        $totalQuantitySum = (float) ((clone $invQuery)->sum('quantity') ?? 0);
        $totalAvailableQuantitySum = (float) ((clone $invQuery)->sum(\DB::raw('GREATEST(0, quantity - COALESCE(reserved_quantity, 0))')) ?? 0);
        $totalStockValuationAtMrp = (float) ((clone $invQuery)->sum(\DB::raw('quantity * mrp')) ?? 0);
        $totalStockValuationAtCost = (float) ((clone $invQuery)->sum(\DB::raw('quantity * purchase_price')) ?? 0);

        // Pagination
        $perPage = (int) $request->input('per_page', 50);
        $page = (int) $request->input('page', 1);
        $paginator = $invQuery->orderBy('product_name', 'asc')->paginate($perPage, ['*'], 'page', $page);

        // Format product list
        $productsList = collect($paginator->items())->map(function ($inv) {
            $source = strtoupper($inv->inventory_source ?: 'MSME');
            $salePrice = (float) ($inv->csp > 0 ? $inv->csp : $inv->mrp);
            $availableStock = max(0, (float) ($inv->quantity - ($inv->reserved_quantity ?? 0)));

            // Product & Category Info
            $categoryName = null;
            $subCategoryName = null;
            $childCategoryName = null;
            $brandName = null;
            $description = null;
            $hsnCode = null;
            $imageUrl = null;

            if ($inv->product) {
                $categoryName = $inv->product->category?->name;
                $subCategoryName = $inv->product->subCategory?->name;
                $childCategoryName = $inv->product->childCategory?->name;
                $brandName = $inv->product->brand?->name;
                $description = $inv->product->description;
                $hsnCode = $inv->product->hsn_code;
                $imageUrl = $inv->product->featured_image
                    ? (filter_var($inv->product->featured_image, FILTER_VALIDATE_URL) ? $inv->product->featured_image : asset('storage/' . $inv->product->featured_image))
                    : null;
            } elseif ($inv->localProduct) {
                $categoryName = $inv->localProduct->category?->name;
                $brandName = $inv->localProduct->brand?->name;
                $hsnCode = $inv->localProduct->hsn_code;
                $imageUrl = $inv->localProduct->product_image
                    ? (filter_var($inv->localProduct->product_image, FILTER_VALIDATE_URL) ? $inv->localProduct->product_image : asset('storage/' . $inv->localProduct->product_image))
                    : null;
            }

            return [
                'inventory_id'        => $inv->id,
                'source'              => $source,
                'product_id'          => $inv->product_id,
                'local_product_id'    => $inv->adc_local_product_id,
                'product_name'        => $inv->product_name ?: ($inv->product?->product_name ?: ($inv->localProduct?->product_name ?: 'Unnamed Product')),
                'product_code'        => $inv->product_code ?: ($inv->product?->product_code ?: ($inv->localProduct?->local_product_code ?: null)),
                'barcode'             => $inv->barcode ?: ($inv->product?->barcode ?: ($inv->localProduct?->barcode ?: null)),
                'category'            => $categoryName,
                'sub_category'        => $subCategoryName,
                'child_category'      => $childCategoryName,
                'brand'               => $brandName,
                'description'         => $description,
                'hsn_code'            => $hsnCode,
                'image_url'           => $imageUrl,
                'unit_of_measure'     => $inv->unit_of_measure ?: ($inv->product?->unit_of_measure ?: ($inv->localProduct?->unit_of_measure ?: 'Unit')),
                'batch_number'        => $inv->batch_number,
                'mfg_date'            => $inv->mfg_date ? \Carbon\Carbon::parse($inv->mfg_date)->format('Y-m-d') : null,
                'expire_date'         => $inv->expire_date ? \Carbon\Carbon::parse($inv->expire_date)->format('Y-m-d') : null,
                'is_expired'          => $inv->expire_date ? (\Carbon\Carbon::parse($inv->expire_date)->isPast()) : false,
                'pricing'             => [
                    'mrp'            => (float) $inv->mrp,
                    'purchase_price' => (float) $inv->purchase_price,
                    'landing_cost'   => (float) $inv->landing_cost,
                    'msp'            => (float) $inv->msp,
                    'adc_price'      => (float) $inv->adc_price,
                    'csp'            => (float) $inv->csp,
                    'selling_price'  => $salePrice,
                    'gst_rate'       => (float) ($inv->gst_rate ?? ($inv->product?->gst_rate ?? ($inv->localProduct?->gst_rate ?? 0))),
                    'adc_margin'     => (float) $inv->adc_margin,
                    'csp_margin'     => (float) $inv->csp_margin,
                ],
                'stock'               => [
                    'total_quantity'     => (float) $inv->quantity,
                    'available_quantity' => $availableStock,
                    'reserved_quantity'  => (float) ($inv->reserved_quantity ?? 0),
                    'sold_quantity'      => (float) ($inv->sold_quantity ?? 0),
                    'returned_quantity'  => (float) ($inv->returned_quantity ?? 0),
                    'is_in_stock'        => $availableStock > 0,
                ],
                'pos_status'          => $inv->pos_status ?? 'Active',
            ];
        });

        // Resolve Assigned DDC Code and Name
        $assignedDdcUser = $adcProfile?->assignedDdc;
        $resolvedDdcCode = $assignedDdcUser?->ddcProfile?->ddc_code
            ?: ($assignedDdcUser?->code
                ?: ($assignedDdcUser?->adc_code ?: null));
        $resolvedDdcName = $assignedDdcUser?->name
            ?: ($assignedDdcUser?->company_name ?: null);

        // Structure complete ADC Response
        return response()->json([
            'success' => true,
            'message' => 'ADC details and product catalog fetched successfully.',
            'data'    => [
                'adc' => [
                    'id'                     => $adcUser->id,
                    'canteen_id'             => $adcUser->id,
                    'name'                   => $adcUser->name,
                    'email'                  => $adcUser->email,
                    'mobile'                 => $adcUser->mobile ?: ($adcProfile?->official_phone ?: null),
                    'adc_code'               => $resolvedAdcCode,
                    'canteen_code'           => $resolvedAdcCode,
                    'canteen_name'           => $canteenName,
                    'trade_name'             => $adcProfile?->trade_name,
                    'company_name'           => $adcProfile?->company_name,
                    'type'                   => $adcProfile?->type ?: 'ADC',
                    'role'                   => $adcUser->role,
                    'status'                 => $adcUser->status === '1' || $adcUser->status === 1 ? 'Active' : 'Inactive',
                    'pos_status'             => $adcProfile?->pos_status ?: 'Active',
                    'show_order'             => $adcProfile?->show_order ?: 'Yes',
                    'kyc_status'             => $adcProfile?->kyc_status ?: 'Pending',
                    'abhhyam_code'           => $adcProfile?->abhhyam_code ?: ($adcUser->abhhyam_code ?: null),
                    'advisor_code'           => $adcProfile?->advisor_code ?: ($adcUser->advisor_code ?: null),
                    'gst_number'             => $adcProfile?->gst_number,
                    'pan_no'                 => $adcProfile?->pan_no ?: $adcUser->pan_no,
                    'aadhaar_no'             => $adcProfile?->aadhaar_no ?: $adcUser->aadhaar_no,
                    'wallet_balance'         => (float) ($adcProfile?->wallet_balance ?? ($adcUser->wallet_balance ?? 0)),
                    'address'                => [
                        'street_address' => $adcProfile?->address ?: $adcUser->address,
                        'district'       => $adcProfile?->district ?: $adcUser->district,
                        'state'          => $adcProfile?->state ?: $adcUser->state,
                        'pincode'        => $adcProfile?->pincode ?: $adcUser->pincode,
                        'state_id'       => $adcProfile?->state_id ?: $adcUser->state_id,
                        'district_id'    => $adcProfile?->district_id ?: $adcUser->district_id,
                    ],
                    'bank_details'           => [
                        'account_holder_name' => $adcProfile?->account_holder_name,
                        'bank_account_number' => $adcProfile?->bank_account_number,
                        'ifsc_code'           => $adcProfile?->ifsc_code,
                        'bank_name'           => $adcProfile?->bank_name,
                        'branch_name'         => $adcProfile?->branch_name,
                    ],
                    'department'             => $adcUser->department ? [
                        'id'   => $adcUser->department->id,
                        'name' => $adcUser->department->name,
                    ] : null,
                    'assigned_ddc_id'        => $adcProfile?->assigned_ddc_id,
                    'assigned_ddc_code'      => $resolvedDdcCode,
                    'ddc_code'               => $resolvedDdcCode,
                    'ddc_name'               => $resolvedDdcName,
                    'first_sale_at'          => $adcProfile?->first_sale_at ? $adcProfile->first_sale_at->format('Y-m-d H:i:s') : null,
                    'created_at'             => $adcUser->created_at ? $adcUser->created_at->format('Y-m-d H:i:s') : null,
                ],
                'summary' => [
                    'total_products_count'        => $totalProductsCount,
                    'in_stock_products_count'     => $inStockProductsCount,
                    'out_of_stock_products_count' => $outOfStockProductsCount,
                    'total_quantity'              => round($totalQuantitySum, 3),
                    'total_available_quantity'    => round($totalAvailableQuantitySum, 3),
                    'total_stock_value_mrp'       => round($totalStockValuationAtMrp, 2),
                    'total_stock_value_cost'      => round($totalStockValuationAtCost, 2),
                ],
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total_items'  => $paginator->total(),
                    'total_pages'  => $paginator->lastPage(),
                    'has_more'     => $paginator->hasMorePages(),
                ],
                'products' => $productsList,
            ],
        ], 200);
    }
}
