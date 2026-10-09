@extends('layouts.admin')

@section('title', 'Change Password')
@section('page-title', 'Change Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        @if(auth('admin')->user()->force_change_password)
        <div class="alert alert-warning">
            <i class="bx bx-info-circle me-1"></i>
            <strong>Action Required:</strong> Your password has been reset by an administrator. Please set a new password to continue.
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <iconify-icon icon="solar:lock-password-bold-duotone" class="me-1 text-warning"></iconify-icon>
                    Change Password
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.profile.change-password.post') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" name="current_password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               placeholder="Enter current password" required>
                        @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Min 8 chars, mixed case & numbers" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation"
                               class="form-control" placeholder="Repeat new password" required>
                    </div>

                    <div class="alert alert-info border-0 p-3">
                        <small>
                            <strong>Password requirements:</strong><br>
                            • Minimum 8 characters<br>
                            • At least one uppercase & one lowercase letter<br>
                            • At least one number
                        </small>
                    </div>

                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bx bx-lock me-1"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
