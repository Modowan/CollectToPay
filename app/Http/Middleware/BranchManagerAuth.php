<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BranchManagerAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si le branch manager est authentifié via notre système personnalisé
        $branchManagerAuth = $request->session()->get('branch_manager_auth');
        $isAuthenticated = $request->session()->get('auth.branch_manager_authenticated', false);
        
        Log::info('BranchManagerAuth Middleware - Vérification authentification branch manager', [
            'has_branch_manager_auth' => !is_null($branchManagerAuth),
            'is_authenticated' => $isAuthenticated,
            'manager_email' => $branchManagerAuth['email'] ?? 'N/A',
            'route' => $request->route()->getName()
        ]);
        
        // Si le branch manager n'est pas authentifié, rediriger vers login
        if (!$branchManagerAuth || !$isAuthenticated) {
            Log::warning('BranchManagerAuth Middleware - Branch manager non authentifié, redirection vers login');
            return redirect()->route('login')->with('error', 'Veuillez vous connecter en tant que branch manager pour accéder à cette page.');
        }
        
        // Vérifier que les données branch manager sont complètes
        if (!isset($branchManagerAuth['email']) || !isset($branchManagerAuth['tenant_id'])) {
            Log::warning('BranchManagerAuth Middleware - Données branch manager incomplètes', [
                'branch_manager_auth' => $branchManagerAuth
            ]);
            
            // Nettoyer la session et rediriger
            $request->session()->forget(['branch_manager_auth', 'auth.branch_manager_authenticated']);
            return redirect()->route('login')->with('error', 'Session branch manager invalide. Veuillez vous reconnecter.');
        }
        
        // Optionnel : Vérifier l'expiration de la session (ex: 2 heures)
        $passwordConfirmedAt = $request->session()->get('auth.password_confirmed_at');
        if ($passwordConfirmedAt && (time() - $passwordConfirmedAt) > 7200) { // 2 heures
            Log::info('BranchManagerAuth Middleware - Session expirée pour le branch manager: ' . $branchManagerAuth['email']);
            
            $request->session()->forget(['branch_manager_auth', 'auth.branch_manager_authenticated', 'auth.password_confirmed_at']);
            return redirect()->route('login')->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
        }
        
        // Ajouter les informations branch manager à la requête pour utilisation dans les contrôleurs
        $request->attributes->set('branch_manager', $branchManagerAuth);
        
        Log::info('BranchManagerAuth Middleware - Accès autorisé pour le branch manager: ' . $branchManagerAuth['email']);
        
        return $next($request);
    }
}

