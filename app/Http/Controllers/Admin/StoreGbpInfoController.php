<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * 店舗の GBP 基本情報編集。
 * GBP API 通過後は GbpFetcher の同期処理で自動上書き予定（gbp_protected で抑制可）。
 */
class StoreGbpInfoController extends Controller
{
    public function edit(Store $store): View
    {
        return view('admin.stores.gbp-basic', compact('store'));
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        $validated = $request->validate([
            'business_status' => ['required', 'in:' . implode(',', array_keys(Store::BUSINESS_STATUSES))],
            'primary_category' => ['nullable', 'string', 'max:80'],
            'additional_categories' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'reservation_url' => ['nullable', 'url', 'max:255'],
            'menu_url' => ['nullable', 'url', 'max:255'],
            'order_url' => ['nullable', 'url', 'max:255'],
            'service_areas' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:750'],
            'opening_date' => ['nullable', 'date'],
            'gbp_protected' => ['nullable', 'boolean'],
            'hours' => ['nullable', 'array'],
        ]);

        // textarea (改行区切り) → array に変換
        $store->additional_categories = $this->splitLines($validated['additional_categories'] ?? null);
        $store->service_areas = $this->splitLines($validated['service_areas'] ?? null);

        // 曜日別営業時間
        $hours = [];
        foreach (Store::WEEKDAYS as $day => $_label) {
            $row = $validated['hours'][$day] ?? [];
            $hours[$day] = [
                'closed' => isset($row['closed']) && $row['closed'],
                'open' => $row['open'] ?? '09:00',
                'close' => $row['close'] ?? '18:00',
            ];
        }
        $store->business_hours = $hours;

        $store->fill([
            'business_status' => $validated['business_status'],
            'primary_category' => $validated['primary_category'] ?? null,
            'website_url' => $validated['website_url'] ?? null,
            'reservation_url' => $validated['reservation_url'] ?? null,
            'menu_url' => $validated['menu_url'] ?? null,
            'order_url' => $validated['order_url'] ?? null,
            'description' => $validated['description'] ?? null,
            'opening_date' => $validated['opening_date'] ?? null,
            'gbp_protected' => (bool) ($validated['gbp_protected'] ?? false),
        ])->save();

        return redirect()->route('admin.stores.gbp-basic.edit', $store)
            ->with('status', 'GBP 基本情報を保存しました。');
    }

    /** "東京都\n埼玉県" のような複数行テキストを ['東京都', '埼玉県'] に */
    private function splitLines(?string $text): ?array
    {
        if (! $text) return null;
        $lines = array_filter(array_map('trim', preg_split('/\R/', $text)));
        return empty($lines) ? null : array_values($lines);
    }
}
