@props([
    'title' => '',
    'subtitle' => '',
    'actions' => [] // Array of action buttons
])

<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3 px-4">
                <div class="row align-items-center justify-content-between g-3">
                    <div class="col-md-7">
                        <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif; font-size: 20px;">{{ $title }}</h4>
                        @if($subtitle)
                            <p class="text-muted small mb-0">{{ $subtitle }}</p>
                        @elseif(isset($slot) && trim($slot) !== '')
                            <div class="text-muted small mb-0">{{ $slot }}</div>
                        @endif
                    </div>
                    
                    <div class="col-md-5 text-md-end">
                        <div class="d-inline-flex flex-wrap gap-2 align-items-center justify-content-md-end w-100">
                            @foreach($actions as $action)
                                @php
                                    $type = $action['type'] ?? 'button'; // button, link, dropdown
                                    $btnClass = $action['btnClass'] ?? ('btn-' . ($action['variant'] ?? 'primary'));
                                    $icon = $action['icon'] ?? '';
                                    $label = $action['label'] ?? '';
                                    $target = $action['target'] ?? ''; // Modal ID, URL, or JS onclick callback
                                    $onclick = $action['onclick'] ?? '';
                                    $url = $action['url'] ?? '';
                                    $permission = $action['permission'] ?? null;

                                    $hasPerm = true;
                                    if ($permission) {
                                        $guards = ['admin', 'ddc', 'adc', 'vendor'];
                                        $user = null;
                                        foreach ($guards as $g) {
                                            if (auth($g)->check()) {
                                                $user = auth($g)->user();
                                                break;
                                            }
                                        }
                                        if ($user) {
                                            if ((auth('admin')->check() && $user->is_super_admin === '1') || $user->parent_id === null) {
                                                $hasPerm = true;
                                            } else {
                                                $hasPerm = $user->hasPermission($permission);
                                            }
                                        } else {
                                            $hasPerm = false;
                                        }
                                    }
                                @endphp

                                @if($hasPerm)
                                    @if($type === 'button' && empty($url))
                                        <button type="button" 
                                                class="btn {{ $btnClass }} d-inline-flex align-items-center gap-1 shadow-sm px-3 py-2 fw-semibold btn-action-header"
                                                @if(!empty($action['disabled'])) disabled @elseif($onclick) onclick="{!! $onclick !!}" @else data-bs-toggle="modal" data-bs-target="{{ $target }}" @endif>
                                            @if($icon)
                                                <iconify-icon icon="{{ $icon }}" class="fs-18"></iconify-icon>
                                            @endif
                                            {{ $label }}
                                        </button>
                                    @elseif($type === 'link' || !empty($url))
                                        <a href="{{ $url ?: $target }}" 
                                           class="btn {{ $btnClass }} d-inline-flex align-items-center gap-1 shadow-sm px-3 py-2 fw-semibold btn-action-header">
                                            @if($icon)
                                                <iconify-icon icon="{{ $icon }}" class="fs-18"></iconify-icon>
                                            @endif
                                            {{ $label }}
                                        </a>
                                    @elseif($type === 'dropdown')
                                        <div class="dropdown">
                                            <button class="btn {{ $btnClass }} dropdown-toggle d-inline-flex align-items-center gap-1 shadow-sm px-3 py-2 fw-semibold" 
                                                    type="button" 
                                                    data-bs-toggle="dropdown" 
                                                    aria-expanded="false">
                                                @if($icon)
                                                    <iconify-icon icon="{{ $icon }}" class="fs-18"></iconify-icon>
                                                @endif
                                                {{ $label }}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                                                @foreach($action['items'] ?? [] as $item)
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" 
                                                           href="{{ $item['url'] ?? '#' }}"
                                                           @if(!empty($item['attributes'])) {!! $item['attributes'] !!} @endif>
                                                            @if(!empty($item['icon']))
                                                                <iconify-icon icon="{{ $item['icon'] }}" class="fs-16 text-muted"></iconify-icon>
                                                            @endif
                                                            <span>{{ $item['label'] }}</span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
