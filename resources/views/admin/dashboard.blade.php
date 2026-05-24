<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 fw-bold mb-0">管理ダッシュボード</h1>
    </x-slot>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="kpi-card">
                <div class="kpi-label"><i class="bi bi-building"></i> 契約中の会社</div>
                <div class="kpi-value">{{ number_format($kpis['companies_count']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="kpi-card">
                <div class="kpi-label"><i class="bi bi-shop"></i> 登録店舗</div>
                <div class="kpi-value">{{ number_format($kpis['stores_count']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="kpi-card">
                <div class="kpi-label"><i class="bi bi-search"></i> 計測中のキーワード</div>
                <div class="kpi-value">{{ number_format($kpis['keywords_count']) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="kpi-card">
                <div class="kpi-label"><i class="bi bi-people"></i> クライアント数</div>
                <div class="kpi-value">{{ number_format($kpis['clients_count']) }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3">今日のステータス</h2>
            <p class="mb-0 small text-muted">
                今日の順位取得数：<strong>{{ number_format($todayRankingsCount) }}</strong> 件
            </p>
        </div>
    </div>
</x-app-layout>
