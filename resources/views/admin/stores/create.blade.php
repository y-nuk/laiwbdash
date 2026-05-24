<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1 class="h4 fw-bold mb-0">新規店舗登録</h1>
            <a href="{{ route('admin.stores.index') }}" class="small">← 一覧に戻る</a>
        </div>
    </x-slot>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="post" action="{{ route('admin.stores.store') }}">
                @include('admin.stores._form')
            </form>
        </div>
    </div>
</x-app-layout>
