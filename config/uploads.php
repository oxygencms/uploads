<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Upload intents
    |--------------------------------------------------------------------------
    |
    | A list of upload intents to choose from for an upload.
    |
    */

    'intents' => [
        'background' => 'Background',
        'preview' => 'Preview',
        'gallery' => 'Gallery',
        'carousel' => 'Carousel',
        'special' => 'Special',
        'other' => 'Other',
    ],

    'dropzone_options' => [
        'url' => env('APP_URL') . '/admin/upload',
        'thumbnailWidth' => 150,
        'maxFilesize' => 1,
//            'headers' => [
//                'X-CSRF-TOKEN' => csrf_token(),
//            ],
    ],

];