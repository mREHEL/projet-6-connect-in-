<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\DB;

class PostService
{
    // Crée un post avec ses fichiers médias
    public function createPostWithMedia(array $data, array $files = [])
    {
        // transaction pour s'assurer que le post et les médias sont tous créés
        return DB::transaction(function () use ($data, $files) {
            
            // Création post
            $post = Post::create([
                'user_id' => $data['user_id'],
                'content' => $data['content'],
            ]);

            // Gestion des médias
            foreach ($files as $file) {
                $path = $file->store('posts/media', 'public');
                
                $post->media()->create([
                    'url' => $path,
                    'type' => str_contains($file->getMimeType(), 'video') ? 'video' : 'image',
                ]);
            }

            // On retourne le post chargé avec ses médias pour confirmation
            return $post->load('media');
        });
    }

    // Supprime un post et ses médias associés
    public function deletePost(int $postId)
    {
        $post = Post::findOrFail($postId);
        // Les médias seront supprimés via la base de données
        return $post->delete();
    }
}