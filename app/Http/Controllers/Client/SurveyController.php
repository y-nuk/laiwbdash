<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $surveys = Survey::whereHas('store', fn ($q) => $q->where('company_id', $user->company_id))
            ->with('store')
            ->withCount('responses')
            ->orderByDesc('id')
            ->paginate(20);

        return view('client.surveys.index', [
            'surveys' => $surveys,
        ]);
    }

    public function show(Survey $survey): View
    {
        $user = Auth::user();

        // 自社店舗のアンケートのみ閲覧可
        abort_unless($survey->store->company_id === $user->company_id, 403);

        $survey->load('store', 'responses');

        return view('client.surveys.show', [
            'survey' => $survey,
            'dist' => $survey->ratingDistribution(),
            'avg' => $survey->averageRating(),
            'recentResponses' => $survey->responses()->latest()->limit(50)->get(),
        ]);
    }
}
