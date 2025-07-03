<?php

namespace App\Http\Controllers\Evervault;

use App\Http\Controllers\Controller;
use App\Services\Payment\StripePaymentService;
use App\Services\Payment\PayPalPaymentService;
use App\Services\Payment\UnifiedPaymentService;
use App\Models\Tenant\EvervaultCardToken;
use App\Models\Tenant\PaymentTransaction;
use App\Models\PaymentProcessorConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentProcessorController extends Controller
{
    private $stripeService;
    private $paypalService;
    private $unifiedService;

    public function __construct(
        StripePaymentService $stripeService,
        PayPalPaymentService $paypalService,
        UnifiedPaymentService $unifiedService
    ) {
        $this->stripeService = $stripeService;
        $this->paypalService = $paypalService;
        $this->unifiedService = $unifiedService;
    }

    /**
     * Configurer Stripe pour une organisation
     */
    public function configureStripe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'api_key' => 'required|string',
            'test_mode' => 'boolean',
            'webhook_endpoint' => 'nullable|url',
            'webhook_secret' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $config = $this->stripeService->configureStripe(
                $request->organization_id,
                $request->api_key,
                $request->test_mode ?? true,
                [
                    'endpoint' => $request->webhook_endpoint,
                    'secret' => $request->webhook_secret
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Configuration Stripe mise à jour avec succès',
                'data' => [
                    'processor_type' => $config->processor_type,
                    'test_mode' => $config->test_mode,
                    'is_active' => $config->is_active,
                    'created_at' => $config->created_at,
                    'updated_at' => $config->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la configuration Stripe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Configurer PayPal pour une organisation
     */
    public function configurePayPal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'test_mode' => 'boolean',
            'webhook_endpoint' => 'nullable|url',
            'webhook_secret' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $config = $this->paypalService->configurePayPal(
                $request->organization_id,
                $request->client_id,
                $request->client_secret,
                $request->test_mode ?? true,
                [
                    'endpoint' => $request->webhook_endpoint,
                    'secret' => $request->webhook_secret
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Configuration PayPal mise à jour avec succès',
                'data' => [
                    'processor_type' => $config->processor_type,
                    'test_mode' => $config->test_mode,
                    'is_active' => $config->is_active,
                    'created_at' => $config->created_at,
                    'updated_at' => $config->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la configuration PayPal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les configurations de processeurs pour une organisation
     */
    public function getConfigurations(Request $request)
    {
        try {
            $organizationId = $request->get('organization_id');

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'organization_id requis'
                ], 400);
            }

            $configs = PaymentProcessorConfig::where('organization_id', $organizationId)
                ->select(['id', 'processor_type', 'test_mode', 'is_active', 'created_at', 'updated_at'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $configs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des configurations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Effectuer un paiement
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'card_token_id' => 'required|exists:evervault_card_tokens,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string|max:255',
            'processor' => 'nullable|in:stripe,paypal'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cardToken = EvervaultCardToken::findOrFail($request->card_token_id);

            // Vérifier que le token appartient à l'organisation ou qu'elle peut l'utiliser
            if (!$this->canOrganizationUseToken($cardToken, $request->organization_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à utiliser ce token'
                ], 403);
            }

            $transaction = $this->unifiedService->processPayment(
                $request->organization_id,
                $cardToken,
                $request->amount,
                $request->currency ?? 'EUR',
                $request->description,
                $request->processor
            );

            return response()->json([
                'success' => true,
                'data' => $transaction->load(['cardToken', 'paymentRequest', 'memberTransfer'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Effectuer un remboursement
     */
    public function refundPayment(Request $request, int $transactionId)
    {
        $validator = Validator::make($request->all(), [
            'refund_amount' => 'nullable|numeric|min:0.01',
            'reason' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $transaction = PaymentTransaction::findOrFail($transactionId);

            // Vérifier que l'organisation peut effectuer ce remboursement
            $organizationId = $request->get('organization_id');
            if ($transaction->organization_id != $organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à rembourser cette transaction'
                ], 403);
            }

            if ($transaction->processor_type === 'stripe') {
                $result = $this->stripeService->refundPayment(
                    $transaction,
                    $request->refund_amount,
                    $request->reason
                );
            } elseif ($transaction->processor_type === 'paypal') {
                $result = $this->paypalService->refundPayment(
                    $transaction,
                    $request->refund_amount,
                    $request->reason
                );
            } else {
                throw new \Exception('Processeur de paiement non supporté pour les remboursements');
            }

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Remboursement effectué avec succès' : 'Échec du remboursement',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du remboursement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister les transactions d'une organisation
     */
    public function getTransactions(Request $request)
    {
        try {
            $organizationId = $request->get('organization_id');
            $status = $request->get('status');
            $processor = $request->get('processor');

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'organization_id requis'
                ], 400);
            }

            $query = PaymentTransaction::with(['cardToken', 'paymentRequest', 'memberTransfer'])
                ->where('organization_id', $organizationId);

            if ($status) {
                $query->where('status', $status);
            }

            if ($processor) {
                $query->where('processor_type', $processor);
            }

            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'une transaction
     */
    public function getTransaction(Request $request, int $transactionId)
    {
        try {
            $transaction = PaymentTransaction::with([
                'cardToken', 
                'paymentRequest', 
                'memberTransfer',
                'organization'
            ])->findOrFail($transactionId);

            return response()->json([
                'success' => true,
                'data' => $transaction
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Tester la configuration d'un processeur
     */
    public function testConfiguration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'processor_type' => 'required|in:stripe,paypal'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $organizationId = $request->organization_id;
            $processorType = $request->processor_type;

            if ($processorType === 'stripe') {
                $config = $this->stripeService->getStripeConfig($organizationId);
                if (!$config) {
                    throw new \Exception('Configuration Stripe non trouvée');
                }
                
                // Test de connexion Stripe
                $testResult = $this->stripeService->testConnection($config);
                
            } elseif ($processorType === 'paypal') {
                $config = $this->paypalService->getPayPalConfig($organizationId);
                if (!$config) {
                    throw new \Exception('Configuration PayPal non trouvée');
                }
                
                // Test de connexion PayPal
                $testResult = $this->paypalService->testConnection($config);
            }

            return response()->json([
                'success' => true,
                'message' => 'Test de configuration réussi',
                'data' => $testResult
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec du test de configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Désactiver un processeur de paiement
     */
    public function deactivateProcessor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'processor_type' => 'required|in:stripe,paypal'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $config = PaymentProcessorConfig::where('organization_id', $request->organization_id)
                ->where('processor_type', $request->processor_type)
                ->first();

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration non trouvée'
                ], 404);
            }

            $config->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Processeur désactivé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la désactivation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier si une organisation peut utiliser un token
     */
    private function canOrganizationUseToken(EvervaultCardToken $cardToken, int $organizationId): bool
    {
        // Si le token provient d'une demande de paiement de cette organisation
        if ($cardToken->paymentRequest && $cardToken->paymentRequest->organization_id == $organizationId) {
            return true;
        }

        // Si le token provient d'un transfert vers cette organisation
        if ($cardToken->memberTransfer && $cardToken->memberTransfer->recipient_organization_id == $organizationId) {
            return true;
        }

        return false;
    }
}

