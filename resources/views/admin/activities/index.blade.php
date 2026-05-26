<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 fw-bold mb-0">活動履歴</h1>
    </x-slot>

    <div class="container-fluid">
        @if (session('status'))
            <div class="alert alert-success small">{{ session('status') }}</div>
        @endif

        <div class="mb-3 d-flex flex-wrap gap-2">
            <a href="{{ route('admin.activities.index') }}" class="btn btn-sm {{ $currentFilter ? 'btn-outline-secondary' : 'btn-primary' }}">
                すべて
            </a>
            <a href="{{ route('admin.activities.index', ['filter' => 'mine']) }}" class="btn btn-sm {{ $currentFilter === 'mine' ? 'btn-primary' : 'btn-outline-secondary' }}">
                自分の記録
            </a>
            <a href="{{ route('admin.activities.index', ['filter' => 'follow_today']) }}" class="btn btn-sm {{ $currentFilter === 'follow_today' ? 'btn-warning' : 'btn-outline-warning' }}">
                <i class="bi bi-alarm"></i> 今日フォロー
            </a>
            <a href="{{ route('admin.activities.index', ['filter' => 'follow_overdue']) }}" class="btn btn-sm {{ $currentFilter === 'follow_overdue' ? 'btn-danger' : 'btn-outline-danger' }}">
                <i class="bi bi-exclamation-triangle"></i> 期限超過
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>日時</th>
                            <th>種別</th>
                            <th>会社</th>
                            <th>件名</th>
                            <th>記録者</th>
                            <th>フォロー</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($activities as $a)
                            <tr>
                                <td>{{ $a->occurred_at->format('m/d H:i') }}</td>
                                <td>
                                    <i class="bi {{ \App\Models\Activity::TYPE_ICONS[$a->type] ?? 'bi-circle' }} text-primary"></i>
                                    {{ \App\Models\Activity::TYPES[$a->type] }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.companies.show', $a->company) }}">{{ $a->company?->name ?? '?' }}</a>
                                </td>
                                <td>{{ $a->title }}</td>
                                <td>{{ $a->user?->name }}</td>
                                <td>
                                    @if ($a->follow_up_at)
                                        @php
                                            $isOverdue = $a->follow_up_at->isPast() && ! $a->follow_up_done;
                                            $badge = $a->follow_up_done ? 'success' : ($isOverdue ? 'danger' : 'warning');
                                        @endphp
                                        <span class="badge bg-{{ $badge }}">
                                            @if ($a->follow_up_done) <i class="bi bi-check"></i> @endif
                                            {{ $a->follow_up_at->format('m/d') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.activities.destroy', $a) }}" onsubmit="return confirm('削除しますか？');" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">活動記録はありません</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $activities->links() }}</div>
    </div>
</x-app-layout>
