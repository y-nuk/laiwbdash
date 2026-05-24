<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Services\Ranking\RandomRankingFetcher;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RankingsFetch extends Command
{
    protected $signature = 'rankings:fetch
                            {--backfill=0 : 過去何日分まで遡って生成するか（0 = 今日のみ）}
                            {--store= : 特定 store_id のみ}';

    protected $description = '計測キーワードの順位を取得して rankings テーブルに保存（スタブ：RandomRankingFetcher）';

    public function handle(RandomRankingFetcher $fetcher): int
    {
        $backfill = (int) $this->option('backfill');
        $storeId = $this->option('store');

        $query = Keyword::query()->where('is_active', true);
        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $keywords = $query->get();

        if ($keywords->isEmpty()) {
            $this->warn('稼働中の計測キーワードがありません。');
            return self::SUCCESS;
        }

        $this->info(sprintf('keywords: %d, backfill: %d days', $keywords->count(), $backfill));
        $bar = $this->output->createProgressBar($keywords->count() * ($backfill + 1));

        foreach ($keywords as $kw) {
            for ($i = $backfill; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $fetcher->fetchAndStore($kw, $date);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('順位取得完了。');

        return self::SUCCESS;
    }
}
