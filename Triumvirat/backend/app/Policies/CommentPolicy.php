<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    // Autoriser la lecture si l'utilisateur est connecté
    public function viewAny(User $user): bool
    {
        return true;
    }
    public function view(User $user, Comment $comment): bool
    {
        return true;
    }

    // Autoriser la création à tout utilisateur connecté
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Comment $comment): bool
    {
        // Seul l'auteur peut modifier son propre commentaire
        return $user->id === $comment->user_id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        // Supprimable par l'auteur du com OU le propriétaire du post
        return $user->id === $comment->user_id || $user->id === $comment->post->user_id;
    }
}