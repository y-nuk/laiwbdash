@component('mail::message')
# {{ $data['name'] }} 様

このたびは laiweb-dash へお問い合わせいただき、誠にありがとうございます。
下記の内容で承りました。**通常 2 営業日以内**に運営担当よりご返信いたします。

---

**お問い合わせ内容：**

{{ $data['message'] }}

---

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
| 受信日時 | {{ now()->format('Y/m/d H:i') }} |

---

※ このメールは自動送信です。本メールへのご返信は運営担当に届きません。
　 追加のご質問がある場合は、再度お問い合わせフォームよりご連絡ください。

@component('mail::button', ['url' => config('app.url')])
laiweb-dash トップへ
@endcomponent

引き続きどうぞよろしくお願いいたします。

{{ config('app.name') }} 運営事務局
@endcomponent
