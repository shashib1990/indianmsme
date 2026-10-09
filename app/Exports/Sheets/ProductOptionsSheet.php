<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductOptionsSheet implements FromArray, WithTitle, WithEvents
{
    protected array $categories;
    protected array $brands;
    protected array $unitsOfMeasure;
    protected array $dimensionUnits;
    protected array $weightUnits;

    public function __construct(
        array $categories = [],
        array $brands = [],
        array $unitsOfMeasure = [],
        array $dimensionUnits = [],
        array $weightUnits = []
    ) {
        $this->categories = array_values($categories);
        $this->brands = array_values($brands);
        $this->unitsOfMeasure = array_values($unitsOfMeasure);
        $this->dimensionUnits = array_values($dimensionUnits);
        $this->weightUnits = array_values($weightUnits);
    }

    public function title(): string
    {
        return 'Options';
    }

    public function array(): array
    {
        $maxCount = max(
            count($this->categories),
            count($this->brands),
            count($this->unitsOfMeasure),
            count($this->dimensionUnits),
            count($this->weightUnits)
        );

        if ($maxCount === 0) {
            return [];
        }

        $rows = [];
        for ($i = 0; $i < $maxCount; $i++) {
            $rows[] = [
                $this->categories[$i] ?? '',
                $this->brands[$i] ?? '',
                $this->unitsOfMeasure[$i] ?? '',
                $this->dimensionUnits[$i] ?? '',
                $this->weightUnits[$i] ?? '',
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
            },
        ];
    }
}
