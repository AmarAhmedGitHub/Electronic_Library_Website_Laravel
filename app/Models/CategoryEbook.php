<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryEbook extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function ebooks(){
        return $this->hasMany(EBook::class);
    }
}
