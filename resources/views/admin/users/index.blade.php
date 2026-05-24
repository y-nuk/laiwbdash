<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 fw-bold mb-0">ユーザー管理</h1>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    {{-- 招待フォーム --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-envelope-plus"></i> ユーザーを招待</h2>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.users.invite') }}" class="row g-2">
                @csrf
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold">役割 <span class="text-danger">*</span></label>
                    <select name="role" id="invite_role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="staff" @selected(old('role') === 'staff')>運営担当（staff）</option>
                        <option value="client" @selected(old('role') === 'client')>クライアント（client）</option>
                    </select>
                </div>
                <div class="col-12 col-md-3" id="invite_company_wrap" style="{{ old('role', 'staff') === 'client' ? '' : 'display: none;' }}">
                    <label class="form-label small fw-semibold">所属会社（client のみ）</label>
                    <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
                        <option value="">— 選択 —</option>
                        @foreach ($companies as $c)
                            <option value="{{ $c->id }}" @selected(old('company_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold">氏名 <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="form-control @error('name') is-invalid @enderror">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold">メールアドレス <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-send"></i> 招待メールを送信
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 検索 --}}
    <form method="get" class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-12 col-md-6">
                    <input type="text" name="q" value="{{ $q }}" placeholder="名前 / メアド" class="form-control">
                </div>
                <div class="col-6 col-md-4">
                    <select name="role" class="form-select">
                        <option value="">役割：すべて</option>
                        @foreach (\App\Models\User::ROLES as $key => $label)
                            <option value="{{ $key }}" @selected($role === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 d-grid">
                    <button type="submit" class="btn btn-outline-primary">検索</button>
                </div>
            </div>
        </div>
    </form>

    {{-- 一覧 --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-people"></i> ユーザー ({{ $users->total() }})</h2>
        </div>
        @if ($users->isEmpty())
            <div class="card-body text-muted small">該当ユーザーがいません。</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0 small">
                    <thead>
                        <tr class="text-muted">
                            <th>ID</th>
                            <th>氏名</th>
                            <th>メアド</th>
                            <th>役割</th>
                            <th>所属</th>
                            <th>状態</th>
                            <th>最終ログイン</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $u)
                            <tr class="{{ $u->isDisabled() ? 'text-muted' : '' }}">
                                <td>{{ $u->id }}</td>
                                <td class="fw-semibold">{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    @php($roleColors = ['admin' => 'text-bg-danger', 'staff' => 'text-bg-primary', 'client' => 'text-bg-info'])
                                    <span class="badge {{ $roleColors[$u->role] ?? 'text-bg-secondary' }}">{{ \App\Models\User::ROLES[$u->role] ?? $u->role }}</span>
                                </td>
                                <td>
                                    @if ($u->company)
                                        <span class="small">{{ $u->company->name }}</span>
                                    @elseif ($u->agency)
                                        <span class="small text-muted">{{ $u->agency->name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($u->isDisabled())
                                        <span class="badge text-bg-secondary">無効</span>
                                    @elseif ($u->isPendingInvitation())
                                        <span class="badge text-bg-warning">招待中</span>
                                    @else
                                        <span class="badge text-bg-success">アクティブ</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $u->last_login_at?->format('Y/m/d H:i') ?? '—' }}
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        @if ($u->isPendingInvitation())
                                            <form method="post" action="{{ route('admin.users.resend', $u) }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-outline-secondary" title="招待再送">
                                                    <i class="bi bi-envelope-arrow-up"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if ($u->isDisabled())
                                            <form method="post" action="{{ route('admin.users.enable', $u) }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-outline-success" title="有効化">
                                                    <i class="bi bi-toggle-on"></i>
                                                </button>
                                            </form>
                                        @elseif (! $u->isAdmin() && $u->id !== auth()->id())
                                            <form method="post" action="{{ route('admin.users.disable', $u) }}" class="d-inline"
                                                  onsubmit="return confirm('{{ $u->email }} を無効化しますか？ログインできなくなります。');">
                                                @csrf
                                                <button class="btn btn-outline-warning" title="無効化">
                                                    <i class="bi bi-toggle-off"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if (! $u->isAdmin() && $u->id !== auth()->id())
                                            <form method="post" action="{{ route('admin.users.destroy', $u) }}" class="d-inline"
                                                  onsubmit="return confirm('{{ $u->email }} を削除しますか？');">
                                                @csrf @method('delete')
                                                <button class="btn btn-outline-danger" title="削除">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <div class="card-footer bg-transparent">{{ $users->links() }}</div>
    </div>

    {{-- role で company_id 表示切替 --}}
    <script>
        document.getElementById('invite_role')?.addEventListener('change', function (e) {
            document.getElementById('invite_company_wrap').style.display = e.target.value === 'client' ? '' : 'none';
        });
    </script>
</x-app-layout>
