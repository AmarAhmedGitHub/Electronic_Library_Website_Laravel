<?php

namespace App\Exports;

use App\Models\Book;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BooksExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
         $book=Book::all();
         return $book;

    }
    public function map($book): array
    {
        return [
            $book->name,
            $book->issn,
            $book->author,
            $book->publisher,
            $book->language,
            $book->quantity,
            $book->status,
            $book->year,
            $book->category_book->id,
            
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'ISSN',
            'Author',
            'Publisher',
            'Language',
            'Quantity',
            'Status',
            'Date Publish',
            'Category',
            
        ];
    }
}
