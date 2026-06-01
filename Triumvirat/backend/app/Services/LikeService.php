<?php

namespace App\Services;

use App\Models\Like;

class LikeService
{
    public function toggleLike(int $userId, int $postId): bool
    {
        $existingLike = Like::where('user_id', $userId)
                            ->where('post_id', $postId)
                            ->first();

        if ($existingLike) {
            return $existingLike->delete();
        }

        Like::create([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);

        return true;
    }
}