<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ReportSchedule;
use App\Models\Store;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $query = ReportSchedule::with('store.company')->orderBy('next_run_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($q = $request->query('q')) {
            $query->whereHas('store', fn ($s) => $s->where('name', 'like', "%{$q}%"))
                ->orWhere('name', 'like', "%{$q}%");
        }

        return view('admin.report-schedules.index', [
            'schedules' => $query->paginate(20)->withQueryString(),
            'statuses' => ReportSchedule::STATUSES,
            'currentStatus' => $request->query('status'),
            'currentQ' => $request->query('q'),
        ]);
    }

    public function create(): View
    {
        return view('admin.report-schedules.form', [
            'schedule' => new ReportSchedule([
                'recurrence' => ReportSchedule::RECURRENCE_MONTHLY,
                'recurrence_day' => 5,
                'subject' => 'MEO 月次レポートの件',
                'body' => $this->defaultBody(),
            ]),
            'companies' => Company::with('stores')->orderBy('name')->get(),
            'recurrences' => ReportSchedule::RECURRENCES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $schedule = ReportSchedule::create($data);
        $schedule->update(['next_run_at' => $schedule->calculateNextRun()]);

        return redirect()->route('admin.report-schedules.show', $schedule)
            ->with('status', 'レポート配信予約を登録しました。');
    }

    public function show(ReportSchedule $reportSchedule): View
    {
        return view('admin.report-schedules.show', [
            'schedule' => $reportSchedule->load('store.company'),
        ]);
    }

    public function edit(ReportSchedule $reportSchedule): View
    {
        return view('admin.report-schedules.form', [
            'schedule' => $reportSchedule,
            'companies' => Company::with('stores')->orderBy('name')->get(),
            'recurrences' => ReportSchedule::RECURRENCES,
        ]);
    }

    public function update(Request $request, ReportSchedule $reportSchedule): RedirectResponse
    {
        $data = $this->validateData($request);
        $reportSchedule->update($data);
        $reportSchedule->update(['next_run_at' => $reportSchedule->calculateNextRun()]);

        return redirect()->route('admin.report-schedules.show', $reportSchedule)
            ->with('status', 'レポート配信予約を更新しました。');
    }

    public function toggle(ReportSchedule $reportSchedule): RedirectResponse
    {
        $next = $reportSchedule->status === ReportSchedule::STATUS_ACTIVE
            ? ReportSchedule::STATUS_PAUSED
            : ReportSchedule::STATUS_ACTIVE;
        $reportSchedule->update(['status' => $next]);
        if ($next === ReportSchedule::STATUS_ACTIVE) {
            $reportSchedule->update(['next_run_at' => $reportSchedule->calculateNextRun()]);
        }

        return back()->with('status', $next === ReportSchedule::STATUS_ACTIVE ? '配信を再開しました。' : '配信を停止しました。');
    }

    public function destroy(ReportSchedule $reportSchedule): RedirectResponse
    {
        $reportSchedule->update(['status' => ReportSchedule::STATUS_CANCELLED]);
        $reportSchedule->delete();

        return redirect()->route('admin.report-schedules.index')
            ->with('status', '配信予約を削除しました。');
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
            'name' => ['required', 'string', 'max:50'],
            'recurrence' => ['required', 'in:once,monthly,weekly'],
            'scheduled_at' => ['nullable', 'date', 'required_if:recurrence,once'],
            'recurrence_day' => ['nullable', 'integer', 'min:0', 'max:31'],
            'recipients' => ['required', 'string', 'max:2000'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:10000'],
            'admin_comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['status'] = $validated['status'] ?? ReportSchedule::STATUS_ACTIVE;
        return $validated;
    }

    private function defaultBody(): string
    {
        return <<<TEXT
表題の件について、
PDF にて先月分の月次レポートを送付いたします。
ご確認のほど、よろしくお願いいたします。

ご不明点ございましたら、お問い合わせフォームよりご連絡くださいませ。
お問い合わせ：https://laiweb.jp/contact/

株式会社 L'aide / laiweb-dash 運営事務局
TEXT;
    }
}
