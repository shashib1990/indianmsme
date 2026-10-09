<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\ProductSampleDataSheet;
use App\Exports\Sheets\ProductOptionsSheet;
use App\Models\Category;
use App\Models\Brand;
use App\Models\UnitOfMeasure;

class ProductSampleExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $categories = Category::active()->orderBy('name', 'asc')->pluck('name')->toArray();
        if (empty($categories)) {
            $categories = ['General'];
        }

        $brands = Brand::active()->orderBy('name', 'asc')->pluck('name')->toArray();
        if (empty($brands)) {
            $brands = ['Generic'];
        }

        $unitsOfMeasure = UnitOfMeasure::getUnitsList();
        $dimensionUnits = ['cm', 'inch', 'mm'];
        $weightUnits = ['kg', 'gm', 'mg'];

        return [
            new ProductSampleDataSheet(
                $categories,
                $brands,
                $unitsOfMeasure,
                $dimensionUnits,
                $weightUnits
            ),
            new ProductOptionsSheet(
                $categories,
                $brands,
                $unitsOfMeasure,
                $dimensionUnits,
                $weightUnits
            ),
        ];
    }
}
