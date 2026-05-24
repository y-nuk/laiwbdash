<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1 class="h4 fw-bold mb-0">店舗管理</h1>
            <a href="{{ route('admin.stores.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> 新規店舗登録
            </a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <form method="get" class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <input type="text" name="q" value="{{ $q }}" placeholder="店舗名 / ビジネス名 / 住所" class="form-control">
                </div>
                <div class="col-6 col-md-3">
                    <select name="company_id" class="form-select">
                        <option value="">会社：すべて</option>
                        @foreach ($companies as $c)
                            <option value="{{ $c->id }}" @selected((string) $companyId === (string) $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="gbp_status" class="form-select">
                        <option value="">GBP：すべて</option>
                        @foreach (\App\Models\Store::GBP_STATUSES as $key => $label)
                            <option value="{{ $key }}" @selected($gbpStatus === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-grid">
                    <button type="submit" class="btn btn-outline-primary">検索</button>
                </div>
            </div>
        </div>
    </form>

    @if ($stores->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-muted small">該当する店舗はまだありません。</div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="small text-muted">
                            <th>ID</th>
                            <th>店舗名</th>
                            <th>会社</th>
                            <th>業種</th>
                            <th class="text-end">KW数</th>
                            <th>GBP</th>
                            <th>Yahoo!</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stores as $s)
                            <tr>
                                <td class="text-muted small">{{ $s->id }}</td>
                                <td>
                                    <a href="{{ route('admin.stores.show', $s) }}" class="text-decoration-none fw-semibold">{{ $s->name }}</a>
                                    @if ($s->business_name && $s->business_name !== $s->name)
                                        <div class="small text-muted">{{ $s->business_name }}</div>
                                    @endif
                                </td>
                                <td class="small">
                                    @if ($s->company)
                                        <a href="{{ route('admin.companies.show', $s->company) }}" class="text-decoration-none">{{ $s->company->name }}</a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small">{{ $s->industry ?? '—' }}</td>
                                <td class="text-end">{{ $s->keywords_count }}</td>
                                <td>
                                    @php($gs = $s->gbp_status)
                                    <span class="badge {{ $gs === 'confirmed' ? 'text-bg-success' : ($gs === 'error' ? 'text-bg-danger' : ($gs === 'pending' ? 'text-bg-warning' : 'text-bg-secondary')) }}">
                                        {{ \App\Models\Store::GBP_STATUSES[$gs] ?? $gs }}
                                    </span>
                                </td>
                                <td>
                                    @if ($s->has_yahoo)
                                        <span class="badge text-bg-light"><i class="bi bi-check"></i></span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.stores.edit', $s) }}" class="btn btn-sm btn-outline-secondary">編集</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $stores->links() }}</div>
    @endif
</x-app-layout>
