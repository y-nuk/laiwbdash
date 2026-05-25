<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportSchedule extends Model
{
    use SoftDeletes;

    public const RECURRENCE_ONCE = 'once';
    public const RECURRENCE_MONTHLY = 'monthly';
    public const RECURRENCE_WEEKLY = 'weekly';

    public const RECURRENCES = [
        self::RECURRENCE_MONTHLY => '月次（繰り返し）',
        self::RECURRENCE_WEEKLY => '週次（繰り返し）',
        self::RECURRENCE_ONCE => '任意日時（1 回のみ）',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_ACTIVE => '配信予約中',
        self::STATUS_PAUSED => '停止中',
        self::STATUS_CANCELLED => 'キャンセル',
    ];

    protected $fillable = [
        'store_id',
        'name',
        'recurrence',
        'scheduled_at',
        'recurrence_day',
        'recipients',
        'subject',
        'body',
        'status',
        'last_sent_at',
        'next_run_at',
        'admin_comment',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'next_run_at' => 'datetime',
            'recurrence_day' => 'integer',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * カンマ区切りの recipients を配列で返す。
     *
     * @return list<string>
     */
    public function recipientList(): array
    {
        return collect(explode(',', $this->recipients ?? ''))
            ->map(fn ($e) => trim($e))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * 次回送信予定日時を計算して next_run_at に保存。
     */
    public function calculateNextRun(?CarbonImmutable $now = null): ?CarbonImmutable
    {
        $now ??= CarbonImmutable::now();

        $next = match ($this->recurrence) {
            self::RECURRENCE_ONCE => $this->scheduled_at
                ? CarbonImmutable::instance($this->scheduled_at)
                : null,
            self::RECURRENCE_MONTHLY => $this->nextMonthlyRun($now),
            self::RECURRENCE_WEEKLY => $this->nextWeeklyRun($now),
            default => null,
        };

        return $next;
    }

    private function nextMonthlyRun(CarbonImmutable $now): CarbonImmutable
    {
        $day = max(1, min(31, $this->recurrence_day ?? 1));
        $candidate = $now->setTime(9, 0)->startOfDay()->setHour(9);
        $thisMonth = $candidate->day(min($day, $now->daysInMonth));
        if ($thisMonth->isFuture()) {
            return $thisMonth;
        }
        $next = $candidate->addMonth()->startOfMonth();
        return $next->day(min($day, $next->daysInMonth));
    }

    private function nextWeeklyRun(CarbonImmutable $now): CarbonImmutable
    {
        $dow = $this->recurrence_day ?? 1; // 0=日〜6=土、月曜デフォルト
        $candidate = $now->setTime(9, 0);
        while ($candidate->dayOfWeek !== $dow || $candidate->lte($now)) {
            $candidate = $candidate->addDay();
        }
        return $candidate;
    }
}
