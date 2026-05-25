<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Store;
use App\Services\Report\MonthlyReportData;
use App\Services\Report\MpdfFactory;
use App\Services\Report\QuickChartRenderer;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(
        private readonly MpdfFactory $mpdfFactory,
        private readonly QuickChartRenderer $chart,
    ) {}

    /**
     * 即時出力フォーム表示。
     */
    public function create(): View
    {
        $companies = Company::orderBy('name')->get();

        return view('admin.reports.output', [
            'companies' => $companies,
            'currentMonth' => now()->subMonth()->format('Y-m'),
        ]);
    }

    /**
     * HTML プレビュー（PDF と同じ内容を画面で確認）。
     */
    public function preview(Request $request): View
    {
        $data = $this->buildData($request);

        return view('reports.monthly-pdf', $data);
    }

    /**
     * PDF を生成してダウンロード。
     */
    public function download(Request $request): Response
    {
        $data = $this->buildData($request);

        $mpdf = $this->mpdfFactory->createA4Portrait();
        $html = view('reports.monthly-pdf', $data)->render();
        $mpdf->WriteHTML($html);

        $store = $data['data']->store;
        $period = $data['data']->month->format('Ym');
        $filename = "monthly-report_{$store->company->name}_{$store->name}_{$period}.pdf";

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . rawurlencode($filename) . '"',
        ]);
    }

    /**
     * フォーム入力から PDF/プレビュー描画用データを組み立てる。
     *
     * @return array{data:MonthlyReportData, kpis:array, charts:array, matrix:array, comment:?string}
     */
    private function buildData(Request $request): array
    {
        $validated = $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
            'month' => ['required', 'date_format:Y-m'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $store = Store::with('company')->findOrFail($validated['store_id']);
        $month = CarbonImmutable::createFromFormat('Y-m-d', $validated['month'] . '-01');

        $data = new MonthlyReportData($store, $month);
        $matrix = $data->keywordHistoryMatrix();

        return [
            'data' => $data,
            'kpis' => $data->kpis(),
            'charts' => [
                'avg_rank' => $this->chart->averageRankLine($data->dailyAverageRank()),
                'keyword_history' => $this->chart->keywordHistoryLine($matrix),
            ],
            'matrix' => $matrix,
            'comment' => $validated['comment'] ?? null,
        ];
    }
}
