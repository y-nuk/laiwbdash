<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\Company;
use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class GmoImport extends Command
{
    protected $signature = 'gmo:import
                            {companies : 会社（事業者）一覧 CSV のパス}
                            {stores : Googleビジネスプロフィール一覧 CSV のパス}
                            {--dry-run : DB 書き込みせず結果サマリーだけ表示}
                            {--encoding=cp932 : CSV のエンコーディング}';

    protected $description = 'GMO MEO Dashboard からエクスポートした CSV を laiweb-dash にインポート';

    public function handle(): int
    {
        $companiesPath = $this->argument('companies');
        $storesPath = $this->argument('stores');
        $dryRun = $this->option('dry-run');
        $encoding = $this->option('encoding');

        if (! is_file($companiesPath)) {
            $this->error("companies CSV が見つかりません：{$companiesPath}");
            return self::FAILURE;
        }
        if (! is_file($storesPath)) {
            $this->error("stores CSV が見つかりません：{$storesPath}");
            return self::FAILURE;
        }

        $companies = $this->readCsv($companiesPath, $encoding);
        $stores = $this->readCsv($storesPath, $encoding);
        $this->info('会社 CSV: ' . count($companies) . ' 行 / 店舗 CSV: ' . count($stores) . ' 行');

        $agency = Agency::where('is_self', true)->firstOrFail();
        $this->info("自社 agency ID: {$agency->id}（{$agency->name}）");

        // 店舗を正規化キー → 配列でマッピング（同名店舗が複数ある場合に対応）
        $storeMap = [];
        foreach ($stores as $s) {
            $key = $this->normalize($s['ビジネス名'] ?? '');
            if ($key === '') { continue; }
            $storeMap[$key][] = $s;
        }

        if ($dryRun) {
            $this->warn('--dry-run モード：DB へ書き込みません');
        }

        DB::beginTransaction();
        try {
            $companiesCreated = 0;
            $companiesSkipped = 0;
            $storesCreated = 0;
            $usedStoreIds = [];
            $partialMatchLog = [];

            foreach ($companies as $c) {
                $name = trim($c['会社名（事業者名）'] ?? '');
                if (! $name) { continue; }

                $companyData = $this->mapCompany($c, $agency->id);

                // 重複チェック（メアド or 名前 で）
                $existingQ = Company::query();
                if (! empty($companyData['email'])) {
                    $existingQ->where('email', $companyData['email']);
                } else {
                    $existingQ->where('name', $companyData['name']);
                }
                $existing = $existingQ->first();

                if ($existing) {
                    $companiesSkipped++;
                    $company = $existing;
                } else {
                    if ($dryRun) {
                        $company = new Company($companyData);
                        $company->id = 999000 + $companiesCreated;
                    } else {
                        $company = Company::create($companyData);
                    }
                    $companiesCreated++;
                }

                // 想定店舗数（GMO の「店舗数」列）
                $expectedStoreCount = (int) ($c['店舗数'] ?? 1);

                // 関連店舗を探す
                $cKey = $this->normalize($name);
                $matched = $this->findMatchingStores($cKey, $name, $storeMap, $usedStoreIds, $expectedStoreCount);

                foreach ($matched['stores'] as $s) {
                    $storeData = $this->mapStore($s, $company);
                    if (! $dryRun) {
                        Store::firstOrCreate(
                            ['company_id' => $company->id, 'gbp_place_id' => null, 'name' => $storeData['name']],
                            $storeData,
                        );
                    }
                    $storesCreated++;
                    $usedStoreIds[] = $s['ID'];
                }

                if ($matched['type'] === 'partial') {
                    $partialMatchLog[] = "  部分一致 '{$name}' → " . collect($matched['stores'])->pluck('ビジネス名')->implode(', ');
                }
            }

            // 紐付かなかった店舗（残）
            $orphan = collect($stores)->filter(fn ($s) => ! in_array($s['ID'], $usedStoreIds))->values();

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }

            $this->newLine();
            $this->info('=== インポート結果 ===');
            $this->info("会社：新規作成 {$companiesCreated} / 既存スキップ {$companiesSkipped}");
            $this->info("店舗：作成 {$storesCreated}");
            $this->info('未紐付け店舗：' . $orphan->count());

            if ($partialMatchLog) {
                $this->newLine();
                $this->warn('[部分一致した会社（要確認）]');
                foreach ($partialMatchLog as $l) { $this->line($l); }
            }

            if ($orphan->isNotEmpty()) {
                $this->newLine();
                $this->warn('[未紐付け店舗 = どの会社にも自動紐付けできなかった]');
                foreach ($orphan as $s) { $this->line('  - ' . ($s['ビジネス名'] ?? '???')); }
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            DB::rollBack();
            $this->error('エラー：' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /** CSV を連想配列で読み込み（指定エンコーディング → UTF-8 変換） */
    private function readCsv(string $path, string $encoding): array
    {
        $content = file_get_contents($path);
        if ($encoding && strtolower($encoding) !== 'utf-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }
        $tmp = tmpfile();
        fwrite($tmp, $content);
        $meta = stream_get_meta_data($tmp);
        $rows = [];
        if (($fp = fopen($meta['uri'], 'r')) !== false) {
            $headers = fgetcsv($fp);
            while (($row = fgetcsv($fp)) !== false) {
                if (count($row) === count($headers)) {
                    $rows[] = array_combine($headers, $row);
                }
            }
            fclose($fp);
        }
        fclose($tmp);
        return $rows;
    }

    private function mapCompany(array $row, int $agencyId): array
    {
        $email = trim($row['メールアドレス'] ?? '');
        return [
            'agency_id' => $agencyId,
            'name' => trim($row['会社名（事業者名）'] ?? ''),
            'kana' => null,
            'contact_person_name' => trim($row['担当者名'] ?? '') ?: null,
            'email' => $email ?: null,
            'phone' => trim($row['電話番号'] ?? '') ?: null,
            'fax' => trim($row['FAX番号'] ?? '') ?: null,
            'postal_code' => trim($row['郵便番号'] ?? '') ?: null,
            'address' => trim($row['住所'] ?? '') ?: null,
            'industry' => trim($row['業種'] ?? '') ?: null,
            'status' => Company::STATUS_ACTIVE,
        ];
    }

    private function mapStore(array $row, Company $company): array
    {
        $bizName = trim($row['ビジネス名'] ?? '');
        $phone = trim($row['電話番号１'] ?? '') ?: null;
        return [
            'company_id' => $company->id,
            'name' => $bizName,
            'business_name' => $bizName,
            'industry' => trim($row['メインカテゴリー'] ?? '') ?: null,
            'primary_category' => trim($row['メインカテゴリー'] ?? '') ?: null,
            'postal_code' => trim($row['郵便番号'] ?? '') ?: null,
            'address' => trim($row['住所'] ?? '') ?: null,
            'phone' => $phone,
            'website_url' => trim($row['WebURL'] ?? '') ?: null,
            'gbp_place_id' => null,
            'gbp_status' => Store::GBP_STATUS_UNSET,
            'has_gbp' => true,
            'business_status' => 'operational',
        ];
    }

    /**
     * 会社名に対応する店舗を探す。
     * 1. 完全一致（正規化後）
     * 2. 部分一致（会社名 ⊂ 店舗名 or 店舗名 ⊂ 会社名）
     * 3. expectedCount まで集める
     *
     * @return array{stores:array, type:string}
     */
    private function findMatchingStores(string $cKey, string $rawName, array &$storeMap, array $usedIds, int $expectedCount): array
    {
        $matched = [];
        $type = 'none';

        // 完全一致
        if (isset($storeMap[$cKey])) {
            foreach ($storeMap[$cKey] as $s) {
                if (! in_array($s['ID'], $usedIds) && count($matched) < $expectedCount) {
                    $matched[] = $s;
                    $type = 'exact';
                }
            }
        }

        // 部分一致（足りない時のみ）
        if (count($matched) < $expectedCount && $cKey !== '') {
            foreach ($storeMap as $sKey => $stores) {
                if ($sKey === $cKey) { continue; }
                if ($cKey === '' || $sKey === '') { continue; }
                if (str_contains($cKey, $sKey) || str_contains($sKey, $cKey)) {
                    foreach ($stores as $s) {
                        if (! in_array($s['ID'], $usedIds)
                            && ! collect($matched)->pluck('ID')->contains($s['ID'])
                            && count($matched) < $expectedCount) {
                            $matched[] = $s;
                            $type = $type === 'exact' ? 'exact' : 'partial';
                        }
                    }
                }
                if (count($matched) >= $expectedCount) { break; }
            }
        }

        return ['stores' => $matched, 'type' => $type];
    }

    /** 名前の正規化：株式会社/(株) 除去 + 空白除去 + 全角→半角 + 小文字化 */
    private function normalize(string $s): string
    {
        $s = preg_replace('/株式会社|有限会社|（株）|\(株\)|（有）|\(有\)/u', '', $s);
        $s = preg_replace('/[\s　・\-_‐ー－]/u', '', $s);
        $s = mb_convert_kana($s, 'a', 'UTF-8'); // 全角英数 → 半角
        return mb_strtolower($s);
    }
}
