<?php

namespace App\Services;

use App\Models\Comment;

class CommentService
{
    public function createComment(array $data): Comment
    {
        return Comment::create([
            'user_id' => $data['user_id'],
            'post_id' => $data['post_id'],
            'content' => $data['content'],
        ]);
    }
}