<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ClientInvitationMail;
use App\Models\Agency;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', '');
        $responsible = $request->input('responsible', ''); // 'mine' = 自分の担当のみ

        $companies = Company::query()
            ->with(['agency', 'responsibleUser'])
            ->withCount('stores')
            ->when($q !== '', fn ($query) => $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                    ->orWhere('kana', 'like', "%{$q}%")
                    ->orWhere('contact_person_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            }))
            ->when(in_array($status, array_keys(Company::STATUSES), true),
                fn ($query) => $query->where('status', $status))
            ->when($responsible === 'mine',
                fn ($query) => $query->where('responsible_user_id', $request->user()->id))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.companies.index', [
            'companies' => $companies,
            'q' => $q,
            'status' => $status,
            'responsible' => $responsible,
        ]);
    }

    public function create(): View
    {
        return view('admin.companies.create', [
            'company' => new Company(['status' => Company::STATUS_ACTIVE]),
            'agencies' => Agency::orderBy('name')->get(),
            'staffUsers' => User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])
                ->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);
        $company = Company::create($validated);

        $invited = $this->ensureClientInvited($company);

        $msg = $invited
            ? "会社を登録し、{$company->email} に招待メールを送信しました。"
            : '会社を登録しました。';

        return redirect()->route('admin.companies.show', $company)->with('status', $msg);
    }

    public function show(Company $company): View
    {
        $company->load(['agency', 'stores', 'users', 'responsibleUser']);

        return view('admin.companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        return view('admin.companies.edit', [
            'company' => $company,
            'agencies' => Agency::orderBy('name')->get(),
            'staffUsers' => User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])
                ->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $validated = $this->validateData($request);
        $company->update($validated);

        return redirect()->route('admin.companies.show', $company)
            ->with('status', '会社情報を更新しました。');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('status', "{$company->name} を削除しました。");
    }

    /**
     * 会社の email + 担当者名で client ユーザーを作って招待メールを送る。
     * 同じ email がもう存在する場合はスキップ。
     */
    public function resendInvitation(Company $company): RedirectResponse
    {
        // 同メアドで既に client がいれば、token 再発行 + メール再送
        $user = User::where('email', $company->email)->first();

        if ($user && $user->invitation_accepted_at) {
            return back()->with('status', 'このユーザーは既にパスワードを設定済みです。');
        }

        $this->ensureClientInvited($company, force: true);

        return back()->with('status', "{$company->email} に招待メールを再送しました。");
    }

    private function ensureClientInvited(Company $company, bool $force = false): bool
    {
        if (! $company->email || ! $company->contact_person_name) {
            return false;
        }

        $user = User::where('email', $company->email)->first();

        // 既存ユーザーで accept 済みなら何もしない
        if ($user && $user->invitation_accepted_at && ! $force) {
            return false;
        }

        $token = Str::random(64);

        if ($user) {
            $user->update([
                'name' => $company->contact_person_name,
                'role' => User::ROLE_CLIENT,
                'company_id' => $company->id,
                'invitation_token' => $token,
                'invitation_sent_at' => now(),
                'invitation_accepted_at' => null,
            ]);
        } else {
            $user = User::create([
                'name' => $company->contact_person_name,
                'email' => $company->email,
                'password' => null,
                'role' => User::ROLE_CLIENT,
                'company_id' => $company->id,
                'invitation_token' => $token,
                'invitation_sent_at' => now(),
            ]);
        }

        Mail::to($user->email)->send(new ClientInvitationMail($user));

        return true;
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'agency_id' => ['required', 'integer', 'exists:agencies,id'],
            'responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:120'],
            'kana' => ['nullable', 'string', 'max:120'],
            'contact_person_name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'fax' => ['nullable', 'string', 'max:20'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:80'],
            'status' => ['required', 'in:' . implode(',', array_keys(Company::STATUSES))],
            'admin_message' => ['nullable', 'string', 'max:2000'],
        ]);

        // admin_message が変更されたら更新日時を記録
        if ($request->filled('admin_message')) {
            $validated['admin_message_updated_at'] = now();
        }
        return $validated;
    }
}
