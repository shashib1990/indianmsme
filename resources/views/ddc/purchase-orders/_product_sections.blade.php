{{--
    DDC Purchase Order — Website-style Product Sections
    Variables expected: $newlyLaunched, $bestSelling, $featuredProducts
--}}

@push('styles')
<style>
.sec-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 14px;
}
.sec-title {
    font-family: 'Outfit', sans-serif;
    font-size: 18px; font-weight: 700; color: #1e293b;
    display: flex; align-items: center; gap: 8px;
}
.sec-title .sec-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.sec-badge {
    font-size: 10.5px; padding: 3px 10px; border-radius: 20px;
    font-weight: 600; letter-spacing: .3px;
}

/* Product Card */
.po-prod-card {
    background: #fff;
    border: 1.5px solid #e8ecf4;
    border-radius: 12px;
    overflow: hidden;
    transition: box-shadow .18s, transform .18s, border-color .18s;
    height: 100%;
    display: flex; flex-direction: column;
}
.po-prod-card:hover {
    box-shadow: 0 8px 24px rgba(99,102,241,.12);
    transform: translateY(-2px);
    border-color: #c7d2fe;
}
.po-prod-img {
    width: 100%; height: 140px;
    object-fit: contain; background: #f8fafc;
    padding: 10px; display: block;
}
.po-prod-img-placeholder {
    width: 100%; height: 140px;
    background: #f1f5f9;
    display: flex; align-items: center; justify-content: center;
}
.po-prod-body { padding: 10px 12px; flex-grow: 1; display: flex; flex-direction: column; }
.po-prod-name {
    font-weight: 700; font-size: 13px; color: #1e293b;
    line-height: 1.35; margin-bottom: 4px;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
.po-prod-cat  { font-size: 11px; color: #94a3b8; margin-bottom: 6px; }
.po-prod-prices { display: flex; align-items: center; gap: 6px; margin-bottom: 8px; flex-wrap: wrap; }
.po-prod-mrp  { font-size: 11px; color: #94a3b8; text-decoration: line-through; }
.po-prod-ddc  { font-size: 13px; font-weight: 700; color: #7c3aed; }
.po-prod-disc { font-size: 11px; background: #dcfce7; color: #15803d; border-radius: 4px; padding: 1px 6px; font-weight: 600; }
.po-prod-footer { padding: 8px 12px; border-top: 1px solid #f1f5f9; margin-top: auto; }
.po-new-badge { font-size: 10px; background: #fef9c3; color: #854d0e; border-radius: 4px; padding: 2px 7px; font-weight: 600; }
</style>
@endpush

@php
    function ddcSectionCard($product) {
        $img = null;
        if ($product->featured_image) {
            $img = \Illuminate\Support\Str::startsWith($product->featured_image, ['http://','https://'])
                ? $product->featured_image
                : asset('storage/' . $product->featured_image);
        }
        $mrp = (float)($product->mrp ?? 0);
        $ddc = (float)($product->display_price ?? $mrp);
        $disc = $mrp > 0 ? round((($mrp - $ddc) / $mrp) * 100, 1) : 0;
        return compact('img', 'mrp', 'ddc', 'disc');
    }
@endphp

<div class="mb-2">
    <p class="text-muted small mb-3">
        <iconify-icon icon="solar:info-circle-bold-duotone" class="align-middle me-1"></iconify-icon>
        नीचे market trends दिख रहे हैं। Seller select करने के बाद उस seller के products order कर सकते हैं।
    </p>
</div>

{{-- ══════════════════════════════════════════
     SECTION 1: Newly Launched Products
     ══════════════════════════════════════════ --}}
@if($newlyLaunched->isNotEmpty())
<div class="mb-4">
    <div class="sec-header">
        <div class="sec-title">
            <span class="sec-dot" style="background:#f59e0b;"></span>
            <iconify-icon icon="solar:star-shine-bold-duotone" style="color:#f59e0b;"></iconify-icon>
            Newly Launched Products
            <span class="sec-badge" style="background:#fef9c3;color:#854d0e;">{{ $newlyLaunched->count() }} Products</span>
        </div>
    </div>
    <div class="row g-3">
        @foreach($newlyLaunched as $product)
        @php $c = ddcSectionCard($product); @endphp
        <div class="col-6 col-sm-4 col-md-3 col-xl-3">
            <div class="po-prod-card">
                @if($c['img'])
                    <img src="{{ $c['img'] }}" alt="{{ $product->product_name }}" class="po-prod-img">
                @else
                    <div class="po-prod-img-placeholder">
                        <iconify-icon icon="solar:box-bold-duotone" style="font-size:36px;color:#cbd5e1;"></iconify-icon>
                    </div>
                @endif
                <div class="po-prod-body">
                    <div class="d-flex align-items-start gap-1 mb-1">
                        <span class="po-new-badge">🆕 New</span>
                    </div>
                    <div class="po-prod-name">{{ $product->product_name }}</div>
                    <div class="po-prod-cat">{{ $product->category?->name ?? '—' }}</div>
                    <div class="po-prod-prices">
                        @if($c['mrp'] > $c['ddc'])
                            <span class="po-prod-mrp">₹{{ number_format($c['mrp'], 0) }}</span>
                        @endif
                        <span class="po-prod-ddc">₹{{ number_format($c['ddc'], 2) }}</span>
                        @if($c['disc'] > 0)
                            <span class="po-prod-disc">{{ $c['disc'] }}% off</span>
                        @endif
                    </div>
                </div>
                <div class="po-prod-footer">
                    <small class="text-muted d-block text-center" style="font-size:10.5px;">
                        <iconify-icon icon="solar:info-circle-line-duotone" class="align-middle"></iconify-icon>
                        Seller select करें फिर order करें
                    </small>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
<hr class="my-4 opacity-15">
@endif

{{-- ══════════════════════════════════════════
     SECTION 2: Best Selling Products
     ══════════════════════════════════════════ --}}
@if($bestSelling->isNotEmpty())
<div class="mb-4">
    <div class="sec-header">
        <div class="sec-title">
            <span class="sec-dot" style="background:#f97316;"></span>
            <iconify-icon icon="solar:fire-bold-duotone" style="color:#f97316;"></iconify-icon>
            Best Selling Products
            <span class="sec-badge" style="background:#ffedd5;color:#9a3412;">{{ $bestSelling->count() }} Products</span>
        </div>
    </div>
    <div class="row g-3">
        @foreach($bestSelling as $i => $product)
        @php $c = ddcSectionCard($product); @endphp
        <div class="col-6 col-sm-4 col-md-3 col-xl-3">
            <div class="po-prod-card" style="border-color:#fed7aa;">
                @if($c['img'])
                    <img src="{{ $c['img'] }}" alt="{{ $product->product_name }}" class="po-prod-img">
                @else
                    <div class="po-prod-img-placeholder">
                        <iconify-icon icon="solar:box-bold-duotone" style="font-size:36px;color:#cbd5e1;"></iconify-icon>
                    </div>
                @endif
                <div class="po-prod-body">
                    <div class="d-flex align-items-start gap-1 mb-1">
                        <span class="sec-badge" style="background:#ffedd5;color:#9a3412;padding:2px 7px;">🔥 #{{ $i+1 }} Best Seller</span>
                    </div>
                    <div class="po-prod-name">{{ $product->product_name }}</div>
                    <div class="po-prod-cat">{{ $product->category?->name ?? '—' }}</div>
                    <div class="po-prod-prices">
                        @if($c['mrp'] > $c['ddc'])
                            <span class="po-prod-mrp">₹{{ number_format($c['mrp'], 0) }}</span>
                        @endif
                        <span class="po-prod-ddc">₹{{ number_format($c['ddc'], 2) }}</span>
                        @if($c['disc'] > 0)
                            <span class="po-prod-disc">{{ $c['disc'] }}% off</span>
                        @endif
                    </div>
                </div>
                <div class="po-prod-footer">
                    <small class="text-muted d-block text-center" style="font-size:10.5px;">
                        <iconify-icon icon="solar:info-circle-line-duotone" class="align-middle"></iconify-icon>
                        Seller select करें फिर order करें
                    </small>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
<hr class="my-4 opacity-15">
@endif

{{-- ══════════════════════════════════════════
     SECTION 3: Daily Discount You'll Love
     ══════════════════════════════════════════ --}}
@if($featuredProducts->isNotEmpty())
<div class="mb-4">
    <div class="sec-header">
        <div class="sec-title">
            <span class="sec-dot" style="background:#6366f1;"></span>
            <iconify-icon icon="solar:tag-price-bold-duotone" style="color:#6366f1;"></iconify-icon>
            Daily Discount You'll Love
            <span class="sec-badge" style="background:#ede9fe;color:#5b21b6;">{{ $featuredProducts->count() }} Products</span>
        </div>
    </div>
    <div class="row g-3">
        @foreach($featuredProducts as $product)
        @php $c = ddcSectionCard($product); @endphp
        <div class="col-6 col-sm-4 col-md-3 col-xl-3">
            <div class="po-prod-card" style="border-color:#e0e7ff;">
                @if($c['img'])
                    <img src="{{ $c['img'] }}" alt="{{ $product->product_name }}" class="po-prod-img">
                @else
                    <div class="po-prod-img-placeholder">
                        <iconify-icon icon="solar:box-bold-duotone" style="font-size:36px;color:#cbd5e1;"></iconify-icon>
                    </div>
                @endif
                <div class="po-prod-body">
                    @if($c['disc'] > 0)
                    <div class="d-flex mb-1">
                        <span class="sec-badge" style="background:#ede9fe;color:#5b21b6;padding:2px 7px;">🏷 {{ $c['disc'] }}% off today</span>
                    </div>
                    @endif
                    <div class="po-prod-name">{{ $product->product_name }}</div>
                    <div class="po-prod-cat">{{ $product->category?->name ?? '—' }}</div>
                    <div class="po-prod-prices">
                        @if($c['mrp'] > $c['ddc'])
                            <span class="po-prod-mrp">₹{{ number_format($c['mrp'], 0) }}</span>
                        @endif
                        <span class="po-prod-ddc">₹{{ number_format($c['ddc'], 2) }}</span>
                    </div>
                </div>
                <div class="po-prod-footer">
                    <small class="text-muted d-block text-center" style="font-size:10.5px;">
                        <iconify-icon icon="solar:info-circle-line-duotone" class="align-middle"></iconify-icon>
                        Seller select करें फिर order करें
                    </small>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($newlyLaunched->isEmpty() && $bestSelling->isEmpty() && $featuredProducts->isEmpty())
<div class="card border-0 shadow-sm text-center py-5">
    <div class="card-body">
        <iconify-icon icon="solar:shop-2-bold-duotone" class="text-primary fs-48 d-block mx-auto mb-3 opacity-50"></iconify-icon>
        <h5 class="fw-bold text-slate-800">Seller Select करें</h5>
        <p class="text-muted small mx-auto" style="max-width: 360px;">
            ऊपर से Seller select करें और Purchase Order generate करें।
        </p>
    </div>
</div>
@endif
