@extends('layouts.admin')

@section('title', 'FAQ Topics Management')
@section('page-title', 'FAQ Topics Management')

@section('content')
    <x-admin.page-header 
        title="FAQ Topics" 
        subtitle="Manage categories/topics for organizing website FAQs."
        :actions="[
            [
                'label' => 'Add Topic',
                'icon' => 'solar:add-circle-bold-duotone',
                'target' => '#createTopicModal',
                'btnClass' => 'btn-primary'
            ]
        ]"
    />

    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">Sort</th>
                            <th>Topic Name</th>
                            <th>Slug</th>
                            <th>Icon Class</th>
                            <th class="text-center">Total FAQs</th>
                            <th class="text-center">Status</th>
                            <th class="text-end" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topics as $topic)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $topic->sort_order }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark flex items-center gap-2">
                                        @if($topic->icon)
                                            <i class="hgi hgi-stroke {{ $topic->icon }} text-emerald-600 fs-5"></i>
                                        @endif
                                        <span>{{ $topic->name }}</span>
                                    </div>
                                </td>
                                <td><code>{{ $topic->slug }}</code></td>
                                <td>
                                    <span class="badge bg-light text-dark font-monospace">{{ $topic->icon ?: 'None' }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.faqs.index', ['topic_id' => $topic->id]) }}" class="badge bg-info-subtle text-info fw-bold">
                                        {{ $topic->faqs_count }} FAQs
                                    </a>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input topic-status-toggle" type="checkbox" 
                                               data-id="{{ $topic->id }}" {{ $topic->is_active ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary me-1 edit-topic-btn" 
                                            data-topic='@json($topic)' data-bs-toggle="modal" data-bs-target="#editTopicModal">
                                        <i class="bx bx-edit-alt"></i> Edit
                                    </button>
                                    <form action="{{ route('admin.faq-topics.destroy', $topic->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this topic and all its FAQs?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No FAQ topics found. Click "Add Topic" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Topic Modal -->
    <div class="modal fade" id="createTopicModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.faq-topics.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add FAQ Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Topic Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. About IndianMSME" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Slug (Optional)</label>
                        <input type="text" name="slug" class="form-control" placeholder="e.g. about">
                        <div class="form-text">Leave blank to auto-generate from name.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Icon Class (HugeIcons)</label>
                        <input type="text" name="icon" class="form-control" placeholder="e.g. hgi-building-01">
                        <div class="form-text">Available: <code>hgi-building-01</code>, <code>hgi-shopping-bag-01</code>, <code>hgi-store-01</code>, <code>hgi-credit-card</code>, <code>hgi-delivery-truck-01</code>, <code>hgi-refresh-circle</code>, <code>hgi-shield-check</code></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="createIsActive">
                                <label class="form-check-label" for="createIsActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Topic</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Topic Modal -->
    <div class="modal fade" id="editTopicModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editTopicForm" action="" method="POST" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit FAQ Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Topic Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editTopicName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Slug</label>
                        <input type="text" name="slug" id="editTopicSlug" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Icon Class</label>
                        <input type="text" name="icon" id="editTopicIcon" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" id="editTopicSortOrder" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editTopicIsActive">
                                <label class="form-check-label" for="editTopicIsActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Topic</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.edit-topic-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const topic = JSON.parse(this.getAttribute('data-topic'));
            const form = document.getElementById('editTopicForm');
            form.action = `/admin/faq-topics/${topic.id}`;
            document.getElementById('editTopicName').value = topic.name;
            document.getElementById('editTopicSlug').value = topic.slug;
            document.getElementById('editTopicIcon').value = topic.icon || '';
            document.getElementById('editTopicSortOrder').value = topic.sort_order;
            document.getElementById('editTopicIsActive').checked = topic.is_active;
        });
    });

    document.querySelectorAll('.topic-status-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const id = this.getAttribute('data-id');
            fetch(`/admin/faq-topics/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    toastr?.success(data.message) || alert(data.message);
                }
            });
        });
    });
</script>
@endpush
