<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcDebitNote;
use App\Models\AdcDebitNoteItem;
use Illuminate\Http\Request;

class DebitNoteController extends Controller
{
    private function adcId()
    {
        return auth('adc')->id();
    }

    public function index(Request $request)
    {
        $adcId = $this->adcId();
        $status = $request->get('status');
        $search = $request->get('search');

        $query = AdcDebitNote::where('adc_id', $adcId)
            ->where('discrepancy_type', '!=', 'EXCESS')
            ->with(['order', 'items.adcOrderItem.product', 'ddc', 'seller', 'creditNote']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('debit_note_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($orderQ) use ($search) {
                      $orderQ->where('order_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('ddc', function ($ddcQ) use ($search) {
                      $ddcQ->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('seller', function ($sellerQ) use ($search) {
                      $sellerQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $debitNotes = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('adc.debit-notes.index', compact('debitNotes', 'status', 'search'));
    }

    public function create($orderId)
    {
        $adcId = $this->adcId();
        $order = \App\Models\AdcOrder::with(['items.product', 'selectedDdc', 'assignedDdc'])
            ->where('adc_id', $adcId)
            ->findOrFail($orderId);

        // Check if Shortage/Damage Debit Note already exists for this order
        $existingNote = AdcDebitNote::where('adc_order_id', $orderId)
            ->where('discrepancy_type', '!=', 'EXCESS')
            ->first();

        if ($existingNote) {
            return redirect()->route('adc.debit-notes.show', $existingNote->id)
                ->with('info', 'Debit Note has already been created for this Order.');
        }

        $discrepancyItems = $order->items->filter(function ($item) {
            return ($item->shortage_qty > 0 || $item->damage_qty > 0);
        });

        if ($discrepancyItems->isEmpty()) {
            return redirect()->route('adc.orders.show', $orderId)
                ->with('error', 'No shortage or damage discrepancy found in this Order to generate a Debit Note.');
        }

        // Determine target supplier (DDC vs Seller)
        $fulfillmentType = $order->fulfillment_type;
        $targetType = 'SELLER';
        $targetDdc = null;
        $targetSeller = null;

        if ($fulfillmentType === 'physical' || !empty($order->selected_ddc_id) || !empty($order->assigned_ddc_id)) {
            $targetType = 'DDC';
            $targetDdc = $order->selectedDdc ?? $order->assignedDdc;
            if (!$targetDdc && $discrepancyItems->first()?->ddc_id) {
                $targetDdc = \App\Models\User::find($discrepancyItems->first()->ddc_id);
            }
        } else {
            $targetType = 'SELLER';
            $sellerId = $discrepancyItems->first()?->seller_id;
            if ($sellerId) {
                $targetSeller = \App\Models\User::find($sellerId);
            }
        }

        return view('adc.debit-notes.create', compact('order', 'discrepancyItems', 'targetType', 'targetDdc', 'targetSeller'));
    }

    public function store(Request $request, $orderId)
    {
        $request->validate([
            'proof_images' => 'required|array|min:1|max:5',
            'proof_images.*' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'remarks' => 'nullable|string|max:1000',
        ], [
            'proof_images.required' => 'At least 1 discrepancy proof file is required to generate a Debit Note.',
            'proof_images.min' => 'Please upload at least 1 proof file.',
            'proof_images.max' => 'You can upload a maximum of 5 proof files.',
            'proof_images.*.mimes' => 'Proof files must be in JPEG, PNG, JPG, or PDF format.',
            'proof_images.*.max' => 'Each proof file must not exceed 5MB.',
        ]);

        $adcId = $this->adcId();
        $adc = auth('adc')->user();
        $order = \App\Models\AdcOrder::with(['items.product', 'selectedDdc', 'assignedDdc'])
            ->where('adc_id', $adcId)
            ->findOrFail($orderId);

        if (AdcDebitNote::where('adc_order_id', $orderId)->where('discrepancy_type', '!=', 'EXCESS')->exists()) {
            return redirect()->back()->with('error', 'Debit Note already exists for this Order.');
        }

        $discrepancyItems = $order->items->filter(function ($item) {
            return ($item->shortage_qty > 0 || $item->damage_qty > 0);
        });

        if ($discrepancyItems->isEmpty()) {
            return redirect()->back()->with('error', 'No shortage or damage discrepancies found to generate a Debit Note.');
        }

        $proofUrls = [];
        if ($request->hasFile('proof_images')) {
            $files = array_slice($request->file('proof_images'), 0, 5);
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $proofUrls[] = $this->uploadFileToService($file, 'debit_notes');
                }
            }
        }

        $totalShortage = $discrepancyItems->sum('shortage_qty');
        $totalDamage = $discrepancyItems->sum('damage_qty');

        $totalDiscrepancyAmount = $discrepancyItems->sum(function ($item) {
            return ($item->shortage_qty + $item->damage_qty) * (float) $item->price;
        });

        $types = [];
        if ($totalShortage > 0) $types[] = 'SHORTAGE';
        if ($totalDamage > 0) $types[] = 'DAMAGE';
        $discrepancyType = count($types) > 1 ? 'MIXED' : ($types[0] ?? 'SHORTAGE');

        // Determine target supplier
        $fulfillmentType = $order->fulfillment_type;
        $targetType = 'SELLER';
        $ddcId = null;
        $sellerId = null;

        if ($fulfillmentType === 'physical' || !empty($order->selected_ddc_id) || !empty($order->assigned_ddc_id)) {
            $targetType = 'DDC';
            $ddcId = $order->selected_ddc_id ?? $order->assigned_ddc_id ?? $discrepancyItems->first()?->ddc_id;
        } else {
            $targetType = 'SELLER';
            $sellerId = $discrepancyItems->first()?->seller_id;
        }

        \DB::beginTransaction();
        try {
            $debitNoteNumber = AdcDebitNote::generateNumber();

            $debitNote = AdcDebitNote::create([
                'debit_note_number'       => $debitNoteNumber,
                'adc_order_id'            => $order->id,
                'adc_id'                  => $adcId,
                'target_type'             => $targetType,
                'ddc_id'                  => $ddcId,
                'seller_id'               => $sellerId,
                'discrepancy_type'        => $discrepancyType,
                'order_qty'               => $discrepancyItems->sum('quantity'),
                'received_qty'            => $discrepancyItems->sum(function($i){ return $i->quantity - $i->shortage_qty - $i->damage_qty; }),
                'shortage_qty'            => $totalShortage,
                'damage_qty'              => $totalDamage,
                'excess_qty'              => 0,
                'unit_price'              => $discrepancyItems->first()?->price ?: 0,
                'total_discrepancy_amount' => $totalDiscrepancyAmount,
                'seller_amount'           => $totalDiscrepancyAmount,
                'final_amount'            => $totalDiscrepancyAmount,
                'proof_images'            => $proofUrls,
                'status'                  => 'PENDING_SELLER_APPROVAL',
                'adpl_remarks'            => $request->input('remarks') ?: "Stock-in discrepancy reported by ADC {$adc->name} for Order #{$order->order_number}",
            ]);

            foreach ($discrepancyItems as $item) {
                $itemDiscrepancyQty = $item->shortage_qty + $item->damage_qty;
                $itemDiscrepancyAmount = round($itemDiscrepancyQty * (float)$item->price, 2);

                AdcDebitNoteItem::create([
                    'adc_debit_note_id'       => $debitNote->id,
                    'adc_order_item_id'       => $item->id,
                    'product_name'            => $item->product_name ?? $item->product?->product_name,
                    'order_qty'               => $item->quantity,
                    'received_qty'            => $item->quantity - $itemDiscrepancyQty,
                    'shortage_qty'            => $item->shortage_qty,
                    'damage_qty'              => $item->damage_qty,
                    'excess_qty'              => 0,
                    'unit_price'              => $item->price,
                    'item_discrepancy_amount' => $itemDiscrepancyAmount,
                ]);
            }

            \DB::commit();

            return redirect()->route('adc.debit-notes.show', $debitNote->id)
                ->with('success', 'Debit Note generated successfully and sent for approval!');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Failed to generate Debit Note: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $adcId = $this->adcId();
        $debitNote = AdcDebitNote::where('adc_id', $adcId)
            ->where('discrepancy_type', '!=', 'EXCESS')
            ->with(['order', 'items.adcOrderItem.product', 'ddc.profile', 'seller.sellerProfile', 'creditNote'])
            ->findOrFail($id);

        return view('adc.debit-notes.show', compact('debitNote'));
    }
}
