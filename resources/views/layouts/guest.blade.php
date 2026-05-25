<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'laiweb-dash') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon-16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/apple-touch-icon.png') }}">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light d-flex flex-column min-vh-100">
    <main class="flex-grow-1 d-flex align-items-center justify-content-center py-5">
        <div class="container" style="max-width: 480px;">
            <div class="text-center mb-4">
                <a href="/" class="brand brand--lg d-inline-flex">
                    <img src="{{ asset('img/laiweb-dash-icon.png') }}" alt="" class="brand-icon">
                    <span class="brand-text">Laiweb dash</span>
                </a>
                <p class="small text-muted mb-0 mt-2">多店舗 MEO 運用ツール</p>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    {{ $slot }}
                </div>
            </div>

            <p class="text-center small text-muted mt-3 mb-0">
                ログインすることで、
                <a href="https://laiweb.jp/terms/" target="_blank" rel="noopener" class="text-muted">Laiweb 利用規約</a>、
                <a href="https://laiweb.jp/terms/laiweb-dash/" target="_blank" rel="noopener" class="text-muted">laiweb-dash 個別規定</a>、
                <a href="https://laiweb.jp/privacy/" target="_blank" rel="noopener" class="text-muted">プライバシーポリシー</a>
                に同意したものとみなされます。
            </p>
        </div>
    </main>

    @include('partials.footer')
</body>
</html>
