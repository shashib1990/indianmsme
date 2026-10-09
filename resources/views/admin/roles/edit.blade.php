@extends('layouts.admin')
@section('title', 'Edit Role')
@section('page-title', 'Edit Role')
@section('content')
<div class="row"><div class="col-lg-8">
<div class="card">
<div class="card-header"><h5 class="card-title mb-0">Edit Role: {{ $role->name }}</h5></div>
<div class="card-body">
<form action="{{ route('admin.roles.update', $role) }}" method="POST">
@csrf @method('PUT')
<div class="row g-3">
<div class="col-md-6">
<label class="form-label">Role Name *</label>
<input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required>
@error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="col-md-6">
<label class="form-label">Department *</label>
<select name="department_id" class="form-select" required>
<option value="">Select Department</option>
@foreach($departments as $dept)
<option value="{{ $dept->id }}" {{ old('department_id', $role->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
@endforeach
</select>
</div>
<div class="col-12">
<label class="form-label">Description</label>
<textarea name="description" class="form-control" rows="2">{{ old('description', $role->description) }}</textarea>
</div>
</div>
@if($permissions->isNotEmpty())
<div class="mt-4"><h6 class="fw-semibold mb-3">Assign Permissions</h6>
@foreach($permissions as $module => $perms)
<div class="card border mb-2">
<div class="card-header py-2 bg-light">
<div class="form-check">
<input class="form-check-input module-check" type="checkbox" id="module_{{ Str::slug($module) }}" data-module="{{ Str::slug($module) }}">
<label class="form-check-label fw-semibold" for="module_{{ Str::slug($module) }}">{{ $module }}</label>
</div></div>
<div class="card-body py-2"><div class="row g-2">
@foreach($perms as $perm)
<div class="col-md-4"><div class="form-check">
<input class="form-check-input perm-check-{{ Str::slug($module) }}" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm_{{ $perm->id }}" {{ in_array($perm->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
<label class="form-check-label small" for="perm_{{ $perm->id }}">{{ $perm->name }} <span class="badge bg-{{ $perm->type === 'write' ? 'warning' : 'info' }}-subtle text-{{ $perm->type === 'write' ? 'warning' : 'info' }} ms-1">{{ $perm->type }}</span></label>
</div></div>
@endforeach
</div></div></div>
@endforeach
</div>
@endif
<div class="mt-4 d-flex gap-2">
<button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Update Role</button>
<a href="{{ route('admin.roles.index') }}" class="btn btn-light">Cancel</a>
</div>
</form>
</div></div>
</div></div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.module-check').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        const module = this.dataset.module;
        document.querySelectorAll('.perm-check-' + module).forEach(function(c) { c.checked = checkbox.checked; });
    });
});
</script>
@endpush
