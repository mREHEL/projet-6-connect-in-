<?php


namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }
    public function view(User $user, Post $post): bool
    {
        return true;
    }

    // Seul un utilisateur connecté peut créer un post
    public function create(User $user): bool
    {
        return true;
    }

    // Seul l'auteur peut modifier le post
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    // Seul l'auteur peut supprimer le post
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
        
    // On ne peut retirer qu'un like qui nous appartient
    public function unlike(User $user, Post $post): bool
    {
        return true;
    }
}