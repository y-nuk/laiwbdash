<section>
    <header class="mb-4">
        <h2 class="h5 fw-bold mb-1">
            <i class="bi bi-shield-lock me-1 text-primary"></i> パスワード変更
        </h2>
        <p class="small text-muted mb-0">
            アカウントを安全に保つため、推測されにくい長めのパスワードを設定してください。
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label small fw-semibold">
                現在のパスワード
            </label>
            <input id="update_password_current_password"
                   name="current_password"
                   type="password"
                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                   autocomplete="current-password"
                   required>
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label small fw-semibold">
                新しいパスワード <span class="text-muted fw-normal">（8 文字以上）</span>
            </label>
            <input id="update_password_password"
                   name="password"
                   type="password"
                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                   autocomplete="new-password"
                   required>
            @error('password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="update_password_password_confirmation" class="form-label small fw-semibold">
                新しいパスワード（確認）
            </label>
            <input id="update_password_password_confirmation"
                   name="password_confirmation"
                   type="password"
                   class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                   autocomplete="new-password"
                   required>
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> パスワードを変更
        </button>
    </form>
</section>
