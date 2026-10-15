<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\SystemSetting;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProductMarginsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $query;
    protected float $defaultAdminMargin;
    protected float $defaultDdcMargin;

    public function __construct($query = null)
    {
        $this->query = $query ?: Product::query()->with(['seller.sellerProfile']);
        $this->defaultAdminMargin = (float) SystemSetting::getVal('admin_margin_percentage', 8.00);
        $this->defaultDdcMargin = (float) SystemSetting::getVal('ddc_margin_percentage', 6.00);
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Product ID',
            'Product Name',
            'Product Code',
            'Seller Name',
            'IMV Code',
            'MRP',
            'Seller Price (Incl GST)',
            'Admin Margin',
            'DDC Margin',
        ];
    }

    public function map($product): array
    {
        $sellerName = $product->seller?->sellerProfile?->company_name
            ?: ($product->seller?->name ?? '—');

        $imvCode = $product->seller?->sellerProfile?->seller_id
            ?: ($product->seller_id ? 'IMV' . $product->seller_id : '—');

        $adminMargin = $product->admin_margin_percentage !== null
            ? (float) $product->admin_margin_percentage
            : $this->defaultAdminMargin;

        $ddcMargin = $product->ddc_margin_percentage !== null
            ? (float) $product->ddc_margin_percentage
            : $this->defaultDdcMargin;

        return [
            $product->id,
            $product->product_name ?? '—',
            $product->product_code ?? '—',
            $sellerName,
            $imvCode,
            number_format((float) $product->mrp, 2, '.', ''),
            number_format((float) $product->seller_price_incl_gst, 2, '.', ''),
            $adminMargin,
            $ddcMargin,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B'], // Slate-800
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
