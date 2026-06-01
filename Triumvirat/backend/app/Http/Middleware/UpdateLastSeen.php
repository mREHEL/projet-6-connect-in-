<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateLastSeen
{
    // Gere une requete a venir
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            // Log pour vérifier dans storage/logs/laravel.log
            \Log::info('Middleware UpdateLastSeen activé pour : ' . Auth::user()->username);

            Auth::user()->updateQuietly(['last_seen_at' => now()]);
        }

        return $next($request);
    }
}