<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="h4 fw-bold mb-0">{{ $company?->name ?? 'マイダッシュボード' }}</h1>
            @if ($company)
                <p class="text-muted small mb-0 mt-1">
                    {{ $company->name }} の店舗順位・レポートをこちらで確認できます。
                </p>
            @endif
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    @if (! $company)
        <div class="alert alert-warning small">
            会社情報が紐づいていません。運営担当者にお問い合わせください。
        </div>
    @else
        {{-- KPI カード --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted mb-1"><i class="bi bi-shop"></i> 登録店舗数</div>
                        <div class="display-6 fw-bold">{{ $stats['stores_total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted mb-1"><i class="bi bi-google"></i> GBP 連携済</div>
                        <div class="display-6 fw-bold">
                            {{ $stats['stores_gbp_linked'] }}
                            <span class="fs-6 text-muted">/ {{ $stats['stores_total'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted mb-1"><i class="bi bi-search"></i> 計測 KW 総数</div>
                        <div class="display-6 fw-bold">{{ $stats['keywords_total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted mb-1"><i class="bi bi-toggle-on"></i> 稼働中 KW</div>
                        <div class="display-6 fw-bold">{{ $stats['keywords_active'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 店舗一覧 --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h2 class="h6 fw-bold mb-0"><i class="bi bi-shop"></i> 登録店舗（{{ $stores->count() }} 店舗）</h2>
            </div>
            @if ($stores->isEmpty())
                <div class="card-body text-muted small">
                    まだ店舗が登録されていません。運営担当が登録すると、ここに表示されます。
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 small">
                        <thead>
                            <tr class="text-muted">
                                <th>店舗名</th>
                                <th>業種</th>
                                <th>住所</th>
                                <th class="text-end">計測 KW</th>
                                <th>GBP</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stores as $store)
                                <tr>
                                    <td class="fw-semibold">
                                        <a href="{{ route('client.stores.show', $store) }}" class="text-decoration-none">{{ $store->name }}</a>
                                    </td>
                                    <td>{{ $store->industry ?? '—' }}</td>
                                    <td class="text-muted">
                                        @if ($store->postal_code)〒{{ $store->postal_code }} @endif
                                        {{ $store->address ?? '—' }}
                                    </td>
                                    <td class="text-end">
                                        {{ $store->active_keywords_count }}
                                        <span class="text-muted small">/ {{ $store->keywords_count }}</span>
                                    </td>
                                    <td>
                                        @php($gs = $store->gbp_status)
                                        <span class="badge {{ $gs === 'confirmed' ? 'text-bg-success' : ($gs === 'error' ? 'text-bg-danger' : ($gs === 'pending' ? 'text-bg-warning' : 'text-bg-secondary')) }}">
                                            {{ \App\Models\Store::GBP_STATUSES[$gs] ?? $gs }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('client.stores.rankings', $store) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-bar-chart"></i> 順位
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="alert alert-info small mt-3 d-flex align-items-start gap-2">
            <i class="bi bi-info-circle mt-1"></i>
            <div>
                順位レポートやクチコミ分析は、今後こちらの画面に追加していきます。<br>
                ご質問・要望は運営担当までお気軽にどうぞ。
            </div>
        </div>
    @endif
</x-app-layout>
