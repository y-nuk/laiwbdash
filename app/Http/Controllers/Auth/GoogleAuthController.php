<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as BaseRedirect;
use Throwable;

/**
 * Google ビジネスプロフィール OAuth 連携。
 *
 * 用途：既ログインの admin/staff が、自分の Google アカウントに紐付く
 * Business Profile を laiweb-dash から操作できるようにする。
 *
 * フロー：
 *  1. /auth/google/redirect       → Google 認証画面へ
 *  2. /auth/google/callback       → Access/Refresh トークンを users に保存
 *  3. /auth/google/disconnect     → トークン削除（連携解除）
 *
 * 必要スコープは config/services.php の google.scopes 参照。
 */
class GoogleAuthController extends Controller
{
    public function redirect(): BaseRedirect
    {
        return Socialite::driver('google')
            ->scopes(config('services.google.scopes', []))
            ->with([
                'access_type' => 'offline',  // refresh_token を取得するため
                'prompt' => 'consent',       // 毎回 refresh_token を返してもらう
            ])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login')->withErrors([
                'gbp' => 'GBP 連携にはログインが必要です。',
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::error('Google OAuth callback failed', ['error' => $e->getMessage()]);
            return redirect()->route('admin.gbp.connect')
                ->withErrors(['gbp' => 'Google 認証に失敗しました：' . $e->getMessage()]);
        }

        $user = Auth::user();
        $user->forceFill([
            'gbp_access_token' => $googleUser->token,
            'gbp_refresh_token' => $googleUser->refreshToken ?: $user->gbp_refresh_token,
            'gbp_token_expires_at' => $googleUser->expiresIn ? now()->addSeconds($googleUser->expiresIn) : null,
            'gbp_account_email' => $googleUser->getEmail(),
            'gbp_account_info' => [
                'id' => $googleUser->getId(),
                'name' => $googleUser->getName(),
                'nickname' => $googleUser->getNickname(),
                'avatar' => $googleUser->getAvatar(),
            ],
        ])->save();

        return redirect()->route('admin.gbp.connect')
            ->with('status', 'Google ビジネスプロフィールと連携しました：' . $googleUser->getEmail());
    }

    public function disconnect(): RedirectResponse
    {
        $user = Auth::user();
        $user->forceFill([
            'gbp_access_token' => null,
            'gbp_refresh_token' => null,
            'gbp_token_expires_at' => null,
            'gbp_account_email' => null,
            'gbp_account_info' => null,
        ])->save();

        return redirect()->route('admin.gbp.connect')
            ->with('status', 'GBP 連携を解除しました。');
    }
}
