<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IncentiveHold;
use Illuminate\Http\Request;

class IncentiveHoldController extends Controller
{
    public function index(Request $request)
    {
        $query = IncentiveHold::with(['purchaseOrder', 'releases']);

        // Search by AMA code (searches the releases table)
        if ($request->filled('ama_code')) {
            $amaCode = $request->input('ama_code');
            $query->whereHas('releases', function ($sub) use ($amaCode) {
                $sub->where('ama_code', 'like', '%' . $amaCode . '%');
            });
        }

        // Filter by Type (SSI, FSI)
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Search by PO Number
        if ($request->filled('po_number')) {
            $poNumber = $request->input('po_number');
            $query->whereHas('purchaseOrder', function ($q) use ($poNumber) {
                $q->where('po_number', 'like', '%' . $poNumber . '%');
            });
        }

        $transactions = $query->orderBy('id', 'desc')->paginate(20);

        return view('admin.ama-transactions.index', compact('transactions'));
    }
}
