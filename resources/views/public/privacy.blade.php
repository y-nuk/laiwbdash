<x-public-layout>
    <x-slot name="title">プライバシーポリシー</x-slot>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 fw-bold mb-1">プライバシーポリシー</h1>
            <p class="text-muted small mb-4">最終更新日：{{ \Carbon\Carbon::create(2026, 5, 25)->format('Y年n月j日') }}</p>

            <p class="small">
                株式会社 L'aide（以下「当社」といいます）は、多店舗 MEO 運用ツール「laiweb-dash」（以下「本サービス」といいます）の提供にあたり、
                利用者の個人情報の取扱いを以下のとおり定めます。
            </p>

            <h2 class="h6 fw-bold mt-4">1. 取得する情報</h2>
            <p class="small">
                本サービスでは、サービス提供のため以下の情報を取得します。
            </p>
            <ul class="small">
                <li>氏名・メールアドレス・電話番号などの連絡先情報</li>
                <li>所属する会社・店舗の名称、住所、業種等の事業者情報</li>
                <li>Google アカウント連携時：プロフィール情報、Google ビジネスプロフィール（以下「GBP」）に関するデータ</li>
                <li>本サービス利用時のアクセスログ・操作履歴</li>
            </ul>

            <h2 class="h6 fw-bold mt-4">2. Google ビジネスプロフィール（GBP）連携について</h2>
            <p class="small">
                本サービスは Google Business Profile API を利用して、利用者が同意した GBP データを取得・更新します。
                取得する情報、利用目的、削除フローは以下のとおりです。
            </p>
            <ul class="small">
                <li><strong>取得する GBP データ：</strong>店舗の基本情報（住所・電話・営業時間等）、メインカテゴリ、サブカテゴリ、ウェブサイト URL、クチコミ、検索パフォーマンス（インプレッション・クリック数等）</li>
                <li><strong>利用目的：</strong>利用者の店舗運営支援のため、本サービス内での表示・編集・順位レポート生成・通知に限定して利用します</li>
                <li><strong>保管期間：</strong>連携解除または退会から 90 日以内に削除します</li>
                <li><strong>第三者提供：</strong>法令に基づく場合を除き、本人の同意なく第三者に提供しません</li>
                <li><strong>連携解除：</strong>管理画面の「設定」よりいつでも連携解除でき、解除後は速やかにデータを削除します</li>
            </ul>

            <h2 class="h6 fw-bold mt-4">3. 個人情報の利用目的</h2>
            <ul class="small">
                <li>本サービスの提供・運営・改善のため</li>
                <li>利用者からの問い合わせ対応のため</li>
                <li>重要なお知らせの通知のため</li>
                <li>利用規約違反等への対応のため</li>
            </ul>

            <h2 class="h6 fw-bold mt-4">4. 個人情報の安全管理</h2>
            <p class="small">
                取得した個人情報は、適切な技術的・組織的安全管理措置を講じて取り扱います。
                通信は SSL/TLS により暗号化し、データベースへのアクセスは権限を持つ担当者に限定します。
            </p>

            <h2 class="h6 fw-bold mt-4">5. 個人情報の第三者提供</h2>
            <p class="small">
                次の場合を除き、本人の同意なく第三者に個人情報を提供しません。
            </p>
            <ul class="small">
                <li>法令に基づく場合</li>
                <li>人の生命、身体、財産の保護のために必要がある場合</li>
                <li>業務委託先に必要な範囲で提供する場合（守秘義務を課したうえで）</li>
            </ul>

            <h2 class="h6 fw-bold mt-4">6. 個人情報の開示・訂正・削除</h2>
            <p class="small">
                利用者ご本人からの個人情報の開示、訂正、利用停止、削除のご要望には、合理的な範囲で速やかに対応します。
                ご要望は <a href="{{ route('public.contact') }}">お問い合わせフォーム</a> よりご連絡ください。
            </p>

            <h2 class="h6 fw-bold mt-4">7. Cookie 等の利用</h2>
            <p class="small">
                本サービスではログイン状態の維持・利用状況の分析のため Cookie を使用することがあります。
                ブラウザの設定で Cookie を無効にすることができますが、一部機能がご利用いただけなくなる場合があります。
            </p>

            <h2 class="h6 fw-bold mt-4">8. お問い合わせ窓口</h2>
            <p class="small">
                本ポリシーに関するお問い合わせは下記までお願いいたします。
            </p>
            <p class="small mb-0">
                株式会社 L'aide<br>
                <a href="{{ route('public.contact') }}">お問い合わせフォーム</a>
            </p>

            <h2 class="h6 fw-bold mt-4">9. 本ポリシーの改定</h2>
            <p class="small mb-0">
                本ポリシーは、必要に応じて改定することがあります。重要な変更がある場合は、本サービス上で通知します。
            </p>
        </div>
    </div>
</x-public-layout>
