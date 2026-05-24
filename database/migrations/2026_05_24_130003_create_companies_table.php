<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->cascadeOnDelete();
            $table->string('name');
            $table->string('kana')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('fax', 20)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('address')->nullable();
            $table->string('industry')->nullable();
            $table->string('status', 16)->default('active');
            $table->string('logo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
        Schema::dropIfExists('companies');
    }
};
