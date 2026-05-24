<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('keyword_id')->nullable()
                ->constrained('keywords')->cascadeOnDelete();
            $table->unsignedSmallInteger('threshold')->default(5);
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_alerted_at')->nullable();
            $table->timestamps();
            $table->index(['store_id', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
