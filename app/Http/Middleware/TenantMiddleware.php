<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Hotel;
use App\Models\Tenant;

class TenantMiddleware
{
    /**
     * Gère la détection et la configuration du tenant basé sur le sous-domaine.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // AJOUT IMPORTANT : Vérifier si l'utilisateur vient de se déconnecter
        if ($request->session()->has('just_logged_out')) {
            Log::info('TenantMiddleware - Détection de déconnexion récente, pas de réauthentification');
            $request->session()->forget('just_logged_out');
            
            // Si la requête est vers le dashboard, rediriger vers login
            if ($request->is('*/dashboard')) {
                Log::info('TenantMiddleware - Redirection vers login après déconnexion');
                return redirect('/login');
            }
            
            return $next($request);
        }
        
        // Obtenir le sous-domaine de la requête
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];
        if (!session('user_role') && Auth::check()) {
           session(['user_role' => Auth::user()->role]);
        }
        Log::info('TenantMiddleware - Host: ' . $host . ', Subdomain: ' . $subdomain);
        
        // Vérifier si nous avons un rôle dans la session (pour les utilisateurs tenant)
        if (session('user_role') && in_array(session('user_role'), ['branch_manager', 'customer'])) {
            Log::info('TenantMiddleware - Accès autorisé via session user_role: ' . session('user_role'));
            
            // Si nous avons aussi un tenant_database dans la session, configurer la connexion
            if (session('tenant_database')) {
                Log::info('TenantMiddleware - Configuration de la base de données depuis la session: ' . session('tenant_database'));
                Config::set('database.connections.tenant.database', session('tenant_database'));
                DB::purge('tenant');
                DB::reconnect('tenant');
            }
            
            return $next($request);
        }
        
        // Si nous sommes sur le domaine principal (pas de sous-domaine spécifique)
        if ($subdomain === 'localhost' || $subdomain === '127' || $subdomain === 'www') {
            Log::info('TenantMiddleware - Domaine principal détecté, utilisation de la base de données centrale');
            
            // Si nous sommes sur la page de login ou logout, ne rien faire de spécial
            if ($request->is('login') || $request->is('logout')) {
                Log::info('TenantMiddleware - Page de login/logout, pas de configuration spéciale');
                return $next($request);
            }
            
            // Utiliser la base de données centrale par défaut
            return $next($request);
        }
        
        // Le reste du code reste inchangé...
        
        // Chercher d'abord dans la table hotels
        $tenant = Hotel::where('domain', $subdomain)->first();
        Log::info('TenantMiddleware - Recherche dans hotels: ' . ($tenant ? 'Trouvé' : 'Non trouvé'));
        
        // Si non trouvé, chercher dans la table tenants
        if (!$tenant) {
            $tenant = Tenant::where('domain', $subdomain)->first();
            Log::info('TenantMiddleware - Recherche dans tenants: ' . ($tenant ? 'Trouvé' : 'Non trouvé'));
        }
        
        if (!$tenant) {
            Log::error('TenantMiddleware - Tenant non trouvé pour le domaine: ' . $subdomain);
            abort(404, 'Hôtel ou entreprise non trouvé');
        }
        
        // Stocker le tenant dans la session et la configuration
        session(['tenant_id' => $tenant->id]);
        session(['tenant_name' => $tenant->name]);
        session(['tenant_database' => $tenant->database_name ?? $tenant->database]);
        app()->instance('tenant', $tenant);
        
        // Configurer la connexion à la base de données du tenant
        $database_name = $tenant->database_name ?? $tenant->database;
        Log::info('TenantMiddleware - Configuration de la base de données: ' . $database_name);
        
        Config::set('database.connections.tenant.database', $database_name);
        DB::purge('tenant');
        DB::reconnect('tenant');
        
        // Vérifier si la base de données existe, sinon rediriger vers une page d'erreur
        try {
            DB::connection('tenant')->getPdo();
            Log::info('TenantMiddleware - Connexion à la base de données réussie');
        } catch (\Exception $e) {
            Log::error('TenantMiddleware - Erreur de connexion à la base de données: ' . $e->getMessage());
            // Utiliser une URL absolue avec le port correct
            return redirect('http://127.0.0.1:8001/tenant-setup/' . $subdomain);
        }
        
        // Si l'utilisateur est authentifié, vérifier s'il a accès à ce tenant
        if (Auth::check()) {
            $user = Auth::user();
            Log::info('TenantMiddleware - Utilisateur authentifié: ' . $user->email . ', Rôle: ' . $user->role);
            
            // Si l'utilisateur est un admin, il a accès à tous les tenants
            if ($user->role === 'admin') {
                Log::info('TenantMiddleware - Accès admin autorisé');
                return $next($request);
            }
            
            // Si l'utilisateur est un gestionnaire d'hôtel, vérifier s'il gère ce tenant
            if ($user->role === 'hotel_manager' && $user->id !== $tenant->manager_id) {
                Log::warning('TenantMiddleware - Accès refusé pour le gestionnaire d\'hôtel');
                Auth::logout();
                // Utiliser une URL absolue avec le port correct
                return redirect('http://127.0.0.1:8001/login')->with('error', 'Vous n\'avez pas accès à cet hôtel ou cette entreprise.');
            }
            
            // Pour les gestionnaires de filiale et les clients, ils sont déjà authentifiés dans la bonne base
            if ($user->role === 'branch_manager' || $user->role === 'customer') {
                Log::info('TenantMiddleware - Accès autorisé pour ' . $user->role);
                return $next($request);
            }
        }
        
        // Si nous sommes sur la page de login et que l'utilisateur n'est pas authentifié,
        // configurer le guard pour utiliser la connexion tenant
        if ($request->is('login') && !Auth::check()) {
            Log::info('TenantMiddleware - Configuration du guard tenant pour la page de login');
            Config::set('auth.guards.tenant.driver', 'session');
            Config::set('auth.guards.tenant.provider', 'tenant_users');
            Config::set('auth.providers.users.connection', 'tenant');
        }
        
        Log::info('TenantMiddleware - Traitement terminé, passage au middleware suivant');
        return $next($request);
    }
}
