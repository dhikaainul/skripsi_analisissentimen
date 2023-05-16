<?php


namespace App\Imports;
use App\Dataset;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DatasetImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (isset($row['label'])) {
            if ($row['label'] == 'positif') {
                $kategori = 1;
            } else if ($row['label'] == 'netral') {
                $kategori = 0;
            } else {
                $kategori = 2;
            }
        } else {
            $kategori = '';
        }
        return new Dataset([
            'author' => isset($row['author']) ? trim($row['author']) : '',
            'text' => isset($row['text']) ? trim($row['text']) : '',
            'platform' => isset($row['platform']) ? trim($row['platform']) : '',
            'kategori' => $kategori
        ]);
    }
}
