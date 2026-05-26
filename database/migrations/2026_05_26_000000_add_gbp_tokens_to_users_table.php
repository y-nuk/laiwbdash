<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Google ビジネスプロフィール OAuth トークン保存
            // - 暗号化したい場合は encrypted カラム or app-level encryption に
            $table->text('gbp_access_token')->nullable()->after('disabled_at');
            $table->text('gbp_refresh_token')->nullable()->after('gbp_access_token');
            $table->timestamp('gbp_token_expires_at')->nullable()->after('gbp_refresh_token');
            $table->string('gbp_account_email')->nullable()->after('gbp_token_expires_at');
            $table->json('gbp_account_info')->nullable()->after('gbp_account_email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'gbp_access_token',
                'gbp_refresh_token',
                'gbp_token_expires_at',
                'gbp_account_email',
                'gbp_account_info',
            ]);
        });
    }
};
