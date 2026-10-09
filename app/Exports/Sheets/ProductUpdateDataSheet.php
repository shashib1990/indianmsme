<?php

namespace App\Exports\Sheets;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class ProductUpdateDataSheet implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithEvents
{
    protected $vendorId;
    protected array $categories;
    protected array $brands;
    protected array $unitsOfMeasure;
    protected array $dimensionUnits;
    protected array $weightUnits;
    protected int $productCount = 0;

    public function __construct(
        $vendorId,
        array $categories = [],
        array $brands = [],
        array $unitsOfMeasure = [],
        array $dimensionUnits = [],
        array $weightUnits = []
    ) {
        $this->vendorId = $vendorId;
        $this->categories = array_values($categories);
        $this->brands = array_values($brands);
        $this->unitsOfMeasure = array_values($unitsOfMeasure);
        $this->dimensionUnits = array_values($dimensionUnits);
        $this->weightUnits = array_values($weightUnits);
    }

    public function title(): string
    {
        return 'Products';
    }

    public function collection()
    {
        $products = Product::where('seller_id', $this->vendorId)
            ->with(['category', 'brand'])
            ->get();

        $this->productCount = $products->count();

        return $products->map(function ($p) {
            $keywordsStr = '';
            if (is_array($p->product_keywords)) {
                $keywordsStr = implode(', ', $p->product_keywords);
            } elseif (is_string($p->product_keywords)) {
                $keywordsStr = $p->product_keywords;
            }

            return [
                'id' => $p->id,
                'product_name' => $p->product_name,
                'description' => $p->description,
                'product_specification' => $p->product_specification,
                'storage_instructions' => $p->storage_instructions,
                'category' => $p->category->name ?? '',
                'brand' => $p->brand->name ?? '',
                'search_keywords' => $keywordsStr,
                'barcode' => $p->barcode,
                'hsn_code' => $p->hsn_code,
                'mrp' => $p->mrp,
                'seller_price_incl_gst' => $p->seller_price_incl_gst,
                'gst_rate' => $p->gst_rate,
                'case_pack_quantity' => $p->case_pack_quantity,
                'unit_of_measure' => $p->unit_of_measure,
                'shelf_life' => $p->shelf_life,
                'length' => $p->length,
                'width' => $p->width,
                'height' => $p->height,
                'dimension_unit' => $p->dimension_unit,
                'weight' => $p->weight,
                'weight_unit' => $p->weight_unit,
                'featured_image' => $p->featured_image
            ];
        });
    }

