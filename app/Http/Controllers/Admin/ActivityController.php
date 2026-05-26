<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $query = Activity::with(['company', 'user'])->latest('occurred_at');

        if ($filter = $request->query('filter')) {
            $today = now()->toDateString();
            $query = match ($filter) {
                'follow_today' => $query
                    ->where('follow_up_at', $today)
                    ->where('follow_up_done', false),
                'follow_overdue' => $query
                    ->whereNotNull('follow_up_at')
                    ->where('follow_up_at', '<', $today)
                    ->where('follow_up_done', false),
                'mine' => $query->where('user_id', $request->user()->id),
                default => $query,
            };
        }

        return view('admin.activities.index', [
            'activities' => $query->paginate(30)->withQueryString(),
            'currentFilter' => $request->query('filter'),
        ]);
    }

    public function store(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:' . implode(',', array_keys(Activity::TYPES))],
            'title' => ['required', 'string', 'max:150'],
            'body' => ['nullable', 'string', 'max:5000'],
            'occurred_at' => ['required', 'date'],
            'follow_up_at' => ['nullable', 'date'],
        ]);

        $data['company_id'] = $company->id;
        $data['user_id'] = Auth::id();
        Activity::create($data);

        return back()->with('status', '活動を記録しました。');
    }

    public function toggleFollowUp(Activity $activity): RedirectResponse
    {
        $activity->update(['follow_up_done' => ! $activity->follow_up_done]);
        return back()->with('status', $activity->follow_up_done ? 'フォローアップを完了にしました。' : 'フォローアップを未完了に戻しました。');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $companyId = $activity->company_id;
        $activity->delete();
        return back()->with('status', '活動を削除しました。');
    }
}
