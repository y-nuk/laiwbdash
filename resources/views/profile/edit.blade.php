<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 fw-bold mb-0">プロフィール設定</h1>
    </x-slot>

    <div class="container-fluid" style="max-width: 720px;">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                @if (session('status') === 'password-updated')
                    <i class="bi bi-check-circle me-1"></i> パスワードを変更しました。
                @elseif (session('status') === 'profile-updated')
                    <i class="bi bi-check-circle me-1"></i> プロフィール情報を更新しました。
                @else
                    {{ session('status') }}
                @endif
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
</x-app-layout>
