<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class PincodeImport implements ToArray
{
    public $rows = [];

    public function array(array $array)
    {
        $this->rows = $array;
    }
}
