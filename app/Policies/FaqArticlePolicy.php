<?php

namespace App\Policies;

use App\Models\FaqArticle;
use App\Models\User;

class FaqArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isUsuario() || $user->isTecnico() || $user->isAdministrador();
    }

    public function view(User $user, FaqArticle $faqArticle): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, FaqArticle $faqArticle): bool
    {
        return false;
    }

    public function delete(User $user, FaqArticle $faqArticle): bool
    {
        return false;
    }
}
