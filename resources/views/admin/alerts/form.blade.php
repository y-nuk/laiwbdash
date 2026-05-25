<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 fw-bold mb-0">{{ $alert->exists ? 'アラート編集' : '新規 順位アラート' }}</h1>
    </x-slot>

    <div class="container-fluid" style="max-width: 820px;">
        @if ($errors->any())
            <div class="alert alert-danger small">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ $alert->exists ? route('admin.alerts.update', $alert) : route('admin.alerts.store') }}">
            @csrf
            @if ($alert->exists) @method('PATCH') @endif

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-bell"></i> アラート設定</h2>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">アラート名</label>
                        <input type="text" name="name" maxlength="100" required class="form-control" value="{{ old('name', $alert->name ?? '順位下落アラート') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">対象店舗</label>
                        <select name="store_id" class="form-select" required id="store-select" onchange="reloadKeywords()">
                            <option value="">-- 店舗を選択 --</option>
                            @foreach ($companies as $company)
                                <optgroup label="{{ $company->name }}">
                                    @foreach ($company->stores as $store)
                                        <option value="{{ $store->id }}"
                                            data-keywords='@json($store->keywords->map(fn($k)=>['id'=>$k->id,'keyword'=>$k->keyword]))'
                                            {{ old('store_id', $alert->store_id) == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">対象キーワード <span class="text-muted fw-normal">（空欄=店舗の全 KW 対象）</span></label>
                        <select name="keyword_id" class="form-select" id="keyword-select">
                            <option value="">店舗の全キーワードを対象</option>
                            @if ($alert->store)
                                @foreach ($alert->store->keywords as $kw)
                                    <option value="{{ $kw->id }}" {{ old('keyword_id', $alert->keyword_id) == $kw->id ? 'selected' : '' }}>
                                        {{ $kw->keyword }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">アラート種別</label>
                        <select name="alert_type" class="form-select" required>
                            @foreach ($types as $key => $label)
                                <option value="{{ $key }}" {{ old('alert_type', $alert->alert_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">しきい値（N 位）</label>
                        <input type="number" name="threshold" min="0" max="200" class="form-control" style="max-width: 160px;" value="{{ old('threshold', $alert->threshold ?? 5) }}" required>
                        <div class="form-text small">
                            ranking_drop: 前回比で N 位以上下落で発報 / out_of_rank: 値は無視 / worse_than: 現在順位 > N で発報
                        </div>
                    </div>

                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="enabled" value="1" id="enabled-cb" {{ old('enabled', $alert->enabled ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="enabled-cb">アラートを有効にする</label>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-envelope"></i> 通知先メールアドレス</h2>
                    <textarea name="recipients" rows="2" class="form-control" placeholder="空欄の場合は管理者メアド ({{ env('ADMIN_EMAIL', 'admin@laiweb-dash.com') }}) に送信">{{ old('recipients', $alert->recipients) }}</textarea>
                    <div class="form-text small">カンマ区切りで複数指定可。空欄なら管理者メアドに通知。</div>

                    <div class="mt-3">
                        <label class="form-label small fw-semibold">運営メモ（任意）</label>
                        <textarea name="admin_comment" rows="2" class="form-control" placeholder="設定理由、クライアント要望等">{{ old('admin_comment', $alert->admin_comment) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mb-5">
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ $alert->exists ? '更新する' : '登録する' }}</button>
                <a href="{{ route('admin.alerts.index') }}" class="btn btn-link">キャンセル</a>
            </div>
        </form>
    </div>

    <script>
        function reloadKeywords() {
            const sel = document.getElementById('store-select');
            const opt = sel.options[sel.selectedIndex];
            const kws = JSON.parse(opt.dataset.keywords || '[]');
            const target = document.getElementById('keyword-select');
            target.innerHTML = '<option value="">店舗の全キーワードを対象</option>';
            kws.forEach(kw => {
                const o = document.createElement('option');
                o.value = kw.id;
                o.textContent = kw.keyword;
                target.appendChild(o);
            });
        }
    </script>
</x-app-layout>
