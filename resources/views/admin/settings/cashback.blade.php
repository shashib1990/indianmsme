@extends('layouts.admin')

@section('title', 'DDC & ADC Shopping Cashback Settings')
@section('page-title', 'DDC & ADC Shopping Cashback Settings')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Main Configuration Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:hand-stars-bold-duotone" class="text-primary fs-22"></iconify-icon>
                    <h5 class="card-title fw-bold text-slate-800 mb-0">DDC & ADC Shopping Cashback Configuration</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Informative Alert -->
                    <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis d-flex align-items-start gap-2 mb-4 p-3 rounded">
                        <iconify-icon icon="solar:info-circle-bold-duotone" class="fs-22 text-info mt-0.5"></iconify-icon>
                        <div>
                            <h6 class="fw-bold mb-1">Shopping Cashback Rules</h6>
                            <p class="mb-0 small">
                                Set the cashback percentages earned by District Development Coordinators (DDC) and Area Development Coordinators (ADC) on shopping/orders. These percentages determine the wallet cashback generated on orders.
                            </p>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:check-circle-bold" class="fs-20 text-success"></iconify-icon>
                                <div>{{ session('success') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:close-circle-bold" class="fs-20 text-danger"></iconify-icon>
                                <div>{{ session('error') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.settings.cashback.update') }}" method="POST" id="cashbackSettingsForm">
                        @csrf
                        <div class="row g-4">
                            <!-- DDC Cashback Percentage -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-slate-700" for="ddcInput">
                                    DDC Shopping Cashback <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="ddc_cashback_percentage" class="form-control" step="0.01"
                                        min="0" max="100" value="{{ old('ddc_cashback_percentage', $ddcCashback) }}"
                                        required id="ddcInput" placeholder="5.00">
                                    <span class="input-group-text bg-light fw-medium">%</span>
                                </div>
                                <small class="text-muted d-block mt-1">Cashback percentage earned by DDC on shopping.</small>
                            </div>

                            <!-- ADC Cashback Percentage -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-slate-700" for="adcInput">
                                    ADC Shopping Cashback <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="adc_cashback_percentage" class="form-control" step="0.01"
                                        min="0" max="100" value="{{ old('adc_cashback_percentage', $adcCashback) }}"
                                        required id="adcInput" placeholder="3.00">
                                    <span class="input-group-text bg-light fw-medium">%</span>
                                </div>
                                <small class="text-muted d-block mt-1">Cashback percentage earned by ADC on shopping.</small>
                            </div>
                        </div>

                        <hr class="my-4 border-slate-100">

                        <!-- Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary px-4 py-2 d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:diskette-bold-duotone" class="fs-18"></iconify-icon>
                                <span>Save Cashback Settings</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Calculation Preview Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:calculator-bold-duotone" class="text-slate-600 fs-20"></iconify-icon>
                    <h6 class="card-title fw-bold text-slate-800 mb-0">Cashback Calculation Example</h6>
                </div>
                <div class="card-body p-4 bg-light bg-opacity-50">
                    <p class="small text-muted mb-3">On a sample shopping cart order of <strong>₹1,000.00</strong>:</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded border border-slate-200">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-bold text-slate-700 small">DDC Cashback</span>
                                    <span class="badge bg-primary-subtle text-primary fw-bold" id="ddcBadge">{{ number_format($ddcCashback, 2) }}%</span>
                                </div>
                                <h4 class="fw-bold text-primary mb-0" id="ddcAmount">₹{{ number_format(1000 * ($ddcCashback / 100), 2) }}</h4>
                                <small class="text-muted fs-12">Credited on ₹1,000 shopping order</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded border border-slate-200">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-bold text-slate-700 small">ADC Cashback</span>
                                    <span class="badge bg-success-subtle text-success fw-bold" id="adcBadge">{{ number_format($adcCashback, 2) }}%</span>
                                </div>
                                <h4 class="fw-bold text-success mb-0" id="adcAmount">₹{{ number_format(1000 * ($adcCashback / 100), 2) }}</h4>
                                <small class="text-muted fs-12">Credited on ₹1,000 shopping order</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ddcInput = document.getElementById('ddcInput');
        const adcInput = document.getElementById('adcInput');
        const ddcBadge = document.getElementById('ddcBadge');
        const adcBadge = document.getElementById('adcBadge');
        const ddcAmount = document.getElementById('ddcAmount');
        const adcAmount = document.getElementById('adcAmount');

        function updatePreview() {
            const ddcVal = parseFloat(ddcInput.value) || 0;
            const adcVal = parseFloat(adcInput.value) || 0;

            ddcBadge.textContent = ddcVal.toFixed(2) + '%';
            adcBadge.textContent = adcVal.toFixed(2) + '%';

            ddcAmount.textContent = '₹' + (1000 * (ddcVal / 100)).toFixed(2);
            adcAmount.textContent = '₹' + (1000 * (adcVal / 100)).toFixed(2);
        }

        ddcInput?.addEventListener('input', updatePreview);
        adcInput?.addEventListener('input', updatePreview);
    });
</script>
@endpush
