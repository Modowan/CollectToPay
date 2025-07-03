<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\Branch;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Afficher la liste des filiales.
     */
    public function index()
    {
        $branches = Branch::withCount('customers')->latest()->paginate(10);
        return view('tenant.branches.index', compact('branches'));
    }

    /**
     * Afficher le formulaire de création d'une filiale.
     */
    public function create()
    {
        return view('tenant.branches.create');
    }

    /**
     * Stocker une nouvelle filiale.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        // Créer la filiale
        $branch = Branch::create([
            'name' => $validated['name'],
            'city' => $validated['city'],
            'address' => $validated['address'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'],
        ]);

        // Créer l'utilisateur administrateur de la filiale
        User::create([
            'branch_id' => $branch->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'role' => 'branch_admin',
            'email_verified_at' => now(),
        ]);

        // Enregistrer l'action dans les logs
        $user = Auth::user();
        ActivityLog::create([
            'user_id' => $user->id,
            'user_type' => 'user',
            'action' => 'branch_create',
            'description' => "Création de la filiale: {$branch->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('tenant.branches.index')->with('success', 'Filiale créée avec succès.');
    }

    /**
     * Afficher les détails d'une filiale.
     */
    public function show(Branch $branch)
    {
        $branch->load(['users', 'customers' => function ($query) {
            $query->latest()->take(10);
        }]);
        
        $customerCount = $branch->customers()->count();
        $tokenizedCount = $branch->customers()->where('token_status', 'tokenized')->count();
        
        return view('tenant.branches.show', compact('branch', 'customerCount', 'tokenizedCount'));
    }

    /**
     * Afficher le formulaire de modification d'une filiale.
     */
    public function edit(Branch $branch)
    {
        return view('tenant.branches.edit', compact('branch'));
    }

    /**
     * Mettre à jour une filiale.
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            // here is trunched
            'contact_phone' => 'nullable|string|max:50',
        ]);

        $branch->update([
            'name' => $validated['name'],
            'city' => $validated['city'],
            'address' => $validated['address'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'],
        ]);

        // Enregistrer l'action dans les logs
        $user = Auth::user();
        ActivityLog::create([
            'user_id' => $user->id,
            'user_type' => 'user',
            'action' => 'branch_update',
            'description' => "Mise à jour de la filiale: {$branch->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('tenant.branches.index')->with('success', 'Filiale mise à jour avec succès.');
    }

    /**
     * Supprimer une filiale.
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();

        // Enregistrer l'action dans les logs
        $user = Auth::user();
        ActivityLog::create([
            'user_id' => $user->id,
            'user_type' => 'user',
            'action' => 'branch_delete',
            'description' => "Suppression de la filiale: {$branch->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('tenant.branches.index')->with('success', 'Filiale supprimée avec succès.');
    }
}