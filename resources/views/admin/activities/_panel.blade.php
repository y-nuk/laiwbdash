{{-- 会社詳細に埋め込む「活動タイムライン + 追加フォーム」 partial --}}
@php
    $activities = $activities ?? $company->activities()->with('user')->take(20)->get();
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <h2 class="h6 fw-bold mb-3"><i class="bi bi-clock-history"></i> 活動履歴</h2>

        <form method="POST" action="{{ route('admin.companies.activities.store', $company) }}" class="border rounded p-3 mb-4" style="background: #fafafa;">
            @csrf
            <div class="row g-2 mb-2">
                <div class="col-md-3">
                    <select name="type" class="form-select form-select-sm" required>
                        @foreach (\App\Models\Activity::TYPES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="datetime-local" name="occurred_at" class="form-control form-control-sm" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>
                <div class="col-md-3">
                    <input type="date" name="follow_up_at" class="form-control form-control-sm" placeholder="フォロー期限">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i> 記録する</button>
                </div>
            </div>
            <input type="text" name="title" class="form-control form-control-sm mb-2" placeholder="件名（例：見積もり依頼、ヒアリング）" required maxlength="150">
            <textarea name="body" rows="2" class="form-control form-control-sm" placeholder="内容・話した内容など（任意）" maxlength="5000"></textarea>
        </form>

        @if ($activities->isEmpty())
            <p class="text-muted small mb-0">まだ活動記録はありません。</p>
        @else
            <ul class="list-unstyled mb-0">
                @foreach ($activities as $a)
                    <li class="d-flex gap-3 mb-3 pb-3 border-bottom">
                        <div class="flex-shrink-0 text-center" style="width: 40px;">
                            <i class="bi {{ \App\Models\Activity::TYPE_ICONS[$a->type] ?? 'bi-circle' }} text-primary fs-4"></i>
                            <div class="small text-muted" style="font-size: 0.7rem;">{{ \App\Models\Activity::TYPES[$a->type] }}</div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <div class="fw-bold small">{{ $a->title }}</div>
                                <div class="small text-muted">{{ $a->occurred_at->format('m/d H:i') }}</div>
                            </div>
                            @if ($a->body)
                                <div class="small mt-1" style="white-space: pre-wrap;">{{ $a->body }}</div>
                            @endif
                            <div class="d-flex justify-content-between mt-2">
                                <span class="small text-muted">記録者: {{ $a->user?->name ?? '?' }}</span>
                                <div class="d-flex gap-2">
                                    @if ($a->follow_up_at)
                                        @php
                                            $isOverdue = $a->follow_up_at->isPast() && ! $a->follow_up_done;
                                            $badge = $a->follow_up_done ? 'success' : ($isOverdue ? 'danger' : 'warning');
                                        @endphp
                                        <form method="POST" action="{{ route('admin.activities.toggle-follow-up', $a) }}" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-link p-0 small">
                                                <span class="badge bg-{{ $badge }}">
                                                    @if ($a->follow_up_done) <i class="bi bi-check"></i> @endif
                                                    フォロー: {{ $a->follow_up_at->format('m/d') }}
                                                </span>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.activities.destroy', $a) }}" class="d-inline" onsubmit="return confirm('削除しますか？');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash small"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
