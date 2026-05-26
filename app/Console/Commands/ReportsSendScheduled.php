<?php

namespace App\Console\Commands;

use App\Mail\MonthlyReportMail;
use App\Models\Report;
use App\Models\ReportSchedule;
use App\Services\Report\MonthlyReportData;
use App\Services\Report\MpdfFactory;
use App\Services\Report\QuickChartRenderer;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ReportsSendScheduled extends Command
{
    protected $signature = 'reports:send-scheduled
        {--dry-run : 送信せずに対象だけ表示}
        {--schedule= : 特定の schedule_id のみ実行}';

    protected $description = '配信予約に従って月次レポート PDF をメール送信';

    public function handle(MpdfFactory $mpdfFactory, QuickChartRenderer $chart): int
    {
        $query = ReportSchedule::with('store.company')
            ->where('status', ReportSchedule::STATUS_ACTIVE)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now());

        if ($id = $this->option('schedule')) {
            $query->where('id', $id);
        }

        $schedules = $query->get();

        if ($schedules->isEmpty()) {
            $this->info('配信対象の予約はありません。');
            return self::SUCCESS;
        }

        $this->info("配信対象：{$schedules->count()} 件");
        $sent = 0;
        $failed = 0;

        foreach ($schedules as $schedule) {
            $label = "[#{$schedule->id} {$schedule->name} / {$schedule->store->name}]";

            if ($this->option('dry-run')) {
                $this->line("DRY-RUN: {$label} -> {$schedule->recipients}");
                continue;
            }

            try {
                $this->sendOne($schedule, $mpdfFactory, $chart);
                $sent++;
                $this->info("✓ {$label} 送信完了");
            } catch (Throwable $e) {
                $failed++;
                Log::error("reports:send-scheduled failed for schedule {$schedule->id}", [
                    'exception' => $e->getMessage(),
                ]);
                $this->error("✗ {$label} 失敗: {$e->getMessage()}");
            }
        }

        $this->info("結果：成功 {$sent} 件 / 失敗 {$failed} 件");
        return self::SUCCESS;
    }

    private function sendOne(ReportSchedule $schedule, MpdfFactory $mpdfFactory, QuickChartRenderer $chart): void
    {
        $store = $schedule->store;
        // 配信時点の「前月」をレポート対象月とする
        $reportMonth = CarbonImmutable::now()->subMonth()->startOfMonth();
        $data = new MonthlyReportData($store, $reportMonth);
        $matrix = $data->keywordHistoryMatrix();

        $viewData = [
            'data' => $data,
            'kpis' => $data->kpis(),
            'charts' => [
                'avg_rank' => $chart->averageRankLine($data->dailyAverageRank()),
                'keyword_history' => $chart->keywordHistoryLine($matrix),
            ],
            'matrix' => $matrix,
            'comment' => $schedule->admin_comment,
        ];

        $mpdf = $mpdfFactory->createA4Portrait();
        $html = view('reports.monthly-pdf', $viewData)->render();
        $mpdf->WriteHTML($html);
        $pdfBinary = $mpdf->Output('', 'S');

        $filename = "monthly-report_{$store->company->name}_{$store->name}_{$reportMonth->format('Ym')}.pdf";

        // PDF を storage に保存（クライアントの過去レポート閲覧用）
        $storagePath = "reports/{$store->company_id}/{$store->id}/{$reportMonth->format('Ym')}.pdf";
        Storage::disk('local')->put($storagePath, $pdfBinary);

        Mail::send(new MonthlyReportMail($schedule, $pdfBinary, $filename));

        // 履歴を reports テーブルに記録
        Report::create([
            'company_id' => $store->company_id,
            'store_id' => $store->id,
            'type' => Report::TYPE_MONTHLY,
            'period_start' => $data->periodStart(),
            'period_end' => $data->periodEnd(),
            'file_path' => $storagePath,
            'sent_at' => now(),
        ]);

        // 次回実行日時を再計算
        $schedule->update([
            'last_sent_at' => now(),
            'next_run_at' => $schedule->calculateNextRun(),
        ]);
    }
}
