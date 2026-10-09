@extends('layouts.admin')

@section('title', 'Newsletter Subscribers')
@section('page-title', 'Newsletter Subscribers')

@section('content')
<!-- Common Page Action Header -->
<x-admin.page-header title="Newsletter Subscribers" subtitle="View and manage email subscriptions for newsletters."
    :actions="[
        [
            'label' => 'Export CSV',
            'icon' => 'solar:document-text-bold-duotone',
            'onclick' => 'triggerExport(\'csv\')',
            'permission' => 'users.view',
            'btnClass' => 'btn-outline-success'
        ]
    ]" />

<div class="row mt-3">
    <div class="col-12">
        <!-- Reusable Table Filters -->
        <x-admin.table-filters action="{{ route('admin.newsletter-subscribers.index') }}" :search="$search"
            placeholder="Search email..." :limit="$limit" :sort="$sort" :order="$order"
            :bulk-actions="['delete' => 'Delete Selected']" />

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                        <thead class="bg-light bg-opacity-50 border-top border-bottom">
                            <tr>
                                <th class="ps-3" style="width: 40px;">
                                    <div class="form-check fs-15">
                                        <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                                    </div>
                                </th>
                                <th class="ps-2" style="width: 60px;">#</th>
                                <x-admin.table-header field="email" :sort="$sort" :order="$order">Email
                                    Address</x-admin.table-header>
                                <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Subscribed
                                    At</x-admin.table-header>
                                <th class="text-end pe-3" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscribers as $subscriber)
                            <tr>
                                <td class="ps-3">
                                    <div class="form-check fs-15">
                                        <input type="checkbox" class="form-check-input row-checkbox"
                                            value="{{ $subscriber->id }}">
                                    </div>
                                </td>
                                <td class="ps-2 text-muted small">
                                    {{ $loop->iteration + (($subscribers->currentPage() - 1) * $subscribers->perPage()) }}
                                </td>
                                <td class="fw-semibold text-slate-800">
                                    <a href="mailto:{{ $subscriber->email }}">{{ $subscriber->email }}</a>
                                </td>
                                <td class="text-muted small">{{ $subscriber->created_at->format('d M Y h:i A') }}</td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        @permission('users.manage')
                                        <form
                                            action="{{ route('admin.newsletter-subscribers.destroy', $subscriber->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger confirm-action d-flex align-items-center justify-content-center p-1"
                                                data-confirm-message="Are you sure you want to delete this subscriber?"
                                                style="width: 28px; height: 28px;" title="Delete">
                                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone"
                                                    class="fs-14"></iconify-icon>
                                            </button>
                                        </form>
                                        @endpermission
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <x-admin.empty-state icon="solar:globus-bold-duotone" title="No Subscribers Found"
                                description="There are no newsletter subscribers registered in the system yet."
                                colspan="5" />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($subscribers->hasPages())
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                        <small class="text-muted">Showing {{ $subscribers->firstItem() }}–{{ $subscribers->lastItem() }} of
                            {{ $subscribers->total() }}</small>
                        {{ $subscribers->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection