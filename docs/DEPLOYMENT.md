# 本番デプロイ手順（Xserver Business）

ターゲット：`https://laiweb-dash.com`

---

## 前提

- Xserver Business 契約済み
- `laiweb-dash.com` ドメインを Xserver で利用可
- SSH ログイン可能（フランキー（xserver-deployer）に依頼推奨）
- GitHub deploy key を Xserver に配置

---

## Xserver Business での doc root ルール

Xserver Business の subdomain doc root は **二段配置必須**：

```
~/<parent>/public_html/<subdomain>/
   └─ laravel の public/ の中身をここに置く
```

### 本プロジェクトの配置例

```
~/laiweb.co.jp/public_html/laiweb-dash/      ← Apache が見る doc root
  ├─ index.php           ← Laravel public/index.php を書き換え
  ├─ .htaccess
  ├─ build/             ← Vite ビルド成果物
  └─ ...

~/laiweb.co.jp/app/laiwbdash/                ← Laravel 本体（doc root の外）
  ├─ app/
  ├─ bootstrap/
  ├─ config/
  ├─ database/
  ├─ routes/
  ├─ storage/
  ├─ vendor/
  ├─ .env
  └─ artisan
```

→ `index.php` の require パスを `__DIR__.'/../../../app/laiwbdash/...'` に書き換える

---

## デプロイ手順

### 1. リポジトリ clone

```bash
ssh xserver
cd ~/laiweb.co.jp/app
git clone git@github.com:y-nuk/laiwbdash.git
cd laiwbdash
```

### 2. Composer / npm

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

### 3. .env 作成（本番値）

```bash
cp .env.example .env
php artisan key:generate
```

`.env` を編集：

```env
APP_NAME=laiweb-dash
APP_ENV=production
APP_KEY=base64:...      # generate 済み
APP_DEBUG=false
APP_URL=https://laiweb-dash.com

LOG_CHANNEL=daily
LOG_LEVEL=warning

# === MySQL（Xserver の DB を作成しておく）===
DB_CONNECTION=mysql
DB_HOST=mysql8000.xserver.jp    # サーバーパネルで確認
DB_PORT=3306
DB_DATABASE=xxxx_laiwbdash
DB_USERNAME=xxxx_laiwbdash
DB_PASSWORD=xxxxxxxxxxxxxxxx

# === Mail（Xserver SMTP）===
MAIL_MAILER=smtp
MAIL_HOST=sv0000.xserver.jp     # ホストはサーバーパネル
MAIL_PORT=465
MAIL_USERNAME=contact@laiweb-dash.com
MAIL_PASSWORD=xxxxxxxxxxxxxxxx
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=contact@laiweb-dash.com
MAIL_FROM_NAME="laiweb-dash 運営事務局"

# === Session / Cache ===
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### 4. パーミッション

```bash
chmod -R 775 storage bootstrap/cache
# Xserver は通常 owner=自分なので chown は不要
```

### 5. migration（admin だけ）

```bash
php artisan migrate --force
php artisan db:seed --force      # admin だけ

# ⚠️ DemoDataSeeder は絶対に本番で走らせない
```

### 6. キャッシュ

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 7. doc root の index.php 配置

`~/laiweb.co.jp/public_html/laiweb-dash/` に：

```php
<?php
// index.php
require __DIR__.'/../../app/laiwbdash/public/index.php';
```

または、`public/` 配下を rsync で配置：

```bash
rsync -av --exclude='index.php' \
  ~/laiweb.co.jp/app/laiwbdash/public/ \
  ~/laiweb.co.jp/public_html/laiweb-dash/

# index.php は専用に書き換えたもの
cat > ~/laiweb.co.jp/public_html/laiweb-dash/index.php <<'EOF'
<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../../app/laiwbdash/vendor/autoload.php';
$app = require_once __DIR__.'/../../app/laiwbdash/bootstrap/app.php';
$app->handleRequest(Illuminate\Http\Request::capture());
EOF
```

### 8. 動作確認

- `https://laiweb-dash.com/` → ログイン画面
- `https://laiweb-dash.com/privacy` → プライバシーポリシー
- `https://laiweb-dash.com/terms` → 利用規約
- `https://laiweb-dash.com/contact` → お問い合わせ
- admin@laiweb-dash.com / `任意パス` でログイン
- Vite asset が 200 で返ること（build/manifest.json が読まれてる）
- メール送信テスト：パスワード再設定 等

---

## cron 設定（順位取得）

Xserver サーバーパネル → cron 設定 →

```cron
0 6 * * * cd ~/laiweb.co.jp/app/laiwbdash && /usr/bin/php8.3 artisan rankings:fetch >> storage/logs/cron.log 2>&1
```

→ 毎朝 6 時に順位を取得（スタブ実装中は RandomRankingFetcher が走る）

---

## バックアップ（推奨）

`docs/DEPLOYMENT.md` 外で別途設定：

```cron
# DB バックアップ：毎日 3 時
0 3 * * * mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > ~/backup/laiwbdash-$(date +\%Y\%m\%d).sql.gz

# 14 日より古いバックアップを削除
0 4 * * * find ~/backup -name "laiwbdash-*.sql.gz" -mtime +14 -delete
```

---

## トラブルシューティング

| 症状 | 対処 |
|---|---|
| 500 error | `storage/logs/laravel.log` 確認、`php artisan config:clear` |
| Vite manifest error | `npm run build` を再実行、`public/build/manifest.json` の存在確認 |
| 招待メール届かない | `.env` の MAIL_* 確認、`MAIL_MAILER=smtp` か確認 |
| migration エラー | DB 接続情報を `php artisan tinker` で確認：`DB::connection()->getPdo()` |
| Sass コンパイルエラー | `npm install` → `npm run build` |

---

## デプロイ後のチェックリスト

```
☐ https://laiweb-dash.com でログイン画面表示
☐ admin でログイン → ダッシュボード KPI 表示
☐ 会社 → 店舗 → KW → 順位グラフ までクリックで遷移できる
☐ /privacy /terms /contact が表示される
☐ お問い合わせフォーム送信 → 運営宛にメール届く
☐ パスワード再設定メールが届く
☐ rankings:fetch を手動実行 → 順位生成 OK
☐ cron 登録（毎朝 6 時）
☐ DB バックアップ cron 登録
☐ Google Cloud Console で OAuth 同意画面：
   ├─ 承認済みドメイン：laiweb-dash.com
   ├─ プライバシー URL：https://laiweb-dash.com/privacy
   └─ 利用規約 URL：https://laiweb-dash.com/terms
☐ GBP API 申請フォーム提出
```

---

## デプロイ困った時の連絡先

- フランキー（xserver-deployer サブエージェント）に頼む
- `.claude/agents/xserver-deployer.md` 参照

Xserver Business 起因のハマり、cron 設定、subdomain 配置、PHP バージョン関連は基本フランキー案件。
