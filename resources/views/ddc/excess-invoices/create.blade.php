@extends('layouts.ddc')

@section('title', 'Generate Excess Stock Invoice')
@section('page-title', 'Excess Stock Invoice')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                    <div>
                        <a href="{{ route('ddc.purchase-orders.receive', $purchaseOrder->id) }}" class="btn btn-sm btn-outline-secondary mb-2 px-3 py-1">
                            <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1"></iconify-icon> Back to Receive Stock
                        </a>
                        <h4 class="fw-bold text-slate-800 mb-1">Generate Excess Stock Invoice</h4>
                        <p class="text-muted small mb-0">
                            PO #<strong>{{ $purchaseOrder->po_number }}</strong> | Seller: <strong>{{ $purchaseOrder->seller->company_name ?: $purchaseOrder->seller->name }}</strong>
                        </p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary fs-13 px-3 py-2">
                        <iconify-icon icon="solar:bill-list-bold" class="align-middle me-1"></iconify-icon> Excess Stock Purchase
                    </span>
                </div>


                <h6 class="fw-bold text-slate-800 mb-3">Excess Items Summary</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Product Details</th>
                                <th class="text-center">Ordered Qty</th>
                                <th class="text-center">Received Qty</th>
                                <th class="text-center">Excess Qty</th>
                                <th class="text-end">DDC Price</th>
                                <th class="text-end">Total Payable (DDC)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($excessItems as $item)
                                <tr>
                                    <td>
                                        <span class="fw-semibold d-block text-slate-800">{{ $item->product->product_name }}</span>
                                        <span class="text-muted small">Code: PROD-{{ $item->product->product_code ?? $item->product->id }}</span>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-center">{{ $item->received_qty }}</td>
                                    <td class="text-center fw-bold text-primary">+{{ $item->excess_qty }}</td>
                                    <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                                    <td class="text-end fw-bold text-slate-800">₹{{ number_format($item->excess_qty * $item->price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td colspan="5" class="text-end fw-bold">Total Excess Payable Amount:</td>
                                <td class="text-end fw-bold fs-16 text-primary">₹{{ number_format($totalExcessDdcAmount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <form action="{{ route('ddc.purchase-orders.excess-invoice.store', $purchaseOrder->id) }}" method="POST">
                    @csrf
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <a href="{{ route('ddc.purchase-orders.receive', $purchaseOrder->id) }}" class="btn btn-secondary px-4 py-2">Cancel</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4 py-2">
                            <iconify-icon icon="solar:check-read-bold" class="align-middle me-1 fs-18"></iconify-icon>
                            Confirm & Generate Excess Stock Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
