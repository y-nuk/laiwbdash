@component('mail::message')
# {{ $companyName ?? 'お客様' }} 様

平素より大変お世話になっております。
このたび、多店舗 MEO 運用ツール **laiweb-dash** にご招待いたしました。

下記のボタンよりパスワードを設定いただくと、ログインしてご利用開始いただけます。

@component('mail::button', ['url' => $acceptUrl])
パスワードを設定してログイン
@endcomponent

---

**ご案内**

- ログイン後は、貴社の店舗情報・Google マップ順位・クチコミ等を 1 画面で確認いただけます。
- 招待 URL は 14 日間有効です。期限が切れた場合は、運営担当までご連絡ください。
- URL をクリックできない場合は、以下のリンクを直接ブラウザに貼り付けてください。

{{ $acceptUrl }}

---

**ご利用にあたって**

パスワード設定の際に、以下の規約・ポリシーへのご同意をお願いしております。事前にご確認いただけますと幸いです。

- [Laiweb 利用規約](https://laiweb.jp/terms/)
- [laiweb-dash 個別規定](https://laiweb.jp/terms/laiweb-dash/)
- [プライバシーポリシー](https://laiweb.jp/privacy/)

---

引き続きどうぞよろしくお願いいたします。

{{ config('app.name') }} 運営事務局
@endcomponent
