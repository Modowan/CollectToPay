<?php

namespace App\Services\Payment;

use App\Models\PaymentProcessorConfig;
use App\Models\Tenant\EvervaultCardToken;
use App\Models\Tenant\PaymentTransaction;
use App\Services\Evervault\EvervaultConfigService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class PayPalPaymentService
{
    private $evervaultConfig;

    public function __construct(EvervaultConfigService $evervaultConfig)
    {
        $this->evervaultConfig = $evervaultConfig;
    }

    /**
     * Traiter un paiement via PayPal en utilisant un token Evervault
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
            // Vérifier que l'organisation a une configuration PayPal active
            $paypalConfig = $this->getPayPalConfig($organizationId);
            if (!$paypalConfig) {
                throw new \Exception('Configuration PayPal non trouvée pour cette organisation');
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
                'processor_type' => 'paypal',
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'description' => $description
            ]);

            // Traiter le paiement via Evervault Function
            $paymentResult = $this->processPayPalPaymentSecurely(
                $paypalConfig,
                $cardToken,
                $amount,
                $currency,
                $description,
                $transaction->transaction_reference
            );

            // Mettre à jour la transaction avec le résultat
            $transaction->update([
                'processor_transaction_id' => $paymentResult['paypal_order_id'] ?? null,
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

                Log::info('Paiement PayPal traité avec succès', [
                    'transaction_id' => $transaction->id,
                    'paypal_order_id' => $paymentResult['paypal_order_id'],
                    'amount' => $amount,
                    'currency' => $currency
                ]);
            } else {
                Log::error('Échec paiement PayPal', [
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
            
            Log::error('Erreur traitement paiement PayPal', [
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
    private function processPayPalPaymentSecurely(
        PaymentProcessorConfig $paypalConfig,
        EvervaultCardToken $cardToken,
        float $amount,
        string $currency,
        ?string $description,
        string $transactionReference
    ): array {
        try {
            $evervault = $this->evervaultConfig->getEvervaultInstance($paypalConfig->organization_id);

            // Utiliser une Evervault Function pour traiter le paiement PayPal
            $result = $evervault->run('process-paypal-payment', [
                'paypal_config' => [
                    'client_id' => Crypt::decryptString($paypalConfig->api_key),
                    'client_secret' => Crypt::decryptString($paypalConfig->api_secret),
                    'test_mode' => $paypalConfig->test_mode
                ],
                'card_token_id' => $cardToken->evervault_token_id,
                'payment_details' => [
                    'amount' => [
                        'currency_code' => strtoupper($currency),
                        'value' => number_format($amount, 2, '.', '')
                    ],
                    'description' => $description ?? 'Paiement via Evervault',
                    'custom_id' => $transactionReference,
                    'invoice_id' => $transactionReference
                ],
                'payer_info' => [
                    'name' => $cardToken->cardholder_name,
                    'email' => $this->getPayerEmail($cardToken)
                ],
                'options' => [
                    'capture' => true,
                    'use_3ds' => $cardToken->is_3ds_verified
                ]
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Erreur Evervault Function PayPal', [
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
     * Effectuer un remboursement via PayPal
     */
    public function refundPayment(
        PaymentTransaction $transaction,
        float $refundAmount = null,
        string $reason = null
    ): array {
        try {
            if ($transaction->processor_type !== 'paypal') {
                throw new \Exception('Cette transaction n\'a pas été traitée via PayPal');
            }

            if ($transaction->status !== 'completed') {
                throw new \Exception('Seules les transactions complétées peuvent être remboursées');
            }

            $paypalConfig = $this->getPayPalConfig($transaction->organization_id);
            if (!$paypalConfig) {
                throw new \Exception('Configuration PayPal non trouvée');
            }

            $evervault = $this->evervaultConfig->getEvervaultInstance($transaction->organization_id);

            // Utiliser une Evervault Function pour le remboursement
            $result = $evervault->run('process-paypal-refund', [
                'paypal_config' => [
                    'client_id' => Crypt::decryptString($paypalConfig->api_key),
                    'client_secret' => Crypt::decryptString($paypalConfig->api_secret),
                    'test_mode' => $paypalConfig->test_mode
                ],
                'capture_id' => $transaction->processor_transaction_id,
                'refund_details' => [
                    'amount' => $refundAmount ? [
                        'currency_code' => $transaction->currency,
                        'value' => number_format($refundAmount, 2, '.', '')
                    ] : null,
                    'note_to_payer' => $reason ?? 'Remboursement demandé',
                    'invoice_id' => $transaction->transaction_reference . '_REFUND'
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

                Log::info('Remboursement PayPal traité', [
                    'transaction_id' => $transaction->id,
                    'refund_id' => $result['refund_id'],
                    'refund_amount' => $refundAmount ?? $transaction->amount
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Erreur remboursement PayPal', [
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
     * Configurer PayPal pour une organisation
     */
    public function configurePayPal(
        int $organizationId,
        string $clientId,
        string $clientSecret,
        bool $testMode = true,
        array $webhookConfig = []
    ): PaymentProcessorConfig {
        try {
            // Valider les identifiants PayPal
            $this->validatePayPalCredentials($clientId, $clientSecret, $testMode);

            // Créer ou mettre à jour la configuration
            $config = PaymentProcessorConfig::updateOrCreate(
                [
                    'organization_id' => $organizationId,
                    'processor_type' => 'paypal'
                ],
                [
                    'api_key' => Crypt::encryptString($clientId),
                    'api_secret' => Crypt::encryptString($clientSecret),
                    'test_mode' => $testMode,
                    'is_active' => true,
                    'configuration' => [
                        'webhook_endpoint' => $webhookConfig['endpoint'] ?? null,
                        'webhook_secret' => $webhookConfig['secret'] ?? null,
                        'supported_currencies' => ['EUR', 'USD', 'GBP', 'CAD', 'AUD'],
                        'payment_methods' => ['card', 'paypal_account']
                    ]
                ]
            );

            // Configurer les webhooks si nécessaire
            if (!empty($webhookConfig)) {
                $this->setupPayPalWebhooks($config, $webhookConfig);
            }

            Log::info('Configuration PayPal mise à jour', [
                'organization_id' => $organizationId,
                'test_mode' => $testMode
            ]);

            return $config;

        } catch (\Exception $e) {
            Log::error('Erreur configuration PayPal', [
                'organization_id' => $organizationId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Obtenir la configuration PayPal pour une organisation
     */
    private function getPayPalConfig(int $organizationId): ?PaymentProcessorConfig
    {
        return PaymentProcessorConfig::where('organization_id', $organizationId)
            ->where('processor_type', 'paypal')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Valider les identifiants PayPal
     */
    private function validatePayPalCredentials(string $clientId, string $clientSecret, bool $testMode): bool
    {
        try {
            $evervault = $this->evervaultConfig->getEvervaultInstance(1); // Utiliser une config par défaut pour le test
            
            $result = $evervault->run('validate-paypal-credentials', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'test_mode' => $testMode
            ]);

            if (!$result['valid']) {
                throw new \Exception('Identifiants PayPal invalides: ' . ($result['error'] ?? 'Unknown error'));
            }

            return true;

        } catch (\Exception $e) {
            throw new \Exception('Impossible de valider les identifiants PayPal: ' . $e->getMessage());
        }
    }

    /**
     * Configurer les webhooks PayPal
     */
    private function setupPayPalWebhooks(PaymentProcessorConfig $config, array $webhookConfig): void
    {
        try {
            $evervault = $this->evervaultConfig->getEvervaultInstance($config->organization_id);

            $result = $evervault->run('setup-paypal-webhooks', [
                'paypal_config' => [
                    'client_id' => Crypt::decryptString($config->api_key),
                    'client_secret' => Crypt::decryptString($config->api_secret),
                    'test_mode' => $config->test_mode
                ],
                'webhook_config' => [
                    'url' => $webhookConfig['endpoint'],
                    'events' => [
                        'PAYMENT.CAPTURE.COMPLETED',
                        'PAYMENT.CAPTURE.DENIED',
                        'PAYMENT.CAPTURE.REFUNDED',
                        'CHECKOUT.ORDER.APPROVED',
                        'CHECKOUT.ORDER.COMPLETED'
                    ]
                ]
            ]);

            if ($result['success']) {
                $config->update([
                    'webhook_endpoint_id' => $result['webhook_id'],
                    'configuration' => array_merge(
                        $config->configuration ?? [],
                        [
                            'webhook_id' => $result['webhook_id'],
                            'webhook_secret' => $result['webhook_secret']
                        ]
                    )
                ]);

                Log::info('Webhooks PayPal configurés', [
                    'organization_id' => $config->organization_id,
                    'webhook_id' => $result['webhook_id']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur configuration webhooks PayPal', [
                'organization_id' => $config->organization_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtenir l'email du payeur depuis le token
     */
    private function getPayerEmail(EvervaultCardToken $cardToken): string
    {
        if ($cardToken->paymentRequest) {
            return $cardToken->paymentRequest->client_email;
        }
        
        if ($cardToken->memberTransfer) {
            return $cardToken->memberTransfer->cardholder_email;
        }
        
        return 'noreply@example.com'; // Email par défaut
    }

    /**
     * Générer une référence de transaction unique
     */
    private function generateTransactionReference(): string
    {
        return 'TXN_PAYPAL_' . date('Ymd') . '_' . strtoupper(substr(md5(uniqid()), 0, 12));
    }
}