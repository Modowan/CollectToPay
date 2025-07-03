<?php

namespace App\Services\Payment;

use App\Models\PaymentProcessorConfig;
use App\Models\Tenant\EvervaultCardToken;
use App\Models\Tenant\PaymentTransaction;
use App\Services\Evervault\EvervaultConfigService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class StripePaymentService
{
    private $evervaultConfig;

    public function __construct(EvervaultConfigService $evervaultConfig)
    {
        $this->evervaultConfig = $evervaultConfig;
    }

    /**
     * Traiter un paiement via Stripe en utilisant un token Evervault
     */
    public function processPayment(
        int $organizationId,
        EvervaultCardToken $cardToken,
        float $amount,
        string $currency = 'EUR',
        string $description = null
    ): PaymentTransaction {
        DB::beginTransaction();
        
        try {
            // Vérifier que l'organisation a une configuration Stripe active
            $stripeConfig = $this->getStripeConfig($organizationId);
            if (!$stripeConfig) {
                throw new \Exception('Configuration Stripe non trouvée pour cette organisation');
            }

            // Vérifier que le token peut être utilisé
            if (!$cardToken->canBeUsed()) {
                throw new \Exception('Token de carte invalide ou expiré');
            }

            // Vérifier le montant disponible
            $availableAmount = $cardToken->getAvailableAmount();
            if ($amount > $availableAmount) {
                throw new \Exception("Montant demandé ({$amount}) supérieur au montant disponible ({$availableAmount})");
            }

            // Créer la transaction en statut pending
            $transaction = PaymentTransaction::create([
                'transaction_reference' => $this->generateTransactionReference(),
                'organization_id' => $organizationId,
                'evervault_card_token_id' => $cardToken->id,
                'payment_request_id' => $cardToken->payment_request_id,
                'member_transfer_id' => $cardToken->member_transfer_id,
                'processor_type' => 'stripe',
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'description' => $description
            ]);

            // Traiter le paiement via Evervault Function
            $paymentResult = $this->processStripePaymentSecurely(
                $stripeConfig,
                $cardToken,
                $amount,
                $currency,
                $description,
                $transaction->transaction_reference
            );

            // Mettre à jour la transaction avec le résultat
            $transaction->update([
                'processor_transaction_id' => $paymentResult['stripe_payment_intent_id'] ?? null,
                'status' => $paymentResult['success'] ? 'completed' : 'failed',
                'processor_response' => $paymentResult,
                'failure_reason' => $paymentResult['success'] ? null : ($paymentResult['error'] ?? 'Unknown error'),
                'processed_at' => now()
            ]);

            if ($paymentResult['success']) {
                // Marquer le transfert comme utilisé si applicable
                if ($cardToken->memberTransfer) {
                    $cardToken->memberTransfer->markAsUsed();
                }

                Log::info('Paiement Stripe traité avec succès', [
                    'transaction_id' => $transaction->id,
                    'stripe_payment_intent_id' => $paymentResult['stripe_payment_intent_id'],
                    'amount' => $amount,
                    'currency' => $currency
                ]);
            } else {
                Log::error('Échec paiement Stripe', [
                    'transaction_id' => $transaction->id,
                    'error' => $paymentResult['error'] ?? 'Unknown error',
                    'amount' => $amount,
                    'currency' => $currency
                ]);
            }

            DB::commit();
            return $transaction;

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erreur traitement paiement Stripe', [
                'organization_id' => $organizationId,
                'card_token_id' => $cardToken->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Traiter le paiement de manière sécurisée via Evervault Function
     */
    private function processStripePaymentSecurely(
        PaymentProcessorConfig $stripeConfig,
        EvervaultCardToken $cardToken,
        float $amount,
        string $currency,
        ?string $description,
        string $transactionReference
    ): array {
        try {
            $evervault = $this->evervaultConfig->getEvervaultInstance($stripeConfig->organization_id);

            // Utiliser une Evervault Function pour traiter le paiement
            $result = $evervault->run('process-stripe-payment', [
                'stripe_config' => [
                    'api_key' => Crypt::decryptString($stripeConfig->api_key),
                    'test_mode' => $stripeConfig->test_mode
                ],
                'card_token_id' => $cardToken->evervault_token_id,
                'payment_details' => [
                    'amount' => $amount * 100, // Stripe utilise les centimes
                    'currency' => strtolower($currency),
                    'description' => $description ?? 'Paiement via Evervault',
                    'metadata' => [
                        'transaction_reference' => $transactionReference,
                        'organization_id' => $stripeConfig->organization_id,
                        'card_token_id' => $cardToken->id
                    ]
                ],
                'options' => [
                    'confirm' => true,
                    'capture_method' => 'automatic',
                    'use_3ds' => $cardToken->is_3ds_verified
                ]
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Erreur Evervault Function Stripe', [
                'card_token_id' => $cardToken->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Erreur lors du traitement sécurisé: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Effectuer un remboursement via Stripe
     */
    public function refundPayment(
        PaymentTransaction $transaction,
        float $refundAmount = null,
        string $reason = null
    ): array {
        try {
            if ($transaction->processor_type !== 'stripe') {
                throw new \Exception('Cette transaction n\'a pas été traitée via Stripe');
            }

            if ($transaction->status !== 'completed') {
                throw new \Exception('Seules les transactions complétées peuvent être remboursées');
            }

            $stripeConfig = $this->getStripeConfig($transaction->organization_id);
            if (!$stripeConfig) {
                throw new \Exception('Configuration Stripe non trouvée');
            }

            $evervault = $this->evervaultConfig->getEvervaultInstance($transaction->organization_id);

            // Utiliser une Evervault Function pour le remboursement
            $result = $evervault->run('process-stripe-refund', [
                'stripe_config' => [
                    'api_key' => Crypt::decryptString($stripeConfig->api_key),
                    'test_mode' => $stripeConfig->test_mode
                ],
                'payment_intent_id' => $transaction->processor_transaction_id,
                'refund_details' => [
                    'amount' => $refundAmount ? ($refundAmount * 100) : null,
                    'reason' => $reason ?? 'requested_by_customer',
                    'metadata' => [
                        'original_transaction_id' => $transaction->id,
                        'refund_requested_at' => now()->toISOString()
                    ]
                ]
            ]);

            if ($result['success']) {
                // Mettre à jour la transaction
                $transaction->update([
                    'status' => 'refunded',
                    'processor_response' => array_merge(
                        $transaction->processor_response ?? [],
                        ['refund' => $result]
                    )
                ]);

                Log::info('Remboursement Stripe traité', [
                    'transaction_id' => $transaction->id,
                    'refund_id' => $result['refund_id'],
                    'refund_amount' => $refundAmount ?? $transaction->amount
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Erreur remboursement Stripe', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Configurer Stripe pour une organisation
     */
    public function configureStripe(
        int $organizationId,
        string $apiKey,
        bool $testMode = true,
        array $webhookConfig = []
    ): PaymentProcessorConfig {
        try {
            // Valider la clé API Stripe
            $this->validateStripeApiKey($apiKey, $testMode);

            // Créer ou mettre à jour la configuration
            $config = PaymentProcessorConfig::updateOrCreate(
                [
                    'organization_id' => $organizationId,
                    'processor_type' => 'stripe'
                ],
                [
                    'api_key' => Crypt::encryptString($apiKey),
                    'test_mode' => $testMode,
                    'is_active' => true,
                    'configuration' => [
                        'webhook_endpoint' => $webhookConfig['endpoint'] ?? null,
                        'webhook_secret' => $webhookConfig['secret'] ?? null,
                        'supported_currencies' => ['EUR', 'USD', 'GBP'],
                        'capture_method' => 'automatic'
                    ]
                ]
            );

            // Configurer les webhooks si nécessaire
            if (!empty($webhookConfig)) {
                $this->setupStripeWebhooks($config, $webhookConfig);
            }

            Log::info('Configuration Stripe mise à jour', [
                'organization_id' => $organizationId,
                'test_mode' => $testMode
            ]);

            return $config;

        } catch (\Exception $e) {
            Log::error('Erreur configuration Stripe', [
                'organization_id' => $organizationId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Obtenir la configuration Stripe pour une organisation
     */
    private function getStripeConfig(int $organizationId): ?PaymentProcessorConfig
    {
        return PaymentProcessorConfig::where('organization_id', $organizationId)
            ->where('processor_type', 'stripe')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Valider une clé API Stripe
     */
    private function validateStripeApiKey(string $apiKey, bool $testMode): bool
    {
        $expectedPrefix = $testMode ? 'sk_test_' : 'sk_live_';
        
        if (!str_starts_with($apiKey, $expectedPrefix)) {
            throw new \Exception("Clé API Stripe invalide pour le mode " . ($testMode ? 'test' : 'production'));
        }

        // Test de connexion basique
        try {
            $evervault = $this->evervaultConfig->getEvervaultInstance(1); // Utiliser une config par défaut pour le test
            
            $result = $evervault->run('validate-stripe-key', [
                'api_key' => $apiKey,
                'test_mode' => $testMode
            ]);

            if (!$result['valid']) {
                throw new \Exception('Clé API Stripe invalide: ' . ($result['error'] ?? 'Unknown error'));
            }

            return true;

        } catch (\Exception $e) {
            throw new \Exception('Impossible de valider la clé API Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Configurer les webhooks Stripe
     */
    private function setupStripeWebhooks(PaymentProcessorConfig $config, array $webhookConfig): void
    {
        try {
            $evervault = $this->evervaultConfig->getEvervaultInstance($config->organization_id);

            $result = $evervault->run('setup-stripe-webhooks', [
                'stripe_config' => [
                    'api_key' => Crypt::decryptString($config->api_key),
                    'test_mode' => $config->test_mode
                ],
                'webhook_config' => [
                    'url' => $webhookConfig['endpoint'],
                    'events' => [
                        'payment_intent.succeeded',
                        'payment_intent.payment_failed',
                        'charge.dispute.created',
                        'invoice.payment_succeeded',
                        'invoice.payment_failed'
                    ]
                ]
            ]);

            if ($result['success']) {
                $config->update([
                    'webhook_endpoint_id' => $result['webhook_endpoint_id'],
                    'configuration' => array_merge(
                        $config->configuration ?? [],
                        [
                            'webhook_endpoint_id' => $result['webhook_endpoint_id'],
                            'webhook_secret' => $result['webhook_secret']
                        ]
                    )
                ]);

                Log::info('Webhooks Stripe configurés', [
                    'organization_id' => $config->organization_id,
                    'webhook_endpoint_id' => $result['webhook_endpoint_id']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur configuration webhooks Stripe', [
                'organization_id' => $config->organization_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Générer une référence de transaction unique
     */
    private function generateTransactionReference(): string
    {
        return 'TXN_STRIPE_' . date('Ymd') . '_' . strtoupper(substr(md5(uniqid()), 0, 12));
    }
}