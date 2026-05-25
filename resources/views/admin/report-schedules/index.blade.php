<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 fw-bold mb-0">レポート配信予約</h1>
            <a href="{{ route('admin.report-schedules.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> 新規予約
            </a>
        </div>
    </x-slot>

    <div class="container-fluid">
        @if (session('status'))
            <div class="alert alert-success small">{{ session('status') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ $currentQ }}" class="form-control form-control-sm" placeholder="店舗名 or レポート名で検索">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">すべてのステータス</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" {{ $currentStatus === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-secondary btn-sm w-100">検索</button>
            </div>
        </form>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>#</th>
                            <th>レポート名</th>
                            <th>店舗</th>
                            <th>配信</th>
                            <th>次回</th>
                            <th>最終</th>
                            <th>状態</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($schedules as $s)
                            <tr>
                                <td>{{ $s->id }}</td>
                                <td>
                                    <a href="{{ route('admin.report-schedules.show', $s) }}">{{ $s->name }}</a>
                                </td>
                                <td>
                                    <div>{{ $s->store->name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $s->store->company->name }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ \App\Models\ReportSchedule::RECURRENCES[$s->recurrence] ?? $s->recurrence }}</span>
                                    @if ($s->recurrence === 'monthly')
                                        <div class="text-muted" style="font-size: 0.7rem;">毎月 {{ $s->recurrence_day }} 日</div>
                                    @elseif ($s->recurrence === 'weekly')
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ ['日','月','火','水','木','金','土'][$s->recurrence_day] ?? '?' }}曜</div>
                                    @endif
                                </td>
                                <td>{{ $s->next_run_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>{{ $s->last_sent_at?->format('Y-m-d') ?? '—' }}</td>
                                <td>
                                    @php
                                        $badge = match($s->status) {
                                            'active' => 'success',
                                            'paused' => 'warning',
                                            'cancelled' => 'secondary',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ \App\Models\ReportSchedule::STATUSES[$s->status] ?? $s->status }}</span>
                                </td>
                                <td class="text-end">
                                    @if ($s->status !== 'cancelled')
                                        <form method="POST" action="{{ route('admin.report-schedules.toggle', $s) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-link p-0 me-2" type="submit" title="{{ $s->status === 'active' ? '停止' : '再開' }}">
                                                <i class="bi bi-{{ $s->status === 'active' ? 'pause-circle' : 'play-circle' }}"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.report-schedules.edit', $s) }}" class="btn btn-sm btn-link p-0">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">配信予約はありません</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $schedules->links() }}
        </div>
    </div>
</x-app-layout>
