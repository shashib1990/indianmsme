{{-- Shared header card for all PO status pages --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <div class="row align-items-center g-3">
                    <div class="col-md-7">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <a href="{{ route('ddc.purchase-orders.index') }}"
                               class="btn btn-sm btn-outline-secondary py-1 px-2">
                                <iconify-icon icon="solar:arrow-left-bold" class="align-middle"></iconify-icon> Back
                            </a>
                            @php
                                $statusMap = [
                                    'Pending'    => ['label' => 'PO Created',  'class' => 'bg-secondary-subtle text-secondary'],
                                    'Accepted'   => ['label' => 'Accepted',    'class' => 'bg-success-subtle text-success'],
                                    'Rejected'   => ['label' => 'Rejected',    'class' => 'bg-danger-subtle text-danger'],
                                    'Cancelled'  => ['label' => 'Cancelled',   'class' => 'bg-danger text-white'],
                                    'Processing' => ['label' => 'Processing',  'class' => 'bg-primary-subtle text-primary'],
                                    'Dispatched' => ['label' => 'Dispatched',  'class' => 'bg-warning-subtle text-warning'],
                                    'Received'   => ['label' => 'Received',    'class' => 'bg-success text-white'],
                                ];
                                $badge = $statusMap[$purchaseOrder->status] ?? ['label' => $purchaseOrder->status, 'class' => 'bg-light text-muted'];
                            @endphp
                            <span class="badge {{ $badge['class'] }} px-3 py-1.5 fs-12">
                                {{ $badge['label'] }}
                            </span>
                        </div>
                        <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                            PO #{{ $purchaseOrder->po_number }}
                        </h4>
                        <p class="text-muted small mb-0">
                            <span>Seller: <strong class="text-dark">{{ $purchaseOrder->seller?->company_name ?: $purchaseOrder->seller?->name }}</strong></span>
                            &nbsp;|&nbsp;
                            <span>Placed: <strong class="text-dark">{{ $purchaseOrder->created_at->format('d M Y, h:i A') }}</strong></span>
                            @if($purchaseOrder->ama_code)
                                &nbsp;|&nbsp;
                                <span>AMA Code: <strong class="text-dark">{{ $purchaseOrder->ama_code }}</strong></span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-5 text-md-end d-flex justify-content-md-end gap-2 flex-wrap">
                        @if(!in_array($purchaseOrder->status, ['Cancelled', 'Rejected', 'Received', 'Processing', 'Dispatched']))
                            <form action="{{ route('ddc.purchase-orders.cancel', $purchaseOrder->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm px-3 confirm-action" data-confirm-message="Are you sure you want to cancel this Purchase Order?">
                                    <iconify-icon icon="solar:close-circle-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                                    Cancel PO
                                </button>
                            </form>
                        @endif
                        @if($purchaseOrder->invoice && in_array($purchaseOrder->status, ['Processing', 'Dispatched', 'Received']))
                            <a href="{{ route('ddc.purchase-orders.invoice', $purchaseOrder->id) }}" target="_blank"
                               class="btn btn-sm btn-outline-primary px-3">
                                <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                                View Invoice
                            </a>
                        @endif
                    </div>
                </div>

                {{-- PO Lifecycle Timeline --}}
                <hr class="text-muted opacity-15 mt-3 mb-3">
                <div class="d-flex align-items-center gap-0 overflow-auto pb-1" style="font-size: 12px;">
                    @php
                        $steps = [
                            ['key' => 'Pending',    'icon' => 'solar:clipboard-list-bold-duotone',     'label' => 'PO Created'],
                            ['key' => 'Accepted',   'icon' => 'solar:check-circle-bold-duotone',        'label' => 'Accepted'],
                            ['key' => 'Processing', 'icon' => 'solar:wallet-money-bold-duotone',        'label' => 'Payment Done'],
                            ['key' => 'Dispatched', 'icon' => 'solar:delivery-bold-duotone',            'label' => 'Dispatched'],
                            ['key' => 'Received',   'icon' => 'solar:box-minimalistic-bold-duotone',    'label' => 'Received'],
                        ];
                        $cancelledOrRejected = in_array($purchaseOrder->status, ['Cancelled', 'Rejected']);
                        $statusOrder = ['Pending', 'Accepted', 'Processing', 'Dispatched', 'Received'];
                        $currentIdx = array_search($purchaseOrder->status, $statusOrder);
                    @endphp

                    @if($cancelledOrRejected)
                        @foreach($steps as $i => $step)
                            <div class="d-flex align-items-center">
                                <div class="text-center" style="min-width: 70px;">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1
                                        {{ $i === 0 ? 'bg-danger text-white' : 'bg-light text-muted' }}"
                                         style="width: 30px; height: 30px;">
                                        <iconify-icon icon="{{ $step['icon'] }}" class="fs-15"></iconify-icon>
                                    </div>
                                    <span class="{{ $i === 0 ? 'text-danger fw-semibold' : 'text-muted' }}">{{ $step['label'] }}</span>
                                </div>
                                @if(!$loop->last)
                                    <div class="flex-grow-1 border-top border-2 border-secondary opacity-25 mx-1" style="min-width: 20px;"></div>
                                @endif
                            </div>
                        @endforeach
                        <div class="d-flex align-items-center ms-1">
                            <div class="border-top border-2 border-danger opacity-50 mx-1" style="min-width: 20px;"></div>
                            <div class="text-center" style="min-width: 70px;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1 bg-danger text-white"
                                     style="width: 30px; height: 30px;">
                                    <iconify-icon icon="solar:close-circle-bold-duotone" class="fs-15"></iconify-icon>
                                </div>
                                <span class="text-danger fw-semibold">{{ $purchaseOrder->status }}</span>
                            </div>
                        </div>
                    @else
                        @foreach($steps as $i => $step)
                            <div class="d-flex align-items-center">
                                <div class="text-center" style="min-width: 70px;">
                                    @php
                                        $isDone    = $currentIdx !== false && $i <= $currentIdx;
                                        $isCurrent = $currentIdx !== false && $i === $currentIdx;
                                        $isFinal   = $purchaseOrder->status === 'Received';
                                    @endphp
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1
                                        {{ ($isCurrent && !$isFinal) ? 'bg-primary text-white' : ($isDone ? 'bg-success text-white' : 'bg-light text-muted') }}"
                                         style="width: 30px; height: 30px;">
                                        <iconify-icon icon="{{ $isDone && (!$isCurrent || $isFinal) ? 'solar:check-circle-bold-duotone' : $step['icon'] }}" class="fs-15"></iconify-icon>
                                    </div>
                                    <span class="{{ ($isCurrent && !$isFinal) ? 'text-primary fw-semibold' : ($isDone ? 'text-success fw-semibold' : 'text-muted') }}">
                                        {{ $step['label'] }}
                                    </span>
                                </div>
                                @if(!$loop->last)
                                    <div class="flex-grow-1 border-top border-2 mx-1 {{ $i < $currentIdx ? 'border-success' : 'border-secondary opacity-25' }}"
                                         style="min-width: 20px;"></div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>


            </div>
        </div>
    </div>
</div>


