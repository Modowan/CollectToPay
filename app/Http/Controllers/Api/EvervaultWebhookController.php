<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PaymentTransaction;
use App\Models\Tenant\EvervaultCardToken;
use App\Services\Notifications\EvervaultNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class EvervaultWebhookController extends Controller
{
    private $notificationService;

    public function __construct(EvervaultNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Traiter les webhooks Evervault Relay
     */
    public function handleRelayWebhook(Request $request)
    {
        try {
            // Vérifier la signature du webhook
            $signature = $request->header('X-Evervault-Signature');
            $payload = $request->getContent();
            
            if (!$this->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Webhook Evervault avec signature invalide', [
                    'signature' => $signature,
                    'ip' => $request->ip()
                ]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $data = $request->json()->all();

            Log::info('Webhook Evervault Relay reçu', [
                'event_type' => $data['event_type'] ?? 'unknown',
                'timestamp' => $data['timestamp'] ?? null
            ]);

            // Traiter selon le type d'événement
            switch ($data['event_type']) {
                case 'relay.request.completed':
                    $this->handleRelayRequestCompleted($data);
                    break;
                    
                case 'relay.request.failed':
                    $this->handleRelayRequestFailed($data);
                    break;
                    
                case 'relay.encryption.completed':
                    $this->handleEncryptionCompleted($data);
                    break;
                    
                case 'relay.decryption.completed':
                    $this->handleDecryptionCompleted($data);
                    break;
                    
                default:
                    Log::info('Webhook Evervault non géré', [
                        'event_type' => $data['event_type'],
                        'data' => $data
                    ]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Erreur traitement webhook Evervault', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->getContent()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Traiter les webhooks des processeurs de paiement
     */
    public function handlePaymentWebhook(Request $request, string $processor)
    {
        try {
            Log::info('Webhook processeur de paiement reçu', [
                'processor' => $processor,
                'headers' => $request->headers->all()
            ]);

            switch ($processor) {
                case 'stripe':
                    return $this->handleStripeWebhook($request);
                    
                case 'paypal':
                    return $this->handlePayPalWebhook($request);
                    
                default:
                    Log::warning('Processeur de paiement inconnu', ['processor' => $processor]);
                    return response()->json(['error' => 'Unknown processor'], 400);
            }

        } catch (\Exception $e) {
            Log::error('Erreur traitement webhook paiement', [
                'processor' => $processor,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Webhook pour les événements de tokenisation
     */
    public function handleTokenizationWebhook(Request $request)
    {
        try {
            $signature = $request->header('X-Evervault-Signature');
            $payload = $request->getContent();
            
            if (!$this->verifyWebhookSignature($payload, $signature)) {
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $data = $request->json()->all();

            Log::info('Webhook tokenisation reçu', [
                'event_type' => $data['event_type'] ?? 'unknown'
            ]);

            switch ($data['event_type']) {
                case 'token.created':
                    $this->handleTokenCreated($data);
                    break;
                    
                case 'token.expired':
                    $this->handleTokenExpired($data);
                    break;
                    
                case 'token.revoked':
                    $this->handleTokenRevoked($data);
                    break;
                    
                default:
                    Log::info('Événement de tokenisation non géré', [
                        'event_type' => $data['event_type']
                    ]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Erreur traitement webhook tokenisation', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Vérifier la signature du webhook
     */
    private function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }

        $webhookSecret = config('evervault.webhook_secret');
        if (!$webhookSecret) {
            Log::warning('Secret webhook Evervault non configuré');
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Traiter la completion d'une requête Relay
     */
    private function handleRelayRequestCompleted(array $data): void
    {
        Log::info('Requête Relay complétée', [
            'request_id' => $data['request_id'] ?? null,
            'destination' => $data['destination'] ?? null,
            'duration_ms' => $data['duration_ms'] ?? null
        ]);
        
        // Mettre à jour les métriques de performance si nécessaire
        $this->updateRelayMetrics($data, 'completed');
    }

    /**
     * Traiter l'échec d'une requête Relay
     */
    private function handleRelayRequestFailed(array $data): void
    {
        Log::warning('Requête Relay échouée', [
            'request_id' => $data['request_id'] ?? null,
            'destination' => $data['destination'] ?? null,
            'error' => $data['error'] ?? null,
            'error_code' => $data['error_code'] ?? null
        ]);
        
        // Mettre à jour les métriques d'erreur
        $this->updateRelayMetrics($data, 'failed');
        
        // Notifier les administrateurs si nécessaire
        if ($this->isHighPriorityError($data)) {
            $this->notifyAdministrators($data);
        }
    }

    /**
     * Traiter la completion d'un chiffrement
     */
    private function handleEncryptionCompleted(array $data): void
    {
        Log::info('Chiffrement Evervault complété', [
            'token_id' => $data['token_id'] ?? null,
            'data_type' => $data['data_type'] ?? null
        ]);
    }

    /**
     * Traiter la completion d'un déchiffrement
     */
    private function handleDecryptionCompleted(array $data): void
    {
        Log::info('Déchiffrement Evervault complété', [
            'token_id' => $data['token_id'] ?? null,
            'function_name' => $data['function_name'] ?? null
        ]);
    }

    /**
     * Traiter les webhooks Stripe
     */
    private function handleStripeWebhook(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->json()->all();
        $eventType = $data['type'] ?? 'unknown';
        
        Log::info('Webhook Stripe reçu', [
            'event_type' => $eventType,
            'event_id' => $data['id'] ?? null
        ]);
        
        switch ($eventType) {
            case 'payment_intent.succeeded':
                $this->handleStripePaymentSucceeded($data);
                break;
                
            case 'payment_intent.payment_failed':
                $this->handleStripePaymentFailed($data);
                break;
                
            case 'charge.dispute.created':
                $this->handleStripeDisputeCreated($data);
                break;
                
            case 'invoice.payment_succeeded':
                $this->handleStripeInvoicePaymentSucceeded($data);
                break;
                
            default:
                Log::info('Événement Stripe non géré', ['event_type' => $eventType]);
        }
        
        return response()->json(['status' => 'success']);
    }

    /**
     * Traiter les webhooks PayPal
     */
    private function handlePayPalWebhook(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->json()->all();
        $eventType = $data['event_type'] ?? 'unknown';
        
        Log::info('Webhook PayPal reçu', [
            'event_type' => $eventType,
            'event_id' => $data['id'] ?? null
        ]);
        
        switch ($eventType) {
            case 'PAYMENT.CAPTURE.COMPLETED':
                $this->handlePayPalPaymentCompleted($data);
                break;
                
            case 'PAYMENT.CAPTURE.DENIED':
                $this->handlePayPalPaymentDenied($data);
                break;
                
            case 'PAYMENT.CAPTURE.REFUNDED':
                $this->handlePayPalPaymentRefunded($data);
                break;
                
            case 'CHECKOUT.ORDER.APPROVED':
                $this->handlePayPalOrderApproved($data);
                break;
                
            default:
                Log::info('Événement PayPal non géré', ['event_type' => $eventType]);
        }
        
        return response()->json(['status' => 'success']);
    }

    /**
     * Traiter la création d'un token
     */
    private function handleTokenCreated(array $data): void
    {
        $tokenId = $data['token_id'] ?? null;
        
        if ($tokenId) {
            // Mettre à jour le statut du token dans la base de données
            EvervaultCardToken::where('evervault_token_id', $tokenId)
                ->update(['status' => 'active']);
                
            Log::info('Token Evervault activé', ['token_id' => $tokenId]);
        }
    }

    /**
     * Traiter l'expiration d'un token
     */
    private function handleTokenExpired(array $data): void
    {
        $tokenId = $data['token_id'] ?? null;
        
        if ($tokenId) {
            // Mettre à jour le statut du token
            EvervaultCardToken::where('evervault_token_id', $tokenId)
                ->update([
                    'status' => 'expired',
                    'expired_at' => now()
                ]);
                
            Log::info('Token Evervault expiré', ['token_id' => $tokenId]);
        }
    }

    /**
     * Traiter la révocation d'un token
     */
    private function handleTokenRevoked(array $data): void
    {
        $tokenId = $data['token_id'] ?? null;
        
        if ($tokenId) {
            // Mettre à jour le statut du token
            EvervaultCardToken::where('evervault_token_id', $tokenId)
                ->update([
                    'status' => 'revoked',
                    'revoked_at' => now()
                ]);
                
            Log::info('Token Evervault révoqué', ['token_id' => $tokenId]);
        }
    }

    /**
     * Traiter le succès d'un paiement Stripe
     */
    private function handleStripePaymentSucceeded(array $data): void
    {
        $paymentIntentId = $data['data']['object']['id'] ?? null;
        
        if ($paymentIntentId) {
            $transaction = PaymentTransaction::where('processor_transaction_id', $paymentIntentId)
                ->first();
                
            if ($transaction) {
                $transaction->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                    'processor_response' => array_merge(
                        $transaction->processor_response ?? [],
                        ['webhook_data' => $data]
                    )
                ]);
                
                // Envoyer notification de succès
                $this->notificationService->sendPaymentCompletedNotification($transaction);
            }
        }
    }

    /**
     * Traiter l'échec d'un paiement Stripe
     */
    private function handleStripePaymentFailed(array $data): void
    {
        $paymentIntentId = $data['data']['object']['id'] ?? null;
        
        if ($paymentIntentId) {
            $transaction = PaymentTransaction::where('processor_transaction_id', $paymentIntentId)
                ->first();
                
            if ($transaction) {
                $transaction->update([
                    'status' => 'failed',
                    'failure_reason' => $data['data']['object']['last_payment_error']['message'] ?? 'Payment failed',
                    'processor_response' => array_merge(
                        $transaction->processor_response ?? [],
                        ['webhook_data' => $data]
                    )
                ]);
                
                // Envoyer notification d'échec
                $this->notificationService->sendPaymentFailedNotification($transaction);
            }
        }
    }

    /**
     * Traiter la création d'un litige Stripe
     */
    private function handleStripeDisputeCreated(array $data): void
    {
        $chargeId = $data['data']['object']['charge'] ?? null;
        
        Log::warning('Litige Stripe créé', [
            'charge_id' => $chargeId,
            'amount' => $data['data']['object']['amount'] ?? null,
            'reason' => $data['data']['object']['reason'] ?? null
        ]);
        
        // Notifier les administrateurs
        $this->notifyAdministrators([
            'type' => 'stripe_dispute',
            'data' => $data
        ]);
    }

    /**
     * Traiter la completion d'un paiement PayPal
     */
    private function handlePayPalPaymentCompleted(array $data): void
    {
        $captureId = $data['resource']['id'] ?? null;
        
        if ($captureId) {
            $transaction = PaymentTransaction::where('processor_transaction_id', $captureId)
                ->first();
                
            if ($transaction) {
                $transaction->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                    'processor_response' => array_merge(
                        $transaction->processor_response ?? [],
                        ['webhook_data' => $data]
                    )
                ]);
                
                // Envoyer notification de succès
                $this->notificationService->sendPaymentCompletedNotification($transaction);
            }
        }
    }

    /**
     * Traiter le refus d'un paiement PayPal
     */
    private function handlePayPalPaymentDenied(array $data): void
    {
        $captureId = $data['resource']['id'] ?? null;
        
        if ($captureId) {
            $transaction = PaymentTransaction::where('processor_transaction_id', $captureId)
                ->first();
                
            if ($transaction) {
                $transaction->update([
                    'status' => 'failed',
                    'failure_reason' => 'Payment denied by PayPal',
                    'processor_response' => array_merge(
                        $transaction->processor_response ?? [],
                        ['webhook_data' => $data]
                    )
                ]);
                
                // Envoyer notification d'échec
                $this->notificationService->sendPaymentFailedNotification($transaction);
            }
        }
    }

    /**
     * Traiter le remboursement PayPal
     */
    private function handlePayPalPaymentRefunded(array $data): void
    {
        $refundId = $data['resource']['id'] ?? null;
        
        Log::info('Remboursement PayPal traité', [
            'refund_id' => $refundId,
            'amount' => $data['resource']['amount'] ?? null
        ]);
    }

    /**
     * Traiter l'approbation d'une commande PayPal
     */
    private function handlePayPalOrderApproved(array $data): void
    {
        $orderId = $data['resource']['id'] ?? null;
        
        Log::info('Commande PayPal approuvée', [
            'order_id' => $orderId
        ]);
    }

    /**
     * Mettre à jour les métriques Relay
     */
    private function updateRelayMetrics(array $data, string $status): void
    {
        // Implémenter la logique de mise à jour des métriques
        // Peut utiliser Redis, base de données, ou service de métriques externe
    }

    /**
     * Vérifier si c'est une erreur de haute priorité
     */
    private function isHighPriorityError(array $data): bool
    {
        $errorCode = $data['error_code'] ?? null;
        $highPriorityErrors = ['ENCRYPTION_FAILED', 'DECRYPTION_FAILED', 'TOKEN_EXPIRED'];
        
        return in_array($errorCode, $highPriorityErrors);
    }

    /**
     * Notifier les administrateurs
     */
    private function notifyAdministrators(array $data): void
    {
        // Implémenter la logique de notification des administrateurs
        // Email, Slack, SMS, etc.
        Log::critical('Notification administrateur requise', $data);
    }
}

