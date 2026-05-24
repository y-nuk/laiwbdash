# laiweb-dash

多店舗 MEO（Map Engine Optimization）運用ツール。会社・店舗・キーワード・順位・GBP 情報を一元管理する Laravel アプリ。

- **本番ドメイン:** https://laiweb-dash.com （デプロイ予定）
- **GitHub:** https://github.com/y-nuk/laiwbdash
- **運営:** 株式会社 L'aide

---

## 技術スタック

| 領域 | 採用 |
|---|---|
| 言語 / FW | PHP 8.3 / Laravel 13.11 |
| フロント | Blade + Bootstrap 5 + bootstrap-icons + Chart.js (CDN) |
| ビルド | Vite + Sass |
| DB | SQLite（開発）/ MySQL（本番想定） |
| 認証 | Laravel Breeze（Blade）+ 招待トークン |
| メール | log driver（開発）/ SMTP（本番、Xserver Business） |
| ホスティング | Xserver Business |

---

## ロール体系

```
agencies（代理店）
  └─ companies（会社）
       └─ stores（店舗）
            ├─ keywords（計測キーワード）
            ├─ rankings（順位履歴）
            └─ competitors（競合店）

users.role
  ├─ admin   : L'aide 内部の全権管理者（agency_id）
  ├─ staff   : L'aide 内部スタッフ、ほぼ admin 同等（agency_id）
  └─ client  : 顧客、自社の店舗のみ閲覧可（company_id）
```

---

## 主要機能

### 内部運営（admin / staff）

- 会社 CRUD（登録時に担当者へ自動招待メール）
- 店舗 CRUD（GBP Place ID 等の連携メタ含む）
- 店舗詳細（タブ：基本情報 / GBP 基本情報 / 計測 KW / 競合 / 順位履歴）
- GBP 基本情報の手入力編集（API 通過後は GbpFetcher で自動同期予定）
- 計測キーワード CRUD + ON/OFF
- 競合店 CRUD（GBP URL リンク付き）
- 順位履歴（Chart.js グラフ + 直近 7 日テーブル）
- ユーザー管理（招待 / 再送 / 無効化 / 削除）

### クライアント側（client）

- ダッシュボード（KPI 4 枚 + 店舗一覧）
- 店舗詳細 read-only（基本情報 / GBP 情報 / 営業時間 / KW 一覧）
- 順位履歴グラフ閲覧

### 公開ページ

- `/` → ログイン画面へリダイレクト
- `/privacy` : プライバシーポリシー（GBP データ取扱い項目あり）
- `/terms` : 利用規約
- `/contact` : お問い合わせフォーム
- `/invitation/{token}` : 招待 URL でパスワード設定 → ログイン

---

## ローカル開発セットアップ

### 前提

- PHP 8.3+（laragon 推奨）
- Composer
- Node.js 18+ / npm
- Git

### 初回セットアップ

```bash
git clone git@github.com:y-nuk/laiwbdash.git
cd laiwbdash

# 依存
composer install
npm install

# 環境ファイル
cp .env.example .env
php artisan key:generate

# SQLite DB ファイル作成
touch database/database.sqlite

# マイグレーション + admin ユーザー作成
php artisan migrate
php artisan db:seed         # admin@laiweb-dash.com / password

# デモデータ投入（任意、開発用）
php artisan db:seed --class=DemoDataSeeder

# 順位スタブデータ生成（過去 30 日分）
php artisan rankings:fetch --backfill=29

# ビルド
npm run build
# or 開発中は
npm run dev

# サーバー起動
php artisan serve
# → http://127.0.0.1:8000
```

### ログイン

| Email | Password | Role |
|---|---|---|
| admin@laiweb-dash.com | password | admin |
| staff@laiweb-dash.com | password | staff |
| sato@colorful-hair.example.com | password | client |
| 他 4 名の client | password | client |

---

## artisan コマンド

| コマンド | 用途 |
|---|---|
| `php artisan migrate` | マイグレーション実行 |
| `php artisan migrate:fresh --seed` | DB を完全リセット + admin だけ作成 |
| `php artisan db:seed --class=DemoDataSeeder` | デモデータ一括投入 |
| `php artisan rankings:fetch` | 当日の順位データを生成（スタブ） |
| `php artisan rankings:fetch --backfill=N` | 過去 N 日分も生成 |
| `php artisan rankings:fetch --store=ID` | 特定 store_id のみ |
| `php artisan tinker` | REPL |

---

## ディレクトリ構成（抜粋）

```
app/
  Console/Commands/RankingsFetch.php
  Http/Controllers/
    Admin/             # admin / staff 用
    Client/            # client 用
    Public/            # 認証外の公開ページ
    InvitationController.php
  Mail/
    ClientInvitationMail.php
    StaffInvitationMail.php
  Models/              # 11 Eloquent モデル
  Services/Ranking/
    RankingFetcher.php       # interface
    RandomRankingFetcher.php # 現状実装（スタブ）
    # 将来：GbpRankingFetcher.php（API 通過後）

database/
  migrations/          # 11 テーブル + 招待カラム + GBP 基本情報
  seeders/
    DatabaseSeeder.php   # 本番用：admin だけ
    DemoDataSeeder.php   # 開発用：会社 5/店舗 7/KW 18/順位 690

resources/
  views/
    admin/             # 内部運営画面
    client/            # クライアント画面
    public/            # 公開ページ
    layouts/
      app.blade.php    # 認証後（admin/client 兼用）
      guest.blade.php  # 認証画面
      public.blade.php # 公開ページ
    emails/            # 招待メール 2 種

routes/
  web.php              # メインルート
  auth.php             # Breeze 認証ルート
```

---

## GBP API 申請ステータス

| 項目 | 状態 |
|---|---|
| 1 回目の申請 | 却下（プライバシーポリシー不備等が原因と推測） |
| 2 回目の申請 | 準備中（本ドキュメントで詳細） |

### 申請に必要な事前準備

1. 本番デプロイ完了（→ [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)）
2. プライバシーポリシー公開 ✅ `/privacy`
3. 利用規約公開 ✅ `/terms`
4. お問い合わせフォーム ✅ `/contact`
5. Google Cloud Console 設定（OAuth 同意画面を本番モード）
6. アクセス申請フォーム提出（ユースケース 800 字 + スクショ）

### API 通過後（Phase 4）

- `App\Services\Ranking\GbpRankingFetcher` を実装し、`RankingFetcher` interface に bind
- `App\Services\Gbp\GbpInfoFetcher` を実装（基本情報の自動同期）
- `App\Services\Gbp\GbpInsightsFetcher`（アクセス・クリック等の取得）
- `gbp_protected` トグルで Local ↔ GBP の双方向同期を制御

---

## 関連ドキュメント

- [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) - 本番デプロイ手順
