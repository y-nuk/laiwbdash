@component('mail::message')
# 順位アラート

**{{ $companyName }} / {{ $storeName }}** で順位の変動を検知しました。

## アラート内容

- アラート名：**{{ $alertName }}**
- 種別：{{ $alertType }}
- しきい値：{{ $threshold }}

## 該当キーワード

@component('mail::table')
| キーワード | 前回 | 今回 | 変動 |
| :--- | ---: | ---: | ---: |
@foreach ($triggers as $t)
| {{ $t['keyword'] }} | {{ $t['prev'] ?? '圏外' }} | {{ $t['curr'] ?? '圏外' }} | {{ $t['drop'] !== null ? ($t['drop'] > 0 ? '+' . $t['drop'] : $t['drop']) : '—' }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => $adminUrl])
laiweb-dash で順位履歴を確認
@endcomponent

ご確認のほど、よろしくお願いいたします。

{{ config('app.name') }} 運営事務局
@endcomponent
