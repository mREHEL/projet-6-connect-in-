<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    // Affiche la liste des utilisateurs
    public function index()
    {
        $users = User::paginate(20);

        $users->getCollection()->transform(function ($user) {
            $user->makeHidden(['email', 'password']);
            $user->profile_photo_url = $user->profile_photo_path
                ? asset('storage/' . $user->profile_photo_path)
                : null;
            return $user;
        });

        return response()->json($users);
    }


    // Affiche un utilisateur spécifique
    public function show(User $user)
    {
        // Masquer les données sensibles si pas le propriétaire
        if (Auth::id() !== $user->id) {
            $user->makeHidden(['email', 'password']);
        }

        $user->profile_photo_url = $user->profile_photo_path
            ? asset('storage/' . $user->profile_photo_path)
            : null;

        return response()->json($user);
    }

    // Création d'un nouvel utilisateur (Inscription)
    public function store(StoreUserRequest $request)
    {
        // Les données sont déjà validées par StoreUserRequest 
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => $user
        ], 201);
    }

    // Mise à jour d'un utilisateur.
    public function update(UpdateUserRequest $request, User $user)
    {
        // méthode update
        $this->authorize('update', $user);

        $updateData = $request->only(['username', 'email', 'first_name', 'last_name', 'bio']);

        // Gestion du mot de passe 
        if ($request->filled('password')) {
            //  Vérifier que current_password est fourni
            if (!$request->filled('current_password')) {
                throw ValidationException::withMessages([
                    'current_password' => ['Le mot de passe actuel est requis pour changer de mot de passe.']
                ]);
            }

            // Vérifier que current_password est correct
            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Le mot de passe actuel est incorrect.']
                ]);
            }

            // 3 Vérifier que le nouveau mot de passe est différent de l'ancien
            if (Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'password' => ['Le nouveau mot de passe doit être différent de l\'ancien.']
                ]);
            }

            //hasher le nouveau mot de passe
            $updateData['password'] = Hash::make($request->password);
        }

        // Gestion de la photo de profil
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $updateData['profile_photo_path'] = $request->file('profile_photo')->store('profiles', 'public');
        } elseif ($request->input('delete_profile_photo') === '1') {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $updateData['profile_photo_path'] = null;
        }

        // Gestion de la bannière 
        if ($request->hasFile('cover_image')) {
            if ($user->cover_image_path) {
                Storage::disk('public')->delete($user->cover_image_path);
            }
            $updateData['cover_image_path'] = $request->file('cover_image')->store('covers', 'public');
        } elseif ($request->input('delete_cover_image') === '1') {
            if ($user->cover_image_path) {
                Storage::disk('public')->delete($user->cover_image_path);
            }
            $updateData['cover_image_path'] = null;
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => $user
        ]);
    }

    public function updatePassword(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Mot de passe mis à jour avec succès']);
    }

    // Suppression compte
    public function destroy(Request $request)
    {
        $user = $request->user();

        if (Auth::id() !== $user->id) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $user->tokens()->delete();


        if ($request->boolean('hard_delete')) {

            if ($user->profile_photo_path)
                Storage::disk('public')->delete($user->profile_photo_path);
            if ($user->cover_image_path)
                Storage::disk('public')->delete($user->cover_image_path);

            // On boucle sur chaque post pour déclencher la suppression de ses médias/commentaires
            $posts = $user->posts()->get();
            foreach ($posts as $post) {
                // Supprime les images de storage
                foreach ($post->media as $media) {
                    Storage::disk('public')->delete($media->url);
                    $media->delete();
                }
                // Supprime les commentaires et likes du post
                $post->comments()->delete();
                $post->likes()->delete();
                $post->delete();
            }

            // Supprime les commentaires et likes que l'utilisateur a laissé sur les autres posts
            $user->comments()->delete();
            $user->likes()->delete();

            // Supprime l'utilisateur de la base
            $user->forceDelete();



            $message = 'Votre compte, ainsi que tous vos posts et commentaires, ont été définitivement supprimés.';
        } else {
            $user->delete();

            $message = 'Votre compte a été désactivé. Vos publications ont été conservées mais anonymisées.';
        }

        return response()->json(['message' => $message]);
    }
}
