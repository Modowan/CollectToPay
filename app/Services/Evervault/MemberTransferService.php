<?php

namespace App\Services\Evervault;

use App\Models\Tenant\MemberTransfer;
use App\Models\Tenant\EvervaultCardToken;
use App\Models\Organization;
use App\Services\Evervault\EvervaultConfigService;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberTransferService
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
     * Créer un transfert entre membres avec données de carte
     */
    public function createMemberTransfer(array $data): MemberTransfer
    {
        DB::beginTransaction();
        
        try {
            // Valider que les organisations existent et sont actives
            $senderOrg = Organization::findOrFail($data['sender_organization_id']);
            $recipientOrg = Organization::findOrFail($data['recipient_organization_id']);

            if (!$senderOrg->is_active || !$recipientOrg->is_active) {
                throw new \Exception('Une des organisations n\'est pas active');
            }

            // Créer le transfert
            $transfer = MemberTransfer::create([
                'transfer_number' => MemberTransfer::generateTransferNumber(),
                'sender_organization_id' => $data['sender_organization_id'],
                'recipient_organization_id' => $data['recipient_organization_id'],
                'cardholder_first_name' => $data['cardholder_first_name'],
                'cardholder_last_name' => $data['cardholder_last_name'],
                'cardholder_phone' => $data['cardholder_phone'],
                'cardholder_email' => $data['cardholder_email'],
                'max_amount' => $data['max_amount'],
                'currency' => $data['currency'] ?? 'EUR',
                'notes' => $data['notes'] ?? null,
                'expires_at' => isset($data['expires_in_days']) 
                    ? now()->addDays($data['expires_in_days']) 
                    : null
            ]);

            // Traiter les données de carte si fournies
            if (isset($data['card_data'])) {
                $this->processCardDataForTransfer($transfer, $data['card_data']);
            }

            DB::commit();

            Log::info('Transfert entre membres créé', [
                'transfer_id' => $transfer->id,
                'transfer_number' => $transfer->transfer_number,
                'sender_org' => $data['sender_organization_id'],
                'recipient_org' => $data['recipient_organization_id']
            ]);

            return $transfer;

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erreur création transfert membre', [
                'sender_org' => $data['sender_organization_id'] ?? null,
                'recipient_org' => $data['recipient_organization_id'] ?? null,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Traiter les données de carte pour un transfert
     */
    public function processCardDataForTransfer(
        MemberTransfer $transfer,
        array $cardData
    ): EvervaultCardToken {
        try {
            // Utiliser la configuration Evervault de l'organisation expéditrice
            $evervault = $this->evervaultConfig->getEvervaultInstance($transfer->sender_organization_id);

            // Créer le token Evervault
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
                    'member_transfer_id' => $transfer->id,
                    'sender_organization_id' => $transfer->sender_organization_id,
                    'recipient_organization_id' => $transfer->recipient_organization_id,
                    'created_at' => now()->toISOString()
                ]
            ]);

            // Sauvegarder le token
            $cardToken = EvervaultCardToken::create([
                'member_transfer_id' => $transfer->id,
                'evervault_token_id' => $evervaultToken['id'],
                'card_mask' => $this->generateCardMask($cardData['card_number']),
                'card_type' => $this->detectCardType($cardData['card_number']),
                'expiry_month' => $cardData['exp_month'],
                'expiry_year' => $cardData['exp_year'],
                'cardholder_name' => $cardData['cardholder_name'],
                'is_active' => true,
                'metadata' => [
                    'transfer_context' => 'member_to_member',
                    'collection_timestamp' => now()->toISOString()
                ]
            ]);

            // Effectuer la vérification 3DS si activée pour l'expéditeur
            if ($this->evervaultConfig->isFeatureEnabled($transfer->sender_organization_id, '3ds')) {
                $this->perform3DSForTransfer($cardToken, $transfer);
            }

            // Effectuer la pré-autorisation si activée
            if ($this->evervaultConfig->isFeatureEnabled($transfer->sender_organization_id, 'preauth')) {
                $this->performPreAuthForTransfer($cardToken, $transfer);
            }

            // Marquer le transfert comme transféré
            $transfer->markAsTransferred();

            // Notifier les deux parties
            $this->notificationService->sendTransferCreatedNotification($transfer, $cardToken);

            Log::info('Données de carte traitées pour transfert', [
                'transfer_id' => $transfer->id,
                'card_token_id' => $cardToken->id,
                'evervault_token_id' => $evervaultToken['id']
            ]);

            return $cardToken;

        } catch (\Exception $e) {
            Log::error('Erreur traitement carte pour transfert', [
                'transfer_id' => $transfer->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Transférer l'accès au token vers l'organisation destinataire
     */
    public function transferTokenAccess(MemberTransfer $transfer): bool
    {
        try {
            if (!$transfer->cardToken) {
                throw new \Exception('Aucun token de carte associé au transfert');
            }

            $senderEvervault = $this->evervaultConfig->getEvervaultInstance($transfer->sender_organization_id);
            $recipientEvervault = $this->evervaultConfig->getEvervaultInstance($transfer->recipient_organization_id);

            // Utiliser une Evervault Function pour transférer l'accès
            $result = $senderEvervault->run('transfer-token-access', [
                'token_id' => $transfer->cardToken->evervault_token_id,
                'recipient_app_id' => $recipientEvervault->getAppId(),
                'transfer_metadata' => [
                    'transfer_id' => $transfer->id,
                    'transfer_number' => $transfer->transfer_number,
                    'max_amount' => $transfer->max_amount,
                    'currency' => $transfer->currency
                ]
            ]);

            if ($result['success']) {
                // Mettre à jour les métadonnées du token
                $transfer->cardToken->update([
                    'metadata' => array_merge(
                        $transfer->cardToken->metadata ?? [],
                        [
                            'transferred_at' => now()->toISOString(),
                            'recipient_access_granted' => true,
                            'transfer_reference' => $result['transfer_reference'] ?? null
                        ]
                    )
                ]);

                // Notifier le destinataire
                $this->notificationService->sendTokenAccessGrantedNotification($transfer);

                Log::info('Accès au token transféré avec succès', [
                    'transfer_id' => $transfer->id,
                    'card_token_id' => $transfer->cardToken->id,
                    'transfer_reference' => $result['transfer_reference'] ?? null
                ]);

                return true;
            } else {
                Log::error('Échec transfert accès token', [
                    'transfer_id' => $transfer->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);

                return false;
            }

        } catch (\Exception $e) {
            Log::error('Erreur transfert accès token', [
                'transfer_id' => $transfer->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Vérifier si un membre peut utiliser un transfert
     */
    public function canMemberUseTransfer(MemberTransfer $transfer, int $organizationId): bool
    {
        // Vérifier que l'organisation est le destinataire
        if ($transfer->recipient_organization_id !== $organizationId) {
            return false;
        }

        // Vérifier que le transfert est dans le bon état
        if (!$transfer->canBeUsed()) {
            return false;
        }

        // Vérifier que le token de carte existe et est actif
        if (!$transfer->cardToken || !$transfer->cardToken->canBeUsed()) {
            return false;
        }

        return true;
    }

    /**
     * Obtenir les transferts disponibles pour une organisation
     */
    public function getAvailableTransfersForOrganization(int $organizationId): \Illuminate\Database\Eloquent\Collection
    {
        return MemberTransfer::where('recipient_organization_id', $organizationId)
            ->usable()
            ->with(['cardToken', 'senderOrganization'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Effectuer la vérification 3DS pour un transfert
     */
    private function perform3DSForTransfer(EvervaultCardToken $cardToken, MemberTransfer $transfer): void
    {
        try {
            $evervault = $this->evervaultConfig->getEvervaultInstance($transfer->sender_organization_id);

            $result = $evervault->run('verify-3ds', [
                'token_id' => $cardToken->evervault_token_id,
                'amount' => $transfer->max_amount,
                'currency' => $transfer->currency,
                'return_url' => route('member.transfer.3ds.return', ['transfer' => $transfer->id]),
                'merchant_info' => [
                    'name' => $transfer->senderOrganization->name,
                    'url' => config('app.url')
                ]
            ]);

            if ($result['success']) {
                $cardToken->mark3DSVerified();
                
                Log::info('Vérification 3DS réussie pour transfert', [
                    'transfer_id' => $transfer->id,
                    'card_token_id' => $cardToken->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur vérification 3DS pour transfert', [
                'transfer_id' => $transfer->id,
                'card_token_id' => $cardToken->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Effectuer la pré-autorisation pour un transfert
     */
    private function performPreAuthForTransfer(EvervaultCardToken $cardToken, MemberTransfer $transfer): void
    {
        try {
            $evervault = $this->evervaultConfig->getEvervaultInstance($transfer->sender_organization_id);

            $result = $evervault->run('preauthorize-card', [
                'token_id' => $cardToken->evervault_token_id,
                'amount' => $transfer->max_amount,
                'currency' => $transfer->currency,
                'description' => 'Pré-autorisation pour transfert ' . $transfer->transfer_number
            ]);

            if ($result['success']) {
                $cardToken->markPreAuthDone(
                    $transfer->max_amount,
                    $result['preauth_reference']
                );
                
                Log::info('Pré-autorisation réussie pour transfert', [
                    'transfer_id' => $transfer->id,
                    'card_token_id' => $cardToken->id,
                    'preauth_reference' => $result['preauth_reference']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur pré-autorisation pour transfert', [
                'transfer_id' => $transfer->id,
                'card_token_id' => $cardToken->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Méthodes utilitaires pour les cartes
     */
    private function generateCardMask(string $cardNumber): string
    {
        $cleaned = preg_replace('/\D/', '', $cardNumber);
        return '**** **** **** ' . substr($cleaned, -4);
    }

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
}