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

    @include('admin.stores._tabs', ['active' => 'competitors'])

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    {{-- 追加フォーム --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-plus-circle"></i> 競合店を追加</h2>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.stores.competitors.store', $store) }}" class="row g-2">
                @csrf
                <div class="col-12 col-md-4">
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="競合店の名称" required
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <input type="url" name="gbp_url" value="{{ old('gbp_url') }}" placeholder="GBP / Google マップ URL"
                           class="form-control @error('gbp_url') is-invalid @enderror">
                    @error('gbp_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-3">
                    <input type="text" name="gbp_place_id" value="{{ old('gbp_place_id') }}" placeholder="Place ID（任意）"
                           class="form-control">
                </div>
                <div class="col-12 col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 一覧 --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-people"></i> 競合店 ({{ $competitors->count() }})</h2>
        </div>
        @if ($competitors->isEmpty())
            <div class="card-body text-muted small">まだ競合店が登録されていません。</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0 small">
                    <thead>
                        <tr class="text-muted">
                            <th>順</th>
                            <th>競合店名</th>
                            <th>GBP URL</th>
                            <th>Place ID</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($competitors as $c)
                            <tr>
                                <td class="text-muted">{{ $c->sort_order }}</td>
                                <td class="fw-semibold">{{ $c->name }}</td>
                                <td>
                                    @if ($c->gbp_url)
                                        <a href="{{ $c->gbp_url }}" target="_blank" rel="noopener" class="text-decoration-none">
                                            <i class="bi bi-box-arrow-up-right"></i> 開く
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if ($c->gbp_place_id)
                                        <code>{{ Str::limit($c->gbp_place_id, 18) }}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="post" action="{{ route('admin.stores.competitors.destroy', [$store, $c]) }}" class="d-inline"
                                          onsubmit="return confirm('「{{ $c->name }}」を競合から削除しますか？');">
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
            GBP API 通過後は、競合店の最新順位やレビュー数も自動取得できるようになります（Phase 4 予定）。
        </div>
    </div>
</x-app-layout>
