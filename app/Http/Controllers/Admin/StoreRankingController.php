<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\View\View;

class StoreRankingController extends Controller
{
    public function index(Store $store): View
    {
        $days = 30;
        $from = Carbon::today()->subDays($days - 1);

        // 過去 N 日の日付ラベル
        $dates = collect(range(0, $days - 1))
            ->map(fn ($i) => $from->copy()->addDays($i))
            ->values();

        // 稼働中 KW を順位込みで取得（過去 N 日分）
        $keywords = $store->keywords()
            ->where('is_active', true)
            ->with(['rankings' => fn ($q) => $q->where('checked_date', '>=', $from)->orderBy('checked_date')])
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        // Chart.js 用に整形（各 KW の position 配列、null は欠損として渡す）
        $datasets = $keywords->map(function ($kw) use ($dates) {
            $byDate = $kw->rankings->keyBy(fn ($r) => $r->checked_date->toDateString());

            return [
                'label' => $kw->keyword,
                'data' => $dates->map(fn ($d) => $byDate->get($d->toDateString())?->position)->values(),
                'keyword_id' => $kw->id,
                'priority' => $kw->priority,
            ];
        });

        return view('admin.stores.rankings', [
            'store' => $store,
            'days' => $days,
            'dateLabels' => $dates->map(fn ($d) => $d->format('m/d'))->values(),
            'datasets' => $datasets,
            'keywords' => $keywords,
        ]);
    }
}
