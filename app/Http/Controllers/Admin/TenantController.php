<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    /**
     * Afficher la liste des tenants.
     */
    public function index()
    {
        $tenants = Tenant::latest()->paginate(10);
        return view('admin.tenants.index', compact('tenants'));
    }

    /**
     * Afficher le formulaire de création d'un tenant.
     */
    public function create()
    {
        return view('admin.tenants.create');
    }

    /**
     * Stocker un nouveau tenant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:tenant_users,email',
            'admin_password' => 'required|string|min:8',
            'logo' => 'nullable|image|max:2048',
        ]);

        // Générer un nom de base de données unique
        $database = 'tenant_' . Str::slug($validated['domain']);

        DB::beginTransaction();

        try {
            // Créer le tenant
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'domain' => $validated['domain'],
                'database' => $database,
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'address' => $validated['address'],
            ]);

            // Gérer le téléchargement du logo
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('tenant-logos', 'public');
                $tenant->logo = $logoPath;
                $tenant->save();
            }

            // Créer l'utilisateur administrateur du tenant
            TenantUser::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'email_verified_at' => now(),
            ]);

            // Créer la base de données du tenant
            // Note: Dans une implémentation réelle, cela serait géré par un package de multi-tenancy
            // comme stancl/tenancy ou hyn/multi-tenant
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$database}`");

            // Exécuter les migrations pour la base de données du tenant
            // Note: Dans une implémentation réelle, cela serait géré par un package de multi-tenancy
            // Ici, nous simulons simplement l'opération

            // Enregistrer l'action dans les logs
            $admin = Auth::guard('admin')->user();
            SystemLog::create([
                'tenant_id' => $tenant->id,
                'user_id' => $admin->id,
                'user_type' => 'admin',
                'action' => 'tenant_create',
                'description' => "Création du tenant: {$tenant->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.tenants.index')->with('success', 'Entreprise créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Une erreur est survenue lors de la création de l\'entreprise: ' . $e->getMessage()]);
        }
    }

    /**
     * Afficher les détails d'un tenant.
     */
    public function show(Tenant $tenant)
    {
        $tenant->load('users');
        return view('admin.tenants.show', compact('tenant'));
    }

    /**
     * Afficher le formulaire de modification d'un tenant.
     */
    public function edit(Tenant $tenant)
    {
        return view('admin.tenants.edit', compact('tenant'));
    }

    /**
     * Mettre à jour un tenant.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => ['required', 'string', 'max:255', Rule::unique('tenants')->ignore($tenant->id)],
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        $tenant->name = $validated['name'];
        $tenant->domain = $validated['domain'];
        $tenant->contact_email = $validated['contact_email'];
        $tenant->contact_phone = $validated['contact_phone'];
        $tenant->address = $validated['address'];

        // Gérer le téléchargement du logo
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('tenant-logos', 'public');
            $tenant->logo = $logoPath;
        }

        $tenant->save();

        // Enregistrer l'action dans les logs
        $admin = Auth::guard('admin')->user();
        SystemLog::create([
            'tenant_id' => $tenant->id,
            'user_id' => $admin->id,
            'user_type' => 'admin',
            'action' => 'tenant_update',
            'description' => "Mise à jour du tenant: {$tenant->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.tenants.show', $tenant)->with('success', 'Entreprise mise à jour avec succès.');
    }

    /**
     * Supprimer un tenant.
     */
    public function destroy(Request $request, Tenant $tenant)
    {
        $database = $tenant->database;
        $tenantName = $tenant->name;
        $tenantId = $tenant->id;

        DB::beginTransaction();

        try {
            // Supprimer le tenant et ses utilisateurs (cascade)
            $tenant->delete();

            // Supprimer la base de données du tenant
            // Note: Dans une implémentation réelle, cela serait géré par un package de multi-tenancy
            DB::statement("DROP DATABASE IF EXISTS `{$database}`");

            // Enregistrer l'action dans les logs
            $admin = Auth::guard('admin')->user();
            SystemLog::create([
                'user_id' => $admin->id,
                'user_type' => 'admin',
                'action' => 'tenant_delete',
                'description' => "Suppression du tenant: {$tenantName} (ID: {$tenantId})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.tenants.index')->with('success', 'Entreprise supprimée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue lors de la suppression de l\'entreprise: ' . $e->getMessage()]);
        }
    }
}
