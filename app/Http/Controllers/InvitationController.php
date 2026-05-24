<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function show(string $token): View
    {
        $user = $this->resolveOrFail($token);

        return view('auth.invitation-accept', [
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $user = $this->resolveOrFail($token);

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->forceFill([
            'password' => Hash::make($request->input('password')),
            'invitation_token' => null,
            'invitation_accepted_at' => now(),
            'email_verified_at' => now(),
        ])->save();

        Auth::login($user);

        return $user->isInternal()
            ? redirect()->route('admin.dashboard')->with('status', 'ようこそ！パスワードを設定しました。')
            : redirect()->route('client.dashboard')->with('status', 'ようこそ！パスワードを設定しました。');
    }

    private function resolveOrFail(string $token): User
    {
        $user = User::where('invitation_token', $token)->first();

        abort_unless($user, 404, '招待 URL が無効です。運営担当にご連絡ください。');

        // 14 日以内に accept されないと無効とする
        if ($user->invitation_sent_at && $user->invitation_sent_at->lt(now()->subDays(14))) {
            abort(410, '招待 URL の有効期限が切れています（14 日間）。運営担当に再送をご依頼ください。');
        }

        return $user;
    }
}
