<?php

namespace App\Imports;

use App\Models\EBook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EBooksImport implements ToModel, WithChunkReading, ShouldQueue,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new EBook([
            'name'          => $row['name'],
            'issn'     => $row['issn'],
            'author'   => $row['author'],
            'publisher'      => $row['publisher'],
            'language'         => $row['language'],
            'status'         => $row['status'],
            'year'         => $row['date_publish'],
            'path'         => Storage::disk('ebook')->putFile('book', $row['path']),
            'category_ebook_id'         => $row['category'],
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
