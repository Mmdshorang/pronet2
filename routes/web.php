<?php

use Illuminate\Support\Facades\Route;

Route::get('/favicon.ico', function () {
    return response('', 204);
});

Route::get('/', function () {
    return "salam";
});

