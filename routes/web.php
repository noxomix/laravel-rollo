<?php

use Illuminate\Support\Facades\Route;

Route::prefix('rollo')->group(function () {
    Route::get('/', function () {
        return 'Laravel Rollo package is working!';
    })->name('rollo.index');
});