<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCommentRequest;
use Illuminate\Http\JsonResponse;
use App\Models\Post;

class CommentController extends Controller
{
    // Affiche tous les commentaires
    public function index()
    {
        $this->authorize('viewAny', Comment::class);

        $comments = Comment::with(['post', 'user'])->orderBy('created_at', 'asc')->get();
        return response()->json($comments);
    }

    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        // Les données sont déjà nettoyées et validées ici
        $comment = $post->comments()->create([
            'user_id' => auth()->id(),
            'content' => $request->validated()['content'],
            'parent_id' => $request->input('parent_id'),
        ]);

        return response()->json([
            'message' => 'Commentaire publié !',
            'comment' => $comment->load('user')
        ], 201);
    }

    public function update(StoreCommentRequest $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $comment->update([
            'content' => $request->validated()['content']
        ]);

        return response()->json([
            'message' => 'Commentaire modifié.',
            'comment' => $comment
        ]);
    }
    public function destroy(Comment $comment)
    {
        // Vérifie si l'utilisateur est l'auteur DU commentaire
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(['message' => 'Commentaire supprimé avec succès'], 200);
    }
}