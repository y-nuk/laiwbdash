<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 fw-bold mb-0">順位アラート</h1>
            <a href="{{ route('admin.alerts.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-bell-plus"></i> 新規アラート
            </a>
        </div>
    </x-slot>

    <div class="container-fluid">
        @if (session('status'))
            <div class="alert alert-success small">{{ session('status') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ $currentQ }}" class="form-control form-control-sm" placeholder="店舗名 or アラート名で検索">
            </div>
            <div class="col-md-3">
                <select name="enabled" class="form-select form-select-sm">
                    <option value="">すべて</option>
                    <option value="yes" {{ $currentEnabled === 'yes' ? 'selected' : '' }}>有効のみ</option>
                    <option value="no" {{ $currentEnabled === 'no' ? 'selected' : '' }}>無効のみ</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary btn-sm w-100">検索</button>
            </div>
        </form>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>#</th>
                            <th>アラート名</th>
                            <th>店舗 / KW</th>
                            <th>種別</th>
                            <th>しきい値</th>
                            <th>最終発報</th>
                            <th>状態</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($alerts as $a)
                            <tr>
                                <td>{{ $a->id }}</td>
                                <td><a href="{{ route('admin.alerts.show', $a) }}">{{ $a->name }}</a></td>
                                <td>
                                    <div>{{ $a->store->name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        {{ $a->keyword ? 'KW: ' . $a->keyword->keyword : '店舗全 KW' }}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $typeBadge = match($a->alert_type) {
                                            'ranking_drop' => 'warning',
                                            'out_of_rank' => 'danger',
                                            'worse_than' => 'info',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $typeBadge }}">{{ ['ranking_drop' => '下落', 'out_of_rank' => '圏外', 'worse_than' => '悪化'][$a->alert_type] ?? $a->alert_type }}</span>
                                </td>
                                <td>{{ $a->threshold }}</td>
                                <td>{{ $a->last_alerted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>
                                    @if ($a->enabled)
                                        <span class="badge bg-success">有効</span>
                                    @else
                                        <span class="badge bg-secondary">無効</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.alerts.toggle', $a) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-link p-0 me-2" title="{{ $a->enabled ? '無効化' : '有効化' }}">
                                            <i class="bi bi-{{ $a->enabled ? 'pause-circle' : 'play-circle' }}"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.alerts.edit', $a) }}" class="btn btn-sm btn-link p-0">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">アラートはありません</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $alerts->links() }}</div>
    </div>
</x-app-layout>
