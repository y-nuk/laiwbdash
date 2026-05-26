<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_ACTIVE => '契約中',
        self::STATUS_SUSPENDED => '一時停止',
        self::STATUS_CANCELLED => '解約済',
    ];

    protected $fillable = [
        'agency_id',
        'name',
        'kana',
        'contact_person_name',
        'email',
        'phone',
        'fax',
        'postal_code',
        'address',
        'industry',
        'status',
        'logo_path',
        'admin_message',
        'admin_message_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'admin_message_updated_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
