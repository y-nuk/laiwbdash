<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class SurveyController extends Controller
{
    public function index(Request $request): View
    {
        $query = Survey::with('store.company')->withCount('responses')->orderByDesc('id');
        if ($q = $request->query('q')) {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhereHas('store', fn ($s) => $s->where('name', 'like', "%{$q}%"));
            });
        }
        return view('admin.surveys.index', [
            'surveys' => $query->paginate(20)->withQueryString(),
            'currentQ' => $request->query('q'),
        ]);
    }

    public function create(): View
    {
        return view('admin.surveys.form', [
            'survey' => new Survey([
                'title' => '店舗ご利用アンケート',
                'description' => 'ご利用ありがとうございました。サービス品質向上のため、ご感想をお聞かせください（1 分で完了）',
                'is_active' => true,
                'high_rating_threshold' => 4,
                'low_rating_message' => '貴重なご意見をありがとうございます。担当者が確認の上、改善に努めてまいります。',
                'thank_you_message' => 'ご回答ありがとうございました。',
            ]),
            'companies' => Company::with('stores')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $survey = Survey::create($this->validateData($request));
        return redirect()->route('admin.surveys.show', $survey)
            ->with('status', 'アンケートを作成しました。QR コードからすぐご利用いただけます。');
    }

    public function show(Survey $survey): View
    {
        $survey->load('store.company', 'responses');
        return view('admin.surveys.show', [
            'survey' => $survey,
            'dist' => $survey->ratingDistribution(),
            'avg' => $survey->averageRating(),
            'recentResponses' => $survey->responses()->latest()->limit(20)->get(),
        ]);
    }

    public function edit(Survey $survey): View
    {
        return view('admin.surveys.form', [
            'survey' => $survey,
            'companies' => Company::with('stores')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Survey $survey): RedirectResponse
    {
        $survey->update($this->validateData($request));
        return redirect()->route('admin.surveys.show', $survey)->with('status', 'アンケートを更新しました。');
    }

    public function destroy(Survey $survey): RedirectResponse
    {
        $survey->delete();
        return redirect()->route('admin.surveys.index')->with('status', 'アンケートを削除しました。');
    }

    /**
     * QR コードを SVG で返す（インライン表示 + DL 両対応）。
     */
    public function qr(Survey $survey, Request $request): Response
    {
        $size = (int) $request->query('size', 320);
        $svg = QrCode::format('svg')->size($size)->margin(1)->generate($survey->publicUrl());

        $disposition = $request->query('download') === '1'
            ? 'attachment; filename="survey-' . $survey->id . '.svg"'
            : 'inline';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => $disposition,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'high_rating_threshold' => ['required', 'integer', 'min:1', 'max:5'],
            'google_review_url' => ['nullable', 'url', 'max:500'],
            'low_rating_message' => ['nullable', 'string', 'max:2000'],
            'thank_you_message' => ['nullable', 'string', 'max:2000'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        // questions は MVP では空配列（将来カスタム質問を追加する余地）
        $data['questions'] = [];
        return $data;
    }
}
