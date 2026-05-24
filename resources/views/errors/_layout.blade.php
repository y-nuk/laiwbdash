{{-- エラーページ共通レイアウト。$code, $title, $message を expects --}}
<x-public-layout>
    <x-slot name="title">{{ $title }}</x-slot>

    <div class="text-center py-5">
        <div class="display-1 fw-bold text-primary mb-3" style="font-size: 6rem;">{{ $code }}</div>
        <h1 class="h4 fw-bold mb-3">{{ $title }}</h1>
        <p class="text-muted small mx-auto" style="max-width: 480px;">
            {!! $message !!}
        </p>

        <div class="mt-4 d-flex gap-2 justify-content-center flex-wrap">
            <a href="/" class="btn btn-primary">
                <i class="bi bi-house"></i> トップへ
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                    <i class="bi bi-speedometer2"></i> ダッシュボード
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary">
                    <i class="bi bi-box-arrow-in-right"></i> ログイン
                </a>
            @endauth
            <a href="{{ route('public.contact') }}" class="btn btn-outline-secondary">
                <i class="bi bi-envelope"></i> お問い合わせ
            </a>
        </div>
    </div>
</x-public-layout>
