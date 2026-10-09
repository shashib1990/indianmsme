@extends('layouts.ddc')

@section('title', 'Add Staff Member')
@section('page-title', 'Add Staff Member')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10">
        <form action="{{ route('ddc.staff.store') }}" method="POST" id="staffCreateForm">
            @csrf

            {{-- ── Section 1: Basic Info ──────────────────────────────────────── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <iconify-icon icon="solar:user-plus-rounded-bold-duotone" class="me-2 text-primary"></iconify-icon>
                        Staff Information
                    </h5>
                    <p class="text-muted small mb-0">Enter the staff member's basic details and login credentials.</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="e.g. Ram Sharma"
                                   value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                            <select name="department_id"
                                    class="form-select @error('department_id') is-invalid @enderror" required>
                                <option value="">— Select Department —</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description / Role Note</label>
                            <textarea name="description" rows="2"
                                      class="form-control"
                                      placeholder="Brief note about this staff member's responsibilities">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Section 2: Login Credentials ──────────────────────────────── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <iconify-icon icon="solar:lock-password-bold-duotone" class="me-2 text-warning"></iconify-icon>
                        Login Credentials
                    </h5>
                    <p class="text-muted small mb-0">These will be used by the staff member to log into the admin panel.</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="e.g. ram@example.com"
                                   value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="passwordField"
                                       class="form-control @error('password') is-invalid @enderror"
                                       autocomplete="new-password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                                    <i class="bx bx-hide" id="togglePwdIcon"></i>
                                </button>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation"
                                   class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone"
                                   class="form-control"
                                   placeholder="e.g. +91 9876543210"
                                   value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Actions ────────────────────────────────────────────────────── --}}
            <div class="d-flex gap-2 mb-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bx bx-check-double me-1"></i> Create Staff Member
                </button>
                <a href="{{ route('ddc.staff.index') }}" class="btn btn-light px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle
    document.getElementById('togglePwd')?.addEventListener('click', function() {
        const field = document.getElementById('passwordField');
        const icon = document.getElementById('togglePwdIcon');
        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'bx bx-show';
        } else {
            field.type = 'password';
            icon.className = 'bx bx-hide';
        }
    });
});
</script>
@endpush
