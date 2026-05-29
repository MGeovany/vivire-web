<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'entry_date',
        'section',
        'blocks',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'blocks' => 'array',
    ];
}
