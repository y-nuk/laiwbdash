<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SurveyController extends Controller
{
    /**
     * 顧客向け：QR スキャン後のアンケート表示。
     */
    public function show(string $token): View
    {
        $survey = Survey::with('store.company')->where('token', $token)->where('is_active', true)->firstOrFail();

        return view('public.survey-show', [
            'survey' => $survey,
        ]);
    }

    /**
     * 顧客向け：回答送信。
     */
    public function store(Request $request, string $token)
    {
        $survey = Survey::with('store')->where('token', $token)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'overall_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'contact_ok' => ['nullable', 'boolean'],
        ]);

        $response = SurveyResponse::create([
            'survey_id' => $survey->id,
            'overall_rating' => $validated['overall_rating'],
            'responses' => [
                'comment' => $validated['comment'] ?? null,
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
                'contact_ok' => $request->boolean('contact_ok'),
            ],
            'ip_address' => $request->ip(),
            'answered_at' => now(),
        ]);

        // 高評価 → Google レビュー画面に誘導
        if ($validated['overall_rating'] >= $survey->high_rating_threshold && $survey->google_review_url) {
            return redirect()->route('public.survey.high', ['token' => $token]);
        }

        return redirect()->route('public.survey.thanks', ['token' => $token]);
    }

    public function high(string $token): View
    {
        $survey = Survey::where('token', $token)->firstOrFail();
        return view('public.survey-high', ['survey' => $survey]);
    }

    public function thanks(string $token): View
    {
        $survey = Survey::where('token', $token)->firstOrFail();
        return view('public.survey-thanks', ['survey' => $survey]);
    }
}
