<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PaymentToken;
use App\Models\Tenant\Customer;
use App\Models\ActivityLog;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur pour la gestion des tokens de paiement des clients
 */
class PaymentTokenController extends Controller
{
    /**
     * Afficher la liste des tokens de paiement d'un client spécifique.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
     *
     * @param  \App\Models\Branch  $branch
     * @param  int  $customerId
     * @return \Illuminate\Http\Response
     */
    public function index(Branch $branch, $customerId)
    {
        $customer = tenant()->run(function () use ($customerId, $branch) {
            return Customer::where('id', $customerId)
                ->where('branch_id', $branch->id)
                ->firstOrFail();
        });
        
        $this->authorize('viewAny', [PaymentToken::class, $customer, $branch]);
        
        $paymentTokens = tenant()->run(function () use ($customer) {
            return $customer->paymentTokens;
        });
        
        return view('tenant.payment_tokens.index', compact('branch', 'customer', 'paymentTokens'));
    }

    /**
     * Afficher le formulaire de création d'un nouveau token de paiement.
     * Cette méthode n'est pas utilisée directement car les tokens sont créés via ixopay.
     * Elle est incluse pour la complétude de l'API.
     *
     * @param  \App\Models\Branch  $branch
     * @param  int  $customerId
     * @return \Illuminate\Http\Response
     */
    public function create(Branch $branch, $customerId)
    {
        $customer = tenant()->run(function () use ($customerId, $branch) {
            return Customer::where('id', $customerId)
                ->where('branch_id', $branch->id)
                ->firstOrFail();
        });
        
        $this->authorize('create', [PaymentToken::class, $customer, $branch]);
        
        return view('tenant.payment_tokens.create', compact('branch', 'customer'));
    }

    /**
     * Enregistrer un nouveau token de paiement dans la base de données.
     * Cette méthode est utilisée pour enregistrer un token reçu d'ixopay.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Branch  $branch
     * @param  int  $customerId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Branch $branch, $customerId)
    {
        $customer = tenant()->run(function () use ($customerId, $branch) {
            return Customer::where('id', $customerId)
                ->where('branch_id', $branch->id)
                ->firstOrFail();
        });
        
        $this->authorize('create', [PaymentToken::class, $customer, $branch]);
        
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|max:255',
            'card_type' => 'nullable|string|max:50',
            'last_four' => 'nullable|string|size:4',
            'expiry_month' => 'nullable|string|size:2',
            'expiry_year' => 'nullable|string|size:4',
            'is_default' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('branches.customers.payment-tokens.create', [$branch, $customerId])
                ->withErrors($validator)
                ->withInput();
        }
        
        $data = $validator->validated();
        $data['customer_id'] = $customerId;
        $data['status'] = 'active';
        
        // Si ce token est défini comme par défaut, désactiver les autres tokens par défaut
        if ($data['is_default']) {
            tenant()->run(function () use ($customerId) {
                PaymentToken::where('customer_id', $customerId)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            });
        }
        
        // Création du token de paiement dans la base de données du tenant
        $paymentToken = tenant()->run(function () use ($data) {
            return PaymentToken::create($data);
        });
        
        // Journalisation de l'activité
        ActivityLog::create([
            'user_id' => Auth::id(),
            'tenant_id' => tenant('id'),
            'action' => 'create',
            'entity_type' => 'payment_token',
            'entity_id' => $paymentToken->id,
            'details' => json_encode([
                'customer_id' => $data['customer_id'],
                'card_type' => $data['card_type'] ?? null,
                'last_four' => $data['last_four'] ?? null,
                'is_default' => $data['is_default'],
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        return redirect()->route('branches.customers.payment-tokens.index', [$branch, $customerId])
            ->with('success', 'Token de paiement créé avec succès.');
    }

    /**
     * Afficher les détails d'un token de paiement spécifique.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
     *
     * @param  \App\Models\Branch  $branch
     * @param  int  $customerId
     * @param  int  $tokenId
     * @return \Illuminate\Http\Response
     */
    public function show(Branch $branch, $customerId, $tokenId)
    {
        $customer = tenant()->run(function () use ($customerId, $branch) {
            return Customer::where('id', $customerId)
                ->where('branch_id', $branch->id)
                ->firstOrFail();
        });
        
        $paymentToken = tenant()->run(function () use ($tokenId, $customerId) {
            return PaymentToken::where('id', $tokenId)
                ->where('customer_id', $customerId)
                ->firstOrFail();
        });
        
        $this->authorize('view', [$paymentToken, $customer, $branch]);
        
        $paymentTransactions = tenant()->run(function () use ($paymentToken) {
            return $paymentToken->paymentTransactions()->latest()->take(10)->get();
        });
        
        return view('tenant.payment_tokens.show', compact('branch', 'customer', 'paymentToken', 'paymentTransactions'));
    }

