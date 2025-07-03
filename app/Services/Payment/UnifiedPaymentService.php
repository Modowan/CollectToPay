<?php

namespace App\Services\Payment;

use App\Models\Tenant\EvervaultCardToken;
use App\Models\Tenant\PaymentTransaction;
use App\Services\Payment\StripePaymentService;
use App\Services\Payment\PayPalPaymentService;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\Log;

class UnifiedPaymentService
{
    private $stripeService;
    private $paypalService;
    private $notificationService;

    public function __construct(
        StripePaymentService $stripeService,
        PayPalPaymentService $paypalService,
        NotificationService $notificationService
    ) {
        $this->stripeService = $stripeService;
        $this->paypalService = $paypalService;
        $this->notificationService = $notificationService;
    }

    /**
     * Traiter un paiement avec le processeur préféré de l'organisation
     */
    public function processPayment(
        int $organizationId,
        EvervaultCardToken $cardToken,
        float $amount,
        string $currency = 'EUR',
        string $description = null,
        string $preferredProcessor = null
    ): PaymentTransaction {
        try {
            // Déterminer le processeur à utiliser
            $processor = $this->determineProcessor($organizationId, $preferredProcessor);
            
            Log::info('Traitement paiement unifié', [
                'organization_id' => $organizationId,
                'card_token_id' => $cardToken->id,
                'amount' => $amount,
                'processor' => $processor
            ]);

            // Traiter le paiement selon le processeur choisi
            $transaction = match($processor) {
                'stripe' => $this->stripeService->processPayment(
                    $organizationId, $cardToken, $amount, $currency, $description
                ),
                'paypal' => $this->paypalService->processPayment(
                    $organizationId, $cardToken, $amount, $currency, $description
                ),
                default => throw new \Exception("Processeur de paiement non supporté: {$processor}")
            };

            // Envoyer les notifications appropriées
            $this->sendPaymentNotifications($transaction);

            return $transaction;

        } catch (\Exception $e) {
            Log::error('Erreur paiement unifié', [
                'organization_id' => $organizationId,
                'card_token_id' => $cardToken->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Déterminer le processeur de paiement à utiliser
     */
    private function determineProcessor(int $organizationId, ?string $preferredProcessor): string
    {
        // Si un processeur est spécifiquement demandé, le vérifier
        if ($preferredProcessor) {
            if ($this->isProcessorAvailable($organizationId, $preferredProcessor)) {
                return $preferredProcessor;
            }
        }

        // Sinon, utiliser l'ordre de préférence par défaut
        $processors = ['stripe', 'paypal'];
        
        foreach ($processors as $processor) {
            if ($this->isProcessorAvailable($organizationId, $processor)) {
                return $processor;
            }
        }

        throw new \Exception('Aucun processeur de paiement configuré pour cette organisation');
    }

    /**
     * Vérifier si un processeur est disponible pour une organisation
     */
    private function isProcessorAvailable(int $organizationId, string $processor): bool
    {
        return match($processor) {
            'stripe' => $this->stripeService->getStripeConfig($organizationId) !== null,
            'paypal' => $this->paypalService->getPayPalConfig($organizationId) !== null,
            default => false
        };
    }

    /**
     * Envoyer les notifications de paiement
     */
    private function sendPaymentNotifications(PaymentTransaction $transaction): void
    {
        try {
            if ($transaction->status === 'completed') {
                $this->notificationService->sendPaymentCompletedNotification($transaction);
            } elseif ($transaction->status === 'failed') {
                $this->notificationService->sendPaymentFailedNotification($transaction);
            }
        } catch (\Exception $e) {
            Log::error('Erreur envoi notifications paiement', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}