<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SystemLog;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Afficher le tableau de bord de l'administrateur.
     */
    public function dashboard()
    {
        $totalTenants = Tenant::count();
        $recentTenants = Tenant::latest()->take(5)->get();
        $recentLogs = SystemLog::latest('created_at')->take(10)->get();

        return view('admin.dashboard', compact('totalTenants', 'recentTenants', 'recentLogs'));
    }

    /**
     * Afficher le profil de l'administrateur.
     */
    public function profile()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.profile', compact('admin'));
    }

    /**
     * Mettre à jour le profil de l'administrateur.
     */
    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $admin->password)) {
                return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
            }
        }

        $admin->name = $validated['name'];
        $admin->email = $validated['email'];

        if ($request->filled('password')) {
            $admin->password = Hash::make($validated['password']);
        }

        $admin->save();

        // Enregistrer l'action dans les logs
        SystemLog::create([
            'user_id' => $admin->id,
            'user_type' => 'admin',
            'action' => 'profile_update',
            'description' => 'Mise à jour du profil administrateur',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.profile')->with('success', 'Profil mis à jour avec succès.');
    }
}
