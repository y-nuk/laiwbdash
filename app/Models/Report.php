<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    public const TYPE_MONTHLY = 'monthly';
    public const TYPE_WEEKLY = 'weekly';
    public const TYPE_ON_DEMAND = 'on_demand';

    public const TYPES = [
        self::TYPE_MONTHLY => '月次',
        self::TYPE_WEEKLY => '週次',
        self::TYPE_ON_DEMAND => '即時',
    ];

    protected $fillable = [
        'company_id',
        'store_id',
        'type',
        'period_start',
        'period_end',
        'file_path',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'sent_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
