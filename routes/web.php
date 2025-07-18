<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClipController;
use App\Http\Controllers\VideoController;

Route::get('/', function () {
    return view('welcome');
});

/* ---------- форма пошуку ---------- */
Route::get ('/clip/search',  [ClipController::class, 'showForm'])->name('clip.form');
Route::post('/clip/search',  [ClipController::class, 'searchUserAndRedirect'])->name('clip.get');
Route::get ('/clips/result/{username}', [ClipController::class, 'getClips'])->name('clip.result');
Route::post('/clip/download', [ClipController::class, 'download'])->name('clip.download');

/* 2. Готові кліпи (status = ready) */
Route::get ('/clips',          [ClipController::class, 'index'])->name('clips.index');
Route::get ('/clips/{clip}',   [ClipController::class, 'show'])->name('clips.show');
Route::put ('/clips/{clip}/srt',[ClipController::class, 'updateSrt'])->name('clips.srt');


Route::post('/clips/{clip}/hardsubs', [ClipController::class, 'generateHardSubs'])
    ->name('clips.hardsubs');


Route::get('/clips/{clip}/download', [ClipController::class, 'downloadHardSub'])
    ->name('clips.download');

