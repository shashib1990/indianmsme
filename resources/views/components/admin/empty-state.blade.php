@props([
    'icon' => 'solar:box-search-bold-duotone',
    'title' => 'No Data Found',
    'description' => 'We couldn\'t find any records matching your search or filters.',
    'colspan' => 12
])

<tr>
    <td colspan="{{ $colspan }}" class="text-center py-5">
        <div class="d-flex flex-column align-items-center justify-content-center my-3">
            <div class="avatar-lg bg-soft-secondary rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                <iconify-icon icon="{{ $icon }}" class="fs-36 text-muted"></iconify-icon>
            </div>
            <h5 class="fw-semibold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">{{ $title }}</h5>
            <p class="text-muted small mb-0 px-3">{{ $description }}</p>
        </div>
    </td>
</tr>
