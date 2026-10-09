@extends('layouts.adc')

@section('title', 'Generate Debit Note')
@section('page-title', 'Generate Debit Note')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <a href="{{ route('adc.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2.5">
                                    <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1"></iconify-icon>
                                    Back to Order
                                </a>
                                <span class="badge bg-warning-subtle text-warning px-2.5 py-1.5 fs-12 fw-semibold">
                                    Order Discrepancy Settlement
                                </span>
                            </div>
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Generate Debit Note for Order #{{ $order->order_number }}
                            </h4>
                            <p class="text-muted small mb-0">
                                Target Supplier: 
                                @if($targetType === 'DDC')
                                    <span class="badge bg-purple-100 text-purple-800 ms-1 me-1">DDC</span>
                                    <strong class="text-dark">{{ $targetDdc->name ?? 'Assigned DDC #'.$order->assigned_ddc_id }}</strong>
                                @else
                                    <span class="badge bg-info-subtle text-info ms-1 me-1">Seller</span>
                                    <strong class="text-dark">{{ $targetSeller->company_name ?: ($targetSeller->name ?? 'Direct Seller') }}</strong>
                                @endif
                                | Total Discrepancy Items: <strong class="text-dark">{{ $discrepancyItems->count() }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <iconify-icon icon="solar:danger-circle-bold" class="align-middle me-1 fs-18"></iconify-icon>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('adc.debit-notes.store', $order->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <!-- Discrepancy Items Table -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold text-slate-800 mb-0">
                            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="align-middle me-1 text-primary fs-18"></iconify-icon>
                            Discrepancy Products Breakdown
                        </h6>
                        <span class="badge bg-light text-muted px-2.5 py-1">Auto-fetched from Stock In</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light bg-opacity-50 border-top border-bottom small text-muted">
                                    <tr>
                                        <th class="ps-3" style="width: 220px;">Product Name</th>
                                        <th class="text-center" style="width: 80px;">Ordered</th>
                                        <th class="text-center" style="width: 80px;">Received</th>
                                        <th class="text-center text-danger" style="width: 80px;">Shortage</th>
                                        <th class="text-center text-warning" style="width: 80px;">Damage</th>
                                        <th class="text-end" style="width: 100px;">Unit Price</th>
                                        <th class="pe-3 text-end" style="width: 110px;">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $grandDiscrepancyAmount = 0; @endphp
                                    @foreach($discrepancyItems as $item)
                                        @php
                                            $itemDiscrepancyQty = $item->shortage_qty + $item->damage_qty;
                                            $itemAmount = $itemDiscrepancyQty * (float) $item->price;
                                            $grandDiscrepancyAmount += $itemAmount;
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-semibold text-slate-800 fs-14">
                                                    {{ $item->product_name ?? $item->product?->product_name }}
                                                </div>
                                                <div class="text-muted small font-monospace">
                                                    {{ $item->product_code ?? 'PROD-'.$item->product_id }}
                                                </div>
                                            </td>
                                            <td class="text-center fw-medium">{{ $item->quantity }}</td>
                                            <td class="text-center text-success fw-medium">{{ $item->quantity - $itemDiscrepancyQty }}</td>
                                            <td class="text-center">
                                                @if($item->shortage_qty > 0)
                                                    <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">{{ $item->shortage_qty }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($item->damage_qty > 0)
                                                    <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-12">{{ $item->damage_qty }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                                            <td class="pe-3 text-end fw-bold text-slate-800">
                                                ₹{{ number_format($itemAmount, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light bg-opacity-50 border-top">
                                    <tr>
                                        <td colspan="6" class="ps-3 text-end fw-bold text-slate-700">Total Discrepancy Claim Amount:</td>
                                        <td class="pe-3 text-end fw-extrabold text-danger fs-15">
                                            ₹{{ number_format($grandDiscrepancyAmount, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Proof Images Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-slate-800 mb-0">
                            <iconify-icon icon="solar:camera-add-bold-duotone" class="align-middle me-1 text-primary fs-18"></iconify-icon>
                            Upload Discrepancy Proof (Max 5) <span class="text-danger">*</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Upload photos/documents as proof for shortage, damage, or excess stock items. (Formats: JPEG, PNG, JPG, PDF | Max size: 5MB per file)
                        </p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-slate-700">
                                Discrepancy Proof Files <span class="text-danger">*</span> (Min 1, Max 5)
                            </label>

                            <!-- Hidden input that holds all files for form submission -->
                            <input type="file" id="proof_images_hidden" name="proof_images[]" multiple class="d-none">

                            <!-- Visible file picker button -->
                            <div class="mb-2">
                                <button type="button" class="btn btn-outline-primary btn-sm w-100 py-2 fw-semibold" id="btn-add-file">
                                    <iconify-icon icon="solar:camera-add-bold" class="align-middle me-1 fs-16"></iconify-icon>
                                    + Add Proof Image / File
                                </button>
                                <input type="file" id="proof_file_picker" class="d-none" accept="image/jpeg,image/png,image/jpg,application/pdf" multiple>
                            </div>

                            @error('proof_images')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('proof_images.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-danger fw-semibold small" id="file-count-text">0 of 5 files added. At least 1 proof file is required.</div>
                        </div>

                        <!-- Selected Files List Preview with Remove option -->
                        <div id="selected-files-container" class="mb-3 d-none">
                            <label class="form-label fw-semibold small text-slate-700 mb-1">Uploaded Proof Files:</label>
                            <div id="selected-files-list" class="d-flex flex-column gap-2"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side Card: Order & Target Summary -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-slate-800 mb-0">
                            <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1 text-primary fs-18"></iconify-icon>
                            Claim Summary
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Order Number:</span>
                            <span class="fw-semibold text-slate-800">#{{ $order->order_number }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Target Recipient:</span>
                            @if($targetType === 'DDC')
                                <span class="badge bg-purple-100 text-purple-800">DDC: {{ $targetDdc->name ?? 'DDC #'.$order->assigned_ddc_id }}</span>
                            @else
                                <span class="badge bg-info-subtle text-info">Seller: {{ $targetSeller->company_name ?: ($targetSeller->name ?? 'Seller') }}</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Claimed Amount:</span>
                            <span class="fw-bold text-danger fs-16">₹{{ number_format($grandDiscrepancyAmount, 2) }}</span>
                        </div>

                        <hr class="my-3">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-slate-700">Remarks / Description</label>
                            <textarea name="remarks" rows="4" class="form-control form-control-sm" placeholder="Add any details about shortage or damage stock..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 py-2.5 fw-bold shadow-sm" id="submit-dn-btn">
                            <iconify-icon icon="solar:document-add-bold" class="align-middle me-1 fs-18"></iconify-icon>
                            Generate Consolidated Debit Note
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const maxFiles = 5;
                let selectedFiles = []; // Store File objects array

                const btnAddFile = document.getElementById('btn-add-file');
                const filePicker = document.getElementById('proof_file_picker');
                const hiddenInput = document.getElementById('proof_images_hidden');
                const container = document.getElementById('selected-files-container');
                const list = document.getElementById('selected-files-list');
                const countText = document.getElementById('file-count-text');
                const form = document.querySelector('form');

                if (btnAddFile && filePicker) {
                    btnAddFile.addEventListener('click', function () {
                        if (selectedFiles.length >= maxFiles) {
                            alert(`Maximum limit of ${maxFiles} proof files reached.`);
                            return;
                        }
                        filePicker.click();
                    });

                    filePicker.addEventListener('change', function () {
                        if (!filePicker.files || filePicker.files.length === 0) return;

                        const newFiles = Array.from(filePicker.files);
                        
                        for (let file of newFiles) {
                            if (selectedFiles.length >= maxFiles) {
                                alert(`You can only add up to ${maxFiles} files in total.`);
                                break;
                            }

                            if (file.size > 5 * 1024 * 1024) {
                                alert(`File "${file.name}" exceeds the 5MB size limit.`);
                                continue;
                            }

                            const exists = selectedFiles.some(f => f.name === file.name && f.size === file.size);
                            if (exists) {
                                alert(`File "${file.name}" is already added.`);
                                continue;
                            }

                            selectedFiles.push(file);
                        }

                        filePicker.value = '';
                        renderFileList();
                    });
                }

                function renderFileList() {
                    list.innerHTML = '';

                    if (selectedFiles.length > 0) {
                        container.classList.remove('d-none');
                        countText.className = 'form-text text-success fw-semibold small';
                        countText.innerText = `${selectedFiles.length} of ${maxFiles} files added.`;
                        
                        if (selectedFiles.length >= maxFiles) {
                            btnAddFile.disabled = true;
                            btnAddFile.innerText = 'Max 5 Files Reached';
                        } else {
                            btnAddFile.disabled = false;
                            btnAddFile.innerHTML = '<iconify-icon icon="solar:camera-add-bold" class="align-middle me-1 fs-16"></iconify-icon> + Add Another Proof Image / File';
                        }

                        selectedFiles.forEach((file, index) => {
                            const itemDiv = document.createElement('div');
                            itemDiv.className = 'border rounded p-2 bg-white shadow-sm d-flex align-items-center justify-content-between gap-2';

                            const isImage = file.type.startsWith('image/');
                            const fileSize = (file.size / 1024 / 1024).toFixed(2);
                            let previewHtml = '';

                            if (isImage) {
                                const objectUrl = URL.createObjectURL(file);
                                previewHtml = `<img src="${objectUrl}" alt="img" class="rounded border object-fit-cover" style="width: 40px; height: 40px;">`;
                            } else {
                                previewHtml = `
                                    <div class="border rounded bg-light text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <iconify-icon icon="solar:document-bold" class="fs-20"></iconify-icon>
                                    </div>
                                `;
                            }

                            itemDiv.innerHTML = `
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    ${previewHtml}
                                    <div class="text-truncate" style="max-width: 180px;">
                                        <span class="fw-semibold small d-block text-truncate text-slate-800">${file.name}</span>
                                        <span class="text-muted extra-small">${fileSize} MB</span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1 rounded-circle remove-file-btn" data-index="${index}" title="Remove file">
                                    <iconify-icon icon="solar:trash-bin-trash-bold" class="fs-16 align-middle"></iconify-icon>
                                </button>
                            `;

                            list.appendChild(itemDiv);
                        });

                        list.querySelectorAll('.remove-file-btn').forEach(btn => {
                            btn.addEventListener('click', function () {
                                const idx = parseInt(this.getAttribute('data-index'));
                                selectedFiles.splice(idx, 1);
                                renderFileList();
                            });
                        });

                    } else {
                        container.classList.add('d-none');
                        countText.className = 'form-text text-danger fw-semibold small';
                        countText.innerText = '0 of 5 files added. At least 1 proof file is required.';
                        btnAddFile.disabled = false;
                        btnAddFile.innerHTML = '<iconify-icon icon="solar:camera-add-bold" class="align-middle me-1 fs-16"></iconify-icon> + Add Proof Image / File';
                    }

                    syncHiddenInput();
                }

                function syncHiddenInput() {
                    const dt = new DataTransfer();
                    selectedFiles.forEach(file => dt.items.add(file));
                    hiddenInput.files = dt.files;
                }

                if (form) {
                    form.addEventListener('submit', function (e) {
                        syncHiddenInput();
                        if (!hiddenInput.files || hiddenInput.files.length === 0) {
                            e.preventDefault();
                            alert('Proof file is mandatory! Please add at least 1 proof image/document before generating a Debit Note.');
                            btnAddFile.focus();
                            return false;
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
