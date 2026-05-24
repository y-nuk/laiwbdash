<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 fw-bold mb-0">{{ $store->name }}</h1>
                <p class="text-muted small mb-0 mt-1">
                    {{ $store->company?->name }} ／ 店舗情報を確認できます
                </p>
            </div>
            <a href="{{ route('client.dashboard') }}" class="small align-self-center">← ダッシュボード</a>
        </div>
    </x-slot>

    @include('client.stores._tabs', ['active' => 'overview'])

    <div class="row g-3">
        {{-- 左カラム：基本 + GBP --}}
        <div class="col-lg-7">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-shop"></i> 店舗情報</h2>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">店舗名</dt>
                        <dd class="col-sm-8">{{ $store->name }}</dd>

                        <dt class="col-sm-4 text-muted">ビジネス名</dt>
                        <dd class="col-sm-8">{{ $store->business_name ?? '—' }}</dd>

                        <dt class="col-sm-4 text-muted">業種</dt>
                        <dd class="col-sm-8">{{ $store->industry ?? '—' }}</dd>

                        <dt class="col-sm-4 text-muted">住所</dt>
                        <dd class="col-sm-8">
                            @if ($store->postal_code)〒{{ $store->postal_code }} @endif
                            {{ $store->address ?? '—' }}
                        </dd>

                        <dt class="col-sm-4 text-muted">電話番号</dt>
                        <dd class="col-sm-8">{{ $store->phone ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-google text-primary"></i> Google ビジネスプロフィール情報
                    </h2>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">営業ステータス</dt>
                        <dd class="col-sm-8">
                            @php($bs = $store->business_status ?? 'operational')
                            <span class="badge {{ $bs === 'operational' ? 'text-bg-success' : ($bs === 'temporary_closed' ? 'text-bg-warning' : 'text-bg-secondary') }}">
                                {{ \App\Models\Store::BUSINESS_STATUSES[$bs] ?? $bs }}
                            </span>
                        </dd>

                        <dt class="col-sm-4 text-muted">メインカテゴリ</dt>
                        <dd class="col-sm-8">{{ $store->primary_category ?? '—' }}</dd>

                        <dt class="col-sm-4 text-muted">サブカテゴリ</dt>
                        <dd class="col-sm-8">
                            @if (! empty($store->additional_categories))
                                @foreach ($store->additional_categories as $cat)
                                    <span class="badge text-bg-light me-1">{{ $cat }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">ウェブサイト</dt>
                        <dd class="col-sm-8">
                            @if ($store->website_url)
                                <a href="{{ $store->website_url }}" target="_blank" rel="noopener">
                                    {{ $store->website_url }} <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">サービス提供エリア</dt>
                        <dd class="col-sm-8">
                            @if (! empty($store->service_areas))
                                @foreach ($store->service_areas as $area)
                                    <span class="badge text-bg-light me-1">{{ $area }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">ビジネス情報</dt>
                        <dd class="col-sm-8">
                            @if ($store->description)
                                <div class="text-muted small" style="white-space: pre-line;">{{ $store->description }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- 右カラム：営業時間 + KW サマリー --}}
        <div class="col-lg-5">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-clock"></i> 営業時間</h2>
                </div>
                <div class="card-body">
                    @if (! empty($store->business_hours))
                        <table class="table table-sm small mb-0">
                            @foreach (\App\Models\Store::WEEKDAYS as $day => $label)
                                @php($h = $store->getHoursForDay($day))
                                <tr>
                                    <td class="text-muted" style="width: 30%;">{{ $label }}</td>
                                    <td>
                                        @if ($h['closed'])
                                            <span class="text-muted">定休日</span>
                                        @else
                                            {{ $h['open'] }} 〜 {{ $h['close'] }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <p class="small text-muted mb-0">未設定</p>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-search"></i> 計測キーワード ({{ $store->keywords->count() }})</h2>
                </div>
                <div class="card-body">
                    @if ($store->keywords->isEmpty())
                        <p class="small text-muted mb-0">まだキーワードが登録されていません。</p>
                    @else
                        <ul class="list-unstyled mb-0 small">
                            @foreach ($store->keywords as $kw)
                                <li class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                    <span>
                                        @if ($kw->is_active)
                                            <i class="bi bi-toggle-on text-success"></i>
                                        @else
                                            <i class="bi bi-toggle-off text-muted"></i>
                                        @endif
                                        {{ $kw->keyword }}
                                    </span>
                                    @php($colors = [1 => 'text-bg-danger', 2 => 'text-bg-warning', 3 => 'text-bg-info', 4 => 'text-bg-secondary', 5 => 'text-bg-light'])
                                    <span class="badge {{ $colors[$kw->priority] ?? 'text-bg-info' }}">優先度 {{ $kw->priority }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('client.stores.rankings', $store) }}" class="btn btn-sm btn-outline-primary mt-3 w-100">
                            <i class="bi bi-bar-chart"></i> 順位履歴グラフを見る
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info small mt-3 d-flex align-items-start gap-2">
        <i class="bi bi-info-circle mt-1"></i>
        <div>
            内容の変更が必要な場合は運営担当（{{ config('mail.from.address', 'support@laiweb-dash.com') }}）までご連絡ください。
        </div>
    </div>
</x-app-layout>
