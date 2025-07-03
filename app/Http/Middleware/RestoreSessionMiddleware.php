<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class RestoreSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Si l'utilisateur n'est pas authentifié mais qu'il y a une session
        if (!Auth::check() && !Auth::guard('tenant')->check() && $request->session()->has('user_role')) {
            $userRole = $request->session()->get('user_role');
            $userName = $request->session()->get('user_name');
            $userEmail = $request->session()->get('user_email');
            
            Log::info('Tentative de restauration de session pour rôle: ' . $userRole);
            
            // Si c'est un utilisateur tenant (branch_manager ou customer)
            if ($userRole === 'branch_manager' || $userRole === 'customer') {
                // Vérifier si nous avons les informations du tenant
                if ($request->session()->has('tenant_database')) {
                    $tenantDatabase = $request->session()->get('tenant_database');
                    
                    // Configurer la connexion tenant
                    Config::set('database.connections.tenant.database', $tenantDatabase);
                    DB::purge('tenant');
                    DB::reconnect('tenant');
                    
                    try {
                        // Tenter de récupérer l'utilisateur depuis la base de données tenant
                        $user = DB::connection('tenant')
                            ->table('users')
                            ->where('email', $userEmail)
                            ->where('role', $userRole)
                            ->first();
                        
                        if ($user) {
                            // Connecter manuellement l'utilisateur avec le guard tenant
                            Auth::guard('tenant')->loginUsingId($user->id);
                            Log::info('Session restaurée: utilisateur reconnecté avec guard tenant: ' . $user->email);
                        } else {
                            Log::warning('Échec de restauration de session: utilisateur non trouvé dans la base tenant');
                        }
                    } catch (\Exception $e) {
                        Log::error('Erreur lors de la restauration de session tenant: ' . $e->getMessage());
                    }
                } else {
                    Log::warning('Échec de restauration de session: information de base de données tenant manquante');
                }
            } else {
                // Pour les utilisateurs standard (admin, hotel_manager)
                try {
                    $user = DB::table('users')
                        ->where('email', $userEmail)
                        ->where('role', $userRole)
                        ->first();
                    
                    if ($user) {
                        // Connecter manuellement l'utilisateur avec le guard standard
                        Auth::loginUsingId($user->id);
                        Log::info('Session restaurée: utilisateur reconnecté avec guard standard: ' . $user->email);
                    } else {
                        Log::warning('Échec de restauration de session: utilisateur non trouvé dans la base standard');
                    }
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la restauration de session standard: ' . $e->getMessage());
                }
            }
        }
        
        return $next($request);
    }
}
