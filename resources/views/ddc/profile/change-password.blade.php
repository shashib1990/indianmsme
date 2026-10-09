@extends('layouts.ddc')
@section('title', 'Change Password')
@section('page-title', 'Change Password')
@section('content')
<div class="row justify-content-center"><div class="col-lg-5">
@if(auth('ddc')->user()->force_change_password)
<div class="alert alert-warning"><i class="bx bx-info-circle me-1"></i><strong>Action Required:</strong> Your password was reset by admin. Please change it now.</div>
@endif
<div class="card">
<div class="card-header"><h5 class="card-title mb-0"><iconify-icon icon="solar:lock-password-bold-duotone" class="me-1 text-warning"></iconify-icon> Change Password</h5></div>
<div class="card-body">
<form action="{{ route('ddc.profile.change-password.post') }}" method="POST">
@csrf
<div class="mb-3">
<label class="form-label">Current Password *</label>
<input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
@error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
<label class="form-label">New Password *</label>
<input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
@error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-4">
<label class="form-label">Confirm Password *</label>
<input type="password" name="password_confirmation" class="form-control" required>
</div>
<button type="submit" class="btn btn-warning w-100"><i class="bx bx-lock me-1"></i> Update Password</button>
</form>
</div></div>
</div></div>
@endsection
