<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Branch;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur pour la gestion des tenants (hôtels ou entreprises touristiques)
 */
class TenantController extends Controller
{
    /**
     * Afficher la liste des tenants.
     * Accessible uniquement par l'administrateur global.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', Tenant::class);
        
        $tenants = Tenant::all();
        
        return view('tenants.index', compact('tenants'));
    }

    /**
     * Afficher le formulaire de création d'un nouveau tenant.
     * Accessible uniquement par l'administrateur global.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', Tenant::class);
        
        return view('tenants.create');
    }

    /**
     * Enregistrer un nouveau tenant dans la base de données.
     * Accessible uniquement par l'administrateur global.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Tenant::class);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants',
            'database' => 'required|string|max:255|unique:tenants',
            'logo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('tenants.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        $data = $validator->validated();
        
        // Traitement du logo si présent
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $logoPath;
        }
        
        // Création du tenant
        $tenant = Tenant::create($data);
        
        // Initialisation de la base de données du tenant
        $tenant->createDatabase();
        
        // Journalisation de l'activité
        ActivityLog::create([
            'user_id' => auth()->id(),
            'tenant_id' => null,
            'action' => 'create',
            'entity_type' => 'tenant',
            'entity_id' => $tenant->id,
            'details' => json_encode($data),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        return redirect()->route('tenants.index')
            ->with('success', 'Tenant créé avec succès.');
    }

    /**
     * Afficher les détails d'un tenant spécifique.
     * Accessible par l'administrateur global ou l'administrateur du tenant.
     *
     * @param  \App\Models\Tenant  $tenant
     * @return \Illuminate\Http\Response
     */
    public function show(Tenant $tenant)
    {
        $this->authorize('view', $tenant);
        
        $branches = $tenant->branches;
        $users = $tenant->users;
        
        return view('tenants.show', compact('tenant', 'branches', 'users'));
    }

    /**
     * Afficher le formulaire de modification d'un tenant.
     * Accessible par l'administrateur global ou l'administrateur du tenant.
     *
     * @param  \App\Models\Tenant  $tenant
     * @return \Illuminate\Http\Response
     */
    public function edit(Tenant $tenant)
    {
        $this->authorize('update', $tenant);
        
        return view('tenants.edit', compact('tenant'));
    }

    /**
     * Mettre à jour un tenant dans la base de données.
     * Accessible par l'administrateur global ou l'administrateur du tenant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tenant  $tenant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tenant $tenant)
    {
        $this->authorize('update', $tenant);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants,domain,' . $tenant->id,
            'logo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('tenants.edit', $tenant)
                ->withErrors($validator)
                ->withInput();
        }
        
        $data = $validator->validated();
        
        // Traitement du logo si présent
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $logoPath;
        }
        
        // Mise à jour du tenant
        $tenant->update($data);
        
        // Journalisation de l'activité
        ActivityLog::create([
            'user_id' => auth()->id(),
            'tenant_id' => $tenant->id,
            'action' => 'update',
            'entity_type' => 'tenant',
            'entity_id' => $tenant->id,
            'details' => json_encode($data),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'Tenant mis à jour avec succès.');
    }

    /**
     * Supprimer un tenant de la base de données.
     * Accessible uniquement par l'administrateur global.
     *
     * @param  \App\Models\Tenant  $tenant
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tenant $tenant, Request $request)
    {
        $this->authorize('delete', $tenant);
        
        // Journalisation de l'activité avant suppression
        ActivityLog::create([
            'user_id' => auth()->id(),
            'tenant_id' => null,
            'action' => 'delete',
            'entity_type' => 'tenant',
            'entity_id' => $tenant->id,
            'details' => json_encode(['name' => $tenant->name]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        // Suppression de la base de données du tenant
        $tenant->deleteDatabase();
        
        // Suppression du tenant
        $tenant->delete();
        
        return redirect()->route('tenants.index')
            ->with('success', 'Tenant supprimé avec succès.');
    }

    public function setup($domain)
{
    // Logique pour configurer un nouveau tenant
    return view('tenant.setup', ['domain' => $domain]);
}
}
