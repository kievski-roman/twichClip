<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClipController;
use App\Http\Controllers\VideoController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/clips', [ClipController::class, 'showForm'])->name('clip.form');

Route::post('/clips', [ClipController::class, 'searchUserAndRedirect'])->name('clip.get');

Route::get('/clips/result/{username}', [ClipController::class, 'getClips'])->name('clip.result');

Route::post('/clip/download', [ClipController::class, 'download'])->name('clip.download');



