<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $currentUser): bool
    {
        return true;
    }

    // utilisateur peut voir un profil
    public function view(User $currentUser, User $model): bool
    {
        return true;
    }

    // Détermine si l'utilisateur peut modifier ce profil
    public function update(User $currentUser, User $model): bool
    {
        // On ne peut modifier que son propre compte
        return $currentUser->id === $model->id;
    }

    // Détermine si l'utilisateur peut supprimer ce profil
    public function delete(User $currentUser, User $model): bool
    {
        // On ne peut supprimer que son propre compte

        return $currentUser->id === $model->id;
    }
}