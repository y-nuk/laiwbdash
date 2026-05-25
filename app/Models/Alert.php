<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alert extends Model
{
    use SoftDeletes;

    public const TYPE_RANKING_DROP = 'ranking_drop';
    public const TYPE_OUT_OF_RANK = 'out_of_rank';
    public const TYPE_WORSE_THAN = 'worse_than';

    public const TYPES = [
        self::TYPE_RANKING_DROP => '順位下落（前回比で N 位以上下落）',
        self::TYPE_OUT_OF_RANK => '圏外転落（順位データが取れなくなった）',
        self::TYPE_WORSE_THAN => '順位悪化（現在順位が N 位より悪い）',
    ];

    protected $fillable = [
        'store_id',
        'keyword_id',
        'name',
        'alert_type',
        'threshold',
        'recipients',
        'enabled',
        'last_alerted_at',
        'last_check_at',
        'admin_comment',
    ];

    protected function casts(): array
    {
        return [
            'threshold' => 'integer',
            'enabled' => 'boolean',
            'last_alerted_at' => 'datetime',
            'last_check_at' => 'datetime',
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

    /**
     * recipients をカンマ区切り → 配列に変換。空なら管理者メアドにフォールバック。
     *
     * @return list<string>
     */
    public function recipientList(): array
    {
        $list = collect(explode(',', $this->recipients ?? ''))
            ->map(fn ($e) => trim($e))
            ->filter()
            ->values()
            ->all();
        return $list ?: [env('ADMIN_EMAIL', 'admin@laiweb-dash.com')];
    }
}
