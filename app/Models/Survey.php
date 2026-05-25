<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Survey extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'token',
        'title',
        'description',
        'questions',
        'redirect_url',
        'is_active',
        'high_rating_threshold',
        'google_review_url',
        'low_rating_message',
        'thank_you_message',
    ];

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'is_active' => 'boolean',
            'high_rating_threshold' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Survey $survey) {
            if (! $survey->token) {
                $survey->token = Str::random(40);
            }
        });
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function publicUrl(): string
    {
        return url('/survey/' . $this->token);
    }

    /**
     * 星評価分布を 1〜5 で返す。
     *
     * @return array<int, int>
     */
    public function ratingDistribution(): array
    {
        $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($this->responses()->whereNotNull('overall_rating')->pluck('overall_rating') as $r) {
            $dist[(int) $r] = ($dist[(int) $r] ?? 0) + 1;
        }
        return $dist;
    }

    public function averageRating(): ?float
    {
        $avg = $this->responses()->whereNotNull('overall_rating')->avg('overall_rating');
        return $avg !== null ? round($avg, 2) : null;
    }
}
