<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 fw-bold mb-0">{{ $schedule->exists ? 'レポート配信予約 編集' : '新規 レポート配信予約' }}</h1>
    </x-slot>

    <div class="container-fluid" style="max-width: 820px;">
        @if ($errors->any())
            <div class="alert alert-danger small">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ $schedule->exists ? route('admin.report-schedules.update', $schedule) : route('admin.report-schedules.store') }}">
            @csrf
            @if ($schedule->exists) @method('PATCH') @endif

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-shop"></i> 対象店舗</h2>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">会社・店舗</label>
                        <select name="store_id" class="form-select" required>
                            <option value="">-- 店舗を選択 --</option>
                            @foreach ($companies as $company)
                                <optgroup label="{{ $company->name }}">
                                    @foreach ($company->stores as $store)
                                        <option value="{{ $store->id }}" {{ old('store_id', $schedule->store_id) == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">レポート名（50 字以内）</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $schedule->name ?? '月次レポート') }}" required maxlength="50">
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-calendar-event"></i> 配信スケジュール</h2>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">配信頻度</label>
                        <select name="recurrence" id="recurrence" class="form-select" required onchange="toggleRecurrence(this.value)">
                            @foreach ($recurrences as $key => $label)
                                <option value="{{ $key }}" {{ old('recurrence', $schedule->recurrence) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3" id="monthly-field" style="display: {{ old('recurrence', $schedule->recurrence) === 'monthly' ? 'block' : 'none' }};">
                        <label class="form-label small fw-semibold">毎月 X 日 に配信（1〜31）</label>
                        <input type="number" name="recurrence_day" min="1" max="31" class="form-control" style="max-width: 120px;" value="{{ old('recurrence_day', $schedule->recurrence_day ?? 5) }}">
                        <div class="form-text small">月末を超える月（例：31 を指定して 2 月）は月末に自動調整されます。配信時刻は 09:00。</div>
                    </div>

                    <div class="mb-3" id="weekly-field" style="display: {{ old('recurrence', $schedule->recurrence) === 'weekly' ? 'block' : 'none' }};">
                        <label class="form-label small fw-semibold">毎週 何曜日 に配信</label>
                        <select name="recurrence_day" class="form-select" style="max-width: 180px;">
                            @foreach (['日','月','火','水','木','金','土'] as $i => $name)
                                <option value="{{ $i }}" {{ (int)old('recurrence_day', $schedule->recurrence_day ?? 1) === $i ? 'selected' : '' }}>{{ $name }}曜日</option>
                            @endforeach
                        </select>
                        <div class="form-text small">配信時刻は 09:00。</div>
                    </div>

                    <div class="mb-3" id="once-field" style="display: {{ old('recurrence', $schedule->recurrence) === 'once' ? 'block' : 'none' }};">
                        <label class="form-label small fw-semibold">配信日時（任意指定）</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" style="max-width: 280px;"
                            value="{{ old('scheduled_at', $schedule->scheduled_at?->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-envelope"></i> メール内容</h2>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">送付先メールアドレス（カンマ区切りで複数可）</label>
                        <textarea name="recipients" rows="2" class="form-control" required placeholder="info@example.com, contact@example.com">{{ old('recipients', $schedule->recipients) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">件名</label>
                        <input type="text" name="subject" class="form-control" value="{{ old('subject', $schedule->subject ?? 'MEO 月次レポートの件') }}" maxlength="255" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">本文</label>
                        <textarea name="body" rows="8" class="form-control">{{ old('body', $schedule->body) }}</textarea>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">運営メモ（任意・社内向け）</label>
                        <textarea name="admin_comment" rows="2" class="form-control" placeholder="クライアントの要望、運用注意事項など">{{ old('admin_comment', $schedule->admin_comment) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mb-5">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> {{ $schedule->exists ? '更新する' : '予約する' }}
                </button>
                <a href="{{ route('admin.report-schedules.index') }}" class="btn btn-link">キャンセル</a>
            </div>
        </form>
    </div>

    <script>
        function toggleRecurrence(v) {
            document.getElementById('monthly-field').style.display = v === 'monthly' ? 'block' : 'none';
            document.getElementById('weekly-field').style.display = v === 'weekly' ? 'block' : 'none';
            document.getElementById('once-field').style.display = v === 'once' ? 'block' : 'none';
        }
    </script>
</x-app-layout>
