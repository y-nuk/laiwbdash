<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $company = $user->company()
            ->with([
                'stores' => fn ($q) => $q->withCount(['keywords', 'activeKeywords']),
            ])
            ->first();

        $stores = $company?->stores ?? collect();

        $stats = [
            'stores_total' => $stores->count(),
            'stores_gbp_linked' => $stores->where('gbp_status', 'confirmed')->count(),
            'keywords_total' => $stores->sum('keywords_count'),
            'keywords_active' => $stores->sum('active_keywords_count'),
        ];

        return view('client.dashboard', [
            'company' => $company,
            'stores' => $stores,
            'stats' => $stats,
        ]);
    }
}
