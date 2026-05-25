<x-guest-layout>
    <div class="text-center mb-4">
        <h2 class="h5 fw-bold mb-1">laiweb-dash へようこそ</h2>
        <p class="text-muted small mb-0">
            @if ($user->company)
                <strong>{{ $user->company->name }}</strong> 様
            @endif
        </p>
        <p class="text-muted small">
            {{ $user->email }} のパスワードを設定して、ログインを完了してください。
        </p>
    </div>

    <form method="POST" action="{{ route('invitation.accept', ['token' => $token]) }}">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold">パスワード（8 文字以上）</label>
            <input id="password" type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label small fw-semibold">パスワード（確認）</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   class="form-control" required autocomplete="new-password">
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input @error('terms') is-invalid @enderror"
                   type="checkbox" name="terms" id="terms" value="1"
                   {{ old('terms') ? 'checked' : '' }} required>
            <label class="form-check-label small" for="terms">
                <a href="https://laiweb.jp/terms/" target="_blank" rel="noopener">Laiweb 利用規約</a>、
                <a href="https://laiweb.jp/terms/laiweb-dash/" target="_blank" rel="noopener">laiweb-dash 個別規定</a>、
                <a href="https://laiweb.jp/privacy/" target="_blank" rel="noopener">プライバシーポリシー</a>
                に同意します
            </label>
            @error('terms')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> パスワードを設定してログイン
            </button>
        </div>
    </form>
</x-guest-layout>
