<?php

namespace App\Policies;

use App\Models\FaqArticle;
use App\Models\User;

class FaqArticlePolicy
{
    public function create(User $user): bool
    {
        return true; // Todos pueden crear artículos según los requerimientos
    }
}
