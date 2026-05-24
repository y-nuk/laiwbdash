<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'laiweb-dash') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        {{-- ----- サイドバー ----- --}}
        <aside class="sidebar d-none d-md-block flex-shrink-0" style="width: 240px;">
            <div class="p-3">
                <a href="{{ route('dashboard') }}" class="text-decoration-none">
                    <h2 class="h5 fw-bold text-primary mb-0">laiweb-dash</h2>
                </a>
                <p class="text-muted small mb-0" style="font-size: 0.75rem;">多店舗 MEO 運用</p>
            </div>

            <ul class="nav flex-column px-2">
                @auth
                    @if (auth()->user()->isInternal())
                        {{-- 自社運営側メニュー --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> ダッシュボード
                            </a>
                        </li>
                        <li class="nav-item mt-3 small text-muted px-3" style="font-size: 0.75rem;">マスタ管理</li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}" href="{{ route('admin.companies.index') }}">
                                <i class="bi bi-building"></i> 会社管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.stores.*') ? 'active' : '' }}" href="{{ route('admin.stores.index') }}">
                                <i class="bi bi-shop"></i> 店舗管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="bi bi-people"></i> ユーザー管理
                            </a>
                        </li>
                        <li class="nav-item mt-3 small text-muted px-3" style="font-size: 0.75rem;">レポート</li>
                        <li class="nav-item">
                            <a class="nav-link disabled" href="#"><i class="bi bi-bar-chart"></i> 順位レポート</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled" href="#"><i class="bi bi-file-earmark-pdf"></i> レポート配信</a>
                        </li>
                    @else
                        {{-- クライアント側メニュー --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}" href="{{ route('client.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> ダッシュボード
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled" href="#"><i class="bi bi-bar-chart"></i> 順位レポート</a>
                        </li>
                    @endif
                @endauth
            </ul>
        </aside>

        {{-- ----- メインエリア ----- --}}
        <div class="flex-grow-1 d-flex flex-column">
            {{-- 上部ヘッダー --}}
            <nav class="navbar bg-white border-bottom px-3">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    {{-- モバイル用ハンバーガー（後で実装）--}}
                    <button class="btn btn-sm btn-outline-secondary d-md-none" type="button">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="d-flex align-items-center ms-auto">
                        @auth
                            <div class="dropdown">
                                <button class="btn btn-sm dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle fs-5"></i>
                                    <span class="small">{{ auth()->user()->name }}</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><span class="dropdown-item-text small text-muted">{{ \App\Models\User::ROLES[auth()->user()->role] ?? auth()->user()->role }}</span></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item small" href="{{ route('profile.edit') }}"><i class="bi bi-gear"></i> プロフィール</a></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button class="dropdown-item small"><i class="bi bi-box-arrow-right"></i> ログアウト</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endauth
                    </div>
                </div>
            </nav>

            {{-- ページヘッダ --}}
            @isset($header)
                <header class="bg-white border-bottom px-4 py-3">
                    {{ $header }}
                </header>
            @endisset

            {{-- ページ本体 --}}
            <main class="flex-grow-1 p-3 p-md-4">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
