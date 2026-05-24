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

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light d-flex flex-column min-vh-100">
    <main class="flex-grow-1 d-flex align-items-center justify-content-center py-5">
        <div class="container" style="max-width: 480px;">
            <div class="text-center mb-4">
                <h1 class="h3 fw-bold text-primary">laiweb-dash</h1>
                <p class="small text-muted mb-0">多店舗 MEO 運用ツール</p>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </main>
</body>
</html>
