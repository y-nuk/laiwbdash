<?php

use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\CompetitorController as AdminCompetitorController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\KeywordController as AdminKeywordController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ReportScheduleController as AdminReportScheduleController;
use App\Http\Controllers\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\Admin\StoreGbpInfoController as AdminStoreGbpInfoController;
use App\Http\Controllers\Admin\StoreRankingController as AdminStoreRankingController;
use App\Http\Controllers\Admin\AlertController as AdminAlertController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\StoreController as ClientStoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\StaticPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// 認証後の振り分け
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// ----- Admin / Staff（自社運営側） -----
Route::middleware(['auth', 'role:admin,staff'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('companies', AdminCompanyController::class);
        Route::post('companies/{company}/resend-invitation', [AdminCompanyController::class, 'resendInvitation'])
            ->name('companies.resend-invitation');
        Route::resource('stores', AdminStoreController::class);

        // 店舗の GBP 基本情報（GMO 風）
        Route::get('stores/{store}/gbp-basic', [AdminStoreGbpInfoController::class, 'edit'])
            ->name('stores.gbp-basic.edit');
        Route::patch('stores/{store}/gbp-basic', [AdminStoreGbpInfoController::class, 'update'])
            ->name('stores.gbp-basic.update');

        // 店舗配下のキーワード（store スコープ）
        Route::get('stores/{store}/keywords', [AdminKeywordController::class, 'index'])
            ->name('stores.keywords.index');
        Route::post('stores/{store}/keywords', [AdminKeywordController::class, 'store'])
            ->name('stores.keywords.store');
        Route::post('stores/{store}/keywords/{keyword}/toggle', [AdminKeywordController::class, 'toggle'])
            ->name('stores.keywords.toggle');
        Route::delete('stores/{store}/keywords/{keyword}', [AdminKeywordController::class, 'destroy'])
            ->name('stores.keywords.destroy');

        // 順位履歴
        Route::get('stores/{store}/rankings', [AdminStoreRankingController::class, 'index'])
            ->name('stores.rankings.index');

        // 競合
        Route::get('stores/{store}/competitors', [AdminCompetitorController::class, 'index'])
            ->name('stores.competitors.index');
        Route::post('stores/{store}/competitors', [AdminCompetitorController::class, 'store'])
            ->name('stores.competitors.store');
        Route::delete('stores/{store}/competitors/{competitor}', [AdminCompetitorController::class, 'destroy'])
            ->name('stores.competitors.destroy');

        // レポート出力（即時 DL + プレビュー）
        Route::get('reports/output', [AdminReportController::class, 'create'])->name('reports.output');
        Route::post('reports/preview', [AdminReportController::class, 'preview'])->name('reports.preview');
        Route::post('reports/download', [AdminReportController::class, 'download'])->name('reports.download');

        // レポート配信予約
        Route::resource('report-schedules', AdminReportScheduleController::class)->except(['destroy']);
        Route::delete('report-schedules/{report_schedule}', [AdminReportScheduleController::class, 'destroy'])->name('report-schedules.destroy');
        Route::post('report-schedules/{report_schedule}/toggle', [AdminReportScheduleController::class, 'toggle'])->name('report-schedules.toggle');

        // 順位アラート
        Route::resource('alerts', AdminAlertController::class)->except(['destroy']);
        Route::delete('alerts/{alert}', [AdminAlertController::class, 'destroy'])->name('alerts.destroy');
        Route::post('alerts/{alert}/toggle', [AdminAlertController::class, 'toggle'])->name('alerts.toggle');

        // ユーザー管理（招待）
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('users/invite', [AdminUserController::class, 'invite'])->name('users.invite');
        Route::post('users/{user}/resend', [AdminUserController::class, 'resend'])->name('users.resend');
        Route::post('users/{user}/disable', [AdminUserController::class, 'disable'])->name('users.disable');
        Route::post('users/{user}/enable', [AdminUserController::class, 'enable'])->name('users.enable');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });

// ----- 招待 URL（パスワード設定）認証不要 -----
Route::get('/invitation/{token}', [InvitationController::class, 'show'])->name('invitation.show');
Route::post('/invitation/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');

// ----- 公開ページ（プライバシー / 利用規約 / お問い合わせ） -----
Route::prefix('/')->name('public.')->group(function () {
    Route::get('/privacy', [StaticPageController::class, 'privacy'])->name('privacy');
    Route::get('/terms', [StaticPageController::class, 'terms'])->name('terms');
    Route::get('/contact', [StaticPageController::class, 'contact'])->name('contact');
    Route::post('/contact', [StaticPageController::class, 'sendContact'])->name('contact.send');
});

// ----- Client（クライアント側、閲覧のみ） -----
Route::middleware(['auth', 'role:client'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::get('/stores/{store}', [ClientStoreController::class, 'show'])->name('stores.show');
        Route::get('/stores/{store}/rankings', [ClientStoreController::class, 'rankings'])->name('stores.rankings');
    });

// ----- 全 role 共通 -----
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // profile.destroy は廃止（自社管理ツールではアカウント削除は管理者経由）
});

require __DIR__.'/auth.php';
