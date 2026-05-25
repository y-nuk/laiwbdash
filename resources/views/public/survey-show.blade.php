<x-public-layout>
    <div class="container py-4" style="max-width: 600px;">
        <div class="text-center mb-4">
            <h1 class="h4 fw-bold">{{ $survey->title }}</h1>
            <p class="text-muted small mb-0">{{ $survey->store->name }}</p>
        </div>

        @if ($survey->description)
            <div class="alert alert-light border small">{!! nl2br(e($survey->description)) !!}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger small">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('public.survey.store', $survey->token) }}" class="card border-0 shadow-sm">
            @csrf
            <div class="card-body p-4">
                <div class="mb-4 text-center">
                    <label class="form-label fw-bold mb-3">ご利用いかがでしたか？</label>
                    <div class="star-rating d-flex justify-content-center gap-2" role="radiogroup">
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="d-inline-block">
                                <input type="radio" name="overall_rating" value="{{ $i }}" required class="visually-hidden star-input" {{ old('overall_rating') == $i ? 'checked' : '' }}>
                                <span class="star-icon" data-value="{{ $i }}" style="font-size: 2.5rem; cursor: pointer; color: #d1d5db; transition: color 0.1s;">★</span>
                            </label>
                        @endfor
                    </div>
                    <div class="small text-muted mt-2" id="rating-label">星をタップして評価してください</div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">コメント（任意）</label>
                    <textarea name="comment" rows="3" class="form-control" placeholder="ご感想、改善要望などお聞かせください" maxlength="2000">{{ old('comment') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">お名前（任意）</label>
                    <input type="text" name="name" class="form-control" maxlength="100" value="{{ old('name') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">メールアドレス（任意）</label>
                    <input type="email" name="email" class="form-control" maxlength="255" value="{{ old('email') }}">
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="contact_ok" value="1" id="contact-ok-cb" {{ old('contact_ok') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="contact-ok-cb">運営からの連絡を受け取ってもよい</label>
                </div>

                <div class="d-grid">
                    <button class="btn btn-primary btn-lg" type="submit">送信する</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const stars = document.querySelectorAll('.star-icon');
            const inputs = document.querySelectorAll('.star-input');
            const label = document.getElementById('rating-label');
            const labels = {1:'😟 改善が必要', 2:'😐 物足りない', 3:'🙂 普通', 4:'😊 満足', 5:'🤩 大満足'};

            function paint(v) {
                stars.forEach(s => {
                    s.style.color = parseInt(s.dataset.value) <= v ? '#fbbf24' : '#d1d5db';
                });
                if (v > 0) label.textContent = labels[v];
            }

            stars.forEach((s, i) => {
                s.addEventListener('click', () => {
                    inputs[i].checked = true;
                    paint(parseInt(s.dataset.value));
                });
                s.addEventListener('mouseenter', () => paint(parseInt(s.dataset.value)));
            });
            stars[0].parentNode.parentNode.addEventListener('mouseleave', () => {
                const checked = document.querySelector('.star-input:checked');
                paint(checked ? parseInt(checked.value) : 0);
            });
            // initial
            const checked = document.querySelector('.star-input:checked');
            if (checked) paint(parseInt(checked.value));
        });
    </script>
</x-public-layout>
