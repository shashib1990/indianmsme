@extends('layouts.ddc')

@section('title', 'Missing Barcodes List')
@section('page-title', 'Missing Barcodes Inventory List')

@push('styles')
    <style>
        .inv-table-wrap {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            overflow: hidden;
            border: 1px solid #e8ecf4;
        }

        .inv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .inv-table thead tr.thead-cols th {
            background: #f8fafd;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .55px;
            padding: 10px 12px;
            border-bottom: 2px solid #e8ecf4;
            white-space: nowrap;
        }

        .inv-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background .15s;
        }

        .inv-table tbody tr:hover {
            background: #f8fafc;
        }

        .inv-table td {
            padding: 10px 12px;
            vertical-align: middle;
        }

        .badge-source-msme {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }

        .badge-source-local {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .barcode-input-group {
            max-width: 260px;
        }

        .barcode-input-group input {
            font-family: monospace;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
    </style>
@endpush

@section('content')
    <!-- Header Navigation Tabs -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body py-3 px-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div>
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                ⚠️ Missing Barcodes List
                            </h4>
                            <p class="text-muted small mb-0">Inventory products requiring Barcode numbers. Update barcodes inline to enable seamless POS barcode scanning.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('ddc.inventory.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                                <iconify-icon icon="solar:box-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> All Inventory List
                            </a>
                            <a href="{{ route('ddc.inventory.missing-barcodes') }}" class="btn btn-warning btn-sm fw-semibold text-dark">
                                <iconify-icon icon="solar:barcode-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Missing Barcodes List ({{ $summaryStats['total_missing'] }})
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Total Missing Barcodes</div>
                        <div class="fs-22 fw-bold text-danger mt-1">{{ number_format($summaryStats['total_missing']) }}</div>
                        <div class="fs-11 text-muted mt-0.5">Inventory items pending barcode</div>
                    </div>
                    <div class="avatar-sm bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <iconify-icon icon="solar:barcode-bold-duotone" class="fs-24"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">MSME Central Items</div>
                        <div class="fs-22 fw-bold text-primary mt-1">{{ number_format($summaryStats['msme_missing']) }}</div>
                        <div class="fs-11 text-muted mt-0.5">Central catalog items missing barcode</div>
                    </div>
                    <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <iconify-icon icon="solar:buildings-bold-duotone" class="fs-24"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Regional Local Items</div>
                        <div class="fs-22 fw-bold text-success mt-1">{{ number_format($summaryStats['local_missing']) }}</div>
                        <div class="fs-11 text-muted mt-0.5">Regional stock items missing barcode</div>
                    </div>
                    <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <iconify-icon icon="solar:shop-bold-duotone" class="fs-24"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Bulk Update Form Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                <div class="fw-bold text-dark fs-13 d-flex align-items-center gap-1">
                    <iconify-icon icon="solar:filter-bold-duotone" class="text-primary fs-18 me-1"></iconify-icon> Filter Missing Barcodes by Source:
                </div>
                <div class="btn-group" role="group" aria-label="Source Filter">
                    <a href="{{ route('ddc.inventory.missing-barcodes', array_merge(request()->query(), ['source' => 'ALL'])) }}"
                        class="btn btn-sm {{ $source === 'ALL' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                        <iconify-icon icon="solar:widget-bold-duotone" class="align-middle me-1"></iconify-icon> All Sources ({{ $summaryStats['total_missing'] }})
                    </a>
                    <a href="{{ route('ddc.inventory.missing-barcodes', array_merge(request()->query(), ['source' => 'MSME'])) }}"
                        class="btn btn-sm {{ $source === 'MSME' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                        <iconify-icon icon="solar:buildings-bold-duotone" class="align-middle me-1"></iconify-icon> MSME Central ({{ $summaryStats['msme_missing'] }})
                    </a>
                    <a href="{{ route('ddc.inventory.missing-barcodes', array_merge(request()->query(), ['source' => 'LOCAL'])) }}"
                        class="btn btn-sm {{ $source === 'LOCAL' ? 'btn-success' : 'btn-outline-success' }} fw-semibold px-3">
                        <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon> Regional Local ({{ $summaryStats['local_missing'] }})
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('ddc.inventory.missing-barcodes') }}" class="row g-2 align-items-center">
                <input type="hidden" name="source" value="{{ $source }}">
                <div class="col-md-9">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <iconify-icon icon="solar:magnifer-linear" class="fs-16 text-muted"></iconify-icon>
                        </span>
                        <input type="text" name="search" class="form-control form-control-sm border-start-0"
                            placeholder="Search by Product Name, Item Code, Batch Number..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        Filter
                    </button>
                    <a href="{{ route('ddc.inventory.missing-barcodes', ['source' => $source]) }}" class="btn btn-light btn-sm" title="Reset Search">
                        <iconify-icon icon="solar:restart-bold-duotone" class="align-middle"></iconify-icon>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Missing Barcodes Table with Inline / Bulk Form -->
    <form method="POST" action="{{ route('ddc.inventory.bulk-update-barcodes') }}">
        @csrf
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-header bg-white py-3 px-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark fs-13">
                    <iconify-icon icon="solar:list-bold-duotone" class="text-warning me-1 fs-16"></iconify-icon> Products Pending Barcode Assignment
                </div>
                @if($inventories->count() > 0)
                    <button type="submit" class="btn btn-success btn-sm fw-semibold">
                        <iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Save All Entered Barcodes
                    </button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="inv-table">
                        <thead>
                            <tr class="thead-cols">
                                <th style="width: 4%;" class="text-center">#</th>
                                <th style="width: 25%;">Product Description</th>
                                <th style="width: 10%;">Product Code</th>
                                <th style="width: 10%;">Source</th>
                                <th style="width: 10%;" class="text-center">Stock Qty</th>
                                <th style="width: 10%;" class="text-end">MRP</th>
                                <th style="width: 23%;">Assign Barcode</th>
                                <th style="width: 8%;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventories as $idx => $inv)
                                <tr>
                                    <td class="text-center text-muted fs-12">{{ $inventories->firstItem() + $idx }}</td>
                                    <td>
                                        <div class="fw-bold text-dark fs-13">{{ $inv->product_name }}</div>
                                        @if($inv->batch_number)
                                            <div class="fs-11 text-muted">Batch: <code>{{ $inv->batch_number }}</code></div>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="fw-bold text-primary fs-12">{{ $inv->product_code ?: '—' }}</code>
                                    </td>
                                    <td>
                                        @if($inv->inventory_source === 'LOCAL')
                                            <span class="badge badge-source-local px-2 py-1 fs-11 fw-bold">Regional Local</span>
                                        @else
                                            <span class="badge badge-source-msme px-2 py-1 fs-11 fw-bold">MSME Central</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold text-dark fs-13">
                                        {{ number_format($inv->quantity, 2) }} {{ $inv->unit_of_measure ?: 'Units' }}
                                    </td>
                                    <td class="text-end fw-semibold text-dark fs-13">
                                        ₹{{ number_format($inv->mrp, 2) }}
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm barcode-input-group">
                                            <span class="input-group-text bg-light text-muted">
                                                <iconify-icon icon="solar:barcode-bold-duotone" class="fs-16"></iconify-icon>
                                            </span>
                                            <input type="text" name="barcodes[{{ $inv->id }}]" id="barcode-input-{{ $inv->id }}"
                                                class="form-control form-control-sm border-start-0 text-dark fw-bold missing-barcode-input"
                                                placeholder="Scan or enter Barcode..."
                                                value="{{ old('barcodes.'.$inv->id, ($inv->barcode && !in_array($inv->barcode, ['N/A', '—', '0']) ? $inv->barcode : '')) }}"
                                                onkeypress="if(event.key === 'Enter'){ event.preventDefault(); quickSaveBarcode({{ $inv->id }}, this); }">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-primary btn-sm fw-semibold px-2.5 py-1" onclick="quickSaveBarcode({{ $inv->id }}, this)">
                                            <iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1"></iconify-icon> Save
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <iconify-icon icon="solar:check-read-bold-duotone" class="fs-48 text-success opacity-75 mb-2"></iconify-icon>
                                            <h6 class="fw-bold text-dark mb-1">Awesome! No Missing Barcodes</h6>
                                            <p class="fs-12 mb-0">All inventory items in this view have updated barcodes assigned.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($inventories->hasPages())
                <div class="card-footer bg-white border-top py-2 px-3">
                    {{ $inventories->links() }}
                </div>
            @endif
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        function quickSaveBarcode(invId, btnElem) {
            const inputElem = document.getElementById('barcode-input-' + invId);
            const barcodeVal = inputElem ? inputElem.value.trim() : '';

            if (!barcodeVal) {
                alert('Please enter or scan a valid Barcode number first.');
                if (inputElem) inputElem.focus();
                return;
            }

            let targetBtn = null;
            if (btnElem && btnElem.tagName === 'BUTTON') {
                targetBtn = btnElem;
            } else {
                const trRow = inputElem ? inputElem.closest('tr') : null;
                if (trRow) targetBtn = trRow.querySelector('button');
            }

            if (targetBtn) {
                targetBtn.disabled = true;
                targetBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...';
            }

            const updateUrl = "{{ route('ddc.inventory.update-barcode', ['id' => ':id']) }}".replace(':id', invId);

            fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    barcode: barcodeVal
                })
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    if (targetBtn) {
                        targetBtn.className = 'btn btn-success btn-sm fw-semibold px-2.5 py-1';
                        targetBtn.innerHTML = '<iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon> Saved';
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || "Barcode updated & item removed from missing list!");
                    }

                    // Animate and remove row from missing barcodes list
                    const trRow = inputElem ? inputElem.closest('tr') : null;
                    if (trRow) {
                        trRow.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                        trRow.style.backgroundColor = '#dcfce7';
                        trRow.style.opacity = '0';
                        trRow.style.transform = 'translateX(30px)';
                        setTimeout(function() {
                            trRow.remove();
                        }, 550);
                    }
                } else {
                    alert(response.message || "Failed to update barcode.");
                    if (targetBtn) {
                        targetBtn.disabled = false;
                        targetBtn.innerHTML = '<iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1"></iconify-icon> Save';
                    }
                }
            })
            .catch(error => {
                console.error('Save Barcode Error:', error);
                alert("An error occurred while updating barcode.");
                if (targetBtn) {
                    targetBtn.disabled = false;
                    targetBtn.innerHTML = '<iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1"></iconify-icon> Save';
                }
            });
        }
    </script>
@endpush
