<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entry extends Model
{
    /** Valid section identifiers. */
    public const SECTIONS = ['feelings', 'thoughts', 'reflections', 'year1', 'year2', 'year3'];

    protected $fillable = [
        'user_id',
        'entry_date',
        'section',
        'blocks',
    ];

    // entry_date stays a plain 'Y-m-d' string so date-keyed upserts match exactly.
    protected $casts = [
        'blocks' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
