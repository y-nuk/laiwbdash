<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 fw-bold mb-0">アンケート</h1>
            <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> 新規アンケート
            </a>
        </div>
    </x-slot>

    <div class="container-fluid">
        @if (session('status'))
            <div class="alert alert-success small">{{ session('status') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ $currentQ }}" class="form-control form-control-sm" placeholder="タイトル or 店舗名で検索">
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
                            <th>タイトル</th>
                            <th>店舗</th>
                            <th>状態</th>
                            <th>回答数</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($surveys as $s)
                            <tr>
                                <td>{{ $s->id }}</td>
                                <td><a href="{{ route('admin.surveys.show', $s) }}">{{ $s->title }}</a></td>
                                <td>
                                    <div>{{ $s->store->name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $s->store->company->name }}</div>
                                </td>
                                <td>
                                    @if ($s->is_active)
                                        <span class="badge bg-success">公開中</span>
                                    @else
                                        <span class="badge bg-secondary">停止</span>
                                    @endif
                                </td>
                                <td>{{ $s->responses_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.surveys.edit', $s) }}" class="btn btn-sm btn-link p-0">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">アンケートはありません</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $surveys->links() }}</div>
    </div>
</x-app-layout>
