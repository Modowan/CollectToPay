<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CustomerAuth
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
        Log::info('=== DÉBUT CustomerAuth middleware ===');
        Log::info('CustomerAuth - URL demandée: ' . $request->url());
        
        // 1. Vérifier si l'utilisateur est connecté avec le guard standard Laravel
        if (Auth::check()) {
            $user = Auth::user();
            Log::info('CustomerAuth - Utilisateur Laravel trouvé', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role ?? 'N/A'
            ]);
            
            // Si c'est un utilisateur avec le rôle customer, autoriser l'accès
            if (isset($user->role) && $user->role === 'customer') {
                Log::info('CustomerAuth - ✅ ACCÈS AUTORISÉ pour customer Laravel');
                return $next($request);
            }
        }
        
        // 2. NOUVEAU : Vérifier le format de session utilisé par votre LoginController
        if (Session::has('customer_auth')) {
            $customerAuth = Session::get('customer_auth');
            Log::info('CustomerAuth - Session customer_auth trouvée', $customerAuth);
            
            // Vérifier que les données de session sont valides
            if (isset($customerAuth['id']) && isset($customerAuth['email'])) {
                Log::info('CustomerAuth - ✅ ACCÈS AUTORISÉ pour client (format customer_auth)', [
                    'customer_id' => $customerAuth['id'],
                    'email' => $customerAuth['email'],
                    'tenant' => $customerAuth['tenant_name'] ?? 'N/A'
                ]);
                return $next($request);
            }
        }
        
        // 3. Vérifier si c'est une session client tenant (format complet)
        if (Session::has('customer_authenticated') && Session::has('customer_data')) {
            $customerData = Session::get('customer_data');
            Log::info('CustomerAuth - Session client tenant trouvée (format complet)', [
                'customer_authenticated' => Session::get('customer_authenticated'),
                'customer_data' => $customerData
            ]);
            
            // Vérifier que les données de session sont valides
            if (isset($customerData['id']) && isset($customerData['email'])) {
                Log::info('CustomerAuth - ✅ ACCÈS AUTORISÉ pour client tenant (format complet)');
                return $next($request);
            }
        }
        
        // 4. Vérifier si c'est une session client simple (format simple)
        if (Session::has('customer_id') && Session::has('customer_email')) {
            Log::info('CustomerAuth - Session client simple trouvée (format simple)', [
                'customer_id' => Session::get('customer_id'),
                'customer_email' => Session::get('customer_email'),
                'customer_tenant_name' => Session::get('customer_tenant_name', 'N/A'),
                'customer_tenant_id' => Session::get('customer_tenant_id', 'N/A')
            ]);
            
            Log::info('CustomerAuth - ✅ ACCÈS AUTORISÉ pour client simple (format simple)');
            return $next($request);
        }
        
        // ÉCHEC: Aucune authentification client valide trouvée
        Log::warning('CustomerAuth - ❌ AUCUNE authentification client valide trouvée');
        Log::info('CustomerAuth - Sessions disponibles:', [
            'customer_auth' => Session::get('customer_auth', 'N/A'),
            'customer_authenticated' => Session::get('customer_authenticated', 'N/A'),
            'customer_data' => Session::get('customer_data', 'N/A'),
            'customer_id' => Session::get('customer_id', 'N/A'),
            'customer_email' => Session::get('customer_email', 'N/A')
        ]);
        
        Log::info('CustomerAuth - Redirection vers /login');
        Log::info('=== FIN CustomerAuth middleware ===');
        
        // Rediriger vers la page de login avec un message
        return redirect('/login')->with('error', 'Vous devez être connecté en tant que client pour accéder à cette page.');
    }
}

