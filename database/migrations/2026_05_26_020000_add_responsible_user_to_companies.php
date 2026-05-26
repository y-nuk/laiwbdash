<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // 担当 staff user (admin/staff のうち誰が担当か)
            $table->foreignId('responsible_user_id')
                ->nullable()
                ->after('agency_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->index('responsible_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('responsible_user_id');
        });
    }
};
