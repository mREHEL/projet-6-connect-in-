<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest; // Réutilisation
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();


        if (!$user || !Hash::check($fields['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        if (!filled($fields['password'])) {
            throw ValidationException::withMessages([
                'password' => ['Le mot de passe est requis.'],
            ]);
        }

        if ($user->trashed()) {
            throw ValidationException::withMessages([
                'email' => ['Ce compte a été supprimé.'],
            ]);
        }


        $latestToken = $user->tokens()->latest()->first();

        if ($latestToken) {
            // On calcule si le jeton est expiré selon la config 'sanctum.expiration'
            $expiration = config('sanctum.expiration');

            // Si expiration est null, les tokens n'expirent jamais
            $isExpired = $expiration && $latestToken->created_at->addMinutes($expiration)->isPast();

            if (!$isExpired) {
                throw ValidationException::withMessages([
                    'email' => ['Ce compte possède déjà une session active.'],
                ]);
            }
        }

        $user->update(['last_seen_at' => now()]);

        // Gestion de l'expiration du token via config
        $expiration = config('sanctum.expiration') ? now()->addMinutes(config('sanctum.expiration')) : null;

        $token = $user->createToken('auth_token', ['*'], $expiration)->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function register(StoreUserRequest $request) // Utilisation de la Request personnalisée
    {
        $user = User::create([
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'last_seen_at' => now(),
        ]);



        $token = $user->createToken('auth_token')->plainTextToken;


        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function logout(Request $request)
    {
        $user = $request->user();



        if ($user) {
            // Suppression du token actuel uniquement
            $user->currentAccessToken()->delete();

            // Si on veux déconnecter l'utilisateur de TOUS ses appareils :
            // $user->tokens()->delete();
        }

        return response()->json(['message' => 'Déconnecté avec succès']);
    }


    public function checkPassword(Request $request)
    {
        $request->validate(['password' => 'required|string']);


        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['valid' => false], 422);
        }

        // Mise à jour l'activité
        $user->update(['last_seen_at' => now()]);

        return response()->json(['valid' => true]);
    }
}