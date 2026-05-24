<?php

namespace App\Services\Ranking;

use App\Models\Keyword;
use App\Models\Ranking;
use Carbon\CarbonInterface;

interface RankingFetcher
{
    public function fetch(Keyword $keyword, CarbonInterface $date): ?int;

    public function fetchAndStore(Keyword $keyword, CarbonInterface $date): Ranking;
}
