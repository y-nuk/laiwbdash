<x-public-layout>
    <div class="container py-4 text-center" style="max-width: 540px;">
        <div class="mb-4">
            <div style="font-size: 4rem;">🎉</div>
            <h1 class="h4 fw-bold mt-2">高評価ありがとうございます！</h1>
            <p class="text-muted">よろしければ Google マップにもレビューをお願いいたします。</p>
        </div>

        @if ($survey->google_review_url)
            <a href="{{ $survey->google_review_url }}" class="btn btn-primary btn-lg w-100 mb-3" target="_blank" rel="noopener">
                <i class="bi bi-google"></i> Google にレビューを書く
            </a>
        @endif

        <p class="text-muted small">
            {{ $survey->thank_you_message ?? 'ご回答ありがとうございました。' }}
        </p>
    </div>
</x-public-layout>
