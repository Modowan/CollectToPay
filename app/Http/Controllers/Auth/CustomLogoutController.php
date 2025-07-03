<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class CustomLogoutController extends Controller
{
    /**
     * Déconnecte l'utilisateur et supprime TOUTES les variables de session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Journaliser la déconnexion avec toutes les clés de session
        Log::info('Déconnexion initiée', [
            'user_id' => Auth::id(),
            'email' => optional(Auth::user())->email,
            'session_keys' => array_keys(Session::all())
        ]);
        
        // Stocker temporairement l'information de déconnexion
        $request->session()->put('logout_requested', true);
        
        // Déconnexion Laravel standard
        Auth::logout();
        
        // Supprimer TOUTES les variables de session personnalisées
        // Variables utilisées par HotelManagerAuth
        Session::forget([
            'hotel_manager_authenticated',
            'hotel_manager_data',
            'hotel_manager_id',
            'hotel_manager_email'
        ]);
        
        // Variables utilisées par TenantMiddleware
        Session::forget([
            'tenant_id',
            'tenant_name',
            'tenant_database',
            'user_role'
        ]);
        
        // Vider complètement la session
        Session::flush();
        
        // Invalider la session et régénérer le token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Marquer la session comme déconnectée pour éviter la réauthentification par TenantMiddleware
        $request->session()->put('just_logged_out', true);
        
        // Journaliser la fin de déconnexion
        Log::info('Déconnexion terminée, redirection vers login');
        
        // Rediriger vers la page de connexion avec des en-têtes anti-cache
        return redirect('/login?logout=' . time())
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
    }
}
