<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class HotelManagerAuth
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
        Log::info('HotelManagerAuth middleware - Vérification authentification hotel manager');
        
        if ($request->session()->has('_previous') && 
        str_contains($request->session()->get('_previous')['url'] ?? '', '/logout')) {
        Log::info('HotelManagerAuth - Détection de déconnexion récente, redirection vers login');
        return redirect('/login');
    }
        // Vérifier si l'utilisateur est connecté avec le guard standard Laravel
        if (Auth::check()) {
            $user = Auth::user();
            Log::info('HotelManagerAuth - Utilisateur Laravel trouvé', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role ?? 'N/A'
            ]);
            
            // Si c'est un utilisateur avec le rôle hotel_manager, autoriser l'accès
            if (isset($user->role) && $user->role === 'hotel_manager') {
                Log::info('HotelManagerAuth - Accès autorisé pour hotel_manager Laravel');
                return $next($request);
            }
        }
        
        // Vérifier si c'est une session hotel manager
        if (Session::has('hotel_manager_authenticated') && Session::has('hotel_manager_data')) {
            $managerData = Session::get('hotel_manager_data');
            Log::info('HotelManagerAuth - Session hotel manager trouvée', [
                'manager_id' => $managerData['id'] ?? 'N/A',
                'email' => $managerData['email'] ?? 'N/A',
                'role' => $managerData['role'] ?? 'N/A'
            ]);
            
            // Vérifier que les données de session sont valides
            if (isset($managerData['id']) && isset($managerData['email']) && 
                isset($managerData['role']) && $managerData['role'] === 'hotel_manager') {
                Log::info('HotelManagerAuth - Accès autorisé pour hotel manager');
                return $next($request);
            }
        }
        
        // Vérifier si c'est une session hotel manager simple
        if (Session::has('hotel_manager_id') && Session::has('hotel_manager_email')) {
            Log::info('HotelManagerAuth - Session hotel manager simple trouvée', [
                'manager_id' => Session::get('hotel_manager_id'),
                'email' => Session::get('hotel_manager_email')
            ]);
            
            Log::info('HotelManagerAuth - Accès autorisé pour hotel manager simple');
            return $next($request);
        }
        
        Log::warning('HotelManagerAuth - Aucune authentification hotel manager valide trouvée');
        Log::info('HotelManagerAuth - Sessions disponibles', [
            'all_sessions' => Session::all(),
            'auth_check' => Auth::check(),
            'auth_user' => Auth::user() ? Auth::user()->toArray() : null
        ]);
        
        // Rediriger vers la page de login avec un message
        return redirect('/login')->with('error', 'Vous devez être connecté en tant que gestionnaire d\'hôtel pour accéder à cette page.');
    }
}

