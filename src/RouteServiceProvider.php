<?php

namespace Oxygencms\Uploads;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the pages.
     *
     * @return void
     */
    public function map()
    {
        Route::namespace('Oxygencms\Uploads\Controllers')->middleware('web')->group(function () {
            // Uploads
            Route::resource('upload', 'UploadController', ['only' => ['store', 'update', 'destroy']]);
            Route::get('upload/list/{model_name}/{model_id}', 'UploadController@uploadsList')
                 ->name('upload.list');
        });
    }
}
