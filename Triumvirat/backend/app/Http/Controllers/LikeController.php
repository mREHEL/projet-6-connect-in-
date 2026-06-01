<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    // Laravel injecte directement le Post
    public function toggle(Post $post)
    {
        $userId = auth()->id();

        // On cherche si le like existe déjà
        $like = Like::where('post_id', $post->id)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();
            return response()->json([
                'message' => 'Like retiré',
                'is_liked' => false,
                'likes_count' => $post->likes()->count()
            ], 200);
        }

        $newLike = Like::create([
            'post_id' => $post->id,
            'user_id' => $userId,
        ]);

        return response()->json([
            'message' => 'Post liké',
            'is_liked' => true,
            'like' => $newLike,
            'likes_count' => $post->likes()->count()
        ], 201);
    }
}