    /**
     * Définir un token de paiement comme token par défaut.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Branch  $branch
     * @param  int  $customerId
     * @param  int  $tokenId
     * @return \Illuminate\Http\Response
     */
    public function setDefault(Request $request, Branch $branch, $customerId, $tokenId)
    {
        $customer = tenant()->run(function () use ($customerId, $branch) {
            return Customer::where('id', $customerId)
                ->where('branch_id', $branch->id)
                ->firstOrFail();
        });
        
        $paymentToken = tenant()->run(function () use ($tokenId, $customerId) {
            return PaymentToken::where('id', $tokenId)
                ->where('customer_id', $customerId)
                ->firstOrFail();
        });
        
        $this->authorize('update', [$paymentToken, $customer, $branch]);
        
        // Désactiver tous les tokens par défaut pour ce client
        tenant()->run(function () use ($customerId) {
            PaymentToken::where('customer_id', $customerId)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        });
        
        // Définir ce token comme par défaut
        tenant()->run(function () use ($paymentToken) {
            $paymentToken->update(['is_default' => true]);
        });
        
        // Journalisation de l'activité
        ActivityLog::create([
            'user_id' => Auth::id(),
            'tenant_id' => tenant('id'),
            'action' => 'update',
            'entity_type' => 'payment_token',
            'entity_id' => $paymentToken->id,
            'details' => json_encode(['is_default' => true]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        return redirect()->route('branches.customers.payment-tokens.index', [$branch, $customerId])
            ->with('success', 'Token de paiement défini comme par défaut.');
    }

    /**
     * Révoquer un token de paiement.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Branch  $branch
     * @param  int  $customerId
     * @param  int  $tokenId
     * @return \Illuminate\Http\Response
     */
    public function revoke(Request $request, Branch $branch, $customerId, $tokenId)
    {
        $customer = tenant()->run(function () use ($customerId, $branch) {
            return Customer::where('id', $customerId)
                ->where('branch_id', $branch->id)
                ->firstOrFail();
        });
        
        $paymentToken = tenant()->run(function () use ($tokenId, $customerId) {
            return PaymentToken::where('id', $tokenId)
                ->where('customer_id', $customerId)
                ->firstOrFail();
        });
        
        $this->authorize('delete', [$paymentToken, $customer, $branch]);
        
        // Révoquer le token
        tenant()->run(function () use ($paymentToken) {
            $paymentToken->update(['status' => 'revoked']);
        });
        
        // Journalisation de l'activité
        ActivityLog::create([
            'user_id' => Auth::id(),
            'tenant_id' => tenant('id'),
            'action' => 'revoke',
            'entity_type' => 'payment_token',
            'entity_id' => $paymentToken->id,
            'details' => json_encode(['status' => 'revoked']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        return redirect()->route('branches.customers.payment-tokens.index', [$branch, $customerId])
            ->with('success', 'Token de paiement révoqué avec succès.');
    }
}
