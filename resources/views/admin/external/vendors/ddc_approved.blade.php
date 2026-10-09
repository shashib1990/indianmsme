@extends('layouts.admin')

@section('title', 'DDC Subscriptions Approved')
@section('page-title', 'DDC Subscriptions Approved')

@section('content')
    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-3">
            {{ session('error') }}
        </div>
    @endif

    <!-- Common Page Action Header -->
    <x-admin.page-header 
        title="DDC Subscriptions Approved" 
        subtitle="List of all vendors with active approved DDC model subscriptions. You can cancel active subscriptions here."
        :actions="[
            [
                'label' => 'All Vendors',
                'icon' => 'solar:shop-bold-duotone',
                'url' => route('admin.external.vendors.index'),
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-secondary'
            ],
            [
                'label' => 'DDC Subscription Requests',
                'icon' => 'solar:user-id-bold-duotone',
                'url' => route('admin.external.vendors.ddc-requests'),
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-warning'
            ]
        ]"
    />

    <!-- Reusable Table Filters -->
    <x-admin.table-filters 
        action="{{ route('admin.external.vendors.ddc-approved') }}" 
        :search="$search" 
        placeholder="Search name, email, company, UTR..." 
        :limit="$limit" 
        :sort="$sort" 
        :order="$order"
    />

    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                            <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                <tr>
                                    <th class="ps-3" style="width: 60px;">#</th>
                                    <x-admin.table-header field="name" :sort="$sort" :order="$order">Vendor / Company</x-admin.table-header>
                                    <th>Seller Code</th>
                                    <th>UTR Number</th>
                                    <th>Payment Slip</th>
                                    <th class="text-end pe-3" style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    @php
                                        $profile = $user->profile;
                                    @endphp
                                    <tr>
                                        <td class="ps-3 text-muted small">{{ $loop->iteration + (($users->currentPage() - 1) * $users->perPage()) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $user->avatar_url }}" alt="avatar" class="rounded-circle border border-light-subtle shadow-sm" width="34" height="34">
                                                <div>
                                                    <span class="fw-semibold text-slate-800 d-block">{{ $profile->company_name ?? ($user->company_name ?? '—') }}</span>
                                                    <span class="text-muted small d-block">({{ $user->name }})</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($user->seller_id)
                                                <code class="small text-primary fw-bold">{{ $user->seller_id }}</code>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($profile && $profile->ddc_subscription_utr)
                                                <code class="small text-dark fw-semibold">{{ $profile->ddc_subscription_utr }}</code>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($profile && $profile->ddc_subscription_slip)
                                                <a href="{{ $profile->ddc_subscription_slip }}" target="_blank" class="btn btn-xs btn-outline-info py-1 px-2 d-inline-flex align-items-center gap-1">
                                                    <iconify-icon icon="solar:document-text-bold-duotone" class="fs-12"></iconify-icon>
                                                    View Slip
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                <form action="{{ route('admin.external.vendors.ddc-cancel', $user->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger confirm-action d-flex align-items-center gap-1"
                                                        data-confirm-message="Cancel DDC subscription for {{ $user->name }}? This will revoke DDC privileges.">
                                                        <iconify-icon icon="solar:close-circle-bold-duotone" class="fs-14"></iconify-icon>
                                                        Cancel Subscription
                                                    </button>
                                                </form>

                                                <a href="{{ route('admin.external.vendors.profile', $user->id) }}" 
                                                    class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center p-1"
                                                    title="View Profile"
                                                    style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:eye-bold-duotone" class="fs-14"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <x-admin.empty-state 
                                        icon="solar:user-id-bold-duotone" 
                                        title="No Approved DDC Subscriptions" 
                                        description="No vendors are currently subscribed to the DDC model." 
                                        colspan="10" 
                                    />
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages())
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}</small>
                            {{ $users->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
