<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ForceRedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // IMPORTANT : Ne pas rediriger si on est déjà sur la page de login
        if ($request->is('login')) {
            return $next($request);
        }

        // Si la requête vient d'une déconnexion (vérification du Referer)
        if (strpos($request->header('Referer') ?? '', '/logout') !== false) {
            Log::info('ForceRedirectMiddleware - Détection de requête post-logout, redirection forcée');
            return redirect('/login');
        }

        // Si l'utilisateur n'est pas authentifié mais tente d'accéder au dashboard
        if (!Auth::check() && strpos($request->path(), 'dashboard') !== false) {
            Log::info('ForceRedirectMiddleware - Tentative d\'accès au dashboard sans authentification');
            return redirect('/login');
        }

        return $next($request);
    }
}
