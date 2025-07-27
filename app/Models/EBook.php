<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class EBook extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function category_ebook(){
        return $this->belongsTo(CategoryEbook::class);
    }

}
