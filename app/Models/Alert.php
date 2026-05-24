<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'store_id',
        'keyword_id',
        'threshold',
        'enabled',
        'last_alerted_at',
    ];

    protected function casts(): array
    {
        return [
            'threshold' => 'integer',
            'enabled' => 'boolean',
            'last_alerted_at' => 'datetime',
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
}
