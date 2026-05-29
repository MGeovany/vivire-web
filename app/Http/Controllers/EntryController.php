<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EntryController extends Controller
{
    /** Upsert one section's blocks for a given date. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'entry_date' => ['required', 'date_format:Y-m-d'],
            'section'    => ['required', Rule::in(Entry::SECTIONS)],
            'blocks'     => ['present', 'array'],
        ]);

        Entry::updateOrCreate(
            [
                'user_id'    => $request->user()->id,
                'entry_date' => $data['entry_date'],
                'section'    => $data['section'],
            ],
            ['blocks' => $data['blocks']],
        );

        return response()->json(['ok' => true]);
    }
}
