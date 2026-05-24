<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1 class="h4 fw-bold mb-0">会社（事業者）管理</h1>
            <a href="{{ route('admin.companies.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> 新規会社登録
            </a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <form method="get" class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-12 col-md-6">
                    <input type="text" name="q" value="{{ $q }}" placeholder="会社名 / カナ / 担当者 / メアド" class="form-control">
                </div>
                <div class="col-8 col-md-4">
                    <select name="status" class="form-select">
                        <option value="">ステータス：すべて</option>
                        @foreach (\App\Models\Company::STATUSES as $key => $label)
                            <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4 col-md-2 d-grid">
                    <button type="submit" class="btn btn-outline-primary">検索</button>
                </div>
            </div>
        </div>
    </form>

    @if ($companies->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-muted small">該当する会社はまだありません。</div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="small text-muted">
                            <th>ID</th>
                            <th>会社名</th>
                            <th>担当者</th>
                            <th>業種</th>
                            <th class="text-end">店舗数</th>
                            <th>ステータス</th>
                            <th>登録日</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($companies as $c)
                            <tr>
                                <td class="text-muted small">{{ $c->id }}</td>
                                <td>
                                    <a href="{{ route('admin.companies.show', $c) }}" class="text-decoration-none fw-semibold">{{ $c->name }}</a>
                                    @if ($c->kana)
                                        <div class="small text-muted">{{ $c->kana }}</div>
                                    @endif
                                </td>
                                <td class="small">{{ $c->contact_person_name ?? '—' }}</td>
                                <td class="small">{{ $c->industry ?? '—' }}</td>
                                <td class="text-end">{{ $c->stores_count }}</td>
                                <td>
                                    @php($s = $c->status)
                                    <span class="badge {{ $s === 'active' ? 'text-bg-success' : ($s === 'suspended' ? 'text-bg-warning' : 'text-bg-secondary') }}">
                                        {{ \App\Models\Company::STATUSES[$s] ?? $s }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $c->created_at->format('Y/m/d') }}</td>
                                <td>
                                    <a href="{{ route('admin.companies.edit', $c) }}" class="btn btn-sm btn-outline-secondary">編集</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $companies->links() }}</div>
    @endif
</x-app-layout>
