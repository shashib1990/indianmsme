<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminInvoiceController extends Controller
{
    /**
     * Display a listing of all invoices across all sales channels.
     */
    public function index(Request $request)
    {
        $search   = trim($request->get('search', ''));
        $channel  = trim($request->get('channel', ''));
        $dateFrom = trim($request->get('date_from', ''));
        $dateTo   = trim($request->get('date_to', ''));

        $items = [];
        $existingPoIds  = [];
        $existingAdcIds = [];
        $existingPosIds = [];

        // 1. Invoices Table Records
        if (empty($channel) || in_array($channel, ['general', 'msme_po', 'adc_order', 'retail', 'wholesale'])) {
            $invQuery = DB::table('invoices')
                ->leftJoin('purchase_orders', 'purchase_orders.id', '=', 'invoices.purchase_order_id')
                ->leftJoin('adc_orders', 'adc_orders.id', '=', 'invoices.adc_order_id')
                ->leftJoin('ddc_pos_orders', 'ddc_pos_orders.id', '=', 'invoices.pos_order_id')
                ->leftJoin('users as po_seller', 'po_seller.id', '=', 'purchase_orders.seller_id')
                ->leftJoin('users as po_ddc', 'po_ddc.id', '=', 'purchase_orders.ddc_id')
                ->leftJoin('users as adc_ddc', 'adc_ddc.id', '=', 'adc_orders.assigned_ddc_id')
                ->leftJoin('users as adc_user', 'adc_user.id', '=', 'adc_orders.adc_id')
                ->leftJoin('users as ddc_pos_user', 'ddc_pos_user.id', '=', 'ddc_pos_orders.ddc_id')
                ->select([
                    'invoices.id as record_id',
                    DB::raw("'invoices' as table_type"),
                    'invoices.invoice_number as invoice_number',
                    DB::raw("COALESCE(purchase_orders.po_number, adc_orders.order_number, ddc_pos_orders.order_no, CONCAT('INV-', invoices.id)) as order_number"),
                    DB::raw("CASE 
                        WHEN invoices.purchase_order_id IS NOT NULL THEN 'MSME PO'
                        WHEN invoices.adc_order_id IS NOT NULL THEN 'ADC MSME Order'
                        WHEN invoices.pos_order_id IS NOT NULL THEN 'DDC POS Retail'
                        ELSE 'General Invoice'
                    END as channel_type"),
                    DB::raw("COALESCE(invoices.dispatch_from_name, po_seller.name, adc_ddc.name, ddc_pos_user.name, 'Supplier') as source_name"),
                    DB::raw("COALESCE(invoices.bill_to_name, po_ddc.name, adc_user.name, ddc_pos_orders.customer_name, 'Customer') as destination_name"),
                    DB::raw("COALESCE(purchase_orders.total_amount, adc_orders.total_amount, ddc_pos_orders.total_amount, 0) as total_amount"),
                    DB::raw("COALESCE(invoices.invoice_date, invoices.created_at) as invoice_date"),
                    DB::raw("COALESCE(purchase_orders.status, adc_orders.status, ddc_pos_orders.payment_status, 'Completed') as status"),
                    'invoices.purchase_order_id',
                    'invoices.adc_order_id',
                    'invoices.pos_order_id',
                ]);

            if ($search) {
                $invQuery->where(function ($q) use ($search) {
                    $q->where('invoices.invoice_number', 'like', "%{$search}%")
                      ->orWhere('purchase_orders.po_number', 'like', "%{$search}%")
                      ->orWhere('adc_orders.order_number', 'like', "%{$search}%")
                      ->orWhere('ddc_pos_orders.order_no', 'like', "%{$search}%")
                      ->orWhere('invoices.bill_to_name', 'like', "%{$search}%")
                      ->orWhere('invoices.dispatch_from_name', 'like', "%{$search}%");
                });
            }

            if ($dateFrom) {
                $invQuery->whereDate('invoices.created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $invQuery->whereDate('invoices.created_at', '<=', $dateTo);
            }

            foreach ($invQuery->get() as $row) {
                if (!empty($row->purchase_order_id)) {
                    $existingPoIds[] = $row->purchase_order_id;
                }
                if (!empty($row->adc_order_id)) {
                    $existingAdcIds[] = $row->adc_order_id;
                }
                if (!empty($row->pos_order_id)) {
                    $existingPosIds[] = $row->pos_order_id;
                }
                $items[] = (array)$row;
            }
        }

        // 2. Purchase Orders (MSME POs)
        if (empty($channel) || $channel === 'msme_po') {
            $poQuery = DB::table('purchase_orders')
                ->leftJoin('users as seller', 'seller.id', '=', 'purchase_orders.seller_id')
                ->leftJoin('users as ddc', 'ddc.id', '=', 'purchase_orders.ddc_id')
                ->leftJoin('invoices', 'invoices.purchase_order_id', '=', 'purchase_orders.id')
                ->select([
                    'purchase_orders.id as record_id',
                    DB::raw("'purchase_orders' as table_type"),
                    'invoices.invoice_number as invoice_number',
                    'purchase_orders.po_number as order_number',
                    DB::raw("'MSME PO' as channel_type"),
                    DB::raw("COALESCE(seller.name, 'Vendor/Seller') as source_name"),
                    DB::raw("COALESCE(ddc.name, 'DDC Coordinator') as destination_name"),
                    'purchase_orders.total_amount',
                    'purchase_orders.created_at as invoice_date',
                    'purchase_orders.status',
                ]);

            if (!empty($existingPoIds)) {
                $poQuery->whereNotIn('purchase_orders.id', $existingPoIds);
            }

            if ($search) {
                $poQuery->where(function ($q) use ($search) {
                    $q->where('purchase_orders.po_number', 'like', "%{$search}%")
                      ->orWhere('invoices.invoice_number', 'like', "%{$search}%")
                      ->orWhere('seller.name', 'like', "%{$search}%")
                      ->orWhere('ddc.name', 'like', "%{$search}%");
                });
            }

            if ($dateFrom) {
                $poQuery->whereDate('purchase_orders.created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $poQuery->whereDate('purchase_orders.created_at', '<=', $dateTo);
            }

            foreach ($poQuery->get() as $row) {
                $items[] = (array)$row;
            }
        }

        // 3. ADC Orders (ADC POs)
        if (empty($channel) || $channel === 'adc_order') {
            $adcQuery = DB::table('adc_orders')
                ->leftJoin('users as ddc', 'ddc.id', '=', 'adc_orders.assigned_ddc_id')
                ->leftJoin('users as adc', 'adc.id', '=', 'adc_orders.adc_id')
                ->leftJoin('invoices', 'invoices.adc_order_id', '=', 'adc_orders.id')
                ->select([
                    'adc_orders.id as record_id',
                    DB::raw("'adc_orders' as table_type"),
                    DB::raw("COALESCE(invoices.invoice_number, adc_orders.received_invoice_number) as invoice_number"),
                    'adc_orders.order_number',
                    DB::raw("'ADC MSME Order' as channel_type"),
                    DB::raw("COALESCE(ddc.name, 'DDC Center') as source_name"),
                    DB::raw("COALESCE(adc.name, 'ADC Center') as destination_name"),
                    'adc_orders.total_amount',
                    'adc_orders.created_at as invoice_date',
                    'adc_orders.status',
                ]);

            if (!empty($existingAdcIds)) {
                $adcQuery->whereNotIn('adc_orders.id', $existingAdcIds);
            }

            if ($search) {
                $adcQuery->where(function ($q) use ($search) {
                    $q->where('adc_orders.order_number', 'like', "%{$search}%")
                      ->orWhere('adc_orders.received_invoice_number', 'like', "%{$search}%")
                      ->orWhere('invoices.invoice_number', 'like', "%{$search}%")
                      ->orWhere('ddc.name', 'like', "%{$search}%")
                      ->orWhere('adc.name', 'like', "%{$search}%");
                });
            }

            if ($dateFrom) {
                $adcQuery->whereDate('adc_orders.created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $adcQuery->whereDate('adc_orders.created_at', '<=', $dateTo);
            }

            foreach ($adcQuery->get() as $row) {
                $items[] = (array)$row;
            }
        }

        // 4. DDC POS Orders
        if (empty($channel) || str_contains($channel, 'pos_ddc') || $channel === 'retail' || $channel === 'wholesale') {
            $ddcPosQuery = DB::table('ddc_pos_orders')
                ->leftJoin('users as ddc', 'ddc.id', '=', 'ddc_pos_orders.ddc_id')
                ->leftJoin('invoices', 'invoices.pos_order_id', '=', 'ddc_pos_orders.id')
                ->select([
                    'ddc_pos_orders.id as record_id',
                    DB::raw("'ddc_pos_orders' as table_type"),
                    'invoices.invoice_number as invoice_number',
                    'ddc_pos_orders.order_no as order_number',
                    DB::raw("CASE 
                        WHEN LOWER(ddc_pos_orders.mode) = 'wholesale' THEN 'DDC POS Wholesale'
                        WHEN LOWER(ddc_pos_orders.mode) = 'online' THEN 'Online Order'
                        ELSE 'DDC POS Retail'
                    END as channel_type"),
                    DB::raw("COALESCE(ddc.name, 'DDC Store') as source_name"),
                    DB::raw("COALESCE(ddc_pos_orders.customer_name, ddc_pos_orders.customer_mobile, 'Customer') as destination_name"),
                    'ddc_pos_orders.total_amount',
                    'ddc_pos_orders.created_at as invoice_date',
                    'ddc_pos_orders.payment_status as status',
                ]);

            if (!empty($existingPosIds)) {
                $ddcPosQuery->whereNotIn('ddc_pos_orders.id', $existingPosIds);
            }

            if ($search) {
                $ddcPosQuery->where(function ($q) use ($search) {
                    $q->where('ddc_pos_orders.order_no', 'like', "%{$search}%")
                      ->orWhere('invoices.invoice_number', 'like', "%{$search}%")
                      ->orWhere('ddc_pos_orders.customer_name', 'like', "%{$search}%")
                      ->orWhere('ddc_pos_orders.customer_mobile', 'like', "%{$search}%")
                      ->orWhere('ddc.name', 'like', "%{$search}%");
                });
            }

            if ($dateFrom) {
                $ddcPosQuery->whereDate('ddc_pos_orders.created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $ddcPosQuery->whereDate('ddc_pos_orders.created_at', '<=', $dateTo);
            }

            foreach ($ddcPosQuery->get() as $row) {
                $items[] = (array)$row;
            }
        }

        // 5. ADC POS Orders
        if (empty($channel) || str_contains($channel, 'pos_adc') || $channel === 'retail' || $channel === 'wholesale') {
            $adcPosQuery = DB::table('adc_pos_orders')
                ->leftJoin('users as adc', 'adc.id', '=', 'adc_pos_orders.adc_id')
                ->leftJoin('invoices', 'invoices.pos_order_id', '=', 'adc_pos_orders.id')
                ->select([
                    'adc_pos_orders.id as record_id',
                    DB::raw("'adc_pos_orders' as table_type"),
                    'invoices.invoice_number as invoice_number',
                    'adc_pos_orders.order_no as order_number',
                    DB::raw("CASE 
                        WHEN LOWER(adc_pos_orders.mode) = 'wholesale' THEN 'ADC POS Wholesale'
                        WHEN LOWER(adc_pos_orders.mode) = 'online' THEN 'Online Order'
                        ELSE 'ADC POS Retail'
                    END as channel_type"),
                    DB::raw("COALESCE(adc.name, 'ADC Store') as source_name"),
                    DB::raw("COALESCE(adc_pos_orders.customer_name, adc_pos_orders.customer_mobile, 'Customer') as destination_name"),
                    'adc_pos_orders.total_amount',
                    'adc_pos_orders.created_at as invoice_date',
                    'adc_pos_orders.payment_status as status',
                ]);

            if ($search) {
                $adcPosQuery->where(function ($q) use ($search) {
                    $q->where('adc_pos_orders.order_no', 'like', "%{$search}%")
                      ->orWhere('invoices.invoice_number', 'like', "%{$search}%")
                      ->orWhere('adc_pos_orders.customer_name', 'like', "%{$search}%")
                      ->orWhere('adc_pos_orders.customer_mobile', 'like', "%{$search}%")
                      ->orWhere('adc.name', 'like', "%{$search}%");
                });
            }

            if ($dateFrom) {
                $adcPosQuery->whereDate('adc_pos_orders.created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $adcPosQuery->whereDate('adc_pos_orders.created_at', '<=', $dateTo);
            }

            foreach ($adcPosQuery->get() as $row) {
                $items[] = (array)$row;
            }
        }

        // 6. DDC Local Purchases
        if (empty($channel) || $channel === 'local_purchase') {
            $ddcLocalQuery = DB::table('ddc_local_purchases')
                ->leftJoin('users as ddc', 'ddc.id', '=', 'ddc_local_purchases.ddc_id')
                ->select([
                    'ddc_local_purchases.id as record_id',
                    DB::raw("'ddc_local_purchases' as table_type"),
                    'ddc_local_purchases.invoice_number as invoice_number',
                    'ddc_local_purchases.purchase_no as order_number',
                    DB::raw("'DDC Local Purchase' as channel_type"),
                    DB::raw("COALESCE(ddc_local_purchases.supplier_name, 'Local Supplier') as source_name"),
                    DB::raw("COALESCE(ddc.name, 'DDC Store') as destination_name"),
                    'ddc_local_purchases.total_amount',
                    'ddc_local_purchases.purchase_date as invoice_date',
                    DB::raw("'Completed' as status"),
                ]);

            if ($search) {
                $ddcLocalQuery->where(function ($q) use ($search) {
                    $q->where('ddc_local_purchases.invoice_number', 'like', "%{$search}%")
                      ->orWhere('ddc_local_purchases.purchase_no', 'like', "%{$search}%")
                      ->orWhere('ddc_local_purchases.supplier_name', 'like', "%{$search}%")
                      ->orWhere('ddc.name', 'like', "%{$search}%");
                });
            }

            if ($dateFrom) {
                $ddcLocalQuery->whereDate('ddc_local_purchases.purchase_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $ddcLocalQuery->whereDate('ddc_local_purchases.purchase_date', '<=', $dateTo);
            }

            foreach ($ddcLocalQuery->get() as $row) {
                $items[] = (array)$row;
            }
        }

        // 7. ADC Local Purchases
        if (empty($channel) || $channel === 'local_purchase') {
            $adcLocalQuery = DB::table('adc_local_purchases')
                ->leftJoin('users as adc', 'adc.id', '=', 'adc_local_purchases.adc_id')
                ->select([
                    'adc_local_purchases.id as record_id',
                    DB::raw("'adc_local_purchases' as table_type"),
                    'adc_local_purchases.invoice_number as invoice_number',
                    'adc_local_purchases.purchase_no as order_number',
                    DB::raw("'ADC Local Purchase' as channel_type"),
                    DB::raw("COALESCE(adc_local_purchases.supplier_name, 'Local Supplier') as source_name"),
                    DB::raw("COALESCE(adc.name, 'ADC Store') as destination_name"),
                    'adc_local_purchases.total_amount',
                    'adc_local_purchases.purchase_date as invoice_date',
                    DB::raw("'Completed' as status"),
                ]);

            if ($search) {
                $adcLocalQuery->where(function ($q) use ($search) {
                    $q->where('adc_local_purchases.invoice_number', 'like', "%{$search}%")
                      ->orWhere('adc_local_purchases.purchase_no', 'like', "%{$search}%")
                      ->orWhere('adc_local_purchases.supplier_name', 'like', "%{$search}%")
                      ->orWhere('adc.name', 'like', "%{$search}%");
                });
            }

            if ($dateFrom) {
                $adcLocalQuery->whereDate('adc_local_purchases.purchase_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $adcLocalQuery->whereDate('adc_local_purchases.purchase_date', '<=', $dateTo);
            }

            foreach ($adcLocalQuery->get() as $row) {
                $items[] = (array)$row;
            }
        }

        // De-duplicate by unique key: table_type + record_id
        $unique = [];
        foreach ($items as $item) {
            $key = $item['table_type'] . '_' . $item['record_id'];
            if (!isset($unique[$key])) {
                $unique[$key] = $item;
            }
        }
        $collection = collect(array_values($unique));

        // Sort by invoice_date descending
        $sorted = $collection->sortByDesc(function ($item) {
            return $item['invoice_date'] ?? '1970-01-01';
        })->values();

        // Paginate manually
        $perPage = 20;
        $page = LengthAwarePaginator::resolveCurrentPage('page');
        $paginatedItems = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        $invoices = new LengthAwarePaginator(
            $paginatedItems,
            $sorted->count(),
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.invoices.index', compact('invoices', 'search', 'channel', 'dateFrom', 'dateTo'));
    }

    /**
     * Update ONLY the invoice number for any order/invoice record.
     */
    public function updateInvoiceNumber(Request $request)
    {
        $request->validate([
            'table_type'     => 'required|string',
            'record_id'      => 'required|integer',
            'invoice_number' => 'required|string|max:100',
        ]);

        $tableType     = $request->input('table_type');
        $recordId      = (int)$request->input('record_id');
        $invoiceNumber = trim($request->input('invoice_number'));

        try {
            switch ($tableType) {
                case 'invoices':
                    DB::table('invoices')
                        ->where('id', $recordId)
                        ->update([
                            'invoice_number' => $invoiceNumber,
                            'updated_at'     => now(),
                        ]);
                    break;

                case 'purchase_orders':
                    $inv = DB::table('invoices')->where('purchase_order_id', $recordId)->first();
                    if ($inv) {
                        DB::table('invoices')->where('id', $inv->id)->update([
                            'invoice_number' => $invoiceNumber,
                            'updated_at'     => now(),
                        ]);
                    } else {
                        DB::table('invoices')->insert([
                            'purchase_order_id' => $recordId,
                            'invoice_number'    => $invoiceNumber,
                            'type'              => 'msme_po',
                            'invoice_date'      => now(),
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ]);
                    }
                    break;

                case 'adc_orders':
                    DB::table('adc_orders')
                        ->where('id', $recordId)
                        ->update([
                            'received_invoice_number' => $invoiceNumber,
                            'updated_at'              => now(),
                        ]);
                    $inv = DB::table('invoices')->where('adc_order_id', $recordId)->first();
                    if ($inv) {
                        DB::table('invoices')->where('id', $inv->id)->update([
                            'invoice_number' => $invoiceNumber,
                            'updated_at'     => now(),
                        ]);
                    }
                    break;

                case 'ddc_pos_orders':
                case 'adc_pos_orders':
                    $inv = DB::table('invoices')->where('pos_order_id', $recordId)->first();
                    if ($inv) {
                        DB::table('invoices')->where('id', $inv->id)->update([
                            'invoice_number' => $invoiceNumber,
                            'updated_at'     => now(),
                        ]);
                    } else {
                        DB::table('invoices')->insert([
                            'pos_order_id'   => $recordId,
                            'invoice_number' => $invoiceNumber,
                            'type'           => $tableType === 'ddc_pos_orders' ? 'ddc_pos' : 'adc_pos',
                            'invoice_date'   => now(),
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                    }
                    break;

                case 'ddc_local_purchases':
                    DB::table('ddc_local_purchases')
                        ->where('id', $recordId)
                        ->update([
                            'invoice_number' => $invoiceNumber,
                            'updated_at'     => now(),
                        ]);
                    break;

                case 'adc_local_purchases':
                    DB::table('adc_local_purchases')
                        ->where('id', $recordId)
                        ->update([
                            'invoice_number' => $invoiceNumber,
                            'updated_at'     => now(),
                        ]);
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid table type specified.',
                    ], 422);
            }

            return response()->json([
                'success'        => true,
                'message'        => 'Invoice Number updated successfully!',
                'invoice_number' => $invoiceNumber,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice number: ' . $e->getMessage(),
            ], 500);
        }
    }
}
