<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Keyword extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'keyword',
        'priority',
        'location_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(Ranking::class);
    }

    public function latestRanking(): HasOne
    {
        return $this->hasOne(Ranking::class)->latestOfMany('checked_date');
    }
}
