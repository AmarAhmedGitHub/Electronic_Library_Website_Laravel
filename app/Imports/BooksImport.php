<?php

namespace App\Imports;

use App\Models\Book;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BooksImport implements ToModel, WithChunkReading, ShouldQueue,WithHeadingRow
{

    public function model(array $row)
    {
        return new Book([
            'name'          => $row['name'],
            'issn'     => $row['issn'],
            'author'   => $row['author'],
            'publisher'      => $row['publisher'],
            'language'         => $row['language'],
            'quantity'         => $row['quantity'],
            'status'         => $row['status'],
            'year'         => $row['date_publish'],
            'category_book_id'         => $row['category'],
        ]);
    }




    public function chunkSize(): int
    {
        return 1000;
    }
}
