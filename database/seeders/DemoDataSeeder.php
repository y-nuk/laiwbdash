<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Company;
use App\Models\Keyword;
use App\Models\Ranking;
use App\Models\Store;
use App\Models\User;
use App\Services\Ranking\RandomRankingFetcher;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * 開発・デモ用のサンプルデータ一式。
 * 本番では実行しない。
 *
 *   php artisan db:seed --class=DemoDataSeeder
 *
 * 既存データは firstOrCreate でスキップ、新規分のみ追加。
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // === 代理店 ===
        $sampleAgency = Agency::firstOrCreate(
            ['name' => '株式会社サンプル代理店'],
            [
                'kana' => 'カブシキガイシャサンプルダイリテン',
                'is_self' => false,
                'email' => 'agent@sample-agency.example.com',
                'phone' => '03-9999-0000',
            ],
        );

        $selfAgency = Agency::where('is_self', true)->first();

        // === 内部スタッフ ===
        $staff = User::firstOrCreate(
            ['email' => 'staff@laiweb-dash.com'],
            [
                'name' => '運営スタッフ 太郎',
                'password' => 'password',  // 'hashed' cast が自動 hash
                'role' => User::ROLE_STAFF,
                'agency_id' => $selfAgency->id,
                'email_verified_at' => now(),
            ],
        );
        $this->command->info("staff: {$staff->email} / password");

        // === デモ会社 5 社 ===
        $companies = [
            [
                'agency_id' => $selfAgency->id,
                'name' => 'カラフルヘア渋谷店',
                'kana' => 'カラフルヘアシブヤテン',
                'contact_person_name' => '佐藤 真一',
                'email' => 'sato@colorful-hair.example.com',
                'phone' => '03-1111-2222',
                'industry' => '美容室',
                'address' => '東京都渋谷区道玄坂2-1-1',
                'postal_code' => '150-0043',
            ],
            [
                'agency_id' => $selfAgency->id,
                'name' => '整骨院いきいきグループ',
                'kana' => 'セイコツインイキイキグループ',
                'contact_person_name' => '田村 美咲',
                'email' => 'tamura@ikiiki-seikotsu.example.com',
                'phone' => '03-2222-3333',
                'industry' => '整骨院',
                'address' => '東京都品川区五反田1-2-3',
                'postal_code' => '141-0022',
            ],
            [
                'agency_id' => $sampleAgency->id,
                'name' => 'カフェ・ド・東京',
                'kana' => 'カフェドトウキョウ',
                'contact_person_name' => '鈴木 健太',
                'email' => 'suzuki@cafe-tokyo.example.com',
                'phone' => '03-3333-4444',
                'industry' => 'カフェ',
                'address' => '東京都新宿区西新宿1-1-1',
                'postal_code' => '160-0023',
            ],
            [
                'agency_id' => $sampleAgency->id,
                'name' => '歯科クリニック スマイル',
                'kana' => 'シカクリニックスマイル',
                'contact_person_name' => '高橋 治',
                'email' => 'takahashi@smile-dental.example.com',
                'phone' => '03-4444-5555',
                'industry' => '歯科医院',
                'address' => '東京都港区六本木3-2-1',
                'postal_code' => '106-0032',
            ],
            [
                'agency_id' => $selfAgency->id,
                'name' => 'フィットネスジム エナジー',
                'kana' => 'フィットネスジムエナジー',
                'contact_person_name' => '山口 麻衣',
                'email' => 'yamaguchi@energy-gym.example.com',
                'phone' => '03-5555-6666',
                'industry' => 'フィットネスジム',
                'address' => '東京都中央区銀座4-5-6',
                'postal_code' => '104-0061',
            ],
        ];

        foreach ($companies as $data) {
            $company = Company::firstOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['status' => Company::STATUS_ACTIVE]),
            );

            // クライアントユーザー（招待完了済として作成）
            $client = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['contact_person_name'],
                    'password' => 'password',
                    'role' => User::ROLE_CLIENT,
                    'company_id' => $company->id,
                    'invitation_accepted_at' => now(),
                    'email_verified_at' => now(),
                ],
            );

            $this->command->info("client: {$client->email} / password");

            // 各会社に 1〜2 店舗
            $this->createStoresForCompany($company);
        }

        // === 過去 30 日分の順位データを全 KW に投入（既存をクリアして再生成）===
        $this->command->info('Truncating existing rankings and regenerating 30 days...');
        Ranking::query()->delete();

        $fetcher = app(RandomRankingFetcher::class);
        $allKws = Keyword::where('is_active', true)->get();
        $bar = $this->command->getOutput()->createProgressBar($allKws->count() * 30);

        foreach ($allKws as $kw) {
            for ($i = 29; $i >= 0; $i--) {
                $fetcher->fetchAndStore($kw, Carbon::today()->subDays($i));
                $bar->advance();
            }
        }
        $bar->finish();
        $this->command->newLine(2);

        $this->command->info('=== Demo data ready ===');
        $this->command->info('admin@laiweb-dash.com / password （admin）');
        $this->command->info('staff@laiweb-dash.com / password （staff）');
        $this->command->info('各 client は上記 email でログイン可（全員 password）');
    }

    private function createStoresForCompany(Company $company): void
    {
        $industryToStores = [
            '美容室' => [
                ['name' => '渋谷本店', 'business_name' => 'カラフルヘア 渋谷本店', 'primary_category' => '美容室',
                 'additional_categories' => ['ヘアサロン', 'ヘアカラー専門店'],
                 'kws' => ['渋谷 美容室', '渋谷 ヘアカラー', '道玄坂 サロン']],
                ['name' => '原宿店', 'business_name' => 'カラフルヘア 原宿店', 'primary_category' => '美容室',
                 'additional_categories' => ['ヘアサロン'],
                 'kws' => ['原宿 美容室', '表参道 サロン']],
            ],
            '整骨院' => [
                ['name' => '五反田本院', 'business_name' => 'いきいき整骨院 五反田本院', 'primary_category' => '整骨院',
                 'additional_categories' => ['鍼灸院', 'マッサージ'],
                 'kws' => ['五反田 整骨院', '品川 マッサージ', '腰痛 五反田']],
                ['name' => '目黒分院', 'business_name' => 'いきいき整骨院 目黒分院', 'primary_category' => '整骨院',
                 'additional_categories' => ['鍼灸院'],
                 'kws' => ['目黒 整骨院', '中目黒 鍼灸']],
            ],
            'カフェ' => [
                ['name' => '西新宿本店', 'business_name' => 'カフェ・ド・東京 西新宿本店', 'primary_category' => 'カフェ',
                 'additional_categories' => ['コーヒーショップ', '喫茶店'],
                 'kws' => ['新宿 カフェ', '西新宿 コーヒー', '新宿 ランチ']],
            ],
            '歯科医院' => [
                ['name' => '六本木本院', 'business_name' => 'スマイル歯科クリニック 六本木本院', 'primary_category' => '歯科医院',
                 'additional_categories' => ['歯科', '小児歯科', '審美歯科'],
                 'kws' => ['六本木 歯医者', '六本木 審美歯科', '麻布 歯科']],
            ],
            'フィットネスジム' => [
                ['name' => '銀座スタジオ', 'business_name' => 'エナジーフィットネス 銀座スタジオ', 'primary_category' => 'フィットネスジム',
                 'additional_categories' => ['パーソナルジム', 'ヨガスタジオ'],
                 'kws' => ['銀座 ジム', '銀座 パーソナルトレーニング', '日比谷 ヨガ']],
            ],
        ];

        $storesDef = $industryToStores[$company->industry] ?? [];

        foreach ($storesDef as $i => $storeDef) {
            $store = Store::firstOrCreate(
                ['company_id' => $company->id, 'name' => $storeDef['name']],
                [
                    'business_name' => $storeDef['business_name'],
                    'industry' => $company->industry,
                    'postal_code' => $company->postal_code,
                    'address' => $company->address . " {$storeDef['name']}",
                    'phone' => $company->phone,
                    'gbp_status' => Store::GBP_STATUS_CONFIRMED,
                    'has_gbp' => true,
                    'business_status' => 'operational',
                    'primary_category' => $storeDef['primary_category'],
                    'additional_categories' => $storeDef['additional_categories'],
                    'website_url' => "https://example.com/{$company->id}/{$i}",
                    'description' => "{$storeDef['business_name']} は地域密着で営業中。",
                    'business_hours' => $this->defaultHours(),
                ],
            );

            foreach ($storeDef['kws'] as $idx => $kw) {
                Keyword::firstOrCreate(
                    ['store_id' => $store->id, 'keyword' => $kw],
                    [
                        'location_code' => explode(' ', $kw)[0] ?? null,
                        'priority' => $idx + 1,
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    private function defaultHours(): array
    {
        $hours = [];
        foreach (Store::WEEKDAYS as $day => $_) {
            $hours[$day] = [
                'closed' => $day === 'wed',  // 水曜定休（デモ用）
                'open' => '10:00',
                'close' => '20:00',
            ];
        }
        return $hours;
    }
}
