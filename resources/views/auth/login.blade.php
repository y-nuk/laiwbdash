<x-guest-layout>
    <h2 class="h5 fw-bold text-center mb-4">ログイン</h2>

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold">パスワード</label>
            <input type="password" name="password" id="password"
                   required autocomplete="current-password"
                   class="form-control @error('password') is-invalid @enderror">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="remember" id="remember_me" class="form-check-input">
            <label for="remember_me" class="form-check-label small text-muted">ログイン状態を保持</label>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="small text-decoration-none">パスワードを忘れた？</a>
            @else
                <span></span>
            @endif
            <button type="submit" class="btn btn-primary">ログイン</button>
        </div>
    </form>
</x-guest-layout>
