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
        // If PHP rejects the multipart payload due to ini limits
        // (upload_max_filesize/post_max_size), the file won't be present at all.
        // Provide a clear error instead of a confusing "file is required".
        if (! $request->hasFile('file')) {
            $contentLength = (int) ($request->server('CONTENT_LENGTH') ?? 0);
            $postMax = $this->iniBytes((string) ini_get('post_max_size'));
            $uploadMax = $this->iniBytes((string) ini_get('upload_max_filesize'));

            if ($contentLength > 0 && ($postMax > 0 && $contentLength > $postMax)) {
                return response()->json([
                    'message' => 'El archivo es demasiado grande para este servidor. Aumenta post_max_size/upload_max_filesize.',
                ], 413);
            }

            return response()->json([
                'message' => $uploadMax > 0
                    ? 'No se recibio el archivo. Puede que exceda el limite del servidor (upload_max_filesize).'
                    : 'No se recibio el archivo.',
            ], 422);
        }

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

    private function iniBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') return -1;
        $unit = strtolower(substr($value, -1));
        $num = (int) $value;

        return match ($unit) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => (int) $value,
        };
    }
}
