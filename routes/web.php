<?php

use App\Http\Controllers\EntryController;
use App\Http\Controllers\UploadController;
use App\Livewire\Journal;
use Illuminate\Support\Facades\Route;

Route::get('/', Journal::class)
    ->middleware('auth')
    ->name('journal');

Route::middleware('auth')->group(function () {
    Route::post('/entries', [EntryController::class, 'store'])->name('entries.store');
    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');
});

require __DIR__.'/auth.php';
