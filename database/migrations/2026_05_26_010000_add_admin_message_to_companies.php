<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // クライアントダッシュボードに表示する admin/staff からのメッセージ
            $table->text('admin_message')->nullable()->after('logo_path');
            $table->timestamp('admin_message_updated_at')->nullable()->after('admin_message');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['admin_message', 'admin_message_updated_at']);
        });
    }
};
