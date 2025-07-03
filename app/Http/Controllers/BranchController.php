<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur pour la gestion des branches (succursales) d'un tenant
 */
class BranchController extends Controller
{
    /**
     * Afficher la liste des branches d'un tenant spécifique.
     * Accessible par l'administrateur global, l'administrateur du tenant.
     *
     * @param  \App\Models\Tenant  $tenant
     * @return \Illuminate\Http\Response
     */
    public function index(Tenant $tenant)
    {
        $this->authorize('viewAny', [Branch::class, $tenant]);
        
        $branches = $tenant->branches;
        
        return view('branches.index', compact('tenant', 'branches'));
    }

    /**
     * Afficher le formulaire de création d'une nouvelle branche.
     * Accessible par l'administrateur global, l'administrateur du tenant.
     *
     * @param  \App\Models\Tenant  $tenant
     * @return \Illuminate\Http\Response
     */
    public function create(Tenant $tenant)
    {
        $this->authorize('create', [Branch::class, $tenant]);
        
        return view('branches.create', compact('tenant'));
    }

    /**
     * Enregistrer une nouvelle branche dans la base de données.
     * Accessible par l'administrateur global, l'administrateur du tenant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tenant  $tenant
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Tenant $tenant)
    {
        $this->authorize('create', [Branch::class, $tenant]);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('tenants.branches.create', $tenant)
                ->withErrors($validator)
                ->withInput();
        }
        
        $data = $validator->validated();
        $data['tenant_id'] = $tenant->id;
        
        // Création de la branche
        $branch = Branch::create($data);
        
        // Journalisation de l'activité
        ActivityLog::create([
            'user_id' => auth()->id(),
            'tenant_id' => $tenant->id,
            'action' => 'create',
            'entity_type' => 'branch',
            'entity_id' => $branch->id,
            'details' => json_encode($data),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        return redirect()->route('tenants.branches.index', $tenant)
            ->with('success', 'Branche créée avec succès.');
    }

    /**
     * Afficher les détails d'une branche spécifique.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
     *
     * @param  \App\Models\Tenant  $tenant
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function show(Tenant $tenant, Branch $branch)
    {
        $this->authorize('view', $branch);
        
        // Vérifier que la branche appartient bien au tenant
        if ($branch->tenant_id !== $tenant->id) {
            abort(404);
        }
        
        // Récupérer les clients de la branche depuis la base de données du tenant
        $customers = $tenant->run(function ($tenant) use ($branch) {
            return \App\Models\Tenant\Customer::where('branch_id', $branch->id)->get();
        });
        
        return view('branches.show', compact('tenant', 'branch', 'customers'));
    }

    /**
     * Afficher le formulaire de modification d'une branche.
     * Accessible par l'administrateur global, l'administrateur du tenant.
     *
     * @param  \App\Models\Tenant  $tenant
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function edit(Tenant $tenant, Branch $branch)
    {
        $this->authorize('update', $branch);
        
        // Vérifier que la branche appartient bien au tenant
        if ($branch->tenant_id !== $tenant->id) {
            abort(404);
        }
        
        return view('branches.edit', compact('tenant', 'branch'));
    }

    /**
     * Mettre à jour une branche dans la base de données.
     * Accessible par l'administrateur global, l'administrateur du tenant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tenant  $tenant
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tenant $tenant, Branch $branch)
    {
        $this->authorize('update', $branch);
        
        // Vérifier que la branche appartient bien au tenant
        if ($branch->tenant_id !== $tenant->id) {
            abort(404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('tenants.branches.edit', [$tenant, $branch])
                ->withErrors($validator)
                ->withInput();
        }
        
        $data = $validator->validated();
        
        // Mise à jour de la branche
        $branch->update($data);
        
        // Journalisation de l'activité
        ActivityLog::create([
            'user_id' => auth()->id(),
            'tenant_id' => $tenant->id,
            'action' => 'update',
            'entity_type' => 'branch',
            'entity_id' => $branch->id,
            'details' => json_encode($data),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        return redirect()->route('tenants.branches.show', [$tenant, $branch])
            ->with('success', 'Branche mise à jour avec succès.');
    }

    /**
     * Supprimer une branche de la base de données.
     * Accessible par l'administrateur global, l'administrateur du tenant.
     *
     * @param  \App\Models\Tenant  $tenant
     * @param  \App\Models\Branch  $branch
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tenant $tenant, Branch $branch, Request $request)
    {
        $this->authorize('delete', $branch);
        
        // Vérifier que la branche appartient bien au tenant
        if ($branch->tenant_id !== $tenant->id) {
            abort(404);
        }
        
        // Journalisation de l'activité avant suppression
        ActivityLog::create([
            'user_id' => auth()->id(),
            'tenant_id' => $tenant->id,
            'action' => 'delete',
            'entity_type' => 'branch',
            'entity_id' => $branch->id,
            'details' => json_encode(['name' => $branch->name]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        // Suppression des clients associés dans la base de données du tenant
        $tenant->run(function ($tenant) use ($branch) {
            \App\Models\Tenant\Customer::where('branch_id', $branch->id)->delete();
        });
        
        // Suppression de la branche
        $branch->delete();
        
        return redirect()->route('tenants.branches.index', $tenant)
            ->with('success', 'Branche supprimée avec succès.');
    }
}
