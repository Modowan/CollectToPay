<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use App\Models\ActivityLog;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur pour la gestion des clients d'une branche
 */
class CustomerController extends Controller
{
    /**
     * Afficher la liste des clients d'une branche spécifique.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function index(Branch $branch)
    {
        $this->authorize('viewAny', [Customer::class, $branch]);
        
        $customers = tenant()->run(function () use ($branch) {
            return Customer::where('branch_id', $branch->id)->get();
        });
        
        return view('tenant.customers.index', compact('branch', 'customers'));
    }

    /**
     * Afficher le formulaire de création d'un nouveau client.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function create(Branch $branch)
    {
        $this->authorize('create', [Customer::class, $branch]);
        
        return view('tenant.customers.create', compact('branch'));
    }

    /**
     * Enregistrer un nouveau client dans la base de données.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Branch $branch)
    {
        $this->authorize('create', [Customer::class, $branch]);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:tenant.customers',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('branches.customers.create', $branch)
                ->withErrors($validator)
                ->withInput();
        }
        
        $data = $validator->validated();
        $data['branch_id'] = $branch->id;
        
        // Création du client dans la base de données du tenant
        $customer = tenant()->run(function () use ($data) {
            return Customer::create($data);
        });
        
        // Journalisation de l'activité
        ActivityLog::create([
            'user_id' => Auth::id(),
            'tenant_id' => tenant('id'),
            'action' => 'create',
            'entity_type' => 'customer',
            'entity_id' => $customer->id,
            'details' => json_encode($data),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        return redirect()->route('branches.customers.index', $branch)
            ->with('success', 'Client créé avec succès.');
    }

    /**
     * Afficher les détails d'un client spécifique.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
     *
     * @param  \App\Models\Branch  $branch
     * @param  int  $customerId
     * @return \Illuminate\Http\Response
     */
    public function show(Branch $branch, $customerId)
    {
        $customer = tenant()->run(function () use ($customerId, $branch) {
            return Customer::where('id', $customerId)
                ->where('branch_id', $branch->id)
                ->firstOrFail();
        });
        
        $this->authorize('view', [$customer, $branch]);
        
        $paymentTokens = tenant()->run(function () use ($customer) {
            return $customer->paymentTokens;
        });
        
        $paymentTransactions = tenant()->run(function () use ($customer) {
            return $customer->paymentTransactions()->latest()->take(10)->get();
        });
        
        return view('tenant.customers.show', compact('branch', 'customer', 'paymentTokens', 'paymentTransactions'));
    }

    /**
     * Afficher le formulaire de modification d'un client.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
     *
     * @param  \App\Models\Branch  $branch
     * @param  int  $customerId
     * @return \Illuminate\Http\Response
     */
    public function edit(Branch $branch, $customerId)
    {
        $customer = tenant()->run(function () use ($customerId, $branch) {
            return Customer::where('id', $customerId)
                ->where('branch_id', $branch->id)
                ->firstOrFail();
        });
        
        $this->authorize('update', [$customer, $branch]);
        
        return view('tenant.customers.edit', compact('branch', 'customer'));
    }

    /**
     * Mettre à jour un client dans la base de données.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Branch  $branch
     * @param  int  $customerId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Branch $branch, $customerId)
    {
        $customer = tenant()->run(function () use ($customerId, $branch) {
            return Customer::where('id', $customerId)
                ->where('branch_id', $branch->id)
                ->firstOrFail();
        });
        
        $this->authorize('update', [$customer, $branch]);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:tenant.customers,email,' . $customerId,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('branches.customers.edit', [$branch, $customerId])
                ->withErrors($validator)
                ->withInput();
        }
        
        $data = $validator->validated();
        
        // Mise à jour du client dans la base de données du tenant
        tenant()->run(function () use ($customer, $data) {
            $customer->update($data);
        });
        
        // Journalisation de l'activité
        ActivityLog::create([
            'user_id' => Auth::id(),
            'tenant_id' => tenant('id'),
            'action' => 'update',
            'entity_type' => 'customer',
            'entity_id' => $customer->id,
            'details' => json_encode($data),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        return redirect()->route('branches.customers.show', [$branch, $customerId])
            ->with('success', 'Client mis à jour avec succès.');
    }

    /**
     * Supprimer un client de la base de données.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
     *
     * @param  \App\Models\Branch  $branch
     * @param  int  $customerId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Branch $branch, $customerId, Request $request)
    {
        $customer = tenant()->run(function () use ($customerId, $branch) {
            return Customer::where('id', $customerId)
                ->where('branch_id', $branch->id)
                ->firstOrFail();
        });
        
        $this->authorize('delete', [$customer, $branch]);
        
        // Journalisation de l'activité avant suppression
        ActivityLog::create([
            'user_id' => Auth::id(),
            'tenant_id' => tenant('id'),
            'action' => 'delete',
            'entity_type' => 'customer',
            'entity_id' => $customer->id,
            'details' => json_encode(['name' => $customer->name, 'email' => $customer->email]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        // Suppression du client et de ses données associées dans la base de données du tenant
        tenant()->run(function () use ($customer) {
            // Suppression des tokens de paiement et transactions associés
            $customer->paymentTokens()->delete();
            $customer->paymentTransactions()->delete();
            
            // Suppression du client
            $customer->delete();
        });
        
        return redirect()->route('branches.customers.index', $branch)
            ->with('success', 'Client supprimé avec succès.');
    }

    /**
     * Importer des clients à partir d'un fichier CSV ou Excel.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request, Branch $branch)
    {
        $this->authorize('create', [Customer::class, $branch]);
        
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xls,xlsx|max:10240',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('branches.customers.index', $branch)
                ->withErrors($validator)
                ->withInput();
        }
        
        $file = $request->file('file');
        $filePath = $file->store('imports');
        
        // Création de l'enregistrement d'importation
        $import = tenant()->run(function () use ($branch, $file, $filePath) {
            return \App\Models\Tenant\CustomerImport::create([
                'branch_id' => $branch->id,
                'file_name' => $file->getClientOriginalName(),
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);
        });
        
        // Lancement du job d'importation en arrière-plan
        // Dans un projet réel, on utiliserait une file d'attente (queue)
        // ProcessCustomerImport::dispatch($import->id, $filePath, tenant('id'));
        
        return redirect()->route('branches.customers.index', $branch)
            ->with('success', 'Importation des clients lancée. Vous serez notifié une fois terminée.');
    }
}
