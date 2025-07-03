<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PaymentTransaction;
use App\Models\Tenant\PaymentToken;
use App\Models\Tenant\Customer;
use App\Models\ActivityLog;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur pour la gestion des transactions de paiement
 */
class PaymentTransactionController extends Controller
{
    /**
     * Afficher la liste des transactions de paiement d'un client spécifique.
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
        
        $this->authorize('viewAny', [PaymentTransaction::class, $customer, $branch]);
        
        $paymentTransactions = tenant()->run(function () use ($customer) {
            return $customer->paymentTransactions()->latest()->paginate(15);
        });
        
        return view('tenant.payment_transactions.index', compact('branch', 'customer', 'paymentTransactions'));
    }

    /**
     * Afficher le formulaire de création d'une nouvelle transaction de paiement.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
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
        
        $this->authorize('create', [PaymentTransaction::class, $customer, $branch]);
        
        $paymentTokens = tenant()->run(function () use ($customer) {
            return $customer->paymentTokens()->where('status', 'active')->get();
        });
        
        if ($paymentTokens->isEmpty()) {
            return redirect()->route('branches.customers.payment-tokens.create', [$branch, $customerId])
                ->with('error', 'Le client doit avoir au moins un token de paiement actif pour effectuer une transaction.');
        }
        
        return view('tenant.payment_transactions.create', compact('branch', 'customer', 'paymentTokens'));
    }

    /**
     * Enregistrer une nouvelle transaction de paiement dans la base de données.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
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
        
        $this->authorize('create', [PaymentTransaction::class, $customer, $branch]);
        
        $validator = Validator::make($request->all(), [
            'token_id' => 'required|exists:tenant.payment_tokens,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('branches.customers.payment-transactions.create', [$branch, $customerId])
                ->withErrors($validator)
                ->withInput();
        }
        
        $data = $validator->validated();
        $data['customer_id'] = $customerId;
        $data['status'] = 'pending';
        
        // Vérifier que le token appartient bien au client
        $tokenExists = tenant()->run(function () use ($data) {
            return PaymentToken::where('id', $data['token_id'])
                ->where('customer_id', $data['customer_id'])
                ->where('status', 'active')
                ->exists();
        });
        
        if (!$tokenExists) {
            return redirect()->route('branches.customers.payment-transactions.create', [$branch, $customerId])
                ->with('error', 'Le token de paiement sélectionné n\'est pas valide.')
                ->withInput();
        }
        
        // Création de la transaction de paiement dans la base de données du tenant
        $paymentTransaction = tenant()->run(function () use ($data) {
            return PaymentTransaction::create($data);
        });
        
        // Journalisation de l'activité
        ActivityLog::create([
            'user_id' => Auth::id(),
            'tenant_id' => tenant('id'),
            'action' => 'create',
            'entity_type' => 'payment_transaction',
            'entity_id' => $paymentTransaction->id,
            'details' => json_encode([
                'customer_id' => $data['customer_id'],
                'token_id' => $data['token_id'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'status' => 'pending',
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        // Dans un cas réel, on appellerait ici l'API ixopay pour traiter la transaction
        // $ixopayService = new IxopayService();
        // $result = $ixopayService->processTransaction($paymentTransaction);
        
        // Pour l'exemple, on simule une transaction réussie
        tenant()->run(function () use ($paymentTransaction) {
            $paymentTransaction->update([
                'status' => 'completed',
                'transaction_id' => 'ixopay_' . uniqid(),
            ]);
        });
        
        return redirect()->route('branches.customers.payment-transactions.index', [$branch, $customerId])
            ->with('success', 'Transaction de paiement créée avec succès.');
    }

    /**
     * Afficher les détails d'une transaction de paiement spécifique.
     * Accessible par l'administrateur global, l'administrateur du tenant, l'administrateur de la branche.
     *
     * @param  \App\Models\Branch  $branch
     * @param  int  $customerId
     * @param  int  $transactionId
     * @return \Illuminate\Http\Response
     */
    public function show(Branch $branch, $customerId, $transactionId)
    {
        $customer = tenant()->run(function () use ($customerId, $branch) {
            return Customer::where('id', $customerId)
                ->where('branch_id', $branch->id)
                ->firstOrFail();
        });
        
        $paymentTransaction = tenant()->run(function () use ($transactionId, $customerId) {
            return PaymentTransaction::where('id', $transactionId)
                ->where('customer_id', $customerId)
                ->firstOrFail();
        });
        
        $this->authorize('view', [$paymentTransaction, $customer, $branch]);
        
        $paymentToken = tenant()->run(function () use ($paymentTransaction) {
            return $paymentTransaction->paymentToken;
        });
        
        return view('tenant.payment_transactions.show', compact('branch', 'customer', 'paymentTransaction', 'paymentToken'));
    }

    /**
     * Rembourser une transaction de paiement.
     * Accessible par l'administrateur global, l'administrateur du tenant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Branch  $branch
     * @param  int  $customerId
     * @param  int  $transactionId
     * @return \Illuminate\Http\Response
     */
    public function refund(Request $request, Branch $branch, $customerId, $transactionId)
    {
        $customer = tenant()->run(function () use ($customerId, $branch) {
            return Customer::where('id', $customerId)
                ->where('branch_id', $branch->id)
                ->firstOrFail();
        });
        
        $paymentTransaction = tenant()->run(function () use ($transactionId, $customerId) {
            return PaymentTransaction::where('id', $transactionId)
                ->where('customer_id', $customerId)
                ->where('status', 'completed')
                ->firstOrFail();
        });
        
        $this->authorize('refund', [$paymentTransaction, $customer, $branch]);
        
        // Dans un cas réel, on appellerait ici l'API ixopay pour traiter le remboursement
        // $ixopayService = new IxopayService();
        // $result = $ixopayService->refundTransaction($paymentTransaction);
        
        // Pour l'exemple, on simule un remboursement réussi
        tenant()->run(function () use ($paymentTransaction) {
            $paymentTransaction->update([
                'status' => 'refunded',
            ]);
        });
        
        // Journalisation de l'activité
        ActivityLog::create([
            'user_id' => Auth::id(),
            'tenant_id' => tenant('id'),
            'action' => 'refund',
            'entity_type' => 'payment_transaction',
            'entity_id' => $paymentTransaction->id,
            'details' => json_encode(['status' => 'refunded']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        return redirect()->route('branches.customers.payment-transactions.show', [$branch, $customerId, $transactionId])
            ->with('success', 'Transaction remboursée avec succès.');
    }
}
