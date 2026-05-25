<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 fw-bold mb-0">{{ $survey->title }}</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.surveys.edit', $survey) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> 編集
                </a>
                <form method="POST" action="{{ route('admin.surveys.destroy', $survey) }}" class="d-inline" onsubmit="return confirm('このアンケートを削除しますか？');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> 削除</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid">
        @if (session('status'))
            <div class="alert alert-success small">{{ session('status') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <h2 class="h6 fw-bold mb-3"><i class="bi bi-qr-code"></i> QR コード</h2>
                        <img src="{{ route('admin.surveys.qr', $survey) }}" alt="QR" style="width: 240px; height: 240px; max-width: 100%;">
                        <div class="small text-muted mt-2">
                            このコードを店舗の店頭に貼って、お客様にスキャンしてもらいます。
                        </div>
                        <div class="mt-3 d-flex gap-2 justify-content-center">
                            <a href="{{ route('admin.surveys.qr', ['survey' => $survey, 'download' => 1]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i> SVG DL
                            </a>
                            <a href="{{ $survey->publicUrl() }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-box-arrow-up-right"></i> URL を開く
                            </a>
                        </div>
                        <div class="mt-3 small">
                            <input type="text" class="form-control form-control-sm" value="{{ $survey->publicUrl() }}" readonly onclick="this.select()">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-4">
                        <h2 class="h6 fw-bold mb-3"><i class="bi bi-bar-chart-line"></i> 集計</h2>
                        <div class="row g-3 mb-3">
                            <div class="col-sm-4">
                                <div class="kpi-card">
                                    <div class="kpi-label">回答総数</div>
                                    <div class="kpi-value">{{ $survey->responses->count() }}</div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="kpi-card">
                                    <div class="kpi-label">平均評価</div>
                                    <div class="kpi-value">{{ $avg ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="kpi-card">
                                    <div class="kpi-label">5 星の数</div>
                                    <div class="kpi-value">{{ $dist[5] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>

                        <h3 class="small fw-bold mb-2">評価分布</h3>
                        @php $total = array_sum($dist); @endphp
                        @for ($i = 5; $i >= 1; $i--)
                            @php
                                $count = $dist[$i] ?? 0;
                                $pct = $total > 0 ? round($count / $total * 100) : 0;
                            @endphp
                            <div class="d-flex align-items-center mb-1 small">
                                <span style="width: 50px;">★ {{ $i }}</span>
                                <div class="progress flex-grow-1" style="height: 14px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $pct }}%;"></div>
                                </div>
                                <span class="ms-2 text-muted" style="width: 60px; text-align: right;">{{ $count }} 件</span>
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h6 fw-bold mb-3"><i class="bi bi-chat-dots"></i> 最近のコメント</h2>
                        @if ($recentResponses->isEmpty())
                            <p class="text-muted small mb-0">まだ回答がありません。</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm small mb-0">
                                    <thead>
                                        <tr>
                                            <th>日時</th>
                                            <th>評価</th>
                                            <th>コメント</th>
                                            <th>連絡先</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentResponses as $r)
                                            <tr>
                                                <td>{{ $r->answered_at->format('m/d H:i') }}</td>
                                                <td>{{ str_repeat('★', $r->overall_rating) }}</td>
                                                <td>{{ $r->responses['comment'] ?? '—' }}</td>
                                                <td style="font-size: 0.75rem;">
                                                    @if (! empty($r->responses['email']))
                                                        {{ $r->responses['name'] ?? '' }}<br>{{ $r->responses['email'] }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="my-4">
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-link">&larr; 一覧へ戻る</a>
        </div>
    </div>
</x-app-layout>
