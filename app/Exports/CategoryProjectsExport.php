<?php

namespace App\Exports;

use App\Models\CategoryProject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoryProjectsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $cat= CategoryProject::all();
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