    public function headings(): array
    {
        return [
            'id',
            'product_name',
            'description',
            'product_specification',
            'storage_instructions',
            'category',
            'brand',
            'search_keywords',
            'barcode',
            'hsn_code',
            'mrp',
            'seller_price_incl_gst',
            'gst_rate',
            'case_pack_quantity',
            'unit_of_measure',
            'shelf_life',
            'length',
            'width',
            'height',
            'dimension_unit',
            'weight',
            'weight_unit',
            'featured_image'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $spreadsheet = $sheet->getParent();
                $optionsSheet = $spreadsheet->getSheetByName('Options');

                // Register Defined Named Ranges for 100% MS Windows Excel compatibility
                if ($optionsSheet) {
                    if (!empty($this->categories)) {
                        $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange('UpdateCategoryList', $optionsSheet, '$A$1:$A$' . count($this->categories)));
                    }
                    if (!empty($this->brands)) {
                        $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange('UpdateBrandList', $optionsSheet, '$B$1:$B$' . count($this->brands)));
                    }
                    if (!empty($this->unitsOfMeasure)) {
                        $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange('UpdateUomList', $optionsSheet, '$C$1:$C$' . count($this->unitsOfMeasure)));
                    }
                    if (!empty($this->dimensionUnits)) {
                        $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange('UpdateDimList', $optionsSheet, '$D$1:$D$' . count($this->dimensionUnits)));
                    }
                    if (!empty($this->weightUnits)) {
                        $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange('UpdateWeightList', $optionsSheet, '$E$1:$E$' . count($this->weightUnits)));
                    }
                }

                $maxRow = max($this->productCount + 100, 1000);

                // Category Validation (Column F)
                if (!empty($this->categories)) {
                    $catValidation = new DataValidation();
                    $catValidation->setType(DataValidation::TYPE_LIST);
                    $catValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $catValidation->setAllowBlank(true);
                    $catValidation->setShowInputMessage(true);
                    $catValidation->setShowErrorMessage(true);
                    $catValidation->setShowDropDown(true);
                    $catValidation->setErrorTitle('Invalid Category');
                    $catValidation->setError('Please select a category from the dropdown list.');
                    $catValidation->setPromptTitle('Select Category');
                    $catValidation->setPrompt('Choose from the category list');
                    $catValidation->setFormula1('=Options!$A$1:$A$' . count($this->categories));
                    $sheet->setDataValidation("F2:F{$maxRow}", $catValidation);
                }

                // Brand Validation (Column G)
                if (!empty($this->brands)) {
                    $brandValidation = new DataValidation();
                    $brandValidation->setType(DataValidation::TYPE_LIST);
                    $brandValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $brandValidation->setAllowBlank(true);
                    $brandValidation->setShowInputMessage(true);
                    $brandValidation->setShowErrorMessage(true);
                    $brandValidation->setShowDropDown(true);
                    $brandValidation->setErrorTitle('Invalid Brand');
                    $brandValidation->setError('Please select a brand from the dropdown list.');
                    $brandValidation->setPromptTitle('Select Brand');
                    $brandValidation->setPrompt('Choose from the brand list');
                    $brandValidation->setFormula1('=Options!$B$1:$B$' . count($this->brands));
                    $sheet->setDataValidation("G2:G{$maxRow}", $brandValidation);
                }

                // Unit of Measure Validation (Column O)
                if (!empty($this->unitsOfMeasure)) {
                    $uomValidation = new DataValidation();
                    $uomValidation->setType(DataValidation::TYPE_LIST);
                    $uomValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $uomValidation->setAllowBlank(true);
                    $uomValidation->setShowInputMessage(true);
                    $uomValidation->setShowErrorMessage(true);
                    $uomValidation->setShowDropDown(true);
                    $uomValidation->setErrorTitle('Invalid Unit of Measure');
                    $uomValidation->setError('Please select a unit of measure from the dropdown list.');
                    $uomValidation->setPromptTitle('Select Unit of Measure');
                    $uomValidation->setPrompt('Choose from the unit of measure list');
                    $uomValidation->setFormula1('=Options!$C$1:$C$' . count($this->unitsOfMeasure));
                    $sheet->setDataValidation("O2:O{$maxRow}", $uomValidation);
                }

                // Dimension Unit Validation (Column T)
                if (!empty($this->dimensionUnits)) {
                    $dimValidation = new DataValidation();
                    $dimValidation->setType(DataValidation::TYPE_LIST);
                    $dimValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $dimValidation->setAllowBlank(true);
                    $dimValidation->setShowInputMessage(true);
                    $dimValidation->setShowErrorMessage(true);
                    $dimValidation->setShowDropDown(true);
                    $dimValidation->setErrorTitle('Invalid Dimension Unit');
                    $dimValidation->setError('Please select a dimension unit from the dropdown list.');
                    $dimValidation->setPromptTitle('Select Dimension Unit');
                    $dimValidation->setPrompt('Choose from the dimension unit list');
                    $dimValidation->setFormula1('=Options!$D$1:$D$' . count($this->dimensionUnits));
                    $sheet->setDataValidation("T2:T{$maxRow}", $dimValidation);
                }

                // Weight Unit Validation (Column V)
                if (!empty($this->weightUnits)) {
                    $weightValidation = new DataValidation();
                    $weightValidation->setType(DataValidation::TYPE_LIST);
                    $weightValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $weightValidation->setAllowBlank(true);
                    $weightValidation->setShowInputMessage(true);
                    $weightValidation->setShowErrorMessage(true);
                    $weightValidation->setShowDropDown(true);
                    $weightValidation->setErrorTitle('Invalid Weight Unit');
                    $weightValidation->setError('Please select a weight unit from the dropdown list.');
                    $weightValidation->setPromptTitle('Select Weight Unit');
                    $weightValidation->setPrompt('Choose from the weight unit list');
                    $weightValidation->setFormula1('=Options!$E$1:$E$' . count($this->weightUnits));
                    $sheet->setDataValidation("V2:V{$maxRow}", $weightValidation);
                }
            },
        ];
    }
}
