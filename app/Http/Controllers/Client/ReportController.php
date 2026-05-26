<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $reports = Report::where('company_id', $user->company_id)
            ->with('store')
            ->orderByDesc('period_start')
            ->paginate(20);

        return view('client.reports.index', [
            'reports' => $reports,
        ]);
    }

    public function download(Report $report): Response
    {
        $user = Auth::user();

        // 権限チェック：自社のレポートのみ
        abort_unless($report->company_id === $user->company_id, 403);
        abort_if(! $report->file_path, 404, 'レポート PDF はまだ保存されていません');
        abort_unless(Storage::disk('local')->exists($report->file_path), 404);

        $filename = sprintf(
            'monthly-report_%s_%s.pdf',
            $report->store?->name ?? 'unknown',
            $report->period_start?->format('Ym') ?? 'na',
        );

        return Storage::disk('local')->download($report->file_path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
