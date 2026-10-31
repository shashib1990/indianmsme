<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\AdcProfile;
use App\Models\DdcInventory;
use App\Models\DdcPosCustomer;
use App\Models\DdcPosOrder;
use App\Models\DdcPosOrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $mode = strtolower($request->get('mode', 'retail'));
        if ($mode === 'wholesale') {
            return redirect()->route('ddc.pos.wholesale');
        }
        return redirect()->route('ddc.pos.retail');
    }

    public function retail(Request $request)
    {
        $mode = 'retail';
        $search = $request->get('search', '');
        return view('ddc.pos.retail', compact('mode', 'search'));
    }

    public function wholesale(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $mode = 'wholesale';
        $search = $request->get('search', '');
        $adcs = $this->getAssignedAdcsForDdc($ddcId);
        return view('ddc.pos.wholesale', compact('mode', 'search', 'adcs'));
    }

    private function getAssignedAdcsForDdc($ddcId)
    {
        // Pincode-mapped ADCs only (strictly from ddc_pincodes table)
        $pincodeIds = DB::table('ddc_pincodes')->where('ddc_id', $ddcId)->pluck('pincode_id')->toArray();
        $mappedPincodes = DB::table('pincodes')->whereIn('id', $pincodeIds)->pluck('pincode')->toArray();
        $mappedPincodes = array_unique(array_filter($mappedPincodes));

        return User::where('role', 'adc')
            ->where('status', '1')
            ->whereHas('adcProfile', function ($apq) use ($mappedPincodes) {
                $apq->whereIn('pincode', $mappedPincodes);
            })
            ->with(['adcProfile'])
            ->orderBy('name')
            ->get();
    }

    public function getProducts(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $mode = strtolower($request->get('mode', 'retail'));
        $search = trim($request->get('search', ''));
        $page = (int) $request->get('page', 1);
        $perPage = 16;
        $today = date('Y-m-d');

        $query = DdcInventory::where('ddc_id', $ddcId)
            ->where('pos_status', 'Active')
            // Enforce available quantity > 0 (quantity - reserved_quantity) and NOT expired for ALL searches & listings
            ->whereRaw('(quantity - COALESCE(reserved_quantity, 0)) > 0')
            ->where(function ($q) use ($today) {
                $q->whereNull('expire_date')
                    ->orWhereDate('expire_date', '>', $today);
            })
            ->with(['product', 'localProduct']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('product_name')->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($inv) use ($mode) {
            $salePrice = $mode === 'wholesale'
                ? ((float) $inv->msp > 0 ? (float) $inv->msp : ((float) $inv->csp > 0 ? (float) $inv->csp : (float) $inv->mrp))
                : ((float) $inv->csp > 0 ? (float) $inv->csp : (float) $inv->mrp);

            $imageUrl = null;
            if ($inv->product) {
                $imageUrl = $inv->product->primary_image_url ?: ($inv->product->image ? (filter_var($inv->product->image, FILTER_VALIDATE_URL) ? $inv->product->image : asset('storage/' . $inv->product->image)) : null);
            } elseif ($inv->localProduct) {
                $imageUrl = $inv->localProduct->primary_image_url ?: ($inv->localProduct->image ? (filter_var($inv->localProduct->image, FILTER_VALIDATE_URL) ? $inv->localProduct->image : asset('storage/' . $inv->localProduct->image)) : null);
            }

            return [
                'id' => $inv->id,
                'product_name' => $inv->product_name,
                'product_code' => $inv->product_code,
                'barcode' => $inv->barcode,
                'mrp' => (float) $inv->mrp,
                'msp' => (float) $inv->msp,
                'csp' => (float) $inv->csp,
                'price' => $salePrice,
                'gst_rate' => (float) ($inv->gst_rate ?? ($inv->product?->gst_rate ?? ($inv->localProduct?->gst_rate ?? 0))),
                'quantity' => (float) $inv->available_quantity,
                'unit_of_measure' => $inv->unit_of_measure ?: ($inv->product?->unit_of_measure ?: ($inv->localProduct?->unit_of_measure ?: 'Unit')),
                'batch_number' => $inv->batch_number,
                'expire_date' => $inv->expire_date ? \Carbon\Carbon::parse($inv->expire_date)->format('Y-m-d') : null,
                'image_url' => $imageUrl,
            ];
        });

        return response()->json([
            'status' => true,
            'items' => $items,
            'has_more' => $paginator->hasMorePages(),
            'total' => $paginator->total(),
            'page' => $page,
        ]);
    }

    public function searchCustomer(Request $request)
    {
        $search = trim($request->get('search', $request->get('mobile', '')));
        if (strlen($search) < 2) {
            return response()->json(['status' => false, 'found' => false, 'customer' => null, 'customers' => []]);
        }

        $ddcId = Auth::guard('ddc')->id();

        // 1. Search in DdcPosCustomer table across all DDCs
        $customer = DdcPosCustomer::where('mobile', 'like', "%{$search}%")
            ->orWhere('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->first();

        // 2. If not found in DdcPosCustomer, search in main Users table
        if (!$customer) {
            $user = User::where('mobile', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->first();

            if ($user) {
                $customer = DdcPosCustomer::firstOrCreate(
                    ['mobile' => $user->mobile],
                    [
                        'ddc_id' => $ddcId,
                        'name' => $user->name,
                        'email' => $user->email,
                        'address' => $user->address ?? null,
                    ]
                );
            }
        }

        // 3. Fetch list of all matching customers
        $customers = DdcPosCustomer::where('mobile', 'like', "%{$search}%")
            ->orWhere('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->limit(10)
            ->get();

        if ($customer) {
            return response()->json([
                'status' => true,
                'found' => true,
                'customer' => $customer,
                'customers' => $customers,
            ]);
        }

        return response()->json([
            'status' => false,
            'found' => false,
            'customer' => null,
            'customers' => [],
        ]);
    }

    public function storeCustomer(Request $request)
    {
        return $this->saveCustomer($request);
    }

    public function saveCustomer(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();

        $request->validate([
            'name' => 'required|string|max:100',
            'mobile' => 'required|string|max:15',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'gst_number' => 'nullable|string|min:15|max:15',
        ], [
            'gst_number.min' => 'GSTIN must be exactly 15 characters long.',
            'gst_number.max' => 'GSTIN must be exactly 15 characters long.',
        ]);

        $cleanMobile = trim($request->mobile);

        $existingCustomer = DdcPosCustomer::where('ddc_id', $ddcId)->where('mobile', $cleanMobile)->first()
            ?? DdcPosCustomer::where('mobile', $cleanMobile)->first();

        if ($existingCustomer) {
            return response()->json([
                'status' => false,
                'already_exists' => true,
                'message' => "Customer with phone number '{$cleanMobile}' is already added ({$existingCustomer->name}).",
                'customer' => $existingCustomer,
            ]);
        }

        $customer = DdcPosCustomer::create([
            'ddc_id' => $ddcId,
            'mobile' => $cleanMobile,
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'pincode' => $request->pincode,
            'gst_number' => $request->gst_number ? strtoupper($request->gst_number) : null,
        ]);

        // Register customer on external Abhhyam API
        \App\Services\AbhhyamService::registerCustomer($customer, 'DDC');

        return response()->json([
            'status' => true,
            'message' => 'Customer saved successfully.',
            'customer' => $customer,
        ]);
    }

    public function checkout(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $ddcUser = User::with('ddcProfile')->find($ddcId);
        $ddcProfile = $ddcUser?->ddcProfile;
        $walletBal = (float) ($ddcProfile?->wallet_balance ?? 0);

        if (!$ddcUser || $ddcUser->status == 0 || $ddcUser->status === '0' || $walletBal <= 0) {
            $reason = $ddcProfile?->deactivation_reason ?: 'POS sales are deactivated because your wallet balance has reached ₹0.00. Please recharge your wallet to resume sales.';
            return response()->json(['status' => false, 'message' => 'Checkout failed: ' . $reason], 403);
        }

        $request->validate([
            'mode' => 'required|in:retail,wholesale',
            'payment_mode' => 'nullable|in:CASH,UPI,CARD,WALLET,CREDIT',
            'discount_amount' => 'nullable|numeric|min:0',
            'special_discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required_without:items.*.inventory_id',
            'items.*.qty' => 'required|numeric|min:0.001',
            'adc_user_id' => 'required_if:mode,wholesale|nullable|exists:users,id',
        ]);

        $mode = $request->mode;
        $discount = (float) ($request->discount_amount ?? 0);
        $specialDiscount = (float) ($request->special_discount_amount ?? 0);
        $tax = (float) ($request->tax_amount ?? 0);
        $today = date('Y-m-d');

        $customerId = $request->customer_id;
        $customerName = $request->customer_name;
        $customerMobile = $request->customer_mobile;
        $adcUserId = null;
        $paymentMode = $request->payment_mode ?: 'CASH';

        if ($mode === 'wholesale') {
            $adcUserId = $request->adc_user_id;
            $adcUser = User::where('id', $adcUserId)->where('role', 'adc')->first();
            if (!$adcUser) {
                return response()->json(['status' => false, 'message' => 'Please select a valid ADC Canteen.'], 422);
            }

            $adcProfile = AdcProfile::where('user_id', $adcUser->id)->first();
            $customerName = $adcProfile?->company_name ?: $adcUser->name;
            $customerMobile = $adcUser->mobile;
            $paymentMode = 'WALLET';
        } else {
            if (empty($customerId) && empty($customerMobile) && empty($customerName)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer selection is required for Retail POS checkout. Please search & select or add a customer.'
                ], 422);
            }

            if ($customerMobile && !$customerId) {
                $existingCust = DdcPosCustomer::where('ddc_id', $ddcId)->where('mobile', $customerMobile)->first();
                if ($existingCust) {
                    $customerId = $existingCust->id;
                    $customerName = $existingCust->name;
                } else if ($customerName) {
                    $newCust = DdcPosCustomer::create([
                        'ddc_id' => $ddcId,
                        'name' => $customerName,
                        'mobile' => $customerMobile,
                        'email' => $request->customer_email,
                        'address' => $request->customer_address,
                        'gst_number' => $request->customer_gst ? strtoupper($request->customer_gst) : null,
                    ]);
                    $customerId = $newCust->id;
                }
            }
        }

        DB::beginTransaction();
        try {
            $totalItems = 0;
            $subtotal = 0;
            $orderItemsData = [];

            foreach ($request->items as $itemData) {
                $invId = $itemData['id'] ?? ($itemData['inventory_id'] ?? null);
                $inventory = DdcInventory::where('id', $invId)
                    ->where('ddc_id', $ddcId)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    throw new \Exception("Inventory item ID {$invId} not found.");
                }

                $qty = (float) $itemData['qty'];

                $uom = strtolower($inventory->unit_of_measure ?: ($inventory->product?->unit_of_measure ?: ($inventory->localProduct?->unit_of_measure ?: '')));
                $allowDecimal = str_contains($uom, 'kg') || str_contains($uom, 'gram') || str_contains($uom, 'gm') || str_contains($uom, 'liter') || str_contains($uom, 'litre') || str_contains($uom, 'ltr') || str_contains($uom, 'ml');

                if (!$allowDecimal && $qty != floor($qty)) {
                    $uomDisp = $inventory->unit_of_measure ?: ($inventory->product?->unit_of_measure ?: ($inventory->localProduct?->unit_of_measure ?: 'Piece'));
                    throw new \Exception("Quantity for '{$inventory->product_name}' (Unit: {$uomDisp}) must be a whole number.");
                }

                // 1. Stock Quantity Validation Check (checking available stock after reserved stock)
                $availableStock = (float) $inventory->available_quantity;
                if ($availableStock <= 0) {
                    throw new \Exception("Product '{$inventory->product_name}' is currently out of stock / reserved for online orders (Available: 0). Please remove it from cart.");
                }

                if ($availableStock < $qty) {
                    throw new \Exception("Insufficient stock for '{$inventory->product_name}'. Available stock: {$availableStock} (Reserved stock: " . (float)($inventory->reserved_quantity ?? 0) . "), Requested: {$qty}. Please adjust cart quantity.");
                }

                // 2. Product Expiry Validation Check
                if (!empty($inventory->expire_date) && strtotime($inventory->expire_date) <= strtotime($today)) {
                    $expFormatted = \Carbon\Carbon::parse($inventory->expire_date)->format('d M Y');
                    throw new \Exception("Product '{$inventory->product_name}' (Batch: " . ($inventory->batch_number ?: 'N/A') . ") expired on {$expFormatted} and cannot be checked out!");
                }

                $price = $mode === 'wholesale'
                    ? ((float) $inventory->msp > 0 ? (float) $inventory->msp : ((float) $inventory->csp > 0 ? (float) $inventory->csp : (float) $inventory->mrp))
                    : ((float) $inventory->csp > 0 ? (float) $inventory->csp : (float) $inventory->mrp);

                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;
                $totalItems += $qty;

                // Deduct inventory stock
                $inventory->decrement('quantity', $qty);
                $inventory->increment('sold_quantity', $qty);

                $orderItemsData[] = [
                    'ddc_inventory_id' => $inventory->id,
                    'product_id' => $inventory->product_id,
                    'product_name' => $inventory->product_name,
                    'product_code' => $inventory->product_code,
                    'barcode' => $inventory->barcode,
                    'unit_price' => $price,
                    'mrp' => $inventory->mrp,
                    'quantity' => $qty,
                    'total_price' => $lineTotal,
                    'batch_number' => $inventory->batch_number,
                    'mfg_date' => $inventory->mfg_date,
                    'expire_date' => $inventory->expire_date,
                ];
            }

            $specialDiscount = min((float) ($request->special_discount_amount ?? 0), $subtotal);
            $grandTotal = max(0, ($subtotal - $specialDiscount));

            // Calculate GST / Tax after deducting Special Discount from subtotal
            $calculatedTaxSum = 0;
            foreach ($orderItemsData as $oItem) {
                $lineTotal = (float) $oItem['total_price'];
                $lineDisc = $subtotal > 0 ? ($specialDiscount * ($lineTotal / $subtotal)) : 0;
                $netLineTotal = max(0, $lineTotal - $lineDisc);

                $inventory = DdcInventory::find($oItem['ddc_inventory_id']);
                $gstRate = (float) (
                    $inventory?->gst_rate
                    ?? $inventory?->product?->gst_rate
                    ?? $inventory?->localProduct?->gst_rate
                    ?? 0
                );

                if ($gstRate > 0) {
                    $itemTaxable = $netLineTotal / (1 + ($gstRate / 100));
                    $itemTax = $netLineTotal - $itemTaxable;
                    $calculatedTaxSum += $itemTax;
                }
            }
            $finalTaxAmount = round($calculatedTaxSum, 2);

            // If Wholesale, verify & deduct ADC canteen wallet balance
            if ($mode === 'wholesale') {
                $adcProfile = AdcProfile::where('user_id', $adcUserId)->lockForUpdate()->first();
                if (!$adcProfile || (float) $adcProfile->wallet_balance < $grandTotal) {
                    $avail = (float) ($adcProfile?->wallet_balance ?? 0);
                    throw new \Exception("Insufficient ADC Canteen Wallet Balance! Available: ₹" . number_format($avail, 2) . ", Required: ₹" . number_format($grandTotal, 2));
                }

                // Deduct wallet balance
                $adcProfile->decrement('wallet_balance', $grandTotal);
            }

            $posInvoiceDetails = DdcPosOrder::generateNextPosInvoiceNumber($ddcId, $mode);

            $order = DdcPosOrder::create([
                'order_no' => $posInvoiceDetails['order_no'],
                'ddc_id' => $ddcId,
                'mode' => $mode,
                'financial_year' => $posInvoiceDetails['financial_year'],
                'serial_number' => $posInvoiceDetails['serial_number'],
                'customer_id' => $customerId,
                'adc_user_id' => $adcUserId,
                'customer_name' => $customerName,
                'customer_mobile' => $customerMobile,
                'total_items' => $totalItems,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'special_discount_amount' => $specialDiscount,
                'tax_amount' => $finalTaxAmount,
                'total_amount' => $grandTotal,
                'payment_mode' => $paymentMode,
                'payment_status' => ($paymentMode === 'CREDIT' ? 'UNPAID' : 'PAID'),
                'remarks' => $request->remarks,
            ]);

            foreach ($orderItemsData as $oItem) {
                $order->items()->create($oItem);
            }

            // If Wholesale Mode: Generate linked AdcOrder + AdcOrderItem + DDC Tax Invoice with status "Dispatched" for ADC
            $adcOrderId = null;
            $finalInvoiceNo = $order->order_no;

            if ($mode === 'wholesale') {
                $adcUser = User::with(['adcProfile'])->find($adcUserId);
                $ddcUser = User::with(['profile'])->find($ddcId);

                $ddcCode = 'INV';
                $ddcGstin = $ddcUser->profile?->ddc_gst_number ?: ($ddcUser->profile?->adpl_gst_number ?: ($ddcUser->profile?->gst_number ?: ($ddcUser->gst_number ?: 'N/A')));
                $invoiceDetails = \App\Models\Invoice::generateNextInvoiceNumber(now(), $ddcGstin, 'INV', 'wholesale');

                $adcOrder = \App\Models\AdcOrder::create([
                    'order_number' => $order->order_no,
                    'adc_id' => $adcUserId,
                    'advisor_code' => $adcProfile?->advisor_code,
                    'total_amount' => $grandTotal,
                    'subtotal_excl_gst' => max(0, round($subtotal - $discount - $tax, 2)),
                    'total_gst' => round($tax, 2),
                    'status' => 'Dispatched', // Auto dispatch for ADC as requested
                    'payment_status' => 'Paid',
                    'payment_method' => 'Wallet',
                    'fulfillment_type' => 'physical',
                    'selected_ddc_id' => $ddcId,
                    'virtual_ddc_id' => null,
                    'assigned_ddc_id' => $ddcId,
                    'delivery_pincode' => $adcProfile?->pincode,
                    'delivery_state' => $adcProfile?->state,
                    'delivery_district' => $adcProfile?->district,
                    'routing_logic' => 'Wholesale POS Direct Sale',
                    'shipping_address' => $adcProfile?->address ?: ($adcUser->name . ', ' . ($adcProfile?->district ?? '')),
                    'notes' => 'Direct Billing via DDC Wholesale POS (' . $order->order_no . ')',
                    'received_invoice_number' => $invoiceDetails['invoice_number'],
                    'received_invoice_date' => now()->format('Y-m-d'),
                ]);

                $adcOrderId = $adcOrder->id;
                $order->update(['adc_order_id' => $adcOrderId]);

                // Create AdcOrderItem records
                foreach ($request->items as $itemData) {
                    $invId = $itemData['id'] ?? ($itemData['inventory_id'] ?? null);
                    $inventory = DdcInventory::find($invId);
                    if (!$inventory)
                        continue;

                    $qty = (float) $itemData['qty'];
                    $price = (float) ($inventory->msp > 0 ? $inventory->msp : ($inventory->csp > 0 ? $inventory->csp : $inventory->mrp));
                    $lineTotal = $price * $qty;

                    $gstRate = (float) ($inventory->gst_rate ?? ($inventory->product?->gst_rate ?? ($inventory->localProduct?->gst_rate ?? 0)));
                    $taxableValue = round($lineTotal / (1 + ($gstRate / 100)), 2);
                    $taxAmount = round($lineTotal - $taxableValue, 2);

                    \App\Models\AdcOrderItem::create([
                        'adc_order_id' => $adcOrder->id,
                        'seller_id' => $inventory->localProduct?->user_id ?? $inventory->product?->user_id ?? $ddcId,
                        'product_id' => $inventory->product_id,
                        'product_name' => $inventory->product_name,
                        'product_code' => $inventory->product_code,
                        'hsn_code' => $inventory->product?->hsn_code ?? $inventory->localProduct?->hsn_code,
                        'quantity' => $qty,
                        'mrp' => $inventory->mrp,
                        'price' => $price,
                        'msp' => $inventory->msp,
                        'csp' => $inventory->csp,
                        'total' => $lineTotal,
                        'taxable_amount' => $taxableValue,
                        'gst_rate' => $gstRate,
                        'gst_amount' => $taxAmount,
                        'batch_number' => $inventory->batch_number,
                        'mfg_date' => $inventory->mfg_date,
                        'expire_date' => $inventory->expire_date,
                        'fulfillment_type' => 'physical',
                        'ddc_id' => $ddcId,
                        'status' => 'Dispatched',
                    ]);
                }

                // Generate Official DDC -> ADC Tax Invoice
                $invoice = \App\Models\Invoice::create([
                    'invoice_number' => $invoiceDetails['invoice_number'],
                    'pos_order_id' => $order->id,
                    'adc_order_id' => $adcOrder->id,
                    'purchase_order_id' => null,
                    'type' => 'ddc_to_adc',
                    'financial_year' => $invoiceDetails['financial_year'],
                    'serial_number' => $invoiceDetails['serial_number'],
                    'invoice_date' => now()->format('Y-m-d'),
                    'supplier_gstin' => $ddcGstin,
                    'ddc_invoice_number' => $invoiceDetails['invoice_number'],
                    'ddc_invoice_date' => now(),
                    'bill_to_name' => $adcUser->name,
                    'bill_to_company' => $adcProfile?->company_name ?: $adcUser->name,
                    'bill_to_address' => $adcProfile?->address,
                    'bill_to_district' => $adcProfile?->district,
                    'bill_to_state' => $adcProfile?->state,
                    'bill_to_pincode' => $adcProfile?->pincode,
                    'bill_to_phone' => $adcUser->mobile,
                    'bill_to_gstin' => $adcProfile?->gst_number ?: 'N/A',
                    'dispatch_from_name' => $ddcUser->name,
                    'dispatch_from_address' => $ddcUser->profile?->address ?? 'DDC Hub',
                    'dispatch_from_district' => $ddcUser->profile?->district,
                    'dispatch_from_state' => $ddcUser->profile?->state,
                    'dispatch_from_pincode' => $ddcUser->profile?->pincode,
                    'dispatch_from_phone' => $ddcUser->mobile,
                    'dispatch_from_gstin' => $ddcGstin,
                ]);

                $finalInvoiceNo = $invoice->invoice_number;
            } else {
                // Generate Official Retail POS Tax Invoice ({DDC_CODE}/R/26-27/0001 format)
                $ddcUser = User::with(['ddcProfile', 'profile'])->find($ddcId);
                $ddcCode = 'INV';
                $ddcGstin = $ddcUser?->ddcProfile?->ddc_gst_number ?: ($ddcUser?->profile?->ddc_gst_number ?: ($ddcUser?->ddcProfile?->adpl_gst_number ?: ($ddcUser?->gst_number ?: 'N/A')));

                $invoiceDetails = \App\Models\Invoice::generateNextInvoiceNumber(now(), $ddcGstin, 'INV', 'retail');

                $invoice = \App\Models\Invoice::create([
                    'invoice_number' => $invoiceDetails['invoice_number'],
                    'pos_order_id' => $order->id,
                    'adc_order_id' => null,
                    'purchase_order_id' => null,
                    'type' => 'ddc_pos_retail',
                    'financial_year' => $invoiceDetails['financial_year'],
                    'serial_number' => $invoiceDetails['serial_number'],
                    'invoice_date' => now()->format('Y-m-d'),
                    'supplier_gstin' => $ddcGstin,
                    'ddc_invoice_number' => $invoiceDetails['invoice_number'],
                    'ddc_invoice_date' => now(),
                    'bill_to_name' => $customerName ?: 'Walk-in Customer',
                    'bill_to_company' => null,
                    'bill_to_address' => $request->customer_address,
                    'bill_to_phone' => $customerMobile,
                    'bill_to_gstin' => $request->customer_gst ? strtoupper($request->customer_gst) : 'N/A',
                    'dispatch_from_name' => $ddcUser->name,
                    'dispatch_from_address' => $ddcUser->profile?->address ?? 'DDC Store',
                    'dispatch_from_district' => $ddcUser->profile?->district,
                    'dispatch_from_state' => $ddcUser->profile?->state,
                    'dispatch_from_pincode' => $ddcUser->profile?->pincode,
                    'dispatch_from_phone' => $ddcUser->mobile,
                    'dispatch_from_gstin' => $ddcGstin,
                ]);

                $finalInvoiceNo = $invoice->invoice_number;
            }

            $heldId = $request->get('held_id');

            // Clear persistent DB cart & held carts for this customer/mode/held_id
            \App\Models\DdcPosCart::where('ddc_id', $ddcId)
                ->where('mode', $mode)
                ->where(function ($q) use ($customerId, $customerMobile, $heldId) {
                    $q->where('status', 'active');
                    if ($heldId)
                        $q->orWhere('id', $heldId);
                    if ($customerId)
                        $q->orWhere('customer_id', $customerId);
                    if ($customerMobile)
                        $q->orWhere('customer_mobile', $customerMobile);
                })
                ->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => ($mode === 'wholesale' ? 'Wholesale POS Order processed & Canteen Wallet deducted successfully!' : 'Retail POS Order processed successfully!'),
                'order_id' => $order->id,
                'adc_order_id' => $adcOrderId,
                'invoice_number' => $finalInvoiceNo,
                'invoice_url' => $adcOrderId ? route('ddc.adc-orders.invoice', $adcOrderId) : null,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Fetch saved active cart for DDC per mode (retail/wholesale).
     */
    public function getCart(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $mode = strtolower($request->get('mode', 'retail'));

        $cartRecord = \App\Models\DdcPosCart::where('ddc_id', $ddcId)
            ->where('mode', $mode)
            ->where('status', 'active')
            ->first();

        if (!$cartRecord || empty($cartRecord->cart_data)) {
            return response()->json([
                'status' => true,
                'items' => [],
                'customer' => null,
                'adc_id' => null,
            ]);
        }

        $today = date('Y-m-d');
        $rawItems = $cartRecord->cart_data;
        $inventoryIds = array_column($rawItems, 'id');

        $inventories = DdcInventory::where('ddc_id', $ddcId)
            ->whereIn('id', $inventoryIds)
            ->where('pos_status', 'Active')
            ->whereRaw('(quantity - COALESCE(reserved_quantity, 0)) > 0')
            ->where(function ($q) use ($today) {
                $q->whereNull('expire_date')
                    ->orWhereDate('expire_date', '>', $today);
            })
            ->get()
            ->keyBy('id');

        $activeItems = [];
        foreach ($rawItems as $ri) {
            $invId = $ri['id'] ?? null;
            if ($invId && isset($inventories[$invId])) {
                $inv = $inventories[$invId];
                $price = $mode === 'wholesale'
                    ? ((float) $inv->msp > 0 ? (float) $inv->msp : ((float) $inv->csp > 0 ? (float) $inv->csp : (float) $inv->mrp))
                    : ((float) $inv->csp > 0 ? (float) $inv->csp : (float) $inv->mrp);

                $requestedQty = min((float) ($ri['qty'] ?? 1), (float) $inv->available_quantity);

                $activeItems[] = [
                    'id' => $inv->id,
                    'name' => $inv->product_name,
                    'code' => $inv->product_code,
                    'barcode' => $inv->barcode,
                    'price' => $price,
                    'mrp' => (float) $inv->mrp,
                    'gst_rate' => (float) ($inv->gst_rate ?? ($inv->product?->gst_rate ?? ($inv->localProduct?->gst_rate ?? 0))),
                    'qty' => $requestedQty,
                    'stock' => (float) $inv->available_quantity,
                    'unit_of_measure' => $inv->unit_of_measure ?: ($inv->product?->unit_of_measure ?: ($inv->localProduct?->unit_of_measure ?: 'Unit')),
                    'batch' => $inv->batch_number,
                    'expire_date' => $inv->expire_date,
                ];
            }
        }

        $customer = null;
        if ($mode === 'retail' && $cartRecord->customer_id) {
            $customer = DdcPosCustomer::find($cartRecord->customer_id);
        }

        return response()->json([
            'status' => true,
            'items' => $activeItems,
            'customer' => $customer,
            'adc_id' => $cartRecord->adc_id,
        ]);
    }

    /**
     * Sync active cart to database per mode (retail/wholesale).
     */
    public function syncCart(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $mode = strtolower($request->get('mode', 'retail'));
        $rawItems = $request->get('items', []);
        $adcId = $request->get('adc_id');
        $customerId = $request->get('customer_id');

        $cleanItems = [];
        if (is_array($rawItems)) {
            foreach ($rawItems as $item) {
                if (isset($item['id']) && isset($item['qty'])) {
                    $cleanItems[] = [
                        'id' => (int) $item['id'],
                        'qty' => (float) $item['qty'],
                    ];
                }
            }
        }

        if (empty($cleanItems)) {
            \App\Models\DdcPosCart::where('ddc_id', $ddcId)
                ->where('mode', $mode)
                ->where('status', 'active')
                ->delete();
        } else {
            \App\Models\DdcPosCart::updateOrCreate(
                ['ddc_id' => $ddcId, 'mode' => $mode, 'status' => 'active'],
                [
                    'adc_id' => $mode === 'wholesale' ? $adcId : null,
                    'customer_id' => $mode === 'retail' ? $customerId : null,
                    'cart_data' => $cleanItems,
                ]
            );
        }

        return response()->json(['status' => true]);
    }

    /**
     * Clear saved cart for DDC per mode.
     */
    public function clearCartDb(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $mode = strtolower($request->get('mode', 'retail'));

        \App\Models\DdcPosCart::where('ddc_id', $ddcId)
            ->where('mode', $mode)
            ->where('status', 'active')
            ->delete();

        return response()->json(['status' => true]);
    }

    /**
     * Save/Hold current cart for a customer (Pending Unbilled Bill).
     */
    public function holdCart(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $mode = strtolower($request->get('mode', 'retail'));

        $request->validate([
            'items' => 'required|array|min:1',
            'customer_name' => 'nullable|string|max:100',
            'customer_mobile' => 'required|regex:/^[0-9]{10}$/',
            'customer_id' => 'nullable|integer',
        ], [
            'customer_mobile.required' => '10-digit mobile number is required to hold bill.',
            'customer_mobile.regex' => 'Mobile number must be exactly 10 digits.',
        ]);

        $cleanItems = [];
        foreach ($request->items as $item) {
            $invId = $item['id'] ?? ($item['inventory_id'] ?? null);
            if ($invId && isset($item['qty'])) {
                $cleanItems[] = [
                    'id' => (int) $invId,
                    'qty' => (float) $item['qty'],
                ];
            }
        }

        if (empty($cleanItems)) {
            return response()->json(['status' => false, 'message' => 'Cart is empty.'], 422);
        }

        $custMobile = $request->customer_mobile;
        $custName = $request->customer_name ?: ('Customer ' . $custMobile);
        $custId = $request->customer_id ?: null;

        // Auto-lookup existing customer by mobile number to link customer_id & real name
        if ($custMobile) {
            $existingCust = DdcPosCustomer::where('ddc_id', $ddcId)->where('mobile', $custMobile)->first();
            if ($existingCust) {
                $custId = $existingCust->id;
                $custName = $existingCust->name;
            }
        }

        // Clean up active cart DB row when bill is held
        \App\Models\DdcPosCart::where('ddc_id', $ddcId)
            ->where('mode', $mode)
            ->where('status', 'active')
            ->delete();

        if ($custId || $custMobile) {
            $held = \App\Models\DdcPosCart::where('ddc_id', $ddcId)
                ->where('mode', $mode)
                ->where('status', 'held')
                ->where(function ($q) use ($custId, $custMobile) {
                    if ($custId)
                        $q->where('customer_id', $custId);
                    if ($custMobile)
                        $q->orWhere('customer_mobile', $custMobile);
                })
                ->first();

            if ($held) {
                $held->update([
                    'customer_id' => $custId ?: $held->customer_id,
                    'customer_name' => $custName,
                    'customer_mobile' => $custMobile,
                    'cart_data' => $cleanItems,
                ]);
                return response()->json([
                    'status' => true,
                    'message' => "Pending bill updated & held for {$custName}!",
                    'held_id' => $held->id,
                ]);
            }
        }

        $newHeld = \App\Models\DdcPosCart::create([
            'ddc_id' => $ddcId,
            'mode' => $mode,
            'status' => 'held',
            'customer_id' => $custId,
            'customer_name' => $custName,
            'customer_mobile' => $custMobile,
            'cart_data' => $cleanItems,
        ]);

        return response()->json([
            'status' => true,
            'message' => "Bill held successfully for {$custName}!",
            'held_id' => $newHeld->id,
        ]);
    }

    /**
     * Get all held carts for current DDC (supports search by Hold ID, name, mobile).
     */
    public function getHeldCarts(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $mode = strtolower($request->get('mode', 'retail'));
        $search = trim($request->get('search', ''));

        $query = \App\Models\DdcPosCart::where('ddc_id', $ddcId)
            ->where('mode', $mode)
            ->where('status', 'held');

        if ($search !== '') {
            $cleanId = preg_replace('/[^0-9]/', '', $search);
            $query->where(function ($q) use ($search, $cleanId) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_mobile', 'like', "%{$search}%");
                if ($cleanId !== '') {
                    $q->orWhere('id', $cleanId);
                }
            });
        }

        $heldCarts = $query->orderBy('id', 'desc')->get();

        $formatted = $heldCarts->map(function ($hc) use ($mode) {
            $rawItems = $hc->cart_data ?: [];
            $items = [];
            $subtotal = 0;

            foreach ($rawItems as $ri) {
                $inv = DdcInventory::with(['product', 'localProduct'])->find($ri['id'] ?? null);
                if (!$inv)
                    continue;

                $salePrice = $mode === 'wholesale'
                    ? ((float) $inv->msp > 0 ? (float) $inv->msp : ((float) $inv->csp > 0 ? (float) $inv->csp : (float) $inv->mrp))
                    : ((float) $inv->csp > 0 ? (float) $inv->csp : (float) $inv->mrp);

                $qty = (float) ($ri['qty'] ?? 1);
                $subtotal += ($salePrice * $qty);

                $imageUrl = null;
                if ($inv->product) {
                    $imageUrl = $inv->product->primary_image_url ?: ($inv->product->image ? (filter_var($inv->product->image, FILTER_VALIDATE_URL) ? $inv->product->image : asset('storage/' . $inv->product->image)) : null);
                } elseif ($inv->localProduct) {
                    $imageUrl = $inv->localProduct->primary_image_url ?: ($inv->localProduct->image ? (filter_var($inv->localProduct->image, FILTER_VALIDATE_URL) ? $inv->localProduct->image : asset('storage/' . $inv->localProduct->image)) : null);
                }

                $items[] = [
                    'id' => $inv->id,
                    'product_name' => $inv->product_name,
                    'price' => $salePrice,
                    'mrp' => (float) $inv->mrp,
                    'qty' => $qty,
                    'stock' => (float) $inv->quantity,
                    'unit_of_measure' => $inv->unit_of_measure ?: ($inv->product?->unit_of_measure ?: ($inv->localProduct?->unit_of_measure ?: 'Unit')),
                    'gst_rate' => (float) ($inv->gst_rate ?? ($inv->product?->gst_rate ?? ($inv->localProduct?->gst_rate ?? 0))),
                    'image_url' => $imageUrl,
                ];
            }

            return [
                'id' => $hc->id,
                'customer_id' => $hc->customer_id,
                'customer_name' => $hc->customer_name ?: 'Pending Customer',
                'customer_mobile' => $hc->customer_mobile,
                'items_count' => count($items),
                'subtotal' => round($subtotal, 2),
                'created_at' => $hc->created_at ? $hc->created_at->format('d M, h:i A') : '',
                'items' => $items,
            ];
        });

        return response()->json([
            'status' => true,
            'carts' => $formatted,
        ]);
    }

    /**
     * Check if a specific customer has a held cart.
     */
    public function checkCustomerHeldCart(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $mode = strtolower($request->get('mode', 'retail'));
        $mobile = trim($request->get('mobile', ''));
        $custId = $request->get('customer_id');

        if (!$mobile && !$custId) {
            return response()->json(['status' => false]);
        }

        $held = \App\Models\DdcPosCart::where('ddc_id', $ddcId)
            ->where('mode', $mode)
            ->where('status', 'held')
            ->where(function ($q) use ($custId, $mobile) {
                if ($custId)
                    $q->where('customer_id', $custId);
                if ($mobile)
                    $q->orWhere('customer_mobile', $mobile);
            })
            ->orderBy('id', 'desc')
            ->first();

        if (!$held) {
            return response()->json(['status' => false]);
        }

        $rawItems = $held->cart_data ?: [];
        $items = [];
        $subtotal = 0;

        foreach ($rawItems as $ri) {
            $inv = DdcInventory::with(['product', 'localProduct'])->find($ri['id'] ?? null);
            if (!$inv)
                continue;

            $salePrice = $mode === 'wholesale'
                ? ((float) $inv->msp > 0 ? (float) $inv->msp : ((float) $inv->csp > 0 ? (float) $inv->csp : (float) $inv->mrp))
                : ((float) $inv->csp > 0 ? (float) $inv->csp : (float) $inv->mrp);

            $qty = (float) ($ri['qty'] ?? 1);
            $subtotal += ($salePrice * $qty);

            $imageUrl = null;
            if ($inv->product) {
                $imageUrl = $inv->product->primary_image_url ?: ($inv->product->image ? (filter_var($inv->product->image, FILTER_VALIDATE_URL) ? $inv->product->image : asset('storage/' . $inv->product->image)) : null);
            } elseif ($inv->localProduct) {
                $imageUrl = $inv->localProduct->primary_image_url ?: ($inv->localProduct->image ? (filter_var($inv->localProduct->image, FILTER_VALIDATE_URL) ? $inv->localProduct->image : asset('storage/' . $inv->localProduct->image)) : null);
            }

            $items[] = [
                'id' => $inv->id,
                'product_name' => $inv->product_name,
                'price' => $salePrice,
                'mrp' => (float) $inv->mrp,
                'qty' => $qty,
                'stock' => (float) $inv->quantity,
                'unit_of_measure' => $inv->unit_of_measure ?: ($inv->product?->unit_of_measure ?: ($inv->localProduct?->unit_of_measure ?: 'Unit')),
                'gst_rate' => (float) ($inv->gst_rate ?? ($inv->product?->gst_rate ?? ($inv->localProduct?->gst_rate ?? 0))),
                'image_url' => $imageUrl,
            ];
        }

        return response()->json([
            'status' => true,
            'cart' => [
                'id' => $held->id,
                'customer_id' => $held->customer_id,
                'customer_name' => $held->customer_name ?: 'Pending Customer',
                'customer_mobile' => $held->customer_mobile,
                'items_count' => count($items),
                'subtotal' => round($subtotal, 2),
                'created_at' => $held->created_at ? $held->created_at->format('d M, h:i A') : '',
                'items' => $items,
            ]
        ]);
    }

    /**
     * Delete a held cart.
     */
    public function deleteHeldCart($id)
    {
        $ddcId = Auth::guard('ddc')->id();
        \App\Models\DdcPosCart::where('ddc_id', $ddcId)->where('id', $id)->delete();
        return response()->json(['status' => true, 'message' => 'Held bill deleted successfully.']);
    }

    /**
     * Resume a held cart: Delete from DB & return cart items.
     */
    public function resumeHeldCart($id)
    {
        $ddcId = Auth::guard('ddc')->id();
        $held = \App\Models\DdcPosCart::where('ddc_id', $ddcId)->where('id', $id)->first();

        if (!$held) {
            return response()->json(['status' => false, 'message' => 'Held bill not found.'], 404);
        }

        $mode = $held->mode ?: 'retail';
        $rawItems = $held->cart_data ?: [];
        $items = [];
        $subtotal = 0;

        foreach ($rawItems as $ri) {
            $inv = DdcInventory::with(['product', 'localProduct'])->find($ri['id'] ?? null);
            if (!$inv)
                continue;

            $salePrice = $mode === 'wholesale'
                ? ((float) $inv->msp > 0 ? (float) $inv->msp : ((float) $inv->csp > 0 ? (float) $inv->csp : (float) $inv->mrp))
                : ((float) $inv->csp > 0 ? (float) $inv->csp : (float) $inv->mrp);

            $qty = (float) ($ri['qty'] ?? 1);
            $subtotal += ($salePrice * $qty);

            $imageUrl = null;
            if ($inv->product) {
                $imageUrl = $inv->product->primary_image_url ?: ($inv->product->image ? (filter_var($inv->product->image, FILTER_VALIDATE_URL) ? $inv->product->image : asset('storage/' . $inv->product->image)) : null);
            } elseif ($inv->localProduct) {
                $imageUrl = $inv->localProduct->primary_image_url ?: ($inv->localProduct->image ? (filter_var($inv->localProduct->image, FILTER_VALIDATE_URL) ? $inv->localProduct->image : asset('storage/' . $inv->localProduct->image)) : null);
            }

            $items[] = [
                'id' => $inv->id,
                'product_name' => $inv->product_name,
                'price' => $salePrice,
                'mrp' => (float) $inv->mrp,
                'qty' => $qty,
                'stock' => (float) $inv->quantity,
                'unit_of_measure' => $inv->unit_of_measure ?: ($inv->product?->unit_of_measure ?: ($inv->localProduct?->unit_of_measure ?: 'Unit')),
                'gst_rate' => (float) ($inv->gst_rate ?? ($inv->product?->gst_rate ?? ($inv->localProduct?->gst_rate ?? 0))),
                'image_url' => $imageUrl,
            ];
        }

        $cartData = [
            'id' => $held->id,
            'customer_id' => $held->customer_id,
            'customer_name' => $held->customer_name ?: 'Pending Customer',
            'customer_mobile' => $held->customer_mobile,
            'items_count' => count($items),
            'subtotal' => round($subtotal, 2),
            'created_at' => $held->created_at ? $held->created_at->format('d M, h:i A') : '',
            'items' => $items,
        ];

        return response()->json([
            'status' => true,
            'cart' => $cartData,
        ]);
    }

    public function orders(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $mode = $request->get('mode', '');
        $search = $request->get('search', '');

        $query = DdcPosOrder::where('ddc_id', $ddcId)->with(['customer', 'adcUser.adcProfile', 'items', 'invoice']);

        if ($mode) {
            $query->where('mode', $mode);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_mobile', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('ddc.pos.orders', compact('orders', 'mode', 'search'));
    }

    public function showOrder($id)
    {
        $ddcId = Auth::guard('ddc')->id();
        $order = DdcPosOrder::where('ddc_id', $ddcId)
            ->with(['items.product', 'items.localProduct', 'customer', 'adcUser.adcProfile', 'ddc.ddcProfile', 'ddc.profile', 'invoice'])
            ->findOrFail($id);

        // Fetch linked invoice if available, or generate on the fly for legacy orders
        $invoice = $order->invoice;
        if (!$invoice && $order->adc_order_id) {
            $invoice = \App\Models\Invoice::where('adc_order_id', $order->adc_order_id)->latest()->first();
        }

        if (!$invoice) {
            $ddcUser = User::with(['profile'])->find($order->ddc_id);
            $ddcCode = 'INV';
            $ddcGstin = $ddcUser?->profile?->ddc_gst_number ?: ($ddcUser?->profile?->adpl_gst_number ?: ($ddcUser?->profile?->gst_number ?: ($ddcUser?->gst_number ?: 'N/A')));

            $invMode = strtolower($order->mode) === 'retail' ? 'retail' : 'wholesale';
            $invoiceDetails = \App\Models\Invoice::generateNextInvoiceNumber($order->created_at, $ddcGstin, 'INV', $invMode);

            $invoice = \App\Models\Invoice::create([
                'invoice_number' => $invoiceDetails['invoice_number'],
                'pos_order_id' => $order->id,
                'adc_order_id' => $order->adc_order_id,
                'purchase_order_id' => null,
                'type' => strtolower($order->mode) === 'retail' ? 'ddc_pos_retail' : 'ddc_to_adc',
                'financial_year' => $invoiceDetails['financial_year'],
                'serial_number' => $invoiceDetails['serial_number'],
                'invoice_date' => $order->created_at ? $order->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                'supplier_gstin' => $ddcGstin,
                'ddc_invoice_number' => $invoiceDetails['invoice_number'],
                'ddc_invoice_date' => $order->created_at ?: now(),
                'bill_to_name' => $order->customer_name ?: 'Walk-in Customer',
                'bill_to_company' => null,
                'bill_to_phone' => $order->customer_mobile,
                'bill_to_gstin' => 'N/A',
                'dispatch_from_name' => $ddcUser?->name,
                'dispatch_from_address' => $ddcUser?->profile?->address ?? 'DDC Store',
                'dispatch_from_district' => $ddcUser?->profile?->district,
                'dispatch_from_state' => $ddcUser?->profile?->state,
                'dispatch_from_pincode' => $ddcUser?->profile?->pincode,
                'dispatch_from_phone' => $ddcUser?->mobile,
                'dispatch_from_gstin' => $ddcGstin,
            ]);
        }
        $order->setRelation('invoice', $invoice);

        $orderSubtotal = (float) ($order->subtotal > 0 ? $order->subtotal : $order->items->sum('total_price'));
        $orderSpecialDisc = (float) ($order->special_discount_amount ?? 0);

        $order->items->transform(function ($item) use ($orderSubtotal, $orderSpecialDisc) {
            $grossPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
            $itemSubtotal = (float) ($item->total_price > 0 ? $item->total_price : ($item->quantity * $grossPrice));
            $itemDisc = ($orderSubtotal > 0 && $orderSpecialDisc > 0) ? ($orderSpecialDisc * ($itemSubtotal / $orderSubtotal)) : 0;
            $netItemBilledTotal = max(0, $itemSubtotal - $itemDisc);
            $effectiveUnitPrice = $item->quantity > 0 ? ($netItemBilledTotal / (float) $item->quantity) : $grossPrice;

            $gstRate = (float) (
                $item->gst_rate
                ?? $item->inventory?->gst_rate
                ?? $item->product?->gst_rate
                ?? $item->localProduct?->gst_rate
                ?? $item->inventory?->product?->gst_rate
                ?? $item->inventory?->localProduct?->gst_rate
                ?? 0
            );
            
            if ($gstRate > 0) {
                $taxable = round($netItemBilledTotal / (1 + ($gstRate / 100)), 2);
                $tax = round($netItemBilledTotal - $taxable, 2);
            } else {
                $taxable = $netItemBilledTotal;
                $tax = 0.0;
            }

            $soldQty = (float) $item->quantity;
            $returnedQty = (float) ($item->returned_quantity ?? 0);
            $maxReturnable = max(0, $soldQty - $returnedQty);

            $item->unit_price = round($effectiveUnitPrice, 2);
            $item->item_gst_rate = $gstRate;
            $item->taxable_amount = $taxable;
            $item->tax_amount = $tax;
            $item->returned_quantity = $returnedQty;
            $item->max_returnable_quantity = $maxReturnable;
            $item->unit_of_measure = $item->unit_of_measure ?: ($item->product?->unit_of_measure ?: ($item->localProduct?->unit_of_measure ?: 'Units'));
            return $item;
        });

        return response()->json([
            'status' => true,
            'order' => $order,
        ]);
    }

    /**
     * Process Retail POS Sales Return
     */
    public function processSalesReturn(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();

        $request->validate([
            'pos_order_id' => 'required|exists:ddc_pos_orders,id',
            'refund_mode' => 'required|string|in:Cash,UPI,Card,Store Credit,Bank Transfer',
            'return_reason' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:ddc_pos_order_items,id',
            'items.*.return_qty' => 'required|numeric|min:0.001',
        ]);

        $order = DdcPosOrder::where('ddc_id', $ddcId)->with('items')->findOrFail($request->pos_order_id);

        \DB::beginTransaction();
        try {
            $totalReturnItems = 0;
            $totalReturnQty = 0;
            $rawSubtotalReturn = 0.0;
            $rawTaxAmountReturn = 0.0;
            $rawTotalReturnAmount = 0.0;

            $orderSubtotal = (float) ($order->subtotal > 0 ? $order->subtotal : $order->items->sum('total_price'));
            $orderSpecialDisc = (float) ($order->special_discount_amount ?? 0);

            $returnItemsData = [];

            foreach ($request->items as $iData) {
                $orderItem = $order->items->firstWhere('id', $iData['item_id']);
                if (!$orderItem) {
                    continue;
                }

                $returnQty = (float) $iData['return_qty'];
                $alreadyReturned = (float) ($orderItem->returned_quantity ?? 0);
                $maxReturnable = max(0, (float) $orderItem->quantity - $alreadyReturned);

                if ($returnQty <= 0) {
                    continue;
                }

                if ($returnQty > $maxReturnable + 0.0001) {
                    \DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => "Cannot return {$returnQty} units for '{$orderItem->product_name}'. Max returnable quantity is {$maxReturnable}.",
                    ], 422);
                }

                $uom = $orderItem->unit_of_measure 
                    ?: ($orderItem->product?->unit_of_measure 
                    ?: ($orderItem->localProduct?->unit_of_measure ?: 'Units'));

                $isDecimalAllowed = \App\Models\UnitOfMeasure::isDecimalAllowed($uom);

                if (!$isDecimalAllowed && fmod($returnQty, 1) != 0) {
                    \DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => "Decimal quantity is not allowed for '{$orderItem->product_name}' (UOM: {$uom}). Please enter a whole number.",
                    ], 422);
                }


                $grossUnitPrice = (float) ($orderItem->unit_price > 0 ? $orderItem->unit_price : ($orderItem->quantity > 0 ? $orderItem->total_price / $orderItem->quantity : 0));
                $itemSubtotal = (float) ($orderItem->total_price > 0 ? $orderItem->total_price : ($orderItem->quantity * $grossUnitPrice));
                $itemDisc = ($orderSubtotal > 0 && $orderSpecialDisc > 0) ? ($orderSpecialDisc * ($itemSubtotal / $orderSubtotal)) : 0;
                $netItemBilledTotal = max(0, $itemSubtotal - $itemDisc);
                $unitPrice = $orderItem->quantity > 0 ? ($netItemBilledTotal / (float) $orderItem->quantity) : $grossUnitPrice;
                $mrp = (float) ($orderItem->mrp ?: $grossUnitPrice);

                $gstRate = (float) (
                    $orderItem->gst_rate
                    ?? $orderItem->inventory?->gst_rate
                    ?? $orderItem->product?->gst_rate
                    ?? $orderItem->localProduct?->gst_rate
                    ?? 0
                );

                $rawItemReturnTotal = $unitPrice * $returnQty;

                if ($gstRate > 0) {
                    $rawItemTaxable = $rawItemReturnTotal / (1.0 + ($gstRate / 100.0));
                    $rawItemTax = $rawItemReturnTotal - $rawItemTaxable;
                } else {
                    $rawItemTaxable = $rawItemReturnTotal;
                    $rawItemTax = 0.0;
                }

                $totalReturnItems++;
                $totalReturnQty += $returnQty;
                $rawSubtotalReturn += $rawItemTaxable;
                $rawTaxAmountReturn += $rawItemTax;
                $rawTotalReturnAmount += $rawItemReturnTotal;

                $returnItemsData[] = [
                    'pos_order_item' => $orderItem,
                    'return_qty' => $returnQty,
                    'unit_price' => round($unitPrice, 2),
                    'mrp' => round($mrp, 2),
                    'gst_rate' => $gstRate,
                    'taxable_amount' => round($rawItemTaxable, 2),
                    'tax_amount' => round($rawItemTax, 2),
                    'total_amount' => round($rawItemReturnTotal, 2),
                ];
            }

            if (count($returnItemsData) === 0) {
                \DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'No valid return items selected.',
                ], 422);
            }

            // Create Sales Return Record
            $salesReturn = \App\Models\DdcPosSalesReturn::create([
                'return_number' => \App\Models\DdcPosSalesReturn::generateReturnNumber(),
                'pos_order_id' => $order->id,
                'invoice_id' => $order->invoice?->id,
                'ddc_id' => $ddcId,
                'customer_id' => $order->customer_id,
                'customer_name' => $order->customer_name ?: ($order->customer?->name ?: 'Walk-in Customer'),
                'customer_mobile' => $order->customer_mobile ?: ($order->customer?->mobile ?: null),
                'total_items' => $totalReturnItems,
                'total_qty' => $totalReturnQty,
                'subtotal' => round($rawSubtotalReturn, 2),
                'tax_amount' => round($rawTaxAmountReturn, 2),
                'discount_amount' => 0.00,
                'total_return_amount' => round($rawTotalReturnAmount, 2),
                'refund_mode' => $request->refund_mode,
                'return_reason' => $request->return_reason,
            ]);

            // Save Return Line Items & Update Order Item + Inventory Restock
            foreach ($returnItemsData as $rData) {
                $orderItem = $rData['pos_order_item'];
                $rQty = $rData['return_qty'];

                \App\Models\DdcPosSalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'pos_order_item_id' => $orderItem->id,
                    'ddc_inventory_id' => $orderItem->ddc_inventory_id,
                    'product_id' => $orderItem->product_id,
                    'product_name' => $orderItem->product_name,
                    'product_code' => $orderItem->product_code,
                    'unit_price' => $rData['unit_price'],
                    'mrp' => $rData['mrp'],
                    'returned_quantity' => $rQty,
                    'unit_of_measure' => $orderItem->unit_of_measure ?: 'Units',
                    'gst_rate' => $rData['gst_rate'],
                    'taxable_amount' => $rData['taxable_amount'],
                    'tax_amount' => $rData['tax_amount'],
                    'total_amount' => $rData['total_amount'],
                ]);

                // Increment returned_quantity on DdcPosOrderItem
                $orderItem->returned_quantity = (float) $orderItem->returned_quantity + $rQty;
                $orderItem->save();

                // Restock DdcInventory if linked
                if ($orderItem->ddc_inventory_id) {
                    $inv = \App\Models\DdcInventory::find($orderItem->ddc_inventory_id);
                    if ($inv) {
                        $inv->quantity += $rQty;
                        $inv->returned_quantity = (float) ($inv->returned_quantity ?? 0) + $rQty;
                        $inv->save();
                    }
                }
            }

            // Recalculate Order Overall Return Status
            $totalSoldInOrder = (float) $order->items->sum('quantity');
            $totalReturnedInOrder = (float) $order->items->sum('returned_quantity');
            $totalReturnedAmountInOrder = (float) \App\Models\DdcPosSalesReturn::where('pos_order_id', $order->id)->sum('total_return_amount');

            if ($totalReturnedInOrder >= $totalSoldInOrder - 0.0001) {
                $order->return_status = 'Fully Returned';
            } elseif ($totalReturnedInOrder > 0) {
                $order->return_status = 'Partially Returned';
            } else {
                $order->return_status = 'None';
            }

            $order->total_returned_amount = $totalReturnedAmountInOrder;
            $order->save();

            \DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Sales Return {$salesReturn->return_number} processed successfully!",
                'return_id' => $salesReturn->id,
                'return_number' => $salesReturn->return_number,
                'print_url' => route('ddc.pos.returns.print', $salesReturn->id),
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error("Sales Return Error: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => false,
                'message' => 'Error processing sales return: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print Sales Return Receipt
     */
    public function printReturnReceipt($id)
    {
        $ddcId = Auth::guard('ddc')->id();
        $salesReturn = \App\Models\DdcPosSalesReturn::where('ddc_id', $ddcId)
            ->with(['order.invoice', 'items', 'customer', 'ddc.ddcProfile', 'ddc.profile'])
            ->findOrFail($id);

        $ddcUser = User::with(['ddcProfile', 'profile'])->find($ddcId);

        return view('ddc.pos.returns.print', compact('salesReturn', 'ddcUser'));
    }

    /**
     * Sales Returns List
     */
    public function salesReturnsList(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $search = $request->get('search', '');

        $query = \App\Models\DdcPosSalesReturn::where('ddc_id', $ddcId)
            ->with(['order', 'customer', 'items']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_mobile', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_no', 'like', "%{$search}%");
                    });
            });
        }

        $returns = $query->latest()->paginate(15)->withQueryString();

        return view('ddc.pos.returns.index', compact('returns', 'search'));
    }

    public function printReceipt($id)
    {
        $ddcId = Auth::guard('ddc')->id();
        $order = DdcPosOrder::where('ddc_id', $ddcId)
            ->with(['items.product', 'items.localProduct', 'items.inventory.product', 'items.inventory.localProduct', 'customer', 'adcUser.adcProfile', 'ddc.ddcProfile', 'ddc.profile', 'invoice'])
            ->findOrFail($id);

        $invoice = $order->invoice;
        if (!$invoice && $order->adc_order_id) {
            $invoice = \App\Models\Invoice::where('adc_order_id', $order->adc_order_id)->latest()->first();
        }

        if (!$invoice) {
            $ddcUser = User::with(['ddcProfile', 'profile'])->find($order->ddc_id);
            $ddcCode = 'INV';
            $ddcGstin = $ddcUser?->ddcProfile?->ddc_gst_number ?: ($ddcUser?->profile?->ddc_gst_number ?: ($ddcUser?->ddcProfile?->adpl_gst_number ?: ($ddcUser?->gst_number ?: 'N/A')));

            $invMode = strtolower($order->mode) === 'retail' ? 'retail' : 'wholesale';
            $invoiceDetails = \App\Models\Invoice::generateNextInvoiceNumber($order->created_at, $ddcGstin, 'INV', $invMode);

            $invoice = \App\Models\Invoice::create([
                'invoice_number' => $invoiceDetails['invoice_number'],
                'pos_order_id' => $order->id,
                'adc_order_id' => $order->adc_order_id,
                'purchase_order_id' => null,
                'type' => strtolower($order->mode) === 'retail' ? 'ddc_pos_retail' : 'ddc_to_adc',
                'financial_year' => $invoiceDetails['financial_year'],
                'serial_number' => $invoiceDetails['serial_number'],
                'invoice_date' => $order->created_at ? $order->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                'supplier_gstin' => $ddcGstin,
                'ddc_invoice_number' => $invoiceDetails['invoice_number'],
                'ddc_invoice_date' => $order->created_at ?: now(),
                'bill_to_name' => $order->customer_name ?: 'Walk-in Customer',
                'bill_to_company' => null,
                'bill_to_phone' => $order->customer_mobile,
                'bill_to_gstin' => 'N/A',
                'dispatch_from_name' => $ddcUser?->name,
                'dispatch_from_address' => $ddcUser?->profile?->address ?? 'DDC Store',
                'dispatch_from_district' => $ddcUser?->profile?->district,
                'dispatch_from_state' => $ddcUser?->profile?->state,
                'dispatch_from_pincode' => $ddcUser?->profile?->pincode,
                'dispatch_from_phone' => $ddcUser?->mobile,
                'dispatch_from_gstin' => $ddcGstin,
            ]);
        }
        $order->setRelation('invoice', $invoice);

        $order->items->transform(function ($item) {
            $item->unit_of_measure = $item->product?->unit_of_measure
                ?: ($item->localProduct?->unit_of_measure
                    ?: ($item->inventory?->product?->unit_of_measure
                        ?: ($item->inventory?->localProduct?->unit_of_measure ?: 'Pcs')));
            return $item;
        });

        return view('ddc.pos.print', compact('order'));
    }

    public function customers(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $search = trim($request->get('search', ''));
        $scope = $request->get('scope', 'all');

        $query = DdcPosCustomer::query()
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->with(['ddc']);

        if ($scope === 'my_ddc') {
            $query->where(function ($q) use ($ddcId) {
                $q->where('ddc_id', $ddcId)
                    ->orWhereHas('orders', function ($oq) use ($ddcId) {
                        $oq->where('ddc_id', $ddcId);
                    });
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('pincode', 'like', "%{$search}%")
                    ->orWhere('gst_number', 'like', "%{$search}%")
                    ->orWhere('abhhyam_code', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $validSorts = ['name', 'mobile', 'created_at', 'orders_count', 'orders_sum_total_amount'];
        if (!in_array($sort, $validSorts)) {
            $sort = 'created_at';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        $customers = $query->orderBy($sort, $order)->paginate(15)->withQueryString();

        // Calculate summary statistics
        $totalCustomers = DdcPosCustomer::count();
        $myCustomersCount = DdcPosCustomer::where(function ($q) use ($ddcId) {
            $q->where('ddc_id', $ddcId)
                ->orWhereHas('orders', function ($oq) use ($ddcId) {
                    $oq->where('ddc_id', $ddcId);
                });
        })->count();

        $totalOrdersCount = DdcPosOrder::where('mode', 'Retail')->count();
        $totalSpend = DdcPosOrder::where('mode', 'Retail')->sum('total_amount');
        $avgSpend = $totalCustomers > 0 ? ($totalSpend / $totalCustomers) : 0;

        return view('ddc.pos.customers', compact(
            'customers',
            'search',
            'scope',
            'sort',
            'order',
            'totalCustomers',
            'myCustomersCount',
            'totalOrdersCount',
            'totalSpend',
            'avgSpend'
        ));
    }
}
