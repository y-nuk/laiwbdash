<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ClientInvitationMail;
use App\Mail\StaffInvitationMail;
use App\Models\Agency;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->input('role', '');
        $q = trim((string) $request->input('q', ''));

        $users = User::query()
            ->with(['agency', 'company'])
            ->when($role !== '' && array_key_exists($role, User::ROLES),
                fn ($qb) => $qb->where('role', $role))
            ->when($q !== '', fn ($qb) => $qb->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            }))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'role' => $role,
            'q' => $q,
            'companies' => Company::orderBy('name')->get(),
            'agencies' => Agency::orderBy('name')->get(),
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in([User::ROLE_STAFF, User::ROLE_CLIENT])],
            'company_id' => [
                'nullable', 'integer', 'exists:companies,id',
                Rule::requiredIf(fn () => $request->input('role') === User::ROLE_CLIENT),
            ],
        ]);

        $isClient = $validated['role'] === User::ROLE_CLIENT;
        $token = Str::random(64);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => null,
            'role' => $validated['role'],
            'agency_id' => $isClient ? null : Agency::where('is_self', true)->value('id'),
            'company_id' => $isClient ? $validated['company_id'] : null,
            'invitation_token' => $token,
            'invitation_sent_at' => now(),
        ]);

        Mail::to($user->email)->send($isClient
            ? new ClientInvitationMail($user)
            : new StaffInvitationMail($user));

        return redirect()->route('admin.users.index')
            ->with('status', "{$user->email} に招待メールを送信しました。");
    }

    public function resend(User $user): RedirectResponse
    {
        if ($user->invitation_accepted_at) {
            return back()->with('status', 'このユーザーは既にパスワードを設定済みです。');
        }

        $user->update([
            'invitation_token' => Str::random(64),
            'invitation_sent_at' => now(),
        ]);

        Mail::to($user->email)->send($user->isClient()
            ? new ClientInvitationMail($user)
            : new StaffInvitationMail($user));

        return back()->with('status', "{$user->email} に招待メールを再送しました。");
    }

    public function disable(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('status', '自分自身を無効化することはできません。');
        }

        $user->update(['disabled_at' => now()]);

        return back()->with('status', "{$user->email} を無効化しました（ログイン不可）。");
    }

    public function enable(User $user): RedirectResponse
    {
        $user->update(['disabled_at' => null]);

        return back()->with('status', "{$user->email} を再有効化しました。");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('status', '自分自身は削除できません。');
        }

        if ($user->isAdmin()) {
            return back()->with('status', 'admin ユーザーは削除できません。');
        }

        $email = $user->email;
        $user->delete();

        return back()->with('status', "{$email} を削除しました。");
    }
}
