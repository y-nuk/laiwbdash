<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->string('token', 64)->unique()->after('id');
            $table->text('description')->nullable()->after('title');
            $table->unsignedTinyInteger('high_rating_threshold')->default(4)->after('redirect_url');
            $table->string('google_review_url')->nullable()->after('high_rating_threshold');
            $table->text('low_rating_message')->nullable()->after('google_review_url');
            $table->text('thank_you_message')->nullable()->after('low_rating_message');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn([
                'token', 'description', 'high_rating_threshold',
                'google_review_url', 'low_rating_message', 'thank_you_message',
            ]);
        });
    }
};
