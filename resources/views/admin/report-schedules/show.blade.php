<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 fw-bold mb-0">{{ $schedule->name }}</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.report-schedules.edit', $schedule) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> 編集
                </a>
                @if ($schedule->status !== 'cancelled')
                    <form method="POST" action="{{ route('admin.report-schedules.toggle', $schedule) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary" type="submit">
                            <i class="bi bi-{{ $schedule->status === 'active' ? 'pause' : 'play' }}"></i>
                            {{ $schedule->status === 'active' ? '停止' : '再開' }}
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.report-schedules.destroy', $schedule) }}" class="d-inline" onsubmit="return confirm('この予約を削除しますか？');">
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
                    <tr>
                        <th style="width: 200px;">配信ステータス</th>
                        <td>
                            @php
                                $badge = match($schedule->status) {
                                    'active' => 'success',
                                    'paused' => 'warning',
                                    'cancelled' => 'secondary',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ \App\Models\ReportSchedule::STATUSES[$schedule->status] }}</span>
                        </td>
                    </tr>
                    <tr><th>配信頻度</th><td>{{ \App\Models\ReportSchedule::RECURRENCES[$schedule->recurrence] }}</td></tr>
                    @if ($schedule->recurrence === 'monthly')
                        <tr><th>配信日</th><td>毎月 {{ $schedule->recurrence_day }} 日 09:00</td></tr>
                    @elseif ($schedule->recurrence === 'weekly')
                        <tr><th>配信曜日</th><td>{{ ['日','月','火','水','木','金','土'][$schedule->recurrence_day] ?? '?' }}曜 09:00</td></tr>
                    @elseif ($schedule->recurrence === 'once')
                        <tr><th>配信日時</th><td>{{ $schedule->scheduled_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
                    @endif
                    <tr><th>次回配信</th><td>{{ $schedule->next_run_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
                    <tr><th>最終配信</th><td>{{ $schedule->last_sent_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
                    <tr><th>会社</th><td>{{ $schedule->store->company->name }}</td></tr>
                    <tr><th>店舗</th><td>{{ $schedule->store->name }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-4">
                <h2 class="h6 fw-bold mb-3"><i class="bi bi-envelope"></i> 配信メール内容</h2>
                <table class="table small mb-0">
                    <tr><th style="width: 140px;">送付先</th><td>{{ $schedule->recipients }}</td></tr>
                    <tr><th>件名</th><td>{{ $schedule->subject }}</td></tr>
                    <tr><th>本文</th><td><div style="white-space: pre-wrap;">{{ $schedule->body }}</div></td></tr>
                </table>
            </div>
        </div>

        @if ($schedule->admin_comment)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-2"><i class="bi bi-sticky"></i> 運営メモ</h2>
                    <div class="small" style="white-space: pre-wrap;">{{ $schedule->admin_comment }}</div>
                </div>
            </div>
        @endif

        <div class="mb-4">
            <a href="{{ route('admin.report-schedules.index') }}" class="btn btn-link">&larr; 配信予約一覧に戻る</a>
        </div>
    </div>
</x-app-layout>
