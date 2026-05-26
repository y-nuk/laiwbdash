<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use SoftDeletes;

    public const TYPE_CALL = 'call';
    public const TYPE_VISIT = 'visit';
    public const TYPE_EMAIL = 'email';
    public const TYPE_LINE = 'line';
    public const TYPE_NOTE = 'note';
    public const TYPE_OTHER = 'other';

    public const TYPES = [
        self::TYPE_CALL => '電話',
        self::TYPE_VISIT => '訪問',
        self::TYPE_EMAIL => 'メール',
        self::TYPE_LINE => 'LINE',
        self::TYPE_NOTE => 'メモ',
        self::TYPE_OTHER => 'その他',
    ];

    public const TYPE_ICONS = [
        self::TYPE_CALL => 'bi-telephone',
        self::TYPE_VISIT => 'bi-geo-alt',
        self::TYPE_EMAIL => 'bi-envelope',
        self::TYPE_LINE => 'bi-chat',
        self::TYPE_NOTE => 'bi-sticky',
        self::TYPE_OTHER => 'bi-three-dots',
    ];

    protected $fillable = [
        'company_id',
        'user_id',
        'type',
        'title',
        'body',
        'occurred_at',
        'follow_up_at',
        'follow_up_done',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'follow_up_at' => 'date',
            'follow_up_done' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
