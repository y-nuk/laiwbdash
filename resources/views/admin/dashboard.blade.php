<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1 class="h4 fw-bold mb-0">管理ダッシュボード</h1>
            <div class="btn-group btn-group-sm" role="group">
                @foreach ([7, 30, 90] as $p)
                    <a href="{{ route('admin.dashboard', ['period' => $p]) }}"
                       class="btn {{ $period === $p ? 'btn-primary' : 'btn-outline-primary' }}">
                        過去 {{ $p }} 日
                    </a>
                @endforeach
            </div>
        </div>
    </x-slot>

    {{-- KPI カード 4 枚 --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-label"><i class="bi bi-building"></i> 契約中の会社</div>
                <div class="kpi-value">{{ number_format($kpis['companies_count']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-label"><i class="bi bi-shop"></i> 登録店舗</div>
                <div class="kpi-value">{{ number_format($kpis['stores_count']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-label"><i class="bi bi-search"></i> 計測中のキーワード</div>
                <div class="kpi-value">{{ number_format($kpis['keywords_count']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-label"><i class="bi bi-people"></i> クライアント数</div>
                <div class="kpi-value">{{ number_format($kpis['clients_count']) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- 平均順位推移グラフ --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-graph-up"></i> 全店舗 平均順位推移（過去 {{ $period }} 日）</h2>
                    <canvas id="avg-rank-chart" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- 今日の状況 --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-calendar-event"></i> 今日のステータス</h2>
                    <ul class="list-unstyled small mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span><i class="bi bi-bar-chart text-primary"></i> 順位取得</span>
                            <strong>{{ number_format($today['rankings_count']) }} 件</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span><i class="bi bi-bell text-warning"></i> アラート発報</span>
                            <strong>{{ number_format($today['alerts_fired_today']) }} 件</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span><i class="bi bi-alarm text-info"></i> 自分のフォロー</span>
                            <strong>
                                <a href="{{ route('admin.activities.index', ['filter' => 'follow_today']) }}">{{ $today['follow_up_today'] }} 件</a>
                            </strong>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span><i class="bi bi-exclamation-triangle text-danger"></i> 期限超過</span>
                            <strong class="{{ $today['follow_up_overdue'] > 0 ? 'text-danger' : '' }}">
                                <a href="{{ route('admin.activities.index', ['filter' => 'follow_overdue']) }}">{{ $today['follow_up_overdue'] }} 件</a>
                            </strong>
                        </li>
                    </ul>
                    <div class="mt-3 small text-muted text-center">
                        期間内のアラート発報数：<strong>{{ $alertsFired }}</strong> 件
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 上位店舗 TOP 5 --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h2 class="h6 fw-bold mb-3"><i class="bi bi-trophy text-warning"></i> 直近 7 日 平均順位 TOP 5</h2>
            @if ($topStores->isEmpty() || $topStores->every(fn ($s) => $s->rankings_avg_position === null))
                <p class="text-muted small mb-0">まだ順位データがありません。`artisan rankings:fetch` 実行後に表示されます。</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm small mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>店舗</th>
                                <th>会社</th>
                                <th class="text-end">平均順位</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topStores as $i => $s)
                                @if ($s->rankings_avg_position !== null)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><a href="{{ route('admin.stores.show', $s) }}">{{ $s->name }}</a></td>
                                        <td class="text-muted">{{ $s->company?->name }}</td>
                                        <td class="text-end fw-bold {{ $s->rankings_avg_position <= 3 ? 'text-success' : '' }}">
                                            {{ round($s->rankings_avg_position, 1) }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('avg-rank-chart');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json(array_keys($dailyAvg)),
                    datasets: [{
                        label: '全店舗 平均順位',
                        data: @json(array_values($dailyAvg)),
                        borderColor: 'rgb(13, 110, 253)',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        tension: 0.2,
                        fill: true,
                        spanGaps: false,
                    }],
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { reverse: true, min: 1, title: { display: true, text: '順位（小さいほど上位）' } },
                    },
                    interaction: { mode: 'index', intersect: false },
                },
            });
        });
    </script>
</x-app-layout>
