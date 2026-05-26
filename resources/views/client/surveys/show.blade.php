<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 fw-bold mb-0">{{ $survey->title }}</h1>
    </x-slot>

    <div class="container-fluid">
        <p class="text-muted small mb-3">
            <i class="bi bi-shop"></i> {{ $survey->store->name }}
        </p>

        <div class="row g-3">
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
                                    <div class="kpi-label">★5 評価</div>
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
                        <h2 class="h6 fw-bold mb-3"><i class="bi bi-chat-dots"></i> いただいたコメント</h2>
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
                                            <th>お名前</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentResponses as $r)
                                            <tr>
                                                <td>{{ $r->answered_at->format('m/d H:i') }}</td>
                                                <td>{{ str_repeat('★', $r->overall_rating) }}</td>
                                                <td>{{ $r->responses['comment'] ?? '—' }}</td>
                                                <td>
                                                    {{ $r->responses['name'] ?? '—' }}
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

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h6 fw-bold mb-3"><i class="bi bi-info-circle"></i> QR コード</h2>
                        <p class="small text-muted mb-2">
                            このアンケートの QR コードは運営担当が管理しています。<br>
                            QR コード（または以下の URL）からお客様にアクセスしていただけます。
                        </p>
                        <div class="mt-3">
                            <label class="form-label small fw-semibold">公開 URL</label>
                            <input type="text" class="form-control form-control-sm" value="{{ $survey->publicUrl() }}" readonly onclick="this.select()">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="my-4">
            <a href="{{ route('client.surveys.index') }}" class="btn btn-link">&larr; アンケート一覧へ戻る</a>
        </div>
    </div>
</x-app-layout>
