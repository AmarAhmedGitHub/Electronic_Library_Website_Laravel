<?php

namespace App\Exports;

use App\Models\CategoryBook;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoryBooksExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $cat= CategoryBook::all();
        return $cat;
    }
    public function map($cat): array
    {
        return [
            $cat->id,
            $cat->name,
            $cat->description,
            
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description'
            
        ];
    }
}
