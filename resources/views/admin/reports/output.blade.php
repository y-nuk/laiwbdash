<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 fw-bold mb-0">レポート出力</h1>
    </x-slot>

    <div class="container-fluid" style="max-width: 720px;">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <p class="small text-muted">
                    店舗と対象月を選んで、PDF レポートを即時ダウンロード or プレビューできます。
                </p>

                @if ($errors->any())
                    <div class="alert alert-danger small">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.reports.download') }}" target="_blank">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">会社・店舗</label>
                        <select name="store_id" class="form-select" required>
                            <option value="">-- 店舗を選択 --</option>
                            @foreach ($companies as $company)
                                <optgroup label="{{ $company->name }}">
                                    @foreach ($company->stores as $store)
                                        <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">対象月</label>
                        <input type="month" name="month" class="form-control" value="{{ old('month', $currentMonth) }}" required>
                        <div class="form-text small">YYYY-MM 形式。直前月がデフォルト。</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">担当者コメント <span class="text-muted fw-normal">（任意）</span></label>
                        <textarea name="comment" rows="4" class="form-control" placeholder="今月の所感、次月のアクション等を記入...">{{ old('comment') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-file-earmark-pdf"></i> PDF ダウンロード
                        </button>
                        <button type="submit" formaction="{{ route('admin.reports.preview') }}" formtarget="_blank" class="btn btn-outline-secondary">
                            <i class="bi bi-eye"></i> プレビュー
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-4 small text-muted">
            <p class="mb-1"><i class="bi bi-info-circle"></i> 月次レポートの構成：</p>
            <ol class="ps-3">
                <li>表紙（会社名・店舗・期間・出力日）</li>
                <li>KPI サマリー（計測 KW 数 / 平均順位 / 1〜3 位 KW / 圏外 KW） + 平均順位推移グラフ</li>
                <li>キーワード別 順位推移グラフ</li>
                <li>キーワード別 日次データ表（横向き）</li>
                <li>担当者コメント</li>
            </ol>
        </div>
    </div>
</x-app-layout>
