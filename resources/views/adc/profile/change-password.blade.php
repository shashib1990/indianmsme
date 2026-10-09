@extends('layouts.adc')
@section('title', 'Change Password')
@section('page-title', 'Change Password')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Change Account Password
                            </h4>
                            <p class="text-muted small mb-0">Update your ADC portal access password to secure your canteen account.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="row">
    <div class="col-xl-5 col-lg-6 col-md-8">
        
        @if(auth('adc')->user()->force_change_password)
            <div class="alert alert-warning border-0 shadow-sm mb-3">
                <i class="bx bx-info-circle me-1"></i><strong>Action Required:</strong> Your password was reset by admin. Please update your password now to continue.
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3 alert-dismissible fade show">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-3 alert-dismissible fade show">
                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-3 alert-dismissible fade show">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:lock-password-bold-duotone" class="text-primary fs-20"></iconify-icon>
                    Change Account Password
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('adc.profile.change-password.post') }}" method="POST">
                    @csrf
                    
                    <!-- Current Password -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark fs-13">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Enter current password" required>
                            <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" type="button" onclick="togglePasswordVisibility('current_password', 'icon_current')">
                                <iconify-icon icon="solar:eye-bold-duotone" class="fs-18 text-muted" id="icon_current"></iconify-icon>
                            </button>
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- New Password -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark fs-13">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter new password (min. 8 characters)" required>
                            <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" type="button" onclick="togglePasswordVisibility('password', 'icon_new')">
                                <iconify-icon icon="solar:eye-bold-duotone" class="fs-18 text-muted" id="icon_new"></iconify-icon>
                            </button>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark fs-13">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Re-enter new password" required>
                            <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" type="button" onclick="togglePasswordVisibility('password_confirmation', 'icon_confirm')">
                                <iconify-icon icon="solar:eye-bold-duotone" class="fs-18 text-muted" id="icon_confirm"></iconify-icon>
                            </button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-primary px-4 py-2">
                            <iconify-icon icon="solar:shield-keyhole-bold-duotone" class="me-1 fs-18 align-middle"></iconify-icon> Update Password
                        </button>
                        <a href="{{ route('adc.profile') }}" class="btn btn-light px-3 py-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('icon', 'solar:eye-closed-bold-duotone');
    } else {
        input.type = 'password';
        icon.setAttribute('icon', 'solar:eye-bold-duotone');
    }
}
</script>
@endpush
