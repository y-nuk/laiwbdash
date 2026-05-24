<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Keyword;
use App\Models\Ranking;
use App\Models\Store;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $kpis = [
            'companies_count' => Company::where('status', Company::STATUS_ACTIVE)->count(),
            'stores_count' => Store::count(),
            'keywords_count' => Keyword::where('is_active', true)->count(),
            'clients_count' => User::where('role', User::ROLE_CLIENT)->count(),
        ];

        $todayRankingsCount = Ranking::whereDate('checked_date', today())->count();

        return view('admin.dashboard', compact('kpis', 'todayRankingsCount'));
    }
}
