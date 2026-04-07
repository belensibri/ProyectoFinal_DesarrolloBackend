<?php

namespace App\Http\Controllers;

use App\Models\FaqArticle;
use Illuminate\Http\JsonResponse;

class FaqArticleController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', FaqArticle::class);

        return response()->json(
            FaqArticle::query()->with(['ticket', 'user'])->latest()->get()
        );
    }

    public function show(FaqArticle $faqArticle): JsonResponse
    {
        $this->authorize('view', $faqArticle);

        return response()->json($faqArticle->load(['ticket', 'user']));
    }
}
