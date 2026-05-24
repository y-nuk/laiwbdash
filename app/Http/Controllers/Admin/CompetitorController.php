<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competitor;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompetitorController extends Controller
{
    public function index(Store $store): View
    {
        $competitors = $store->competitors()->get();

        return view('admin.competitors.index', compact('store', 'competitors'));
    }

    public function store(Request $request, Store $store): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'gbp_url' => ['nullable', 'url', 'max:500'],
            'gbp_place_id' => ['nullable', 'string', 'max:255'],
        ]);

        $maxOrder = $store->competitors()->max('sort_order') ?? 0;

        $store->competitors()->create([
            'name' => $validated['name'],
            'gbp_url' => $validated['gbp_url'] ?? null,
            'gbp_place_id' => $validated['gbp_place_id'] ?? null,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.stores.competitors.index', $store)
            ->with('status', "「{$validated['name']}」を競合店に追加しました。");
    }

    public function destroy(Store $store, Competitor $competitor): RedirectResponse
    {
        abort_unless($competitor->store_id === $store->id, 404);

        $name = $competitor->name;
        $competitor->delete();

        return back()->with('status', "「{$name}」を競合店から削除しました。");
    }
}
