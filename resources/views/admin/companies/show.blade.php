<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1 class="h4 fw-bold mb-0">{{ $company->name }}</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.companies.index') }}" class="small align-self-center">← 一覧</a>
                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> 編集
                </a>
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-building"></i> 基本情報</h2>
        </div>
        <div class="card-body">
            <dl class="row mb-0 small">
                <dt class="col-sm-3 text-muted">会社 ID</dt>
                <dd class="col-sm-9">{{ $company->id }}</dd>

                <dt class="col-sm-3 text-muted">会社名</dt>
                <dd class="col-sm-9">
                    {{ $company->name }}
                    @if ($company->kana)<span class="text-muted small ms-2">{{ $company->kana }}</span>@endif
                </dd>

                <dt class="col-sm-3 text-muted">担当代理店</dt>
                <dd class="col-sm-9">{{ $company->agency->name ?? '—' }}</dd>

                <dt class="col-sm-3 text-muted">ステータス</dt>
                <dd class="col-sm-9">
                    @php($s = $company->status)
                    <span class="badge {{ $s === 'active' ? 'text-bg-success' : ($s === 'suspended' ? 'text-bg-warning' : 'text-bg-secondary') }}">
                        {{ \App\Models\Company::STATUSES[$s] ?? $s }}
                    </span>
                </dd>

                <dt class="col-sm-3 text-muted">業種</dt>
                <dd class="col-sm-9">{{ $company->industry ?? '—' }}</dd>

                <dt class="col-sm-3 text-muted">担当者名</dt>
                <dd class="col-sm-9">{{ $company->contact_person_name ?? '—' }}</dd>

                <dt class="col-sm-3 text-muted">メールアドレス</dt>
                <dd class="col-sm-9">{{ $company->email ?? '—' }}</dd>

                <dt class="col-sm-3 text-muted">電話 / FAX</dt>
                <dd class="col-sm-9">
                    {{ $company->phone ?? '—' }}
                    @if ($company->fax)<span class="text-muted ms-2">/ FAX: {{ $company->fax }}</span>@endif
                </dd>

                <dt class="col-sm-3 text-muted">住所</dt>
                <dd class="col-sm-9">
                    @if ($company->postal_code)〒{{ $company->postal_code }} @endif
                    {{ $company->address ?? '—' }}
                </dd>

                <dt class="col-sm-3 text-muted">登録日</dt>
                <dd class="col-sm-9 text-muted">{{ $company->created_at->format('Y/m/d H:i') }}</dd>
            </dl>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-shop"></i> 登録店舗 ({{ $company->stores->count() }})</h2>
            <a href="{{ route('admin.stores.create', ['company_id' => $company->id]) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-lg"></i> 店舗を追加
            </a>
        </div>
        <div class="card-body">
            @if ($company->stores->isEmpty())
                <p class="text-muted small mb-0">まだ店舗が登録されていません。</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($company->stores as $store)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.stores.show', $store) }}" class="text-decoration-none fw-semibold">{{ $store->name }}</a>
                                <div class="small text-muted">{{ $store->industry ?? '—' }}</div>
                            </div>
                            <span class="badge text-bg-light">{{ \App\Models\Store::GBP_STATUSES[$store->gbp_status] ?? $store->gbp_status }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-people"></i> 関連ユーザー ({{ $company->users->count() }})</h2>
            @if ($company->email)
                <form method="post" action="{{ route('admin.companies.resend-invitation', $company) }}"
                      onsubmit="return confirm('{{ $company->email }} に招待メールを再送しますか？');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-envelope-arrow-up"></i> 招待を再送
                    </button>
                </form>
            @endif
        </div>
        <div class="card-body">
            @if ($company->users->isEmpty())
                <p class="text-muted small mb-0">この会社にひも付いているユーザーはまだいません。</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($company->users as $u)
                        <li class="list-group-item d-flex justify-content-between align-items-center small">
                            <div>
                                <div>{{ $u->name }}</div>
                                <div class="text-muted">{{ $u->email }}</div>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                @if ($u->isPendingInvitation())
                                    <span class="badge text-bg-warning">招待送信済（パス未設定）</span>
                                @elseif ($u->invitation_accepted_at)
                                    <span class="badge text-bg-success">アクティブ</span>
                                @endif
                                <span class="badge text-bg-light">{{ \App\Models\User::ROLES[$u->role] ?? $u->role }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h2 class="h6 fw-bold mb-0 text-danger"><i class="bi bi-exclamation-triangle"></i> 危険操作</h2>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.companies.destroy', $company) }}"
                  onsubmit="return confirm('{{ $company->name }} を削除しますか？店舗データは残ります（後で復元可）。');">
                @csrf @method('delete')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash"></i> この会社を削除
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
