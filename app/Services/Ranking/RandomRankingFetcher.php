<?php

namespace App\Services\Ranking;

use App\Models\Keyword;
use App\Models\Ranking;
use Carbon\CarbonInterface;

/**
 * GBP API 申請通過前のスタブ実装。
 * - 80% の確率で 1〜30 位の数値を返す
 * - 20% の確率で null（圏外）を返す
 * - keyword の priority に応じて上位寄りにバイアス
 *
 * API 通過後は GbpRankingFetcher 等に差し替え予定。
 */
class RandomRankingFetcher implements RankingFetcher
{
    public function fetch(Keyword $keyword, CarbonInterface $date): ?int
    {
        // 圏外の確率（priority が低いほど圏外になりやすい）
        $outOfRankRate = match (true) {
            $keyword->priority <= 1 => 5,   // 5%
            $keyword->priority === 2 => 15, // 15%
            $keyword->priority === 3 => 25, // 25%
            default => 40,                  // 40%
        };

        if (random_int(1, 100) <= $outOfRankRate) {
            return null;
        }

        // 上限位置（priority に応じてバイアス）
        $maxPosition = match (true) {
            $keyword->priority <= 1 => 10,
            $keyword->priority === 2 => 20,
            default => 30,
        };

        return random_int(1, $maxPosition);
    }

    public function fetchAndStore(Keyword $keyword, CarbonInterface $date): Ranking
    {
        $position = $this->fetch($keyword, $date);

        return Ranking::updateOrCreate(
            ['keyword_id' => $keyword->id, 'checked_date' => $date->toDateString()],
            [
                'store_id' => $keyword->store_id,
                'position' => $position,
                'source_type' => Ranking::SOURCE_MANUAL,
            ],
        );
    }
}
