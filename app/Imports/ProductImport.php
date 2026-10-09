<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class ProductImport implements ToArray
{
    public $rows = [];

    public function array(array $array)
    {
        // Only capture data from the primary product sheet (sheet index 0)
        if (empty($this->rows)) {
            $this->rows = $array;
        }
    }
}
