<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFaqArticleRequest;
use App\Models\FaqArticle;
use Illuminate\Support\Facades\Gate;

class FaqArticleController extends Controller
{
    public function index()
    {
        return FaqArticle::with('user')->get();
    }

    public function store(StoreFaqArticleRequest $request)
    {
        Gate::authorize('create', FaqArticle::class);

        $faq = FaqArticle::create(array_merge($request->validated(), [
            'usuario_id' => $request->user()->id
        ]));

        return response()->json($faq, 201);
    }
}
