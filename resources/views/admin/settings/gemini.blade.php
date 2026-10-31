@extends('layouts.admin')

@section('title', 'Google Gemini AI Settings')
@section('page-title', 'Google Gemini AI Settings')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">

        {{-- Top Notification Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-3" role="alert">
                <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-20 text-success"></iconify-icon>
                <div class="flex-grow-1 fw-medium">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-3" role="alert">
                <iconify-icon icon="solar:danger-triangle-bold-duotone" class="fs-20 text-danger"></iconify-icon>
                <div class="flex-grow-1 fw-medium">{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Main Configuration Card --}}
        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-3 bg-primary-subtle text-primary d-inline-flex">
                        <iconify-icon icon="solar:magic-stick-3-bold-duotone" class="fs-22"></iconify-icon>
                    </span>
                    <div>
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Google Gemini AI Configuration</h5>
                        <p class="text-muted small mb-0">Manage Google Gemini API credentials and model parameters</p>
                    </div>
                </div>

                <div>
                    @if($isConfigured)
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill fs-12 fw-semibold d-inline-flex align-items-center gap-1.5">
                            <span class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;" role="status"></span>
                            API Key Configured
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1.5 rounded-pill fs-12 fw-semibold d-inline-flex align-items-center gap-1.5">
                            <iconify-icon icon="solar:info-circle-bold-duotone" class="fs-14"></iconify-icon>
                            API Key Not Configured
                        </span>
                    @endif
                </div>
            </div>

            <div class="card-body p-4">
                {{-- Informative Card --}}
                <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis d-flex align-items-start gap-2.5 mb-4 p-3 rounded-3">
                    <iconify-icon icon="solar:info-circle-bold-duotone" class="fs-22 text-info mt-0.5 flex-shrink-0"></iconify-icon>
                    <div class="small">
                        <div class="fw-bold mb-1">What does Google Gemini AI power?</div>
                        <p class="mb-0">
                            Gemini AI powers the <strong>Smart Invoice Scanner</strong> in the ADC &amp; Depot modules, automatically parsing uploaded invoice images and PDFs (Vendor names, line items, quantities, and GST rates) into inventory in seconds.
                        </p>
                    </div>
                </div>

                {{-- Live Connection Test Result Alert --}}
                <div id="testResultBox" class="alert d-none border-0 shadow-sm p-3 rounded-3 mb-4 align-items-center gap-2" role="alert">
                    <iconify-icon id="testResultIcon" class="fs-22 flex-shrink-0"></iconify-icon>
                    <div class="small flex-grow-1" id="testResultMessage"></div>
                </div>

                <form action="{{ route('admin.settings.gemini.update') }}" method="POST" id="geminiSettingsForm">
                    @csrf

                    {{-- 1. API Key --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-1.5 flex-wrap gap-2">
                            <label for="gemini_api_key" class="form-label fw-bold text-slate-700 mb-0">
                                Google Gemini API Key <span class="text-danger">*</span>
                            </label>
                            <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" class="text-primary small text-decoration-none d-inline-flex align-items-center gap-1 fw-semibold">
                                <span>Get API Key from Google AI Studio</span>
                                <iconify-icon icon="solar:square-top-down-bold-duotone" class="fs-14"></iconify-icon>
                            </a>
                        </div>

                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <iconify-icon icon="solar:key-square-bold-duotone" class="text-muted fs-18"></iconify-icon>
                            </span>
                            <input type="password" 
                                   name="gemini_api_key" 
                                   id="gemini_api_key" 
                                   class="form-control border-start-0 border-end-0 @error('gemini_api_key') is-invalid @enderror" 
                                   placeholder="AIzaSy..." 
                                   value="{{ old('gemini_api_key', $apiKey) }}" 
                                   required 
                                   autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary" id="toggleApiKeyBtn" title="Show / Hide Key">
                                <iconify-icon icon="solar:eye-bold-duotone" id="toggleApiKeyIcon" class="fs-16"></iconify-icon>
                            </button>
                            <button type="button" class="btn btn-outline-primary fw-medium d-flex align-items-center gap-1.5 px-3" id="btnTestConnection">
                                <span class="spinner-border spinner-border-sm d-none" id="testSpinner" role="status" aria-hidden="true"></span>
                                <iconify-icon icon="solar:bolt-bold-duotone" class="fs-16" id="testBoltIcon"></iconify-icon>
                                <span>Test Connection</span>
                            </button>
                        </div>
                        @error('gemini_api_key')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">
                            Your API key is stored securely in the system settings and encrypted database configuration.
                        </small>
                    </div>

                    {{-- 2. Gemini Model Selection --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <label for="gemini_model_select" class="form-label fw-bold text-slate-700">
                                AI Model <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('gemini_model') is-invalid @enderror" id="gemini_model_select">
                                @foreach($availableModels as $modelKey => $modelLabel)
                                    <option value="{{ $modelKey }}" {{ old('gemini_model', $model) === $modelKey ? 'selected' : '' }}>
                                        {{ $modelLabel }}
                                    </option>
                                @endforeach
                                <option value="custom" {{ !array_key_exists(old('gemini_model', $model), $availableModels) ? 'selected' : '' }}>
                                    Custom Model Name...
                                </option>
                            </select>
                            <input type="text" 
                                   name="gemini_model" 
                                   id="gemini_model_input" 
                                   class="form-control mt-2 {{ !array_key_exists(old('gemini_model', $model), $availableModels) ? '' : 'd-none' }}" 
                                   placeholder="e.g. gemini-2.5-flash" 
                                   value="{{ old('gemini_model', $model) }}" 
                                   required>
                            @error('gemini_model')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">Recommended: <code>gemini-3.5-flash</code> or <code>gemini-3.5-flash-lite</code> for high accuracy OCR and fast response times.</small>
                        </div>

                        {{-- 3. Request Timeout --}}
                        <div class="col-md-5">
                            <label for="gemini_timeout" class="form-label fw-bold text-slate-700">
                                Request Timeout <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       name="gemini_timeout" 
                                       id="gemini_timeout" 
                                       class="form-control @error('gemini_timeout') is-invalid @enderror" 
                                       min="10" 
                                       max="300" 
                                       step="1" 
                                       value="{{ old('gemini_timeout', $timeout) }}" 
                                       required>
                                <span class="input-group-text bg-light fw-medium">Seconds</span>
                            </div>
                            @error('gemini_timeout')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">Recommended: 60s for multi-page PDF processing.</small>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-4 flex-wrap gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-light px-3 fw-medium">
                            <iconify-icon icon="solar:arrow-left-linear" class="align-middle me-1"></iconify-icon>
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary px-4 fw-bold d-flex align-items-center gap-1.5 shadow-sm">
                            <iconify-icon icon="solar:check-read-bold-duotone" class="fs-18"></iconify-icon>
                            <span>Save Gemini Settings</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Guide Card --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h6 class="fw-bold text-slate-800 d-flex align-items-center gap-2 mb-3">
                    <iconify-icon icon="solar:shield-check-bold-duotone" class="text-success fs-20"></iconify-icon>
                    How to get a Google Gemini API Key
                </h6>
                <ol class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                    <li>Visit the official <a href="https://aistudio.google.com/app/apikey" target="_blank" class="fw-semibold text-primary">Google AI Studio API Keys page</a>.</li>
                    <li>Sign in with your Google Workspace or standard Google account.</li>
                    <li>Click <strong>"Create API key"</strong>, select or create a project, and copy the generated key.</li>
                    <li>Paste the key into the <strong>Google Gemini API Key</strong> field above and click <strong>"Test Connection"</strong> to verify.</li>
                    <li>Click <strong>"Save Gemini Settings"</strong> to apply the changes across the entire platform.</li>
                </ol>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const apiKeyInput = document.getElementById('gemini_api_key');
    const toggleBtn = document.getElementById('toggleApiKeyBtn');
    const toggleIcon = document.getElementById('toggleApiKeyIcon');
    const modelSelect = document.getElementById('gemini_model_select');
    const modelInput = document.getElementById('gemini_model_input');
    const testBtn = document.getElementById('btnTestConnection');
    const testSpinner = document.getElementById('testSpinner');
    const testBoltIcon = document.getElementById('testBoltIcon');
    const testBox = document.getElementById('testResultBox');
    const testIcon = document.getElementById('testResultIcon');
    const testMsg = document.getElementById('testResultMessage');

    // Toggle Show / Hide API Key
    if (toggleBtn && apiKeyInput) {
        toggleBtn.addEventListener('click', function () {
            if (apiKeyInput.type === 'password') {
                apiKeyInput.type = 'text';
                toggleIcon.setAttribute('icon', 'solar:eye-closed-bold-duotone');
            } else {
                apiKeyInput.type = 'password';
                toggleIcon.setAttribute('icon', 'solar:eye-bold-duotone');
            }
        });
    }

    // Model dropdown vs custom input handler
    if (modelSelect && modelInput) {
        modelSelect.addEventListener('change', function () {
            if (this.value === 'custom') {
                modelInput.classList.remove('d-none');
                modelInput.focus();
            } else {
                modelInput.classList.add('d-none');
                modelInput.value = this.value;
            }
        });
    }

    // Live Connection Test
    if (testBtn) {
        testBtn.addEventListener('click', function () {
            const key = apiKeyInput.value.trim();
            const model = (modelSelect.value === 'custom') ? modelInput.value.trim() : modelSelect.value;

            if (!key) {
                alert('Please enter a Gemini API Key first.');
                apiKeyInput.focus();
                return;
            }

            testBtn.disabled = true;
            testSpinner.classList.remove('d-none');
            testBoltIcon.classList.add('d-none');
            testBox.classList.add('d-none');

            fetch("{{ route('admin.settings.gemini.test') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    gemini_api_key: key,
                    gemini_model: model
                })
            })
            .then(res => res.json())
            .then(data => {
                testBox.classList.remove('d-none', 'alert-success', 'alert-danger');
                testIcon.className = 'fs-22 flex-shrink-0';

                if (data.success) {
                    testBox.classList.add('alert-success');
                    testIcon.setAttribute('icon', 'solar:check-circle-bold-duotone');
                    testIcon.classList.add('text-success');
                    testMsg.innerHTML = '<strong>Success!</strong> ' + data.message;
                } else {
                    testBox.classList.add('alert-danger');
                    testIcon.setAttribute('icon', 'solar:danger-triangle-bold-duotone');
                    testIcon.classList.add('text-danger');
                    testMsg.innerHTML = '<strong>Connection Failed:</strong> ' + data.message;
                }
            })
            .catch(err => {
                testBox.classList.remove('d-none', 'alert-success');
                testBox.classList.add('alert-danger');
                testIcon.setAttribute('icon', 'solar:danger-triangle-bold-duotone');
                testIcon.classList.add('text-danger');
                testMsg.innerHTML = '<strong>Network Error:</strong> Failed to reach test endpoint (' + err.message + ')';
            })
            .finally(() => {
                testBtn.disabled = false;
                testSpinner.classList.add('d-none');
                testBoltIcon.classList.remove('d-none');
            });
        });
    }
});
</script>
@endpush
