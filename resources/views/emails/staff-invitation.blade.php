@component('mail::message')
# {{ $user->name }} 様

laiweb-dash（多店舗 MEO 運用ツール）の運営スタッフとしてご招待いたしました。

下記のボタンよりパスワードを設定いただくと、管理画面にログインできます。

@component('mail::button', ['url' => $acceptUrl])
パスワードを設定してログイン
@endcomponent

---

- 招待 URL は 14 日間有効です。
- URL をクリックできない場合は、以下のリンクを直接ブラウザに貼り付けてください。

{{ $acceptUrl }}

---

よろしくお願いいたします。

{{ config('app.name') }} 運営事務局
@endcomponent
