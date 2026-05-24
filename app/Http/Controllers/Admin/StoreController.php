<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));
        $companyId = $request->input('company_id', '');
        $gbpStatus = $request->input('gbp_status', '');

        $stores = Store::query()
            ->with('company')
            ->withCount('keywords')
            ->when($q !== '', fn ($query) => $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                    ->orWhere('business_name', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%");
            }))
            ->when($companyId !== '', fn ($query) => $query->where('company_id', $companyId))
            ->when(in_array($gbpStatus, array_keys(Store::GBP_STATUSES), true),
                fn ($query) => $query->where('gbp_status', $gbpStatus))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.stores.index', [
            'stores' => $stores,
            'companies' => Company::orderBy('name')->get(),
            'q' => $q,
            'companyId' => $companyId,
            'gbpStatus' => $gbpStatus,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.stores.create', [
            'store' => new Store([
                'company_id' => (int) $request->input('company_id'),
                'gbp_status' => Store::GBP_STATUS_UNSET,
            ]),
            'companies' => Company::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);
        $store = Store::create($validated);

        return redirect()->route('admin.stores.show', $store)
            ->with('status', '店舗を登録しました。');
    }

    public function show(Store $store): View
    {
        $store->load(['company', 'keywords', 'competitors']);

        return view('admin.stores.show', compact('store'));
    }

    public function edit(Store $store): View
    {
        return view('admin.stores.edit', [
            'store' => $store,
            'companies' => Company::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        $validated = $this->validateData($request);
        $store->update($validated);

        return redirect()->route('admin.stores.show', $store)
            ->with('status', '店舗情報を更新しました。');
    }

    public function destroy(Store $store): RedirectResponse
    {
        $store->delete();

        return redirect()->route('admin.stores.index')
            ->with('status', "{$store->name} を削除しました。");
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:120'],
            'business_name' => ['nullable', 'string', 'max:120'],
            'industry' => ['nullable', 'string', 'max:80'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gbp_place_id' => ['nullable', 'string', 'max:255'],
            'gbp_location_id' => ['nullable', 'string', 'max:255'],
            'gbp_status' => ['required', 'in:' . implode(',', array_keys(Store::GBP_STATUSES))],
            'has_gbp' => ['nullable', 'boolean'],
            'has_yahoo' => ['nullable', 'boolean'],
        ]);

        // checkbox 由来：未送信時は false
        $validated['has_gbp'] = (bool) ($validated['has_gbp'] ?? false);
        $validated['has_yahoo'] = (bool) ($validated['has_yahoo'] ?? false);

        return $validated;
    }
}
