<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * 初期データ：L'aide 代理店 + admin ユーザー
     */
    public function run(): void
    {
        // 自社代理店（L'aide）
        $agency = Agency::firstOrCreate(
            ['is_self' => true],
            [
                'name' => '株式会社L\'aide',
                'kana' => 'カブシキガイシャレイド',
                'email' => env('ADMIN_EMAIL', 'admin@laiweb-dash.com'),
                'phone' => null,
                'address' => null,
            ],
        );

        // 管理者ユーザー
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@laiweb-dash.com')],
            [
                'name' => env('ADMIN_NAME', '運営管理者'),
                // User モデルの casts で 'password' => 'hashed' なので生パスワードで渡す
                'password' => env('ADMIN_PASSWORD', 'password'),
                'role' => User::ROLE_ADMIN,
                'agency_id' => $agency->id,
                'company_id' => null,
                'email_verified_at' => now(),
            ],
        );
    }
}
