<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
       {
        $user = Auth::user();
    
        // Redirection basée sur le rôle de l'utilisateur
        if ($user->role === 'admin') {
            return view('admin.dashboard');
        } elseif ($user->role === 'hotel_manager') {
            return view('hotel_manager.dashboard');
        } elseif ($user->role === 'tenant_admin') {
            return view('tenant.dashboard');
        } elseif ($user->role === 'branch_manager') {
            return view('branch_manager.dashboard');
        } elseif ($user->role === 'customer') {
            return view('customer.dashboard');
        } else {
            // Vue par défaut pour tout autre rôle non spécifié
            return view('dashboard');
        }
    }
    
    /**
     * Afficher la page de profil de l'utilisateur.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function profile()
    {
        return view('profile', ['user' => Auth::user()]);
    }
    
    /**
     * Mettre à jour le profil de l'utilisateur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:password|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        
        $user->name = $request->name;
        $user->email = $request->email;
        
        if ($request->filled('password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
            }
            
            $user->password = Hash::make($request->password);
        }
        
        $user->save();
        
        return back()->with('status', 'Profil mis à jour avec succès.');
    }
}
