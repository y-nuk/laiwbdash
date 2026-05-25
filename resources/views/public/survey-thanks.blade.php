<x-public-layout>
    <div class="container py-4 text-center" style="max-width: 540px;">
        <div class="mb-4">
            <div style="font-size: 4rem;">🙏</div>
            <h1 class="h4 fw-bold mt-2">ご回答ありがとうございました</h1>
        </div>

        <div class="alert alert-light border text-start small">
            {!! nl2br(e($survey->low_rating_message ?? $survey->thank_you_message ?? '貴重なご意見をありがとうございます。')) !!}
        </div>
    </div>
</x-public-layout>
