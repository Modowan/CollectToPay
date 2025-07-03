<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        if (auth()->attempt($credentials)) {
            $user = auth()->user();
            
            // Redirection simple sans aucune logique complexe
            return redirect('/test-success');
        }
        
        return back()->withErrors(['email' => 'Ces identifiants ne correspondent pas à nos enregistrements.']);
    }
    
    public function success()
    {
        return "Connexion réussie!";
    }
}
