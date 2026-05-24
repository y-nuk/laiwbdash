<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? '' }}{{ isset($title) ? ' | ' : '' }}laiweb-dash</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    {{-- ヘッダー --}}
    <header class="bg-white border-bottom">
        <div class="container py-3 d-flex justify-content-between align-items-center">
            <a href="/" class="text-decoration-none">
                <h1 class="h5 fw-bold text-primary mb-0">laiweb-dash</h1>
                <p class="text-muted small mb-0" style="font-size: 0.7rem;">多店舗 MEO 運用ツール</p>
            </a>
            <nav class="small">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-decoration-none">マイページ →</a>
                @else
                    <a href="{{ route('login') }}" class="text-decoration-none">ログイン →</a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- 本文 --}}
    <main class="flex-grow-1 py-4">
        <div class="container" style="max-width: 800px;">
            {{ $slot }}
        </div>
    </main>

    {{-- フッター --}}
    <footer class="bg-white border-top py-4 mt-5">
        <div class="container" style="max-width: 800px;">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div class="small">
                    <strong>laiweb-dash</strong><br>
                    運営：株式会社 L'aide<br>
                    <span class="text-muted">多店舗 MEO 運用ツール</span>
                </div>
                <ul class="list-unstyled small text-end mb-0">
                    <li><a href="{{ route('public.privacy') }}" class="text-decoration-none">プライバシーポリシー</a></li>
                    <li><a href="{{ route('public.terms') }}" class="text-decoration-none">利用規約</a></li>
                    <li><a href="{{ route('public.contact') }}" class="text-decoration-none">お問い合わせ</a></li>
                </ul>
            </div>
            <hr>
            <p class="text-center text-muted small mb-0">
                © {{ date('Y') }} laiweb-dash. All rights reserved.
            </p>
        </div>
    </footer>
</body>
</html>
