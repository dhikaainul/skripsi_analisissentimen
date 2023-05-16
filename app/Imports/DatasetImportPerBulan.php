<?php

namespace App\Imports;

use App\DatasetPerBulan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DatasetImportPerBulan implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
        return new DatasetPerBulan([
            'author' => isset($row['author']) ? trim($row['author']) : '',
            'text' => isset($row['text']) ? trim($row['text']) : '',
            'month' => isset($row['month']) ? trim($row['month']) : '',
            'date' => isset($row['date']) ? trim($row['date']) : '',
        ]);
    }
}
