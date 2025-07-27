<?php

namespace App\Imports;

use App\Models\CategoryBook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoryBooksImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new CategoryBook([
            'name'          => $row['name'],
            'description'     => $row['description'],
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
