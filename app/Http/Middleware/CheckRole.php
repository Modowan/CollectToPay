<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Gère une requête entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Vérifier si l'utilisateur est authentifié
        if (!Auth::check()) {
            return redirect('login');
        }

        // Récupérer l'utilisateur authentifié
        $user = Auth::user();

        // Si aucun rôle n'est spécifié, continuer
        if (empty($roles)) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a l'un des rôles spécifiés
        foreach ($roles as $role) {
            // Administrateur global a accès à tout
            if ($user->role === 'admin') {
                return $next($request);
            }

            // Vérifier le rôle spécifique
            if ($user->role === $role) {
                // Pour les administrateurs de tenant, vérifier l'accès au tenant
                if ($role === 'tenant_admin' && $request->route('tenant')) {
                    $tenant = $request->route('tenant');
                    
                    // Vérifier si l'utilisateur est administrateur de ce tenant
                    if ($user->tenant_id == $tenant->id) {
                        return $next($request);
                    }
                } 
                // Pour les administrateurs de branche, vérifier l'accès à la branche
                else if ($role === 'branch_admin' && $request->route('branch')) {
                    $branch = $request->route('branch');
                    
                    // Vérifier si l'utilisateur est administrateur de cette branche
                    if ($user->branch_id == $branch->id) {
                        return $next($request);
                    }
                }
                // Pour les autres rôles sans vérification supplémentaire
                else {
                    return $next($request);
                }
            }
        }

        // Si l'utilisateur n'a aucun des rôles requis, refuser l'accès
        return response()->json(['message' => 'Accès non autorisé.'], 403);
    }
}