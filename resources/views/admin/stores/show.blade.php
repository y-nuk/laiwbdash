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
            <div class="d-flex gap-2">
                <a href="{{ route('admin.stores.index') }}" class="small align-self-center">← 一覧</a>
                <a href="{{ route('admin.stores.edit', $store) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-pencil"></i> 内部情報を編集
                </a>
            </div>
        </div>
    </x-slot>

    @include('admin.stores._tabs', ['active' => 'overview'])

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <div class="row g-3">
        {{-- ===== 左カラム：店舗情報 (内部 + GBP NAP) ===== --}}
        <div class="col-lg-7">

            {{-- 内部情報 --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-shop"></i> 店舗情報</h2>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">店舗 ID</dt>
                        <dd class="col-sm-8">{{ $store->id }}</dd>

                        <dt class="col-sm-4 text-muted">店舗名（内部）</dt>
                        <dd class="col-sm-8">{{ $store->name }}</dd>

                        <dt class="col-sm-4 text-muted">ビジネス名（GBP 表示用）</dt>
                        <dd class="col-sm-8">{{ $store->business_name ?? '—' }}</dd>

                        <dt class="col-sm-4 text-muted">業種（内部）</dt>
                        <dd class="col-sm-8">{{ $store->industry ?? '—' }}</dd>

                        <dt class="col-sm-4 text-muted">住所</dt>
                        <dd class="col-sm-8">
                            @if ($store->postal_code)〒{{ $store->postal_code }} @endif
                            {{ $store->address ?? '—' }}
                        </dd>

                        <dt class="col-sm-4 text-muted">電話番号</dt>
                        <dd class="col-sm-8">{{ $store->phone ?? '—' }}</dd>

                        <dt class="col-sm-4 text-muted">登録日</dt>
                        <dd class="col-sm-8 text-muted">{{ $store->created_at->format('Y/m/d H:i') }}</dd>
                    </dl>
                </div>
            </div>

            {{-- GBP 基本情報サマリー --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-google text-primary"></i> GBP 基本情報サマリー
                    </h2>
                    <a href="{{ route('admin.stores.gbp-basic.edit', $store) }}" class="small text-decoration-none">
                        <i class="bi bi-pencil"></i> 編集
                    </a>
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
                                <a href="{{ $store->website_url }}" target="_blank" rel="noopener" class="text-truncate d-inline-block" style="max-width: 280px;">
                                    {{ $store->website_url }} <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">予約 / メニュー / 注文</dt>
                        <dd class="col-sm-8 small">
                            @if ($store->reservation_url)<a href="{{ $store->reservation_url }}" target="_blank" rel="noopener" class="me-2">予約 <i class="bi bi-box-arrow-up-right"></i></a>@endif
                            @if ($store->menu_url)<a href="{{ $store->menu_url }}" target="_blank" rel="noopener" class="me-2">メニュー <i class="bi bi-box-arrow-up-right"></i></a>@endif
                            @if ($store->order_url)<a href="{{ $store->order_url }}" target="_blank" rel="noopener">注文 <i class="bi bi-box-arrow-up-right"></i></a>@endif
                            @if (! $store->reservation_url && ! $store->menu_url && ! $store->order_url)
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

                        <dt class="col-sm-4 text-muted">開業日</dt>
                        <dd class="col-sm-8">{{ $store->opening_date?->format('Y/m/d') ?? '—' }}</dd>

                        <dt class="col-sm-4 text-muted">ビジネス情報</dt>
                        <dd class="col-sm-8">
                            @if ($store->description)
                                <div class="text-muted small" style="white-space: pre-line;">{{ Str::limit($store->description, 200) }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">改ざん防止</dt>
                        <dd class="col-sm-8">
                            @if ($store->gbp_protected)
                                <span class="badge text-bg-primary"><i class="bi bi-shield-check"></i> ON</span>
                                <span class="text-muted small ms-1">こちらの値で GBP を上書き同期</span>
                            @else
                                <span class="badge text-bg-secondary">OFF</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- ===== 右カラム：GBP 連携 + 営業時間 + ユーザー ===== --}}
        <div class="col-lg-5">

            {{-- GBP 連携状態 --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-link-45deg"></i> GBP 連携状態</h2>
                </div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted">状態</dt>
                        <dd class="col-sm-7">
                            @php($gs = $store->gbp_status)
                            <span class="badge {{ $gs === 'confirmed' ? 'text-bg-success' : ($gs === 'error' ? 'text-bg-danger' : ($gs === 'pending' ? 'text-bg-warning' : 'text-bg-secondary')) }}">
                                {{ \App\Models\Store::GBP_STATUSES[$gs] ?? $gs }}
                            </span>
                        </dd>

                        <dt class="col-sm-5 text-muted">Place ID</dt>
                        <dd class="col-sm-7">
                            @if ($store->gbp_place_id)
                                <code class="small">{{ Str::limit($store->gbp_place_id, 18) }}</code>
                            @else
                                <span class="text-muted">未設定</span>
                            @endif
                        </dd>

                        <dt class="col-sm-5 text-muted">最終同期</dt>
                        <dd class="col-sm-7">
                            @if ($store->gbp_last_synced_at)
                                {{ $store->gbp_last_synced_at->format('Y/m/d H:i') }}
                            @else
                                <span class="text-warning">未同期（API 申請中）</span>
                            @endif
                        </dd>
                    </dl>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-3 w-100" disabled
                            title="GBP API 申請中">
                        <i class="bi bi-google"></i> Sign in with Google
                    </button>
                </div>
            </div>

            {{-- 営業時間サマリー --}}
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
                        <p class="small text-muted mb-0">未設定（GBP 基本情報タブから入力できます）</p>
                    @endif
                </div>
            </div>

            {{-- 関連ユーザー --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-people"></i> 関連ユーザー</h2>
                </div>
                <div class="card-body">
                    @if ($store->company && $store->company->users->isNotEmpty())
                        <ul class="list-unstyled mb-0 small">
                            @foreach ($store->company->users as $u)
                                <li class="d-flex justify-content-between align-items-center py-1">
                                    <div>
                                        <div>{{ $u->name }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $u->email }}</div>
                                    </div>
                                    <span class="badge text-bg-light">{{ \App\Models\User::ROLES[$u->role] ?? $u->role }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small mb-0">この会社にひも付くユーザーはまだいません。</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 危険操作 --}}
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-transparent">
            <h2 class="h6 fw-bold mb-0 text-danger"><i class="bi bi-exclamation-triangle"></i> 危険操作</h2>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.stores.destroy', $store) }}"
                  onsubmit="return confirm('{{ $store->name }} を削除しますか？');">
                @csrf @method('delete')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash"></i> この店舗を削除
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
