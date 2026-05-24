<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ranking extends Model
{
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_API = 'api';

    protected $fillable = [
        'store_id',
        'keyword_id',
        'position',
        'source_type',
        'checked_date',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'checked_date' => 'date',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }

    public function isOutOfRank(): bool
    {
        return $this->position === null;
    }
}
