<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Alert;
use App\Models\Company;
use App\Models\Keyword;
use App\Models\Ranking;
use App\Models\Store;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $period = (int) $request->query('period', 30);
        if (! in_array($period, [7, 30, 90], true)) {
            $period = 30;
        }

        $todayDate = CarbonImmutable::today();
        $periodStart = $todayDate->subDays($period - 1);

        $kpis = [
            'companies_count' => Company::where('status', Company::STATUS_ACTIVE)->count(),
            'stores_count' => Store::count(),
            'keywords_count' => Keyword::where('is_active', true)->count(),
            'clients_count' => User::where('role', User::ROLE_CLIENT)->count(),
        ];

        $today = [
            'rankings_count' => Ranking::whereDate('checked_date', today())->count(),
            'alerts_fired_today' => Alert::whereDate('last_alerted_at', today())->count(),
            'follow_up_today' => Activity::where('user_id', $request->user()->id)
                ->whereDate('follow_up_at', today())
                ->where('follow_up_done', false)
                ->count(),
            'follow_up_overdue' => Activity::where('user_id', $request->user()->id)
                ->whereNotNull('follow_up_at')
                ->whereDate('follow_up_at', '<', today())
                ->where('follow_up_done', false)
                ->count(),
        ];

        // 期間内の平均順位推移（全店舗）
        $dailyAvg = [];
        $cur = $periodStart;
        while ($cur->lte($todayDate)) {
            $avg = Ranking::whereDate('checked_date', $cur->toDateString())
                ->whereNotNull('position')
                ->avg('position');
            $dailyAvg[$cur->format('m/d')] = $avg !== null ? round($avg, 1) : null;
            $cur = $cur->addDay();
        }

        $alertsFired = Alert::whereBetween('last_alerted_at', [$periodStart, $todayDate])->count();

        $topStores = Store::with('company')
            ->withAvg([
                'rankings' => fn ($q) => $q->whereDate('checked_date', '>=', $todayDate->subDays(7))->whereNotNull('position'),
            ], 'position')
            ->orderByRaw('rankings_avg_position IS NULL, rankings_avg_position ASC')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'kpis' => $kpis,
            'today' => $today,
            'period' => $period,
            'dailyAvg' => $dailyAvg,
            'alertsFired' => $alertsFired,
            'topStores' => $topStores,
        ]);
    }
}
