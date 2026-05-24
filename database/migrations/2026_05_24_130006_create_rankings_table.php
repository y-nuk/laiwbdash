<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('keyword_id')->constrained('keywords')->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->nullable();
            $table->string('source_type', 16)->default('api');
            $table->date('checked_date');
            $table->timestamps();
            $table->unique(['keyword_id', 'checked_date']);
            $table->index(['store_id', 'checked_date']);
            $table->index('checked_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};
