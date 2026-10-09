@props([
    'action' => '',
    'search' => '',
    'placeholder' => 'Search...',
    'limit' => 15,
    'sort' => '',
    'order' => '',
    'bulkActions' => [],
    'export' => true,
    'exportUrl' => null
])

<div class="row mb-3 g-2">
    <div class="col-12">
        <form id="filterForm" action="{{ $action }}" method="GET" class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <input type="hidden" name="sort" id="formSort" value="{{ $sort }}">
            <input type="hidden" name="order" id="formOrder" value="{{ $order }}">
            
            <!-- Left Side: Search, Filters Slot, and Limit Dropdown -->
            <div class="d-flex flex-wrap gap-2 align-items-center flex-grow-1">
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text bg-white border-end-0 py-1.5">
                        <iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 py-1.5" 
                           placeholder="{{ $placeholder }}" value="{{ $search }}">
                </div>
                
                {{ $slot }}
                
                <select name="limit" class="form-select w-auto py-1.5" onchange="this.form.submit()">
                    <option value="10" {{ $limit == 10 ? 'selected' : '' }}>10 per page</option>
                    <option value="15" {{ $limit == 15 ? 'selected' : '' }}>15 per page</option>
                    <option value="25" {{ $limit == 25 ? 'selected' : '' }}>25 per page</option>
                    <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50 per page</option>
                    <option value="100" {{ $limit == 100 ? 'selected' : '' }}>100 per page</option>
                </select>
                
                <button type="submit" class="btn btn-outline-secondary py-1.5 px-3">Filter</button>
                
                @if($search || request('status') || request('type') || request('category') || request('state_id') || request('district_id'))
                    <a href="{{ $action }}" class="btn btn-outline-danger py-1.5 px-3">Clear</a>
                @endif

                @if($export)
                    <a href="{{ $exportUrl ?: request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-outline-success py-1.5 px-3 d-inline-flex align-items-center gap-1" title="Export to CSV">
                        <iconify-icon icon="solar:export-bold-duotone" class="fs-15"></iconify-icon> Export CSV
                    </a>
                @endif
            </div>

            <!-- Right Side: Bulk Actions -->
            @if(!empty($bulkActions))
                <div id="bulkActionsDropdownContainer" style="display: none;" class="ms-auto">
                    <div class="dropdown">
                        <button class="btn btn-outline-danger dropdown-toggle d-inline-flex align-items-center gap-1 py-1.5 px-3 fs-13" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <iconify-icon icon="solar:checklist-bold-duotone" class="fs-16"></iconify-icon>
                            Bulk Actions (<span id="selectedCount">0</span>)
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2">
                            @foreach($bulkActions as $key => $label)
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="#" onclick="triggerBulkAction('{{ $key }}'); return false;">
                                        @if($key === 'delete')
                                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="text-danger fs-16"></iconify-icon>
                                        @elseif($key === 'status_active' || $key === 'status_inactive')
                                            <iconify-icon icon="solar:shield-check-bold-duotone" class="text-primary fs-16"></iconify-icon>
                                        @endif
                                        <span>{{ $label }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>
