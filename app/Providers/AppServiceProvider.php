<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ページネーションを Bootstrap 5 スタイルに（標準は Tailwind なので Bootstrap 環境では崩れる）
        Paginator::useBootstrapFive();
    }
}
