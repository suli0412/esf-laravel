<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TeilnehmerController;
Route::resource('teilnehmer', TeilnehmerController::class);

Route::get('/', function () {
    return view('welcome');
});
