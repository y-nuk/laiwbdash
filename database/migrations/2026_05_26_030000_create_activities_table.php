<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // type: call / visit / email / line / note / other
            $table->string('type', 16);
            $table->string('title', 150);
            $table->text('body')->nullable();
            $table->timestamp('occurred_at');
            $table->date('follow_up_at')->nullable();
            $table->boolean('follow_up_done')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'occurred_at']);
            $table->index(['user_id', 'follow_up_at', 'follow_up_done']);
            $table->index(['follow_up_at', 'follow_up_done']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
