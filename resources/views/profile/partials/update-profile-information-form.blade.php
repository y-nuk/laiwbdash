<section>
    <header class="mb-4">
        <h2 class="h5 fw-bold mb-1">
            <i class="bi bi-person-circle me-1 text-primary"></i> プロフィール情報
        </h2>
        <p class="small text-muted mb-0">
            お名前とメールアドレスを変更できます。
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label small fw-semibold">お名前</label>
            <input id="name"
                   name="name"
                   type="text"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $user->name) }}"
                   autocomplete="name"
                   required autofocus>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="form-label small fw-semibold">メールアドレス</label>
            <input id="email"
                   name="email"
                   type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $user->email) }}"
                   autocomplete="username"
                   required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> 保存
        </button>
    </form>
</section>
