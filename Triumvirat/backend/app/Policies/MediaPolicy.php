<?php
namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function delete(User $user, Media $media): bool
    {
        // Le média appartient à un post et le post appartient à un utilisateur On vérifie si l'utilisateur connecté est le propriétaire
        return $user->id === $media->post->user_id;
    }

    public function update(User $user, Media $media): bool
    {
        return $user->id === $media->post->user_id;
    }
}