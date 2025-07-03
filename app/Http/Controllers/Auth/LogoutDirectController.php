<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class LogoutDirectController extends Controller
{
    public function logout(Request $request)
    {
        // 1. Journaliser la déconnexion
        Log::info('Déconnexion directe initiée', [
            'user_id' => Auth::id(),
            'email' => optional(Auth::user())->email,
            'session_id' => $request->session()->getId()
        ]);
        
        // 2. Obtenir l'ID de session actuel
        $sessionId = $request->session()->getId();
        
        // 3. Déconnexion Laravel standard
        Auth::logout();
        
        // 4. Supprimer directement la session de la base de données ou du stockage
        if (config('session.driver') === 'file') {
            // Pour le driver de session 'file'
            $sessionPath = storage_path('framework/sessions/' . $sessionId);
            if (file_exists($sessionPath)) {
                unlink($sessionPath);
                Log::info('Session file supprimée: ' . $sessionPath);
            }
        } elseif (config('session.driver') === 'database') {
            // Pour le driver de session 'database'
            DB::table('sessions')->where('id', $sessionId)->delete();
            Log::info('Session database supprimée pour ID: ' . $sessionId);
        }
        
        // 5. Régénérer un nouvel ID de session
        $request->session()->regenerate(true);
        
        // 6. Rediriger vers la page de login avec un paramètre anti-cache
        Log::info('Redirection vers login après déconnexion directe');
        return redirect('/login?t=' . time());
    }
}
