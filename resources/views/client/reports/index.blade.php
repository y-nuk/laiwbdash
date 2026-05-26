<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 fw-bold mb-0">月次レポート</h1>
    </x-slot>

    <div class="container-fluid">
        <p class="text-muted small mb-3">
            運営担当が作成した月次 MEO レポートを過去にさかのぼってご覧いただけます。
        </p>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>対象月</th>
                            <th>店舗</th>
                            <th>種別</th>
                            <th>配信日</th>
                            <th class="text-end">PDF</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($reports as $r)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $r->period_start?->isoFormat('Y 年 M 月') }}</span>
                                </td>
                                <td>{{ $r->store?->name ?? '会社全体' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ \App\Models\Report::TYPES[$r->type] ?? $r->type }}
                                    </span>
                                </td>
                                <td>{{ $r->sent_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="text-end">
                                    @if ($r->file_path)
                                        <a href="{{ route('client.reports.download', $r) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i> DL
                                        </a>
                                    @else
                                        <span class="text-muted small">未保存</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    まだ配信されたレポートはありません。<br>
                                    <span class="small">運営担当が月次レポートを配信すると、こちらに表示されます。</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $reports->links() }}</div>
    </div>
</x-app-layout>
