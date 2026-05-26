<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 fw-bold mb-0">アンケート結果</h1>
    </x-slot>

    <div class="container-fluid">
        <p class="text-muted small mb-3">
            運営担当が設定した QR コード経由のアンケート結果を確認できます。
        </p>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>タイトル</th>
                            <th>店舗</th>
                            <th>状態</th>
                            <th class="text-end">回答数</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($surveys as $s)
                            <tr>
                                <td>{{ $s->title }}</td>
                                <td>{{ $s->store->name }}</td>
                                <td>
                                    @if ($s->is_active)
                                        <span class="badge bg-success">公開中</span>
                                    @else
                                        <span class="badge bg-secondary">停止</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold">{{ $s->responses_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('client.surveys.show', $s) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-bar-chart"></i> 集計を見る
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    まだアンケートが設定されていません。<br>
                                    <span class="small">運営担当に QR コードの作成を相談してください。</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $surveys->links() }}</div>
    </div>
</x-app-layout>
