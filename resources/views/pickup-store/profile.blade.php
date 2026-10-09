@extends('layouts.pickup_store')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="row">
    <!-- Read-Only Information Side Card -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title fw-bold text-slate-800 mb-0">Store Credentials</h5>
            </div>
            <div class="card-body">
                <div class="text-center py-3 mb-4">
                    <div class="avatar-lg bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 72px; height: 72px;">
                        <iconify-icon icon="solar:shop-bold-duotone" class="fs-40"></iconify-icon>
                    </div>
                    <h5 class="fw-bold text-slate-800 dark:text-white mb-1">{{ $store->pickup_store_name }}</h5>
                    <span class="badge bg-primary-subtle text-primary px-2.5 py-1 fs-12">{{ $store->pickup_store_code }}</span>
                </div>

                <div class="mb-3">
                    <label class="text-muted fs-12 mb-1">Owner</label>
                    <div class="text-slate-800 dark:text-slate-200 fw-semibold">{{ $store->owner?->name }} ({{ $store->owner_type }})</div>
                </div>

                <div class="mb-3">
                    <label class="text-muted fs-12 mb-1">Status</label>
                    <div>
                        @if($store->status === 'Active')
                            <span class="badge bg-success-subtle text-success px-2 py-0.5 fs-11">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger px-2 py-0.5 fs-11">Inactive</span>
                        @endif
                    </div>
                </div>

                <div class="mb-0">
                    <label class="text-muted fs-12 mb-1">Created Date</label>
                    <div class="text-slate-800 dark:text-slate-200 fw-semibold">{{ $store->created_at->format('d M Y, h:i A') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Editable Details Form -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title fw-bold text-slate-800 mb-0">Update Store Profile</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('store.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Store Name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required fw-medium text-slate-700">Pickup Store Name</label>
                            <input type="text" name="pickup_store_name" class="form-control" value="{{ old('pickup_store_name', $store->pickup_store_name) }}" required>
                        </div>

                        <!-- Contact Person -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required fw-medium text-slate-700">Contact Person Name</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $store->contact_person) }}" required>
                        </div>

                        <!-- Mobile -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required fw-medium text-slate-700">Mobile Number</label>
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $store->mobile) }}" required>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required fw-medium text-slate-700">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $store->email) }}" required>
                        </div>

                        <!-- GST Number -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required fw-medium text-slate-700">GST Number</label>
                            <input type="text" name="gst_number" class="form-control text-uppercase" placeholder="15-digit GSTIN" value="{{ old('gst_number', $store->gst_number) }}" maxlength="15" oninput="this.value = this.value.toUpperCase().slice(0, 15)" required>
                        </div>

                        <!-- Pincode -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required fw-medium text-slate-700">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $store->pincode) }}" required>
                        </div>

                        <!-- Address -->
                        <div class="col-12 mb-3">
                            <label class="form-label required fw-medium text-slate-700">Address</label>
                            <textarea name="address" rows="3" class="form-control" required>{{ old('address', $store->address) }}</textarea>
                        </div>

                        <!-- Landmark -->
                        <div class="col-12 mb-3">
                            <label class="form-label fw-medium text-slate-700">Landmark (Optional)</label>
                            <input type="text" name="landmark" class="form-control" value="{{ old('landmark', $store->landmark) }}">
                        </div>

                        <!-- State -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required fw-medium text-slate-700">State</label>
                            <select id="stateSelect" name="state" class="form-select" required>
                                <option value="">— Select State —</option>
                                @foreach($states as $st)
                                    <option value="{{ $st->id }}" {{ old('state', $store->state_id) == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- District -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required fw-medium text-slate-700">District</label>
                            <select id="districtSelect" name="district" class="form-select" required>
                                <option value="">— Select District —</option>
                                @foreach($districts as $dt)
                                    <option value="{{ $dt->id }}" {{ old('district', $store->district_id) == $dt->id ? 'selected' : '' }}>{{ $dt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-theme">
                            <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const stateSelect = document.getElementById('stateSelect');
    const districtSelect = document.getElementById('districtSelect');

    stateSelect.addEventListener('change', function () {
        const stateId = this.value;
        districtSelect.innerHTML = '<option value="">Loading districts...</option>';

        if (!stateId) {
            districtSelect.innerHTML = '<option value="">— Select State First —</option>';
            return;
        }

        fetch(`/api/locations/districts?state_id=${stateId}`)
            .then(res => res.json())
            .then(data => {
                districtSelect.innerHTML = '<option value="">— Select District —</option>';
                data.forEach(d => {
                    const option = document.createElement('option');
                    option.value = d.id;
                    option.textContent = d.name;
                    districtSelect.appendChild(option);
                });
            })
            .catch(err => {
                districtSelect.innerHTML = '<option value="">Error loading districts</option>';
            });
    });
});
</script>
@endpush
