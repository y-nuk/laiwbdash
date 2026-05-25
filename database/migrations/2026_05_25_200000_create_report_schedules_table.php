<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('recurrence', 16)->default('monthly'); // monthly / weekly / once
            $table->timestamp('scheduled_at')->nullable(); // once の時の日時
            $table->tinyInteger('recurrence_day')->nullable(); // monthly: 1-31 / weekly: 0=日〜6=土
            $table->text('recipients'); // カンマ区切りメアド
            $table->string('subject', 255)->default('MEO 月次レポートの件');
            $table->text('body')->nullable();
            $table->string('status', 16)->default('active'); // active / paused / cancelled
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->text('admin_comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['next_run_at', 'status'], 'idx_next_run');
            $table->index('store_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_schedules');
    }
};
