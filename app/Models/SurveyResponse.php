<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    protected $fillable = [
        'survey_id',
        'responses',
        'overall_rating',
        'ip_address',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'responses' => 'array',
            'overall_rating' => 'integer',
            'answered_at' => 'datetime',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }
}
