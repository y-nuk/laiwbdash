<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 fw-bold mb-0">{{ $alert->name }}</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.alerts.edit', $alert) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> 編集
                </a>
                <form method="POST" action="{{ route('admin.alerts.toggle', $alert) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-{{ $alert->enabled ? 'pause' : 'play' }}"></i>
                        {{ $alert->enabled ? '無効化' : '有効化' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.alerts.destroy', $alert) }}" class="d-inline" onsubmit="return confirm('このアラートを削除しますか？');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> 削除</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid" style="max-width: 820px;">
        @if (session('status'))
            <div class="alert alert-success small">{{ session('status') }}</div>
        @endif

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-4">
                <table class="table small mb-0">
                    <tr><th style="width: 200px;">状態</th><td>
                        @if ($alert->enabled)
                            <span class="badge bg-success">有効</span>
                        @else
                            <span class="badge bg-secondary">無効</span>
                        @endif
                    </td></tr>
                    <tr><th>会社</th><td>{{ $alert->store->company->name }}</td></tr>
                    <tr><th>店舗</th><td>{{ $alert->store->name }}</td></tr>
                    <tr><th>対象 KW</th><td>{{ $alert->keyword ? $alert->keyword->keyword : '店舗の全 KW' }}</td></tr>
                    <tr><th>種別</th><td>{{ \App\Models\Alert::TYPES[$alert->alert_type] ?? $alert->alert_type }}</td></tr>
                    <tr><th>しきい値</th><td>{{ $alert->threshold }}</td></tr>
                    <tr><th>通知先</th><td>{{ $alert->recipients ?: '（管理者メアド '.env('ADMIN_EMAIL').'）' }}</td></tr>
                    <tr><th>最終発報</th><td>{{ $alert->last_alerted_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
                    <tr><th>最終チェック</th><td>{{ $alert->last_check_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
                </table>
            </div>
        </div>

        @if ($alert->admin_comment)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-2"><i class="bi bi-sticky"></i> 運営メモ</h2>
                    <div class="small" style="white-space: pre-wrap;">{{ $alert->admin_comment }}</div>
                </div>
            </div>
        @endif

        <div class="mb-4">
            <a href="{{ route('admin.alerts.index') }}" class="btn btn-link">&larr; 一覧へ戻る</a>
        </div>
    </div>
</x-app-layout>
