<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('business_name')->nullable();
            $table->string('industry')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('gbp_place_id')->nullable();
            $table->string('gbp_location_id')->nullable();
            $table->boolean('has_gbp')->default(false);
            $table->string('gbp_status', 16)->default('unset');
            $table->boolean('has_yahoo')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'gbp_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
