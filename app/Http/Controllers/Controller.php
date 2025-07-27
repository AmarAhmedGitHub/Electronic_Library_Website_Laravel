<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function saveFile($file, $path)
    {
        if (!$file) {
            return null;
        }


        // save image
        $pathr = Storage::disk('ebook')->put($path, $file);

        //return the path
        // Url is the base url exp: localhost:8000
        return $pathr;
    }
    public function deleteFile($file)
    {



        if (Storage::disk('ebook')->exists($file)) {
            Storage::disk('ebook')->delete($file);

        }
        
    }
}
