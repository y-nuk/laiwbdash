<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Company;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(Request $request): View
    {
        $query = Alert::with(['store.company', 'keyword'])->orderByDesc('id');

        if ($enabled = $request->query('enabled')) {
            $query->where('enabled', $enabled === 'yes');
        }
        if ($q = $request->query('q')) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhereHas('store', fn ($s) => $s->where('name', 'like', "%{$q}%"));
            });
        }

        return view('admin.alerts.index', [
            'alerts' => $query->paginate(20)->withQueryString(),
            'currentEnabled' => $request->query('enabled'),
            'currentQ' => $request->query('q'),
        ]);
    }

    public function create(): View
    {
        return view('admin.alerts.form', [
            'alert' => new Alert([
                'name' => '順位下落アラート',
                'alert_type' => Alert::TYPE_RANKING_DROP,
                'threshold' => 5,
                'enabled' => true,
            ]),
            'companies' => Company::with('stores.keywords')->orderBy('name')->get(),
            'types' => Alert::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $alert = Alert::create($this->validateData($request));

        return redirect()->route('admin.alerts.show', $alert)
            ->with('status', '順位アラートを登録しました。');
    }

    public function show(Alert $alert): View
    {
        return view('admin.alerts.show', [
            'alert' => $alert->load(['store.company', 'keyword']),
        ]);
    }

    public function edit(Alert $alert): View
    {
        return view('admin.alerts.form', [
            'alert' => $alert,
            'companies' => Company::with('stores.keywords')->orderBy('name')->get(),
            'types' => Alert::TYPES,
        ]);
    }

    public function update(Request $request, Alert $alert): RedirectResponse
    {
        $alert->update($this->validateData($request));

        return redirect()->route('admin.alerts.show', $alert)
            ->with('status', 'アラートを更新しました。');
    }

    public function toggle(Alert $alert): RedirectResponse
    {
        $alert->update(['enabled' => ! $alert->enabled]);

        return back()->with('status', $alert->enabled ? 'アラートを有効化しました。' : 'アラートを無効化しました。');
    }

    public function destroy(Alert $alert): RedirectResponse
    {
        $alert->delete();

        return redirect()->route('admin.alerts.index')
            ->with('status', 'アラートを削除しました。');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
            'keyword_id' => ['nullable', 'exists:keywords,id'],
            'name' => ['required', 'string', 'max:100'],
            'alert_type' => ['required', 'in:ranking_drop,out_of_rank,worse_than'],
            'threshold' => ['required', 'integer', 'min:0', 'max:200'],
            'recipients' => ['nullable', 'string', 'max:2000'],
            'enabled' => ['nullable', 'boolean'],
            'admin_comment' => ['nullable', 'string', 'max:2000'],
        ]) + ['enabled' => $request->boolean('enabled')];
    }
}
