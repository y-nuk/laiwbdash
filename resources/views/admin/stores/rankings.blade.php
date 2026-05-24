<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 fw-bold mb-0">{{ $store->name }}</h1>
                @if ($store->company)
                    <a href="{{ route('admin.companies.show', $store->company) }}" class="small text-decoration-none">
                        <i class="bi bi-building"></i> {{ $store->company->name }}
                    </a>
                @endif
            </div>
            <a href="{{ route('admin.stores.index') }}" class="small align-self-center">← 一覧</a>
        </div>
    </x-slot>

    @include('admin.stores._tabs', ['active' => 'rankings'])

    @if ($keywords->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-muted small">
                計測キーワードが未登録です。<a href="{{ route('admin.stores.keywords.index', $store) }}">キーワード追加</a> から登録してください。
            </div>
        </div>
    @else
        <div class="alert alert-info small d-flex align-items-start gap-2 mb-3">
            <i class="bi bi-info-circle mt-1"></i>
            <div>
                過去 <strong>{{ $days }} 日</strong>の順位推移（GBP API 申請中のため、現在は <code>RandomRankingFetcher</code> によるスタブデータ）。<br>
                グラフは <strong>1 位が上、30 位が下</strong>、欠損日（圏外）は線が途切れます。データ更新は <code>php artisan rankings:fetch</code>。
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent">
                <h2 class="h6 fw-bold mb-0"><i class="bi bi-bar-chart"></i> 順位推移グラフ</h2>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 420px;">
                    <canvas id="rankingChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h2 class="h6 fw-bold mb-0"><i class="bi bi-table"></i> 直近 7 日の順位サマリー</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle small mb-0">
                    <thead>
                        <tr class="text-muted">
                            <th>キーワード</th>
                            <th>優先度</th>
                            @foreach ($dateLabels->slice(-7) as $label)
                                <th class="text-end">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datasets as $ds)
                            <tr>
                                <td class="fw-semibold">{{ $ds['label'] }}</td>
                                <td>
                                    @php($colors = [1 => 'text-bg-danger', 2 => 'text-bg-warning', 3 => 'text-bg-info', 4 => 'text-bg-secondary', 5 => 'text-bg-light'])
                                    <span class="badge {{ $colors[$ds['priority']] ?? 'text-bg-info' }}">{{ $ds['priority'] }}</span>
                                </td>
                                @foreach (array_slice($ds['data']->toArray(), -7) as $pos)
                                    <td class="text-end">
                                        @if ($pos === null)
                                            <span class="text-muted">—</span>
                                        @elseif ($pos <= 3)
                                            <strong class="text-success">{{ $pos }}</strong>
                                        @elseif ($pos <= 10)
                                            <strong>{{ $pos }}</strong>
                                        @else
                                            {{ $pos }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Chart.js (CDN) --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
        <script>
            (function () {
                const labels = @json($dateLabels);
                const datasets = @json($datasets);
                const palette = ['#0d6efd', '#dc3545', '#198754', '#fd7e14', '#6f42c1', '#20c997', '#d63384', '#ffc107'];

                const ctx = document.getElementById('rankingChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets.map((ds, i) => ({
                            label: ds.label,
                            data: ds.data,
                            borderColor: palette[i % palette.length],
                            backgroundColor: palette[i % palette.length] + '22',
                            spanGaps: false,
                            tension: 0.2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                        })),
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            y: {
                                reverse: true,         // 1 位を上に
                                min: 1,
                                max: 30,
                                ticks: { stepSize: 5 },
                                title: { display: true, text: '順位' },
                            },
                            x: {
                                title: { display: true, text: '日付' },
                            },
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (item) => {
                                        const v = item.parsed.y;
                                        return item.dataset.label + ': ' + (v === null ? '圏外' : v + '位');
                                    },
                                },
                            },
                            legend: { position: 'top' },
                        },
                    },
                });
            })();
        </script>
    @endif
</x-app-layout>
