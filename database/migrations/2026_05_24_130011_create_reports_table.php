<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()
                ->constrained('stores')->cascadeOnDelete();
            $table->string('type', 16);
            $table->date('period_start');
            $table->date('period_end');
            $table->string('file_path')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'type', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
