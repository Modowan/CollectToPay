<?php

namespace App\Services\Evervault;

use App\Models\Tenant\PaymentRequest;
use App\Models\Tenant\EvervaultCardToken;
use App\Models\Tenant\AuditLog;
use App\Services\Evervault\EvervaultConfigService;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CardDataCollectionService
{
    private $evervaultConfig;
    private $notificationService;

    public function __construct(
        EvervaultConfigService $evervaultConfig,
        NotificationService $notificationService
    ) {
        $this->evervaultConfig = $evervaultConfig;
        $this->notificationService = $notificationService;
    }

    /**
     * Créer une demande de collecte de données bancaires
     */
    public function createPaymentRequest(array $data): PaymentRequest
    {
        DB::beginTransaction();
        
        try {
            $paymentRequest = PaymentRequest::create([
                'request_number' => PaymentRequest::generateRequestNumber(),
                'organization_id' => $data['organization_id'],
                'client_first_name' => $data['client_first_name'],
                'client_last_name' => $data['client_last_name'],
                'client_email' => $data['client_email'],
                'client_phone' => $data['client_phone'] ?? null,
                'max_amount' => $data['max_amount'],
                'currency' => $data['currency'] ?? 'EUR',
                'secure_token' => PaymentRequest::generateSecureToken(),
                'expires_at' => now()->addDays($data['expires_in_days'] ?? 7),
                'description' => $data['description'] ?? null,
                'metadata' => $data['metadata'] ?? []
            ]);

            // Enregistrer l'audit
            $this->logAuditEvent(
                $data['organization_id'],
                'payment_request_created',
                'payment_request',
                $paymentRequest->id,
                null,
                $paymentRequest->toArray()
            );

            // Envoyer la notification au client
            $this->notificationService->sendPaymentRequestNotification($paymentRequest);

            DB::commit();

            Log::info('Demande de paiement créée', [
                'request_id' => $paymentRequest->id,
                'request_number' => $paymentRequest->request_number,
                'organization_id' => $data['organization_id']
            ]);

            return $paymentRequest;

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erreur création demande de paiement', [
                'organization_id' => $data['organization_id'],
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Traiter les données de carte collectées via Evervault
     */
    public function processCollectedCardData(
        PaymentRequest $paymentRequest,
        array $cardData
    ): EvervaultCardToken {
        DB::beginTransaction();
        
        try {
            $evervault = $this->evervaultConfig->getEvervaultInstance($paymentRequest->organization_id);

            // Créer le token Evervault avec les données de carte
            $evervaultToken = $evervault->createToken([
                'card' => [
                    'number' => $cardData['card_number'],
                    'expMonth' => $cardData['exp_month'],
                    'expYear' => $cardData['exp_year'],
                    'cvc' => $cardData['cvc']
                ],
                'cardholder' => [
                    'name' => $cardData['cardholder_name']
                ],
                'metadata' => [
                    'payment_request_id' => $paymentRequest->id,
                    'organization_id' => $paymentRequest->organization_id,
                    'created_at' => now()->toISOString()
                ]
            ]);

            // Sauvegarder le token dans la base de données
            $cardToken = EvervaultCardToken::create([
                'payment_request_id' => $paymentRequest->id,
                'evervault_token_id' => $evervaultToken['id'],
                'card_mask' => $this->generateCardMask($cardData['card_number']),
                'card_type' => $this->detectCardType($cardData['card_number']),
                'expiry_month' => $cardData['exp_month'],
                'expiry_year' => $cardData['exp_year'],
                'cardholder_name' => $cardData['cardholder_name'],
                'is_active' => true,
                'metadata' => [
                    'collection_ip' => request()->ip(),
                    'collection_user_agent' => request()->userAgent(),
                    'collection_timestamp' => now()->toISOString()
                ]
            ]);

            // Marquer la demande comme complétée
            $paymentRequest->markAsCompleted();

            // Effectuer la vérification 3D Secure si activée
            if ($this->evervaultConfig->isFeatureEnabled($paymentRequest->organization_id, '3ds')) {
                $this->perform3DSVerification($cardToken, $paymentRequest);
            }

            // Effectuer la pré-autorisation si activée
            if ($this->evervaultConfig->isFeatureEnabled($paymentRequest->organization_id, 'preauth')) {
                $this->performPreAuthorization($cardToken, $paymentRequest);
            }

            // Enregistrer l'audit
            $this->logAuditEvent(
                $paymentRequest->organization_id,
                'card_data_collected',
                'evervault_card_token',
                $cardToken->id,
                null,
                [
                    'card_mask' => $cardToken->card_mask,
                    'card_type' => $cardToken->card_type,
                    'payment_request_id' => $paymentRequest->id
                ]
            );

            // Notifier l'organisation
            $this->notificationService->sendCardDataCollectedNotification($paymentRequest, $cardToken);

            DB::commit();

            Log::info('Données de carte collectées avec succès', [
                'payment_request_id' => $paymentRequest->id,
                'card_token_id' => $cardToken->id,
                'evervault_token_id' => $evervaultToken['id']
            ]);

            return $cardToken;

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erreur traitement données de carte', [
                'payment_request_id' => $paymentRequest->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Effectuer la vérification 3D Secure
     */
    private function perform3DSVerification(
        EvervaultCardToken $cardToken,
        PaymentRequest $paymentRequest
    ): void {
        try {
            $evervault = $this->evervaultConfig->getEvervaultInstance($paymentRequest->organization_id);

            // Utiliser une Evervault Function pour la vérification 3DS
            $result = $evervault->run('verify-3ds', [
                'token_id' => $cardToken->evervault_token_id,
                'amount' => $paymentRequest->max_amount,
                'currency' => $paymentRequest->currency,
                'return_url' => route('payment.3ds.return', ['token' => $cardToken->id]),
                'merchant_info' => [
                    'name' => $paymentRequest->organization->name,
                    'url' => config('app.url')
                ]
            ]);

            if ($result['success']) {
                $cardToken->mark3DSVerified();
                
                Log::info('Vérification 3DS réussie', [
                    'card_token_id' => $cardToken->id,
                    'transaction_id' => $result['transaction_id'] ?? null
                ]);
            } else {
                Log::warning('Échec vérification 3DS', [
                    'card_token_id' => $cardToken->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur vérification 3DS', [
                'card_token_id' => $cardToken->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Effectuer la pré-autorisation
     */
    private function performPreAuthorization(
        EvervaultCardToken $cardToken,
        PaymentRequest $paymentRequest
    ): void {
        try {
            $evervault = $this->evervaultConfig->getEvervaultInstance($paymentRequest->organization_id);

            // Utiliser une Evervault Function pour la pré-autorisation
            $result = $evervault->run('preauthorize-card', [
                'token_id' => $cardToken->evervault_token_id,
                'amount' => $paymentRequest->max_amount,
                'currency' => $paymentRequest->currency,
                'description' => 'Pré-autorisation pour ' . $paymentRequest->request_number
            ]);

            if ($result['success']) {
                $cardToken->markPreAuthDone(
                    $paymentRequest->max_amount,
                    $result['preauth_reference']
                );
                
                Log::info('Pré-autorisation réussie', [
                    'card_token_id' => $cardToken->id,
                    'preauth_reference' => $result['preauth_reference'],
                    'amount' => $paymentRequest->max_amount
                ]);
            } else {
                Log::warning('Échec pré-autorisation', [
                    'card_token_id' => $cardToken->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur pré-autorisation', [
                'card_token_id' => $cardToken->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Générer un masque de carte pour l'affichage
     */
    private function generateCardMask(string $cardNumber): string
    {
        $cleaned = preg_replace('/\D/', '', $cardNumber);
        return '**** **** **** ' . substr($cleaned, -4);
    }

    /**
     * Détecter le type de carte
     */
    private function detectCardType(string $cardNumber): string
    {
        $cleaned = preg_replace('/\D/', '', $cardNumber);
        
        $patterns = [
            'visa' => '/^4/',
            'mastercard' => '/^5[1-5]/',
            'amex' => '/^3[47]/',
            'discover' => '/^6(?:011|5)/',
            'diners' => '/^3[0689]/',
            'jcb' => '/^35/'
        ];
        
        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $cleaned)) {
                return $type;
            }
        }
        
        return 'unknown';
    }

    /**
     * Enregistrer un événement d'audit
     */
    private function logAuditEvent(
        int $organizationId,
        string $action,
        string $entityType,
        int $entityId,
        ?array $oldValues,
        ?array $newValues
    ): void {
        try {
            AuditLog::create([
                'organization_id' => $organizationId,
                'user_id' => auth()->id(),
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur enregistrement audit', [
                'organization_id' => $organizationId,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }
}