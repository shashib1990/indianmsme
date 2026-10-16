<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class AdcLocalProductImport implements ToArray
{
    public $rows = [];

    public function array(array $array)
    {
        if (empty($this->rows)) {
            $this->rows = $array;
        }
    }
}
