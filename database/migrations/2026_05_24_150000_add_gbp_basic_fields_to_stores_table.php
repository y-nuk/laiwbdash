<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GBP（Google ビジネスプロフィール）基本情報のフィールドを stores に追加。
 *
 * 今は手入力運用、GBP API 通過後は GbpFetcher が同期で書き換える前提のスキーマ。
 * 命名は GBP の locations API のフィールドに寄せる。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // 営業ステータス
            $table->string('business_status', 32)->default('operational')->after('has_yahoo');

            // カテゴリ
            $table->string('primary_category', 80)->nullable()->after('business_status');
            $table->json('additional_categories')->nullable()->after('primary_category');

            // 各種 URL
            $table->string('website_url')->nullable()->after('additional_categories');
            $table->string('reservation_url')->nullable()->after('website_url');
            $table->string('menu_url')->nullable()->after('reservation_url');
            $table->string('order_url')->nullable()->after('menu_url');

            // サービス提供エリア
            $table->json('service_areas')->nullable()->after('order_url');

            // 営業時間（曜日別）
            $table->json('business_hours')->nullable()->after('service_areas');
            $table->json('special_hours')->nullable()->after('business_hours');

            // 説明
            $table->text('description')->nullable()->after('special_hours');
            $table->date('opening_date')->nullable()->after('description');

            // GBP 同期管理
            $table->boolean('gbp_protected')->default(false)->after('opening_date');
            $table->timestamp('gbp_last_synced_at')->nullable()->after('gbp_protected');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'business_status',
                'primary_category',
                'additional_categories',
                'website_url',
                'reservation_url',
                'menu_url',
                'order_url',
                'service_areas',
                'business_hours',
                'special_hours',
                'description',
                'opening_date',
                'gbp_protected',
                'gbp_last_synced_at',
            ]);
        });
    }
};
