<?php

namespace App\Services\Report;

use App\Models\Store;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * 月次レポートのデータ集計サービス。
 *
 * 与えられた店舗と月（YYYY-MM-01 を期待）から、レポート生成に必要な
 * KPI / 順位推移 / KW × 日付マトリックス を算出する。
 */
class MonthlyReportData
{
    public function __construct(
        public readonly Store $store,
        public readonly CarbonImmutable $month,
    ) {}

    public function periodStart(): CarbonImmutable
    {
        return $this->month->startOfMonth();
    }

    public function periodEnd(): CarbonImmutable
    {
        return $this->month->endOfMonth();
    }

    public function periodLabel(): string
    {
        return $this->month->format('Y年n月');
    }

    /**
     * 期間内の各キーワードの最新順位から、KPI 4 値を算出。
     *
     * @return array{keyword_count:int, avg_rank:float|null, top3_count:int, out_ranked_count:int}
     */
    public function kpis(): array
    {
        $keywords = $this->activeKeywords();
        $latestPositions = collect();

        foreach ($keywords as $kw) {
            $latest = $kw->rankings()
                ->whereBetween('checked_date', [$this->periodStart(), $this->periodEnd()])
                ->orderByDesc('checked_date')
                ->first();
            if ($latest) {
                $latestPositions->push($latest->position);
            }
        }

        $ranked = $latestPositions->filter(fn ($p) => $p !== null);
        $outRanked = $latestPositions->count() - $ranked->count();

        return [
            'keyword_count' => $keywords->count(),
            'avg_rank' => $ranked->isNotEmpty() ? round($ranked->avg(), 1) : null,
            'top3_count' => $ranked->filter(fn ($p) => $p <= 3)->count(),
            'out_ranked_count' => $outRanked,
        ];
    }

    /**
     * 対象月の月初〜月末の全 KW 平均順位（圏外は集計から除外）。
     *
     * @return array<string, float|null>  キー = 'm/d'、値 = 平均順位（null = データなし）
     */
    public function dailyAverageRank(): array
    {
        $result = [];
        $current = $this->periodStart();
        $end = $this->periodEnd();
        while ($current->lte($end)) {
            $rankings = $this->store->rankings()
                ->whereDate('checked_date', $current->toDateString())
                ->whereNotNull('position')
                ->pluck('position');
            $result[$current->format('m/d')] = $rankings->isNotEmpty() ? round($rankings->avg(), 1) : null;
            $current = $current->addDay();
        }
        return $result;
    }

    /**
     * キーワード別に対象月の日次順位 + 統計値（best/worst/avg/out_count）を返す。
     *
     * @return array<int, array{keyword:string, priority:int, days:array<string, int|null>, best:int|null, worst:int|null, avg:float|null, out_count:int}>
     */
    public function keywordHistoryMatrix(): array
    {
        $matrix = [];
        foreach ($this->activeKeywords() as $kw) {
            $days = [];
            $current = $this->periodStart();
            $end = $this->periodEnd();
            while ($current->lte($end)) {
                $ranking = $kw->rankings()
                    ->whereDate('checked_date', $current->toDateString())
                    ->first();
                $days[$current->format('m/d')] = $ranking?->position;
                $current = $current->addDay();
            }
            $positions = array_filter($days, fn ($p) => $p !== null);
            $matrix[] = [
                'keyword' => $kw->keyword,
                'priority' => $kw->priority,
                'days' => $days,
                'best' => $positions ? min($positions) : null,
                'worst' => $positions ? max($positions) : null,
                'avg' => $positions ? round(array_sum($positions) / count($positions), 1) : null,
                'out_count' => count($days) - count($positions),
            ];
        }
        return $matrix;
    }

    private function activeKeywords(): Collection
    {
        return $this->store->keywords()->where('is_active', true)->orderBy('priority')->get();
    }
}
