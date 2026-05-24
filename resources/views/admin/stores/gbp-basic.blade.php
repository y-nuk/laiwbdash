<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 fw-bold mb-0">{{ $store->name }}</h1>
                @if ($store->company)
                    <a href="{{ route('admin.companies.show', $store->company) }}" class="small text-decoration-none">
                        <i class="bi bi-building"></i> {{ $store->company->name }}
                    </a>
                @endif
            </div>
            <div class="small text-muted">
                @if ($store->gbp_last_synced_at)
                    最終 GBP 同期：{{ $store->gbp_last_synced_at->format('Y/m/d H:i') }}
                @else
                    <span class="text-warning"><i class="bi bi-info-circle"></i> 未同期（API 申請中）</span>
                @endif
            </div>
        </div>
    </x-slot>

    @include('admin.stores._tabs', ['active' => 'gbp'])

    {{-- サブタブ：GBP 基本情報の中の細分（属性 / サービスは API 後） --}}
    <ul class="nav nav-pills mb-3 small">
        <li class="nav-item">
            <a class="nav-link active" href="#"><i class="bi bi-info-circle"></i> 基本情報</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled text-muted" href="#">属性（API 後）</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled text-muted" href="#">サービス（API 後）</a>
        </li>
    </ul>

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <div class="alert alert-warning small d-flex align-items-start gap-2 mb-3">
        <i class="bi bi-info-circle mt-1"></i>
        <div>
            <strong>API 申請中のため、現在は手入力での運用です。</strong>
            ここで入力した内容は、GBP API 連携完了後、1日1回 GBP に自動反映される想定の項目です。<br>
            「<strong>改ざん防止</strong>」を ON にすると、GBP 側で誰かが変更しても、こちらの値で上書き同期します（API 連携後）。
        </div>
    </div>

    <form method="post" action="{{ route('admin.stores.gbp-basic.update', $store) }}">
        @csrf @method('patch')

        <div class="row g-3">

            {{-- ===== 左カラム：店舗情報 (NAP) ===== --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h2 class="h6 fw-bold mb-0">店舗情報（NAP情報）</h2>
                        <div class="form-check form-switch small">
                            <input type="hidden" name="gbp_protected" value="0">
                            <input class="form-check-input" type="checkbox" id="gbp_protected" name="gbp_protected" value="1"
                                   {{ old('gbp_protected', $store->gbp_protected) ? 'checked' : '' }}>
                            <label for="gbp_protected" class="form-check-label">改ざん防止</label>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">営業ステータス <i class="bi bi-google text-primary"></i></label>
                            <div class="d-flex gap-3 flex-wrap">
                                @foreach (\App\Models\Store::BUSINESS_STATUSES as $key => $label)
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="business_status" id="bs_{{ $key }}"
                                               value="{{ $key }}" @checked(old('business_status', $store->business_status) === $key)>
                                        <label for="bs_{{ $key }}" class="form-check-label">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">メインカテゴリ <i class="bi bi-google text-primary"></i></label>
                            <input type="text" name="primary_category" value="{{ old('primary_category', $store->primary_category) }}"
                                   placeholder="例：美容室 / 整体院 / 塗装工" class="form-control @error('primary_category') is-invalid @enderror">
                            @error('primary_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text small">GBP のメインカテゴリ。後で Google が提示する候補から選択 UI に変更予定。</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">サブカテゴリ <i class="bi bi-google text-primary"></i></label>
                            <textarea name="additional_categories" rows="3" class="form-control"
                                      placeholder="1 行に 1 つ。例：&#10;建設会社&#10;リフォーム業&#10;屋根ふき業者">{{ old('additional_categories', is_array($store->additional_categories) ? implode("\n", $store->additional_categories) : '') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">ウェブサイト URL <i class="bi bi-google text-primary"></i></label>
                            <input type="url" name="website_url" value="{{ old('website_url', $store->website_url) }}"
                                   placeholder="https://..." class="form-control @error('website_url') is-invalid @enderror">
                            @error('website_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">予約リンク <i class="bi bi-google text-primary"></i></label>
                                <input type="url" name="reservation_url" value="{{ old('reservation_url', $store->reservation_url) }}"
                                       placeholder="https://..." class="form-control @error('reservation_url') is-invalid @enderror">
                                @error('reservation_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">メニュー URL <i class="bi bi-google text-primary"></i></label>
                                <input type="url" name="menu_url" value="{{ old('menu_url', $store->menu_url) }}"
                                       placeholder="https://..." class="form-control @error('menu_url') is-invalid @enderror">
                                @error('menu_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">注文 URL <i class="bi bi-google text-primary"></i></label>
                                <input type="url" name="order_url" value="{{ old('order_url', $store->order_url) }}"
                                       placeholder="https://..." class="form-control @error('order_url') is-invalid @enderror">
                                @error('order_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">サービス提供エリア <i class="bi bi-google text-primary"></i></label>
                            <textarea name="service_areas" rows="3" class="form-control"
                                      placeholder="1 行に 1 つ。例：&#10;東京都&#10;埼玉県&#10;神奈川県">{{ old('service_areas', is_array($store->service_areas) ? implode("\n", $store->service_areas) : '') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">開業日 <i class="bi bi-google text-primary"></i></label>
                            <input type="date" name="opening_date" value="{{ old('opening_date', $store->opening_date?->format('Y-m-d')) }}"
                                   class="form-control" style="max-width: 200px;">
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-semibold">ビジネス情報（店舗の簡単な説明） <i class="bi bi-google text-primary"></i></label>
                            <textarea name="description" rows="5" maxlength="750" class="form-control @error('description') is-invalid @enderror"
                                      placeholder="店舗の特徴・強み等（750 文字まで）">{{ old('description', $store->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text small">GBP の「ビジネス情報」フィールド。最大 750 文字。</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== 右カラム：営業時間 ===== --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent">
                        <h2 class="h6 fw-bold mb-0">営業時間 <i class="bi bi-google text-primary"></i></h2>
                    </div>
                    <div class="card-body">
                        @foreach (\App\Models\Store::WEEKDAYS as $day => $label)
                            @php($h = $store->getHoursForDay($day))
                            @php($old_closed = old("hours.$day.closed", $h['closed']))
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-3">
                                    <span class="small fw-semibold">{{ $label }}</span>
                                </div>
                                <div class="col-3">
                                    <div class="form-check">
                                        <input type="hidden" name="hours[{{ $day }}][closed]" value="0">
                                        <input type="checkbox" class="form-check-input" id="closed_{{ $day }}"
                                               name="hours[{{ $day }}][closed]" value="1" @checked($old_closed)>
                                        <label for="closed_{{ $day }}" class="form-check-label small">定休</label>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <input type="time" name="hours[{{ $day }}][open]"
                                           value="{{ old("hours.$day.open", $h['open']) }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-3">
                                    <input type="time" name="hours[{{ $day }}][close]"
                                           value="{{ old("hours.$day.close", $h['close']) }}" class="form-control form-control-sm">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="alert alert-light border small mt-3">
                    <strong>特別営業時間</strong>（年末年始・祝日等）は API 連携後に対応予定です。
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('admin.stores.show', $store) }}" class="btn btn-outline-secondary">キャンセル</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg"></i> 保存する
            </button>
        </div>
    </form>
</x-app-layout>
