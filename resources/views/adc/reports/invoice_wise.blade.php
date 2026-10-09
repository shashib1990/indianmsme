@extends('layouts.adc')

@section('title', 'Invoice Wise Report')
@section('page-title', 'Invoice Wise Report')

@section('content')
    <!-- Header Card (DDC Reference Design) -->
    <div class="row mb-3 mt-0">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Invoice Wise Sales Report</h4>
                            <p class="text-muted small mb-0">Itemized billing history, payment modes (`CASH` / `CREDIT`), customer records, and net totals.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2">
                            <a href="{{ route('adc.reports.invoice-wise.export', request()->all()) }}"
                                class="btn btn-outline-success btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:file-download-bold-duotone" width="18" height="18" style="font-size: 18px !important; width: 18px !important; height: 18px !important;"></iconify-icon>
                                Export CSV
                            </a>
                            <a href="{{ route('adc.reports.invoice-wise.print', request()->all()) }}" target="_blank"
                                class="btn btn-primary btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:printer-bold-duotone" width="18" height="18" style="font-size: 18px !important; width: 18px !important; height: 18px !important;"></iconify-icon>
                                Print Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Cards (DDC Reference Design) -->
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Total Revenue</div>
                        <div class="fs-20 fw-bold text-primary mt-1">₹{{ number_format($totalRevenue, 2) }}</div>
                        <div class="fs-11 text-muted mt-1">Net Sales Income</div>
                    </div>
                    <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:dollar-minimalistic-bold-duotone" width="22" height="22" style="font-size: 22px !important; width: 22px !important; height: 22px !important;"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Cash Sales</div>
                        <div class="fs-20 fw-bold text-success mt-1">₹{{ number_format($cashSales, 2) }}</div>
                        <div class="fs-11 text-muted mt-1">Direct Cash Transactions</div>
                    </div>
                    <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:wallet-money-bold-duotone" width="22" height="22" style="font-size: 22px !important; width: 22px !important; height: 22px !important;"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Credit Sales</div>
                        <div class="fs-20 fw-bold text-purple-600 mt-1" style="color: #7c3aed;">₹{{ number_format($creditSales, 2) }}</div>
                        <div class="fs-11 text-muted mt-1">Customer Udhaar Sales</div>
                    </div>
                    <div class="avatar-sm bg-purple-subtle text-purple-600 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #f3e8ff; color: #7c3aed;">
                        <iconify-icon icon="solar:card-recive-bold-duotone" width="22" height="22" style="font-size: 22px !important; width: 22px !important; height: 22px !important;"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Total Invoices</div>
                        <div class="fs-20 fw-bold text-dark mt-1">{{ number_format($totalInvoicesCount) }}</div>
                        <div class="fs-11 text-muted mt-1">Invoices Issued</div>
                    </div>
                    <div class="avatar-sm bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:notes-bold-duotone" width="22" height="22" style="font-size: 22px !important; width: 22px !important; height: 22px !important;"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section (DDC Reference Design) -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('adc.reports.invoice-wise') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Payment Mode</label>
                    <select name="payment_mode" class="form-select form-select-sm">
                        <option value="">All Payment Modes</option>
                        <option value="cash" {{ request('payment_mode') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="credit" {{ request('payment_mode') === 'credit' ? 'selected' : '' }}>Credit</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Search Keywords</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Invoice No, Customer, Mobile..." value="{{ request('search') }}">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <iconify-icon icon="solar:filter-bold-duotone" width="16" height="16" class="align-middle me-0.5"></iconify-icon> Filter
                    </button>
                    <a href="{{ route('adc.reports.invoice-wise') }}" class="btn btn-light btn-sm" title="Reset Filters">
                        <iconify-icon icon="solar:restart-bold-duotone" width="16" height="16" class="align-middle"></iconify-icon>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card (DDC Reference Design) -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light fs-11 text-uppercase text-muted">
                    <tr>
                        <th class="ps-4">INVOICE NO</th>
                        <th>CUSTOMER NAME</th>
                        <th>MOBILE</th>
                        <th>DATE & TIME</th>
                        <th>ITEMS</th>
                        <th>SUBTOTAL</th>
                        <th>DISCOUNT</th>
                        <th>NET TOTAL</th>
                        <th>PAYMENT MODE</th>
                        <th class="pe-4 text-end">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $ord)
                        <tr>
                            <td class="ps-4 font-monospace fw-bold fs-13">
                                @php
                                    $invNo = $ord->invoice?->invoice_number ?: ($ord->received_invoice_number ?: null);
                                @endphp
                                <a href="{{ route('adc.pos.orders.show', $ord->id) }}" class="text-primary text-decoration-none">
                                    {{ $invNo ?: '#'.$ord->order_no }}
                                </a>
                                @if($invNo && $invNo !== $ord->order_no)
                                    <div class="fs-11 text-muted fw-normal font-sans">Order: #{{ $ord->order_no }}</div>
                                @endif
                            </td>
                            <td class="fw-bold text-slate-900 fs-13">
                                <div>{{ $ord->customer_name ?: ($ord->customer?->name ?: 'Walk-in Customer') }}</div>
                                @if(!empty($ord->customer?->abhhyam_code))
                                    <span class="badge fs-10 px-2 py-0.5 rounded-pill font-monospace fw-bold mt-0.5" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a;" title="AVI Code">
                                        ★ AVI Code: {{ $ord->customer->abhhyam_code }}
                                    </span>
                                @endif
                            </td>
                            <td class="font-monospace text-muted fs-12">{{ $ord->customer_mobile ?: ($ord->customer?->mobile ?: '—') }}</td>
                            <td class="text-muted fs-12 font-monospace">{{ $ord->created_at->format('d M Y, h:i A') }}</td>
                            <td><span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fs-11 font-monospace">{{ $ord->items->count() }} Items</span></td>
                            <td class="font-monospace fs-13">₹{{ number_format($ord->subtotal ?? 0, 2) }}</td>
                            <td class="font-monospace text-danger fs-13">₹{{ number_format($ord->discount_amount ?? 0, 2) }}</td>
                            <td class="font-monospace fw-bold fs-14 text-success">₹{{ number_format((float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0), 2) }}</td>
                            <td>
                                @if(strtolower($ord->payment_mode ?? 'cash') === 'credit')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-11 px-2.5 py-1 font-monospace fw-semibold rounded-2">CREDIT</span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle fs-11 px-2.5 py-1 font-monospace fw-semibold rounded-2">CASH</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('adc.pos.orders.show', $ord->id) }}" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 me-1 fs-12 fw-semibold">View</a>
                                <a href="{{ route('adc.pos.orders.print', $ord->id) }}" target="_blank" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fs-12 fw-semibold">Print</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted fs-13">
                                No sales invoices found for the selected filter parameters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="p-3 border-top d-flex justify-content-end bg-white">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection
