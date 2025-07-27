<?php

namespace App\Exports;

use App\Models\CategoryEbook;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoryEBooksExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $cat= CategoryEbook::all();
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
