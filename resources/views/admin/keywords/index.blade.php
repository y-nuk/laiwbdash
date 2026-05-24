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

    @include('admin.stores._tabs', ['active' => 'keywords'])

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    {{-- 追加フォーム --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-plus-circle"></i> キーワード追加</h2>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.stores.keywords.store', $store) }}" class="row g-2">
                @csrf
                <div class="col-12 col-md-5">
                    <input type="text" name="keyword" value="{{ old('keyword') }}"
                           placeholder="例：江戸川区 外壁塗装" required
                           class="form-control @error('keyword') is-invalid @enderror">
                    @error('keyword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-md-3">
                    <input type="text" name="location_code" value="{{ old('location_code') }}"
                           placeholder="検索エリア（例：江戸川区）"
                           class="form-control">
                </div>
                <div class="col-3 col-md-2">
                    <select name="priority" class="form-select">
                        <option value="3" @selected(old('priority') == 3)>優先度 3</option>
                        <option value="1" @selected(old('priority') == 1)>優先度 1（最優先）</option>
                        <option value="2" @selected(old('priority') == 2)>優先度 2</option>
                        <option value="4" @selected(old('priority') == 4)>優先度 4</option>
                        <option value="5" @selected(old('priority') == 5)>優先度 5（低）</option>
                    </select>
                </div>
                <div class="col-3 col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> 追加
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 一覧 --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-list-check"></i> 計測中キーワード ({{ $keywords->count() }})</h2>
        </div>
        @if ($keywords->isEmpty())
            <div class="card-body text-muted small">まだキーワードが登録されていません。上のフォームから追加してください。</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0 small">
                    <thead>
                        <tr class="text-muted">
                            <th>ID</th>
                            <th>検索エリア</th>
                            <th>キーワード</th>
                            <th>優先度</th>
                            <th>状態</th>
                            <th>最新順位</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($keywords as $kw)
                            <tr class="{{ $kw->is_active ? '' : 'text-muted' }}">
                                <td>{{ $kw->id }}</td>
                                <td>
                                    @if ($kw->location_code)
                                        <span class="badge text-bg-light">{{ $kw->location_code }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $kw->keyword }}</td>
                                <td>
                                    @php($colors = [1 => 'text-bg-danger', 2 => 'text-bg-warning', 3 => 'text-bg-info', 4 => 'text-bg-secondary', 5 => 'text-bg-light'])
                                    <span class="badge {{ $colors[$kw->priority] ?? 'text-bg-info' }}">{{ $kw->priority }}</span>
                                </td>
                                <td>
                                    <form method="post" action="{{ route('admin.stores.keywords.toggle', [$store, $kw]) }}" class="d-inline">
                                        @csrf
                                        @if ($kw->is_active)
                                            <button class="btn btn-sm btn-success" style="min-width: 60px;">ON</button>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" style="min-width: 60px;">OFF</button>
                                        @endif
                                    </form>
                                </td>
                                <td>
                                    @php($latest = $kw->latestRanking)
                                    @if ($latest)
                                        @if ($latest->position)
                                            <strong>{{ $latest->position }}位</strong>
                                            <span class="text-muted small">{{ $latest->checked_date?->format('m/d') }}</span>
                                        @else
                                            <span class="text-muted">圏外</span>
                                        @endif
                                    @else
                                        <span class="text-muted small">未取得</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="post" action="{{ route('admin.stores.keywords.destroy', [$store, $kw]) }}" class="d-inline"
                                          onsubmit="return confirm('「{{ $kw->keyword }}」を削除しますか？順位履歴も消えます。');">
                                        @csrf @method('delete')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
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
            <strong>優先度について：</strong> 1=最優先〜5=低。レポート/アラート優先順に使います。<br>
            <strong>検索エリア：</strong> 「江戸川区 外壁塗装」のように地域＋業種で測定する場合、エリア名（江戸川区）を分けて入れると後の集計が楽になります。
        </div>
    </div>
</x-app-layout>
