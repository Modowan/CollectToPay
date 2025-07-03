<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimpleAuthController extends Controller
{
    public function showLoginForm( )
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Redirection simple sans aucune logique complexe
            return redirect()->to('http://127.0.0.1:8001/success' );
        }
        
        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ]);
    }
    
    public function success()
    {
        return "Connexion réussie! Utilisateur: " . Auth::user()->name;
    }
    
    public function logout()
    {
        Auth::logout();
        return redirect()->to('http://127.0.0.1:8001/login-simple' );
    }
}
