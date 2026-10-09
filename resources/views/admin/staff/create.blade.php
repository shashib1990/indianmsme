@extends('layouts.admin')

@section('title', 'Add Staff Member')
@section('page-title', 'Add Staff Member')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10">
        <form action="{{ route('admin.staff.store') }}" method="POST" id="staffCreateForm">
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

            {{-- ── Section 3: Permissions ─────────────────────────────────────── --}}
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-0">
                            <iconify-icon icon="solar:shield-check-bold-duotone" class="me-2 text-success"></iconify-icon>
                            Module Permissions
                        </h5>
                        <p class="text-muted small mb-0">Select which modules and actions this staff member can access.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" id="selectAllBtn">
                            <i class="bx bx-check-double me-1"></i> Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllBtn">
                            <i class="bx bx-x me-1"></i> Clear All
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @error('permissions')
                        <div class="alert alert-danger py-2 mb-3">{{ $message }}</div>
                    @enderror

                    @if($permissions->isNotEmpty())
                        @foreach($permissions as $module => $perms)
                        <div class="border rounded mb-3 overflow-hidden">
                            <div class="bg-light px-3 py-2 d-flex align-items-center gap-2 border-bottom">
                                <input class="form-check-input module-check mt-0 flex-shrink-0"
                                       type="checkbox"
                                       id="mod_{{ Str::slug($module) }}"
                                       data-module="{{ Str::slug($module) }}">
                                <label class="form-check-label fw-semibold text-uppercase small mb-0 flex-grow-1 cursor-pointer"
                                       for="mod_{{ Str::slug($module) }}">
                                    <iconify-icon icon="solar:folder-bold-duotone" class="me-1 text-primary"></iconify-icon>
                                    {{ str_replace('_', ' ', $module) }}
                                </label>
                                <span class="badge bg-primary-subtle text-primary">{{ $perms->count() }} actions</span>
                            </div>
                            <div class="px-3 py-2">
                                <div class="row g-2">
                                    @foreach($perms as $perm)
                                    <div class="col-xl-3 col-md-4 col-sm-6">
                                        <div class="form-check d-flex align-items-start gap-2">
                                            <input class="form-check-input perm-check-{{ Str::slug($module) }} mt-1 flex-shrink-0"
                                                   type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $perm->id }}"
                                                   id="perm_{{ $perm->id }}"
                                                   {{ is_array(old('permissions')) && in_array($perm->id, old('permissions')) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="perm_{{ $perm->id }}">
                                                {{ $perm->name }}
                                                <span class="badge ms-1 {{ $perm->type === 'write' ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info' }}">
                                                    {{ $perm->type }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <iconify-icon icon="solar:shield-warning-bold-duotone" class="fs-40 mb-2 d-block"></iconify-icon>
                            No permissions found. <a href="{{ route('admin.permissions.index') }}">Create permissions first</a>.
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── Actions ────────────────────────────────────────────────────── --}}
            <div class="d-flex gap-2 mb-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bx bx-check-double me-1"></i> Create Staff Member
                </button>
                <a href="{{ route('admin.staff.index') }}" class="btn btn-light px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Module-level checkbox toggles all children
    document.querySelectorAll('.module-check').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const module = this.dataset.module;
            document.querySelectorAll('.perm-check-' + module).forEach(function(c) {
                c.checked = checkbox.checked;
            });
        });
    });

    // Update module checkbox state when individual perms change
    document.querySelectorAll('[class*="perm-check-"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const classList = Array.from(this.classList);
            const permClass = classList.find(c => c.startsWith('perm-check-'));
            if (!permClass) return;
            const module = permClass.replace('perm-check-', '');
            const allPerms = document.querySelectorAll('.perm-check-' + module);
            const checkedPerms = document.querySelectorAll('.perm-check-' + module + ':checked');
            const moduleCheck = document.querySelector('#mod_' + module);
            if (moduleCheck) {
                moduleCheck.checked = checkedPerms.length === allPerms.length;
                moduleCheck.indeterminate = checkedPerms.length > 0 && checkedPerms.length < allPerms.length;
            }
        });
    });

    // Select All button
    document.getElementById('selectAllBtn')?.addEventListener('click', function() {
        document.querySelectorAll('input[name="permissions[]"]').forEach(c => c.checked = true);
        document.querySelectorAll('.module-check').forEach(c => { c.checked = true; c.indeterminate = false; });
    });

    // Clear All button
    document.getElementById('clearAllBtn')?.addEventListener('click', function() {
        document.querySelectorAll('input[name="permissions[]"]').forEach(c => c.checked = false);
        document.querySelectorAll('.module-check').forEach(c => { c.checked = false; c.indeterminate = false; });
    });

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
