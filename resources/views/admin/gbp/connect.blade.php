<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 fw-bold mb-0">Google ビジネスプロフィール 連携</h1>
    </x-slot>

    <div class="container-fluid" style="max-width: 720px;">
        @if (session('status'))
            <div class="alert alert-success small">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger small">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @if (! $configured)
            <div class="alert alert-warning small">
                <strong>未構成</strong>：本番 <code>.env</code> に <code>GOOGLE_CLIENT_ID</code> / <code>GOOGLE_CLIENT_SECRET</code> が未設定。
                <br>OneDrive の <code>SQLGoogle\laiwebdash\client_secret_*.json</code> から転記してください。
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-4">
                <h2 class="h6 fw-bold mb-3"><i class="bi bi-google text-primary"></i> 現在の連携状態</h2>

                @if ($user->hasGbpConnected())
                    <div class="d-flex align-items-center mb-3 gap-3">
                        @if ($user->gbp_account_info['avatar'] ?? null)
                            <img src="{{ $user->gbp_account_info['avatar'] }}" alt="" style="width: 48px; height: 48px; border-radius: 50%;">
                        @endif
                        <div>
                            <div class="fw-bold">{{ $user->gbp_account_info['name'] ?? $user->gbp_account_email }}</div>
                            <div class="small text-muted">{{ $user->gbp_account_email }}</div>
                        </div>
                    </div>

                    <table class="table small mb-3">
                        <tr><th style="width: 200px;">トークン状態</th><td>
                            @if ($user->isGbpTokenExpired())
                                <span class="badge bg-warning">期限切れ（リフレッシュ要）</span>
                            @else
                                <span class="badge bg-success">有効</span>
                            @endif
                        </td></tr>
                        <tr><th>トークン期限</th><td>{{ $user->gbp_token_expires_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
                        <tr><th>連携 Google アカウント</th><td>{{ $user->gbp_account_email }}</td></tr>
                    </table>

                    <div class="d-flex gap-2">
                        <a href="{{ route('auth.google.redirect') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-repeat"></i> 再認証（別アカウントに切替）
                        </a>
                        <form method="POST" action="{{ route('auth.google.disconnect') }}" class="d-inline" onsubmit="return confirm('連携を解除しますか？再連携が必要になります。');">
                            @csrf
                            <button class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle"></i> 連携を解除
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-muted small mb-3">
                        まだ Google ビジネスプロフィールと連携していません。<br>
                        連携するには、L'aide が管理権限を持つ Google アカウントでログインしてください。
                    </p>

                    <a href="{{ route('auth.google.redirect') }}" class="btn btn-primary {{ $configured ? '' : 'disabled' }}">
                        <i class="bi bi-google"></i> Sign in with Google
                    </a>

                    @if (! $configured)
                        <div class="form-text small mt-2 text-danger">
                            <i class="bi bi-exclamation-triangle"></i> .env 設定後に有効化されます
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h6 fw-bold mb-3"><i class="bi bi-info-circle"></i> このページについて</h2>
                <p class="small mb-2">
                    laiweb-dash から Google ビジネスプロフィールの情報を取得・編集するには、L'aide が管理権限を持つ Google アカウントで連携する必要があります。
                </p>
                <p class="small mb-0">
                    連携後、以下の機能が利用できます（GBP API 承認後）：
                </p>
                <ul class="small mb-0 mt-2">
                    <li>ビジネス情報の取得・編集（営業時間 / 住所 / 電話 / カテゴリ）</li>
                    <li>クチコミ取得・返信</li>
                    <li>最新情報・イベント・特典の投稿</li>
                    <li>写真管理</li>
                    <li>インサイト分析（検索表示・アクション数）</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
