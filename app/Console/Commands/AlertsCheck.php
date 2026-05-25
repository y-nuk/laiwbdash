<?php

namespace App\Console\Commands;

use App\Services\Alert\AlertChecker;
use Illuminate\Console\Command;

class AlertsCheck extends Command
{
    protected $signature = 'alerts:check';

    protected $description = '有効な順位アラートをスキャンし、しきい値超えのものをメール通知';

    public function handle(AlertChecker $checker): int
    {
        $results = $checker->checkAll();

        if (empty($results)) {
            $this->info('発報対象なし。');
            return self::SUCCESS;
        }

        $this->info('発報件数：' . count($results));
        foreach ($results as $r) {
            $a = $r['alert'];
            $this->line("  [#{$a->id} {$a->name}] 店舗={$a->store->name} / 該当 KW=" . count($r['triggers']));
        }
        return self::SUCCESS;
    }
}
