<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keyword;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KeywordController extends Controller
{
    /**
     * 店舗配下の KW を一括管理（GMO 風：エリア × KW のテーブル）。
     * Store スコープ前提で、/admin/stores/{store}/keywords。
     */
    public function index(Store $store): View
    {
        $keywords = $store->keywords()
            ->orderBy('location_code')
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        return view('admin.keywords.index', [
            'store' => $store,
            'keywords' => $keywords,
        ]);
    }

    public function store(Request $request, Store $store): RedirectResponse
    {
        $validated = $request->validate([
            'keyword' => [
                'required', 'string', 'max:120',
                Rule::unique('keywords')->where(fn ($q) => $q
                    ->where('store_id', $store->id)
                    ->where('location_code', $request->input('location_code'))
                    ->whereNull('deleted_at')),
            ],
            'location_code' => ['nullable', 'string', 'max:32'],
            'priority' => ['nullable', 'integer', 'between:1,9'],
        ]);

        $store->keywords()->create([
            'keyword' => $validated['keyword'],
            'location_code' => $validated['location_code'] ?? null,
            'priority' => $validated['priority'] ?? 3,
            'is_active' => true,
        ]);

        return redirect()->route('admin.stores.keywords.index', $store)
            ->with('status', "「{$validated['keyword']}」を追加しました。");
    }

    public function toggle(Store $store, Keyword $keyword): RedirectResponse
    {
        abort_unless($keyword->store_id === $store->id, 404);

        $keyword->update(['is_active' => ! $keyword->is_active]);

        $state = $keyword->is_active ? '有効化' : '無効化';

        return back()->with('status', "「{$keyword->keyword}」を{$state}しました。");
    }

    public function destroy(Store $store, Keyword $keyword): RedirectResponse
    {
        abort_unless($keyword->store_id === $store->id, 404);

        $name = $keyword->keyword;
        $keyword->delete();

        return back()->with('status', "「{$name}」を削除しました。");
    }
}
