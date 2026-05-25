<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->string('name', 100)->after('keyword_id')->default('順位下落アラート');
            $table->string('alert_type', 24)->after('name')->default('ranking_drop');
            // ranking_drop: 前回比で threshold 位以上下落
            // out_of_rank: 圏外（NULL）転落
            // worse_than: 順位が threshold 位より悪化（現在順位 > threshold）
            $table->text('recipients')->nullable()->after('threshold');
            $table->timestamp('last_check_at')->nullable()->after('last_alerted_at');
            $table->text('admin_comment')->nullable()->after('last_check_at');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['name', 'alert_type', 'recipients', 'last_check_at', 'admin_comment']);
        });
    }
};
