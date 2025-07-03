<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use App\Models\Tenant\PaymentToken;
use App\Models\Tenant\PaymentTransaction;
use App\Services\IxopayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur API pour l'intégration avec ixopay
 */
class IxopayController extends Controller
{
    /**
     * Service d'intégration ixopay
     *
     * @var IxopayService
     */
    protected $ixopayService;

    /**
     * Constructeur du contrôleur
     *
     * @param IxopayService $ixopayService
     */
    public function __construct(IxopayService $ixopayService)
    {
        $this->ixopayService = $ixopayService;
    }

    /**
     * Générer un lien de tokenisation pour un client
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateTokenizationLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:tenant.customers,id',
            'return_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = tenant()->run(function () use ($request) {
            return Customer::findOrFail($request->customer_id);
        });

        $tokenizationUrl = $this->ixopayService->generateTokenizationLink(
            $customer,
            $request->return_url
        );

        if (!$tokenizationUrl) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de générer le lien de tokenisation',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'tokenization_url' => $tokenizationUrl,
        ]);
    }

    /**
     * Webhook pour recevoir les notifications de tokenisation d'ixopay
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function tokenizationWebhook(Request $request)
    {
        $data = $request->all();

        // Vérifier que les données nécessaires sont présentes
        if (!isset($data['token'], $data['customer_id'], $data['signature'])) {
            return response()->json([
                'success' => false,
                'message' => 'Données incomplètes',
            ], 400);
        }

        $customer = tenant()->run(function () use ($data) {
            return Customer::find($data['customer_id']);
        });

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Client introuvable',
            ], 404);
        }

        $token = $this->ixopayService->saveToken($data, $customer);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de l\'enregistrement du token',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token enregistré avec succès',
        ]);
    }

    /**
     * Traiter une transaction de paiement
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|exists:tenant.payment_transactions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $transaction = tenant()->run(function () use ($request) {
            return PaymentTransaction::findOrFail($request->transaction_id);
        });

        $success = $this->ixopayService->processTransaction($transaction);

        return response()->json([
            'success' => $success,
            'transaction' => tenant()->run(function () use ($transaction) {
                return [
                    'id' => $transaction->id,
                    'status' => $transaction->status,
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                ];
            }),
        ]);
    }

    /**
     * Webhook pour recevoir les notifications de transaction d'ixopay
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transactionWebhook(Request $request)
    {
        $data = $request->all();

        // Vérifier que les données nécessaires sont présentes
        if (!isset($data['transaction_id'], $data['status'], $data['signature'])) {
            return response()->json([
                'success' => false,
                'message' => 'Données incomplètes',
            ], 400);
        }

        $transaction = tenant()->run(function () use ($data) {
            return PaymentTransaction::where('transaction_id', $data['transaction_id'])->first();
        });

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction introuvable',
            ], 404);
        }

        // Mettre à jour le statut de la transaction
        tenant()->run(function () use ($transaction, $data) {
            $status = match ($data['status']) {
                'approved' => 'completed',
                'declined' => 'failed',
                'refunded' => 'refunded',
                default => $transaction->status,
            };

            $transaction->update([
                'status' => $status,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Statut de la transaction mis à jour avec succès',
        ]);
    }
}
