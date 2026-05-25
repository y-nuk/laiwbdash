@component('mail::message')
{{ $companyName }} 御中

{!! nl2br(e($body)) !!}

---

**添付PDF**：{{ $storeName }} の月次 MEO レポートを添付しております。

引き続きどうぞよろしくお願いいたします。

{{ config('app.name') }} 運営事務局
@endcomponent
