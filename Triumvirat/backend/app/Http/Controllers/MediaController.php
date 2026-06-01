<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index()
    {
        $media = Media::with('post')->get();
        return response()->json($media);
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'url' => 'required|string',
            'type' => 'required|string',
        ]);

        $media = Media::create([
            'post_id' => $request->post_id,
            'url' => $request->url,
            'type' => $request->type,
        ]);

        return response()->json($media, 201);
    }

    public function destroy(Media $media)
    {
        $media = Media::find($media->id);
        if (!$media) {
            return response()->json(['message' => 'Media introuvable'], 404);
        }

        $media->delete();

        return response()->json(data: ["message" => "Media supprimé"], status: 200);
    }

    public function update(Request $request, Media $media)
    {
        $media = Media::find($media->id);
        if (!$media) {
            return response()->json(['message' => 'Media introuvable'], 404);
        }

        $media->update([
            'url' => $request->url ?? $media->url,
            'type' => $request->type ?? $media->type,
        ]);

        return response()->json(data: ["message" => "Media mis à jour"], status: 200);
    }
}