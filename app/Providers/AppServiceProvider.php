<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()

    {
        
        ini_set("memory_limit", "256M");
        ini_set('post_max_size', '128M');
        ini_set('upload_max_filesize', '64M');
        Schema::defaultStringLength(191);
        if(request()->is('admin/*')){
            if(app()->getLocale()=='ar'){
               Paginator::defaultView('vendor.pagination.custom-rtl'); 
            }elseif(app()->getLocale()=='en'){
                 Paginator::defaultView('vendor.pagination.custom');
                 
            }
           
       }
        

    }
}
