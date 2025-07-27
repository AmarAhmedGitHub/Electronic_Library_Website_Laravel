<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new User([
            'name'          => $row['name'],
            'username'     => $row['username'],
            'specialty'     => $row['specialty'],
            'level'     => $row['level'],
            'status'     => 1,
            'password'     => $row['password']
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
