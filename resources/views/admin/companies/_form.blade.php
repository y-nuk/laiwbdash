@php
    $isEdit = $company->exists ?? false;
@endphp

@csrf
@if ($isEdit)
    @method('patch')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label small fw-semibold">担当代理店 <span class="text-danger">*</span></label>
        <select name="agency_id" class="form-select @error('agency_id') is-invalid @enderror" required>
            @foreach ($agencies as $a)
                <option value="{{ $a->id }}" @selected(old('agency_id', $company->agency_id) == $a->id)>{{ $a->name }}</option>
            @endforeach
        </select>
        @error('agency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label small fw-semibold">ステータス <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            @foreach (\App\Models\Company::STATUSES as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $company->status) === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-8">
        <label class="form-label small fw-semibold">会社名 <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $company->name) }}" required class="form-control @error('name') is-invalid @enderror">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label small fw-semibold">カナ</label>
        <input type="text" name="kana" value="{{ old('kana', $company->kana) }}" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label small fw-semibold">担当者名 <span class="text-danger">*</span></label>
        <input type="text" name="contact_person_name" value="{{ old('contact_person_name', $company->contact_person_name) }}" required class="form-control @error('contact_person_name') is-invalid @enderror">
        @error('contact_person_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label small fw-semibold">担当者メールアドレス <span class="text-danger">*</span></label>
        <input type="email" name="email" value="{{ old('email', $company->email) }}" required class="form-control @error('email') is-invalid @enderror">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @unless ($isEdit)
            <div class="form-text small text-primary">
                <i class="bi bi-envelope-paper"></i> 登録と同時に、このアドレス宛にログイン招待メールが自動送信されます。
            </div>
        @endunless
    </div>

    <div class="col-md-4">
        <label class="form-label small fw-semibold">電話番号</label>
        <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label small fw-semibold">FAX</label>
        <input type="text" name="fax" value="{{ old('fax', $company->fax) }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label small fw-semibold">業種</label>
        <input type="text" name="industry" value="{{ old('industry', $company->industry) }}" placeholder="例：美容室" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label small fw-semibold">郵便番号</label>
        <input type="text" name="postal_code" value="{{ old('postal_code', $company->postal_code) }}" placeholder="000-0000" class="form-control">
    </div>

    <div class="col-md-9">
        <label class="form-label small fw-semibold">住所</label>
        <input type="text" name="address" value="{{ old('address', $company->address) }}" class="form-control">
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">キャンセル</a>
    <button type="submit" class="btn btn-primary px-4">
        @if ($isEdit)
            <i class="bi bi-check-lg"></i> 更新する
        @else
            <i class="bi bi-plus-lg"></i> 登録する
        @endif
    </button>
</div>
