@extends('layouts.admin')

@section('title', 'Contact Inquiries')
@section('page-title', 'Contact Inquiries')

@section('content')
<!-- Common Page Action Header -->
<x-admin.page-header title="Contact Inquiries"
    subtitle="View and manage messages submitted by users through the Contact Us form." :actions="[
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
        <x-admin.table-filters action="{{ route('admin.contact-inquiries.index') }}" :search="$search"
            placeholder="Search name, email, subject..." :limit="$limit" :sort="$sort" :order="$order"
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
                                <x-admin.table-header field="first_name" :sort="$sort"
                                    :order="$order">Name</x-admin.table-header>
                                <x-admin.table-header field="email" :sort="$sort"
                                    :order="$order">Email</x-admin.table-header>
                                <x-admin.table-header field="phone" :sort="$sort"
                                    :order="$order">Phone</x-admin.table-header>
                                <x-admin.table-header field="subject" :sort="$sort"
                                    :order="$order">Subject</x-admin.table-header>
                                <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Submitted
                                    At</x-admin.table-header>
                                <th class="text-end pe-3" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inquiries as $inquiry)
                            <tr>
                                <td class="ps-3">
                                    <div class="form-check fs-15">
                                        <input type="checkbox" class="form-check-input row-checkbox"
                                            value="{{ $inquiry->id }}">
                                    </div>
                                </td>
                                <td class="ps-2 text-muted small">
                                    {{ $loop->iteration + (($inquiries->currentPage() - 1) * $inquiries->perPage()) }}
                                </td>
                                <td class="fw-semibold text-slate-800">{{ $inquiry->first_name }}
                                    {{ $inquiry->last_name }}
                                </td>
                                <td>{{ $inquiry->email }}</td>
                                <td>{{ $inquiry->phone ?? '—' }}</td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;">
                                        {{ $inquiry->subject }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $inquiry->created_at->format('d M Y h:i A') }}</td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <button
                                            class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1"
                                            data-bs-toggle="modal" data-bs-target="#viewModal"
                                            data-name="{{ $inquiry->first_name }} {{ $inquiry->last_name }}"
                                            data-email="{{ $inquiry->email }}" data-phone="{{ $inquiry->phone ?? '—' }}"
                                            data-subject="{{ $inquiry->subject }}"
                                            data-message="{{ $inquiry->message }}"
                                            data-date="{{ $inquiry->created_at->format('d M Y h:i A') }}"
                                            style="width: 28px; height: 28px;" title="View Message">
                                            <iconify-icon icon="solar:eye-bold-duotone" class="fs-14"></iconify-icon>
                                        </button>

                                        @permission('users.manage')
                                        <form action="{{ route('admin.contact-inquiries.destroy', $inquiry->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger confirm-action d-flex align-items-center justify-content-center p-1"
                                                data-confirm-message="Are you sure you want to delete this inquiry?"
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
                            <x-admin.empty-state icon="solar:letter-bold-duotone" title="No Inquiries Found"
                                description="There are no contact inquiries registered in the system yet."
                                colspan="8" />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($inquiries->hasPages())
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                        <small class="text-muted">Showing {{ $inquiries->firstItem() }}–{{ $inquiries->lastItem() }} of
                            {{ $inquiries->total() }}</small>
                        {{ $inquiries->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- View Details Modal --}}
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold text-slate-800">Inquiry Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium mb-1">From</label>
                    <div class="fw-semibold text-slate-800" id="viewName"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium mb-1">Email</label>
                        <div id="viewEmail"></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small fw-medium mb-1">Phone</label>
                        <div id="viewPhone"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium mb-1">Subject</label>
                    <div class="fw-semibold text-slate-800" id="viewSubject"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-medium mb-1">Message</label>
                    <div class="p-3 bg-light rounded text-slate-800 border" style="white-space: pre-wrap;"
                        id="viewMessage"></div>
                </div>
                <div class="mb-0">
                    <label class="form-label text-muted small fw-medium mb-1">Submitted On</label>
                    <div class="text-muted" id="viewDate"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const viewModal = document.getElementById('viewModal');
            if (viewModal) {
                viewModal.addEventListener('show.bs.modal', function (e) {
                    const btn = e.relatedTarget;
                    document.getElementById('viewName').textContent = btn.dataset.name;
                    document.getElementById('viewEmail').innerHTML = `<a href="mailto:${btn.dataset.email}">${btn.dataset.email}</a>`;
                    document.getElementById('viewPhone').innerHTML = `<a href="tel:${btn.dataset.phone}">${btn.dataset.phone}</a>`;
                    document.getElementById('viewSubject').textContent = btn.dataset.subject;
                    document.getElementById('viewMessage').textContent = btn.dataset.message;
                    document.getElementById('viewDate').textContent = btn.dataset.date;
                });
            }
        });
    </script>
@endpush