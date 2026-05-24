<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 16)->default('client')->after('email');
            $table->foreignId('agency_id')->nullable()->after('role')
                ->constrained('agencies')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->after('agency_id');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->softDeletes();
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
            $table->dropSoftDeletes();
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'agency_id', 'company_id', 'last_login_at']);
        });
    }
};
