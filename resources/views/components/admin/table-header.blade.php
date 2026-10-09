@props([
    'field',
    'sort',
    'order'
])

@php
    $isActive = $sort === $field;
    $nextOrder = $isActive && $order === 'asc' ? 'desc' : 'asc';
    $icon = 'solar:sort-vertical-bold-duotone';
    if ($isActive) {
        $icon = $order === 'asc' ? 'solar:arrow-up-bold-duotone' : 'solar:arrow-down-bold-duotone';
    }

    $classAttr = $attributes->get('class', '');
    $justifyClass = 'justify-content-between';
    if (str_contains($classAttr, 'text-center')) {
        $justifyClass = 'justify-content-center';
    } elseif (str_contains($classAttr, 'text-end') || str_contains($classAttr, 'text-right')) {
        $justifyClass = 'justify-content-end';
    }
@endphp

<th {{ $attributes->merge(['class' => 'sortable-header py-3 ' . ($isActive ? 'table-active' : '')]) }}
    style="cursor: pointer; user-select: none;"
    onclick="sortBy('{{ $field }}')">
    <div class="d-flex align-items-center gap-1 {{ $justifyClass }}">
        <span>{{ $slot }}</span>
        <iconify-icon icon="{{ $icon }}" 
                      class="fs-16 {{ $isActive ? 'text-primary' : 'text-muted' }}" 
                      style="{{ !$isActive ? 'opacity: 0.4;' : '' }}"></iconify-icon>
    </div>
</th>
