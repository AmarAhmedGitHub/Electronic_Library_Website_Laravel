<?php

namespace App\Imports;

use App\Models\Project;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProjectsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Project([
            'name'          => $row['name'],
            'supervisor'     => $row['supervisor'],
            'language'     => $row['language'],
            'year'     => $row['year'],
            'status'     => $row['status'],
            'file_path'     => Storage::disk('ebook')->putFile('book', $row['file_path']),
            'category_project_id'     => $row['category_project_id'],
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
