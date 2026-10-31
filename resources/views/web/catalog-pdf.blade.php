<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $companyName }} - Product Catalog | Indian MSME</title>
    <style>
        @page {
            margin: 10mm 10mm 14mm 10mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.35;
        }

        /* ── Utility Classes ── */
        .w-100 { width: 100%; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        .text-navy { color: #071a41; }
        .text-green { color: #059669; }

        /* ── Tables ── */
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        /* ── Header Bar ── */
        .top-header-table {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 2px solid #071a41;
        }

        .brand-logo-img {
            max-height: 38px;
            max-width: 150px;
        }

        .top-doc-title {
            font-size: 13pt;
            font-weight: bold;
            color: #071a41;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .top-doc-subtitle {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
        }

        /* ── Seller Hero Showcase (Re-aligned & Clean) ── */
        .seller-hero-box {
            background-color: #071a41;
            color: #ffffff;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 10px;
            border: 1px solid #0d285c;
        }

        .seller-logo-box {
            width: 58px;
            height: 58px;
            background: #ffffff;
            border-radius: 50%;
            border: 2px solid #f59e0b;
            text-align: center;
            overflow: hidden;
            display: block;
            margin: 0;
            padding: 0;
        }

        .seller-logo-img {
            max-width: 50px;
            max-height: 50px;
            vertical-align: middle;
            display: inline-block;
        }

        .seller-title-heading {
            font-size: 13pt;
            font-weight: bold;
            color: #ffffff;
            margin: 0 0 4px 0;
            line-height: 1.25;
        }

        .seller-category-text {
            font-size: 7.5pt;
            color: #94a3b8;
            margin-top: 3px;
        }

        .seller-category-text strong {
            color: #e2e8f0;
        }

        .verified-badge {
            display: inline-block;
            background: #16a34a;
            color: #ffffff;
            font-size: 6.5pt;
            font-weight: bold;
            padding: 2px 7px;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-right: 5px;
        }

        .imv-badge {
            display: inline-block;
            background: #f59e0b;
            color: #071a41;
            font-size: 7pt;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 10px;
            letter-spacing: 0.3px;
        }

        .catalog-stat-box {
            background-color: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            padding: 6px 14px;
            text-align: center;
            display: inline-block;
        }

        .catalog-stat-label {
            font-size: 6.5pt;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .catalog-stat-value {
            font-size: 13pt;
            font-weight: bold;
            color: #f59e0b;
            margin-top: 1px;
            line-height: 1.1;
        }

        /* ── Highlights Ribbon ── */
        .highlights-table {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .highlights-table td {
            padding: 5px 4px;
            text-align: center;
            font-size: 7.5pt;
            font-weight: bold;
            color: #334155;
            border-right: 1px solid #e2e8f0;
        }

        .highlights-table td:last-child {
            border-right: none;
        }

        /* ── Products Grid ── */
        .product-grid-table {
            width: 100%;
            margin-bottom: 8px;
        }

        .product-card-cell {
            width: 50%;
            padding: 4px;
            vertical-align: top;
            page-break-inside: avoid;
        }

        .product-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background-color: #ffffff;
            padding: 7px;
            min-height: 122px;
            page-break-inside: avoid;
        }

        .product-img-box {
            width: 80px;
            height: 80px;
            border: 1px solid #f1f5f9;
            border-radius: 6px;
            background-color: #f8fafc;
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
            padding: 2px;
        }

        .product-img {
            max-width: 76px;
            max-height: 76px;
            vertical-align: middle;
        }

        .product-brand {
            font-size: 6.5pt;
            font-weight: bold;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .product-title {
            font-size: 8pt;
            font-weight: bold;
            color: #071a41;
            line-height: 1.25;
            margin: 0 0 5px 0;
            word-wrap: break-word;
            overflow: visible;
        }

        .meta-pill {
            display: inline-block;
            background-color: #f1f5f9;
            color: #475569;
            font-size: 6.5pt;
            font-weight: 500;
            padding: 1px 5px;
            border-radius: 4px;
            margin-right: 3px;
            margin-bottom: 2px;
        }

        .product-price-box {
            background-color: #f8fafc;
            border-top: 1px dashed #cbd5e1;
            padding: 4px 6px 3px 6px;
            margin-top: 5px;
            border-radius: 4px;
        }

        .selling-price-val {
            font-size: 11pt;
            font-weight: bold;
            color: #071a41;
        }

        .selling-unit-text {
            font-size: 7pt;
            color: #64748b;
            font-weight: normal;
        }

        .mrp-strikethrough {
            font-size: 7.5pt;
            color: #94a3b8;
            text-decoration: line-through;
            margin-left: 4px;
        }

        .save-badge {
            display: inline-block;
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
            font-size: 6.5pt;
            font-weight: bold;
            padding: 1px 5px;
            border-radius: 4px;
            float: right;
            margin-top: 2px;
        }

        /* ── Order Support Banner ── */
        .order-support-box {
            background: #f8fafc;
            border: 1.5px solid #071a41;
            border-radius: 8px;
            padding: 10px 14px;
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .order-support-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #071a41;
            margin-bottom: 4px;
        }

        .order-support-text {
            font-size: 7.5pt;
            color: #334155;
            line-height: 1.4;
        }

        /* ── Watermark / Empty ── */
        .no-products-box {
            text-align: center;
            padding: 50px 20px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            color: #64748b;
        }
    </style>
</head>
<body>

    {{-- Top MSME Header Bar --}}
    <table class="top-header-table">
        <tr>
            <td style="width: 50%; vertical-align: middle;">
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" class="brand-logo-img" alt="Indian MSME">
                @else
                    <span style="font-size: 15pt; font-weight: bold; color: #071a41;">INDIAN MSME</span>
                @endif
                <div class="top-doc-subtitle">Official Marketplace • Ministry of MSME Initiative Partner</div>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: middle;">
                <div class="top-doc-title">PRODUCT CATALOG</div>
                <div class="top-doc-subtitle">
                    Catalog Ref: <strong>CAT-{{ $imv_code }}-{{ date('Y') }}</strong> &bull; Generated: {{ date('d M, Y') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Seller Hero Showcase (Re-aligned without mobile/email/location/GST) --}}
    <div class="seller-hero-box">
        <table style="width: 100%;">
            <tr>
                {{-- Left: Seller Logo Box --}}
                <td style="width: 66px; vertical-align: middle;">
                    <div class="seller-logo-box">
                        <table style="width: 58px; height: 58px; margin: 0; padding: 0; border: none;">
                            <tr>
                                <td align="center" valign="middle" style="width: 58px; height: 58px; text-align: center; vertical-align: middle; padding: 0;">
                                    @if(!empty($sellerLogoPath) && file_exists($sellerLogoPath))
                                        <img src="{{ $sellerLogoPath }}" class="seller-logo-img" alt="{{ $companyName }}">
                                    @else
                                        <span style="font-size: 22pt; font-weight: bold; color: #071a41; line-height: 1; display: inline-block;">
                                            {{ strtoupper(substr($companyName, 0, 1)) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>

                {{-- Center: Company Name & Credentials --}}
                <td style="padding-left: 12px; vertical-align: middle;">
                    <div class="seller-title-heading">{{ $companyName }}</div>
                    <div>
                        <span class="verified-badge">&#10003; VERIFIED SELLER</span>
                        <span class="imv-badge">IMV: {{ $imv_code }}</span>
                        @if(!empty($profile->is_ddc_subscribed))
                            <span style="display: inline-block; background: #eab308; color: #071a41; font-size: 6.5pt; font-weight: bold; padding: 2px 7px; border-radius: 10px; text-transform: uppercase; letter-spacing: 0.3px; margin-left: 4px;">&#9733; DDC SUBSCRIBED</span>
                        @endif
                    </div>
                    <div class="seller-category-text">
                        Category: <strong>{{ $profile->category?->name ?? 'General MSME' }}</strong>
                    </div>
                </td>

                {{-- Right: Total Products Box --}}
                <td style="width: 130px; text-align: right; vertical-align: middle;">
                    <div class="catalog-stat-box">
                        <div class="catalog-stat-label">CATALOG SIZE</div>
                        <div class="catalog-stat-value">{{ count($products) }} <span style="font-size: 7.5pt; color: #ffffff; font-weight: normal;">Items</span></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Value Proposition Highlights Ribbon --}}
    <table class="highlights-table">
        <tr>
            <td style="width: 25%;">Direct Website Price</td>
            <td style="width: 25%;">Authentic MSME Certified</td>
            <td style="width: 25%;">Single & Bulk Supply MOQ</td>
            <td style="width: 25%;">Pan-India Fast Dispatch</td>
        </tr>
    </table>

    {{-- Products Grid Section --}}
    @if(count($products) === 0)
        <div class="no-products-box">
            <h3 style="margin: 0 0 6px 0; color: #475569;">No products currently listed</h3>
            <p style="margin: 0; font-size: 8pt;">Please check back soon or visit the seller portal online.</p>
        </div>
    @else
        <table class="product-grid-table">
            @foreach($products->chunk(2) as $row)
                <tr>
                    @foreach($row as $p)
                        <td class="product-card-cell">
                            <div class="product-card">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        {{-- Product Image (Local Path - Fast & 100% reliable) --}}
                                        <td style="width: 84px; vertical-align: top;">
                                            <div class="product-img-box">
                                                @if(!empty($p->image_path) && file_exists($p->image_path))
                                                    <img src="{{ $p->image_path }}" class="product-img" alt="{{ $p->name }}">
                                                @elseif(file_exists($logoPath))
                                                    <img src="{{ $logoPath }}" class="product-img" style="opacity: 0.5;" alt="Indian MSME">
                                                @else
                                                    <div style="line-height: 76px; font-size: 8pt; color: #94a3b8;">NO IMAGE</div>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Product Info (Clean without description & without GST) --}}
                                        <td style="padding-left: 8px; vertical-align: top;">
                                            <div class="product-brand">{{ $p->brand ?: ($p->category ?: 'MSME PRODUCT') }}</div>
                                            <div class="product-title" title="{{ $p->name }}">{{ $p->name }}</div>

                                            <div>
                                                @if($p->code)
                                                    <span class="meta-pill">Code: {{ $p->code }}</span>
                                                @endif
                                                @if($p->moq > 1)
                                                    <span class="meta-pill">MOQ: {{ $p->moq }} {{ $p->unit }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                                {{-- Price Block (Exact Website Display Price) --}}
                                <div class="product-price-box">
                                    <span class="selling-price-val">&#8377;{{ number_format($p->price, 2) }}</span>
                                    <span class="selling-unit-text">/ {{ $p->unit }}</span>

                                    @if($p->discount > 0)
                                        <span class="mrp-strikethrough">&#8377;{{ number_format($p->mrp, 2) }}</span>
                                        <span class="save-badge">{{ $p->discount }}% OFF</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                    @endforeach

                    {{-- If odd number in row, fill with empty cell --}}
                    @if($row->count() == 1)
                        <td class="product-card-cell" style="border: none; background: transparent;"></td>
                    @endif
                </tr>
            @endforeach
        </table>
    @endif

    {{-- Ordering Guide Banner (At end of catalog) --}}
    <div class="order-support-box">
        <table style="width: 100%;">
            <tr>
                <td style="width: 70%; vertical-align: middle;">
                    <div class="order-support-title">How to Order from this Catalog:</div>
                    <div class="order-support-text">
                        1. Visit Indian MSME Store directly: <strong>{{ url('/vendor/' . $imv_code) }}</strong><br>
                        2. Search for the Product Code or Name and Add to Cart.<br>
                        3. Secure checkout with door-step delivery & genuine tax invoices.
                    </div>
                </td>
                <td style="width: 30%; text-align: right; vertical-align: middle;">
                    <div style="font-size: 7.5pt; color: #64748b; margin-bottom: 3px;">Marketplace Portal:</div>
                    <div style="font-size: 8.5pt; font-weight: bold; color: #071a41;">indianmsme.org</div>
                    <div style="font-size: 7pt; color: #16a34a; font-weight: bold; margin-top: 2px;">Verified MSME Seller</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Script for DomPDF page numbers and running footer on each page --}}
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            $size = 7.5;
            $color = array(0.45, 0.50, 0.55);

            // Left footer
            $pdf->page_text(28, 818, "Indian MSME Marketplace  •  Seller Catalog: {{ addslashes($companyName) }} ({{ $imv_code }})", $font, $size, $color);

            // Right footer
            $pdf->page_text(500, 818, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, $size, $color);
        }
    </script>

</body>
</html>
