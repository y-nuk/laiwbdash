@php($isEdit = $store->exists ?? false)

@csrf
@if ($isEdit) @method('patch') @endif

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label small fw-semibold">所属会社 <span class="text-danger">*</span></label>
        <select name="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
            <option value="">— 選択 —</option>
            @foreach ($companies as $c)
                <option value="{{ $c->id }}" @selected(old('company_id', $store->company_id) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label small fw-semibold">GBP 連携状態 <span class="text-danger">*</span></label>
        <select name="gbp_status" class="form-select @error('gbp_status') is-invalid @enderror">
            @foreach (\App\Models\Store::GBP_STATUSES as $key => $label)
                <option value="{{ $key }}" @selected(old('gbp_status', $store->gbp_status) === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('gbp_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label small fw-semibold">店舗名（内部識別用）<span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $store->name) }}" required class="form-control @error('name') is-invalid @enderror">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label small fw-semibold">ビジネス名（GBP 表示用）</label>
        <input type="text" name="business_name" value="{{ old('business_name', $store->business_name) }}" placeholder="Google マップに表示される名前" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label small fw-semibold">業種</label>
        <input type="text" name="industry" value="{{ old('industry', $store->industry) }}" placeholder="例：美容室" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label small fw-semibold">郵便番号</label>
        <input type="text" name="postal_code" value="{{ old('postal_code', $store->postal_code) }}" placeholder="000-0000" class="form-control">
    </div>

    <div class="col-md-5">
        <label class="form-label small fw-semibold">電話番号</label>
        <input type="text" name="phone" value="{{ old('phone', $store->phone) }}" class="form-control">
    </div>

    <div class="col-12">
        <label class="form-label small fw-semibold">住所</label>
        <input type="text" name="address" value="{{ old('address', $store->address) }}" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label small fw-semibold">GBP Place ID</label>
        <input type="text" name="gbp_place_id" value="{{ old('gbp_place_id', $store->gbp_place_id) }}" placeholder="ChIJxxxxxxxxxxxxxxxxx" class="form-control">
        <div class="form-text small">Google マップの場所 ID。手入力するか、後で GBP API で自動取得。</div>
    </div>

    <div class="col-md-6">
        <label class="form-label small fw-semibold">GBP Location ID（API 後）</label>
        <input type="text" name="gbp_location_id" value="{{ old('gbp_location_id', $store->gbp_location_id) }}" class="form-control" readonly>
        <div class="form-text small">GBP API 連携で自動入力されます。</div>
    </div>

    <div class="col-md-6">
        <div class="form-check mt-3">
            <input type="hidden" name="has_gbp" value="0">
            <input type="checkbox" name="has_gbp" value="1" id="has_gbp" class="form-check-input" {{ old('has_gbp', $store->has_gbp) ? 'checked' : '' }}>
            <label for="has_gbp" class="form-check-label small">GBP 連携対象</label>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-check mt-3">
            <input type="hidden" name="has_yahoo" value="0">
            <input type="checkbox" name="has_yahoo" value="1" id="has_yahoo" class="form-check-input" {{ old('has_yahoo', $store->has_yahoo) ? 'checked' : '' }}>
            <label for="has_yahoo" class="form-check-label small">Yahoo! プレイス連携</label>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    <a href="{{ route('admin.stores.index') }}" class="btn btn-outline-secondary">キャンセル</a>
    <button type="submit" class="btn btn-primary px-4">
        @if ($isEdit)
            <i class="bi bi-check-lg"></i> 更新する
        @else
            <i class="bi bi-plus-lg"></i> 登録する
        @endif
    </button>
</div>
