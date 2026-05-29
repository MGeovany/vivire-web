<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UploadController extends Controller
{
    /** Store an uploaded media file and return its public URL. */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200'], // 50 MB
            'type' => ['nullable', Rule::in(['image', 'audio', 'video', 'document'])],
        ]);

        $file = $request->file('file');
        $type = $request->input('type', 'document');
        $ext  = $file->getClientOriginalExtension();
        $name = Str::random(16) . ($ext ? ".{$ext}" : '');
        $path = "{$request->user()->id}/{$type}/{$name}";

        $disk = Storage::disk(config('filesystems.media', 'public'));
        $disk->put($path, file_get_contents($file->getRealPath()), 'public');

        return response()->json([
            'url'  => $disk->url($path),
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);
    }
}
