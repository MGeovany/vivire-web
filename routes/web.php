<?php

use App\Http\Controllers\EntryController;
use App\Http\Controllers\UploadController;
use App\Livewire\Journal;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('journal')
        : response()->view('landing');
})->name('home');

Route::get('/journal', Journal::class)
    ->middleware('auth')
    ->name('journal');

Route::get('/sitemap.xml', function () {
    $url = rtrim(config('seo.url'), '/');

    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>{$url}/</loc>
  </url>
</urlset>
XML;

    return response($xml, 200)
        ->header('Content-Type', 'application/xml; charset=UTF-8');
});

Route::middleware('auth')->group(function () {
    Route::post('/entries', [EntryController::class, 'store'])->name('entries.store');
    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');
});

require __DIR__.'/auth.php';
