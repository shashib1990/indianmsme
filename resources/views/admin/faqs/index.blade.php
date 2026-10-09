@extends('layouts.admin')

@section('title', 'Manage FAQs')
@section('page-title', 'Manage FAQs')

@section('content')
    <x-admin.page-header 
        title="Manage FAQs" 
        subtitle="Add, edit, filter, and organize website FAQ questions & answers."
        :actions="[
            [
                'label' => 'Add Question',
                'icon' => 'solar:add-circle-bold-duotone',
                'target' => '#createFaqModal',
                'btnClass' => 'btn-primary'
            ]
        ]"
    />

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form action="{{ route('admin.faqs.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search question or answer...">
                </div>
                <div class="col-md-4">
                    <select name="topic_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Topics</option>
                        @foreach($topics as $topic)
                            <option value="{{ $topic->id }}" {{ request('topic_id') == $topic->id ? 'selected' : '' }}>
                                {{ $topic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 text-end">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="{{ route('admin.faqs.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">Sort</th>
                            <th style="width: 250px;">Question</th>
                            <th>Answer Snippet</th>
                            <th style="width: 160px;">Topic</th>
                            <th class="text-center" style="width: 90px;">Status</th>
                            <th class="text-end" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($faqs as $faq)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $faq->sort_order }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $faq->question }}</div>
                                </td>
                                <td>
                                    <div class="text-muted small text-truncate" style="max-width: 400px;">
                                        {{ Str::limit(strip_tags($faq->answer), 100) }}
                                    </div>
                                </td>
                                <td>
                                    @if($faq->topic)
                                        <span class="badge bg-success-subtle text-success fw-semibold">
                                            {{ $faq->topic->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted">Unassigned</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input faq-status-toggle" type="checkbox" 
                                               data-id="{{ $faq->id }}" {{ $faq->is_active ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary me-1 edit-faq-btn" 
                                            data-faq='@json($faq)' data-bs-toggle="modal" data-bs-target="#editFaqModal">
                                        <i class="bx bx-edit-alt"></i> Edit
                                    </button>
                                    <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this FAQ?')">
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
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No FAQs found. Click "Add Question" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                {{ $faqs->links() }}
            </div>
        </div>
    </div>

    <!-- Create FAQ Modal -->
    <div class="modal fade" id="createFaqModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('admin.faqs.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add FAQ Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Topic <span class="text-danger">*</span></label>
                        <select name="faq_topic_id" class="form-select" required>
                            <option value="">Select Topic</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Question <span class="text-danger">*</span></label>
                        <input type="text" name="question" class="form-control" placeholder="e.g. How do I track my order?" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Answer <span class="text-danger">*</span></label>
                        <textarea name="answer" rows="4" class="form-control" placeholder="Provide detailed answer..." required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="createFaqActive">
                                <label class="form-check-label" for="createFaqActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Question</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit FAQ Modal -->
    <div class="modal fade" id="editFaqModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="editFaqForm" action="" method="POST" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit FAQ Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Topic <span class="text-danger">*</span></label>
                        <select name="faq_topic_id" id="editFaqTopicId" class="form-select" required>
                            <option value="">Select Topic</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Question <span class="text-danger">*</span></label>
                        <input type="text" name="question" id="editFaqQuestion" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Answer <span class="text-danger">*</span></label>
                        <textarea name="answer" id="editFaqAnswer" rows="4" class="form-control" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" id="editFaqSortOrder" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editFaqIsActive">
                                <label class="form-check-label" for="editFaqIsActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Question</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.edit-faq-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const faq = JSON.parse(this.getAttribute('data-faq'));
            const form = document.getElementById('editFaqForm');
            form.action = `/admin/faqs/${faq.id}`;
            document.getElementById('editFaqTopicId').value = faq.faq_topic_id;
            document.getElementById('editFaqQuestion').value = faq.question;
            document.getElementById('editFaqAnswer').value = faq.answer;
            document.getElementById('editFaqSortOrder').value = faq.sort_order;
            document.getElementById('editFaqIsActive').checked = faq.is_active;
        });
    });

    document.querySelectorAll('.faq-status-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const id = this.getAttribute('data-id');
            fetch(`/admin/faqs/${id}/toggle-status`, {
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
