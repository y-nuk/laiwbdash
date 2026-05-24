<x-public-layout>
    <x-slot name="title">お問い合わせ</x-slot>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 fw-bold mb-1">お問い合わせ</h1>
            <p class="text-muted small mb-4">
                laiweb-dash に関するご質問・ご要望はこちらからお寄せください。<br>
                通常 2 営業日以内にご返信いたします。
            </p>

            @if (session('status'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> {{ session('status') }}
                </div>
            @endif

            <form method="post" action="{{ route('public.contact.send') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">お名前 <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">会社名</label>
                        <input type="text" name="company" value="{{ old('company') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">メールアドレス <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">電話番号</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">お問い合わせ種別</label>
                        <select name="category" class="form-select">
                            <option value="general" @selected(old('category') === 'general')>サービスについて</option>
                            <option value="trial" @selected(old('category') === 'trial')>トライアル・導入相談</option>
                            <option value="bug" @selected(old('category') === 'bug')>不具合のご報告</option>
                            <option value="other" @selected(old('category') === 'other')>その他</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">お問い合わせ内容 <span class="text-danger">*</span></label>
                        <textarea name="message" rows="6" required
                                  class="form-control @error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="bi bi-send"></i> 送信する
                        </button>
                    </div>
                </div>
            </form>

            <hr class="my-4">

            <div class="small text-muted">
                <strong>株式会社 L'aide</strong><br>
                多店舗 MEO 運用ツール laiweb-dash 運営事務局
            </div>
        </div>
    </div>
</x-public-layout>
