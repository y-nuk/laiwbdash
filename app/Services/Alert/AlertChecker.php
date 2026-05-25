<?php

namespace App\Services\Alert;

use App\Mail\RankingDropAlertMail;
use App\Models\Alert;
use App\Models\Ranking;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * 順位アラートの判定 + メール送信。
 *
 * 想定呼び出しタイミング：
 *  - artisan rankings:fetch の直後（順位データ取得後）
 *  - 手動 artisan alerts:check
 *
 * 判定ロジック：
 *  - ranking_drop: 当日順位 - 前日順位 >= threshold（圏外は最低値扱い）
 *  - out_of_rank:  当日が NULL（圏外）かつ 前日が NULL じゃない
 *  - worse_than:   当日順位 > threshold
 */
class AlertChecker
{
    /**
     * 有効なアラートを全部チェック。発火したものを返す。
     *
     * @return array<int, array{alert:Alert, triggers:array}>
     */
    public function checkAll(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $results = [];

        Alert::where('enabled', true)
            ->with(['store.company', 'keyword'])
            ->chunkById(50, function ($alerts) use ($now, &$results) {
                foreach ($alerts as $alert) {
                    $triggers = $this->checkOne($alert, $now);
                    if ($triggers) {
                        $this->notify($alert, $triggers);
                        $results[] = ['alert' => $alert, 'triggers' => $triggers];
                    }
                    $alert->update(['last_check_at' => $now]);
                }
            });

        return $results;
    }

    /**
     * 単一アラートをチェック、発火対象 KW のリストを返す。
     *
     * @return array<int, array{keyword:string, prev:int|null, curr:int|null, drop:int|null}>
     */
    private function checkOne(Alert $alert, CarbonImmutable $now): array
    {
        $keywordIds = $alert->keyword_id
            ? [$alert->keyword_id]
            : $alert->store->keywords()->where('is_active', true)->pluck('id')->all();

        $triggers = [];

        foreach ($keywordIds as $kwId) {
            $latestTwo = Ranking::where('keyword_id', $kwId)
                ->orderByDesc('checked_date')
                ->limit(2)
                ->get();

            if ($latestTwo->count() < 2) {
                continue;
            }

            [$curr, $prev] = [$latestTwo[0], $latestTwo[1]];

            $shouldTrigger = match ($alert->alert_type) {
                Alert::TYPE_RANKING_DROP => $this->isRankingDrop($prev->position, $curr->position, $alert->threshold),
                Alert::TYPE_OUT_OF_RANK => $prev->position !== null && $curr->position === null,
                Alert::TYPE_WORSE_THAN => $curr->position !== null && $curr->position > $alert->threshold,
                default => false,
            };

            if ($shouldTrigger) {
                $triggers[] = [
                    'keyword' => $curr->keyword->keyword ?? '(KW)',
                    'prev' => $prev->position,
                    'curr' => $curr->position,
                    'drop' => ($prev->position !== null && $curr->position !== null)
                        ? $curr->position - $prev->position : null,
                ];
            }
        }

        return $triggers;
    }

    private function isRankingDrop(?int $prev, ?int $curr, int $threshold): bool
    {
        // 圏外（null）を最低位 999 として比較
        $prevPos = $prev ?? 999;
        $currPos = $curr ?? 999;
        return ($currPos - $prevPos) >= $threshold && $threshold > 0;
    }

    private function notify(Alert $alert, array $triggers): void
    {
        try {
            Mail::send(new RankingDropAlertMail($alert, $triggers));
            $alert->update(['last_alerted_at' => now()]);
        } catch (Throwable $e) {
            Log::error('AlertChecker mail failed', [
                'alert_id' => $alert->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
