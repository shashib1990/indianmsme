@extends('layouts.admin')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-body p-4">
                <div class="position-relative d-inline-block mb-3">
                    <img src="{{ $admin->avatar_url }}" alt="avatar" class="avatar-xl rounded-circle" id="admin-avatar-preview">
                    <!-- Admin profile upload loader overlay -->
                    <div id="admin-avatar-loader" class="position-absolute top-0 start-0 w-100 h-100 rounded-circle bg-dark bg-opacity-50 d-none flex-column align-items-center justify-content-center text-white" style="z-index: 10;">
                        <span class="spinner-border spinner-border-sm mb-1" role="status" aria-hidden="true"></span>
                        <span class="fs-10 fw-bold">Saving...</span>
                    </div>
                </div>
                <h5 class="mb-1">{{ $admin->name }}</h5>
                <p class="text-muted mb-2">{{ $admin->email }}</p>
                @if($admin->is_super_admin)
                <span class="badge bg-danger-subtle text-danger px-3">Super Admin</span>
                @else
                <span class="badge bg-primary-subtle text-primary px-3">Admin</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <iconify-icon icon="solar:pen-bold-duotone" class="me-1 text-primary"></iconify-icon>
                    Edit Profile
                </h5>
            </div>
            <div class="card-body">
                <form id="adminProfileForm" action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $admin->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $admin->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', $admin->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Avatar Photo</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                            <small class="text-muted">Max 2MB. JPG, PNG, GIF allowed.</small>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const profileForm = document.getElementById('adminProfileForm');
        const fileInput = document.querySelector('input[name="avatar"]');
        
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0] && window.showToast) {
                    window.showToast('Profile image selected! Click "Save Changes" below to save.', 'info');
                }
            });
        }

        if (profileForm) {
            profileForm.addEventListener('submit', function() {
                if (!profileForm.checkValidity()) {
                    return;
                }
                const loader = document.getElementById('admin-avatar-loader');
                if (fileInput && fileInput.files && fileInput.files.length > 0 && loader) {
                    loader.classList.remove('d-none');
                    loader.classList.add('d-flex');
                }
            });
        }
    });
</script>
@endpush
@endsection
