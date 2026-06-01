<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StorePostRequest;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with([
            'user' => function ($query) {
                $query->select('id', 'username', 'first_name', 'last_name', 'profile_photo_path', 'last_seen_at', 'deleted_at')->withTrashed();
            },
            'media',
            'likes'
        ])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->get();

        $data = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'user' => $post->user,
                'media' => $post->media,
                'likes_count' => $post->likes_count,
                'comments_count' => $post->comments_count,
                'is_liked' => $post->likes->contains('user_id', auth()->id()),
                'can_update' => auth()->check() && auth()->user()->can('update', $post),
                'can_delete' => auth()->check() && auth()->user()->can('delete', $post),
                'created_at' => $post->created_at,
                'formatted_date' => $post->created_at->diffForHumans(),
            ];
        });

        return response()->json($data);
    }

    public function show(Post $post)
    {
        $this->authorize('view', $post);

        // charger l'auteur même supprimé
        return response()->json($post->load(['user' => fn($q) => $q->withTrashed(), 'comments.user' => fn($q) => $q->withTrashed(), 'media', 'likes']));
    }

    public function getUserPosts($userId)
    {
        $this->authorize('viewAny', Post::class);

        $posts = Post::with([
            'user' => function ($query) {
                // ajout de deleted_at et withTrashed()
                $query->select('id', 'username', 'first_name', 'last_name', 'profile_photo_path', 'last_seen_at', 'deleted_at')->withTrashed();
            },
            'media',
            'likes'
        ])
            ->withCount(['likes', 'comments'])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'user' => $post->user,
                    'media' => $post->media,
                    'likes_count' => $post->likes_count,
                    'comments_count' => $post->comments_count,
                    'is_liked' => $post->likes->contains('user_id', auth()->id()),
                    'can_update' => auth()->check() && auth()->user()->can('update', $post),
                    'can_delete' => auth()->check() && auth()->user()->can('delete', $post),
                    'created_at' => $post->created_at,
                    'formatted_date' => $post->created_at->diffForHumans(),
                ];
            });

        return response()->json($posts);
    }

    public function store(StorePostRequest $request) // Utilise la nouvelle Request
    {
        $this->authorize('create', Post::class);

        // Utilise uniquement les données validées
        $validated = $request->validated();

        $post = Post::create([
            'content' => $validated['content'],
            'user_id' => auth()->id()
        ]);


        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
            $post->media()->create([
                'url' => $path,
                'type' => 'image'
            ]);
        }

        return response()->json($post->load(['media', 'user']), 201);
    }

    public function update(StorePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        // On applique le même nettoyage que pour le store
        $post->update([
            'content' => $request->validated()['content']
        ]);

        return response()->json([
            "message" => "Post mis à jour",
            "post" => $post->load('media', 'user')
        ], 200);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        foreach ($post->media as $media) {
            Storage::disk('public')->delete($media->url);
        }

        $post->delete();

        return response()->json(['message' => 'Post supprimé avec succès'], 200);
    }
}