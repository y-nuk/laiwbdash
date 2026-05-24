<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * クライアント側の店舗閲覧（read-only）。
 * 自社（user.company_id）の店舗のみ表示可。
 */
class StoreController extends Controller
{
    public function show(Store $store): View
    {
        $this->authorizeStore($store);

        $store->load(['company', 'keywords', 'competitors']);

        return view('client.stores.show', compact('store'));
    }

    public function rankings(Store $store): View
    {
        $this->authorizeStore($store);

        $days = 30;
        $from = Carbon::today()->subDays($days - 1);

        $dates = collect(range(0, $days - 1))
            ->map(fn ($i) => $from->copy()->addDays($i))
            ->values();

        $keywords = $store->keywords()
            ->where('is_active', true)
            ->with(['rankings' => fn ($q) => $q->where('checked_date', '>=', $from)->orderBy('checked_date')])
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        $datasets = $keywords->map(function ($kw) use ($dates) {
            $byDate = $kw->rankings->keyBy(fn ($r) => $r->checked_date->toDateString());

            return [
                'label' => $kw->keyword,
                'data' => $dates->map(fn ($d) => $byDate->get($d->toDateString())?->position)->values(),
                'priority' => $kw->priority,
            ];
        });

        return view('client.stores.rankings', [
            'store' => $store,
            'days' => $days,
            'dateLabels' => $dates->map(fn ($d) => $d->format('m/d'))->values(),
            'datasets' => $datasets,
            'keywords' => $keywords,
        ]);
    }

    private function authorizeStore(Store $store): void
    {
        $user = Auth::user();
        abort_unless($user->company_id === $store->company_id, 403, 'この店舗を閲覧する権限がありません。');
    }
}
