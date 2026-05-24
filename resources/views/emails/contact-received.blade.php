@component('mail::message')
# 新しいお問い合わせがあります

@php
$categoryLabels = [
    'general' => 'サービスについて',
    'trial' => 'トライアル・導入相談',
    'bug' => '不具合のご報告',
    'other' => 'その他',
];
@endphp

| 項目 | 内容 |
|---|---|
| 種別 | {{ $categoryLabels[$data['category'] ?? 'general'] ?? '—' }} |
| お名前 | {{ $data['name'] }} |
| 会社名 | {{ $data['company'] ?? '—' }} |
| メール | {{ $data['email'] }} |
| 電話 | {{ $data['phone'] ?? '—' }} |
| 受信日時 | {{ now()->format('Y/m/d H:i') }} |

---

**お問い合わせ内容：**

{{ $data['message'] }}

---

@component('mail::button', ['url' => 'mailto:' . $data['email']])
{{ $data['name'] }} 様に返信する
@endcomponent

— laiweb-dash お問い合わせ通知
@endcomponent
