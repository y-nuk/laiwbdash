<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 fw-bold mb-0">{{ $survey->exists ? 'アンケート編集' : '新規 アンケート' }}</h1>
    </x-slot>

    <div class="container-fluid" style="max-width: 820px;">
        @if ($errors->any())
            <div class="alert alert-danger small">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ $survey->exists ? route('admin.surveys.update', $survey) : route('admin.surveys.store') }}">
            @csrf
            @if ($survey->exists) @method('PATCH') @endif

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-shop"></i> 対象店舗</h2>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">会社・店舗</label>
                        <select name="store_id" class="form-select" required>
                            <option value="">-- 店舗を選択 --</option>
                            @foreach ($companies as $company)
                                <optgroup label="{{ $company->name }}">
                                    @foreach ($company->stores as $store)
                                        <option value="{{ $store->id }}" {{ old('store_id', $survey->store_id) == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-chat-square-text"></i> アンケート内容</h2>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">タイトル（顧客に表示）</label>
                        <input type="text" name="title" class="form-control" required maxlength="255" value="{{ old('title', $survey->title ?? '店舗ご利用アンケート') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">説明文（任意）</label>
                        <textarea name="description" rows="2" class="form-control" maxlength="1000">{{ old('description', $survey->description) }}</textarea>
                    </div>

                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is-active-cb" {{ old('is_active', $survey->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="is-active-cb">公開中（QR スキャン後の回答受付を有効化）</label>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-star"></i> 評価による分岐</h2>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">高評価しきい値（N 以上で Google レビュー誘導）</label>
                            <select name="high_rating_threshold" class="form-select" required>
                                @foreach ([3, 4, 5] as $n)
                                    <option value="{{ $n }}" {{ old('high_rating_threshold', $survey->high_rating_threshold ?? 4) == $n ? 'selected' : '' }}>★ {{ $n }} 以上</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Google レビュー URL（高評価誘導先）</label>
                        <input type="url" name="google_review_url" class="form-control" placeholder="https://g.page/r/..." value="{{ old('google_review_url', $survey->google_review_url) }}">
                        <div class="form-text small">
                            店舗の Google ビジネスプロフィール → 「プロフィールを共有」→ 「レビューを書いてもらう」から取得できる短縮 URL を貼ってください。
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">低評価時のメッセージ</label>
                        <textarea name="low_rating_message" rows="2" class="form-control" maxlength="2000">{{ old('low_rating_message', $survey->low_rating_message ?? '貴重なご意見をありがとうございます。担当者が確認の上、改善に努めてまいります。') }}</textarea>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">送信完了時のメッセージ</label>
                        <textarea name="thank_you_message" rows="2" class="form-control" maxlength="2000">{{ old('thank_you_message', $survey->thank_you_message ?? 'ご回答ありがとうございました。') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mb-5">
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ $survey->exists ? '更新する' : '作成する' }}</button>
                <a href="{{ route('admin.surveys.index') }}" class="btn btn-link">キャンセル</a>
            </div>
        </form>
    </div>
</x-app-layout>
