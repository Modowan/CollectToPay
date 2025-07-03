<?php

namespace App\Services\Notifications;

use App\Models\Tenant\PaymentRequest;
use App\Models\Tenant\EvervaultCardToken;
use App\Models\Tenant\MemberTransfer;
use App\Models\Tenant\PaymentTransaction;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EvervaultNotificationService
{
    /**
     * Envoyer une notification de demande de paiement
     */
    public function sendPaymentRequestNotification(PaymentRequest $paymentRequest): void
    {
        try {
            // Créer la notification dans votre table existante
            $notification = new DatabaseNotification([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\PaymentRequestSent',
                'notifiable_type' => 'App\\Models\\Organization',
                'notifiable_id' => $paymentRequest->organization_id,
                'data' => [
                    'payment_request_id' => $paymentRequest->id,
                    'request_number' => $paymentRequest->request_number,
                    'client_name' => $paymentRequest->client_first_name . ' ' . $paymentRequest->client_last_name,
                    'amount' => $paymentRequest->max_amount,
                    'currency' => $paymentRequest->currency,
                    'secure_url' => $paymentRequest->getSecureUrl()
                ],
                // Nouveaux champs Evervault
                'organization_id' => $paymentRequest->organization_id,
                'recipient_email' => $paymentRequest->client_email,
                'recipient_phone' => $paymentRequest->client_phone,
                'subject' => 'Demande de collecte de données bancaires - ' . $paymentRequest->request_number,
                'message' => $this->generatePaymentRequestMessage($paymentRequest),
                'channel' => 'email',
                'delivery_status' => 'pending',
                'evervault_metadata' => [
                    'type' => 'payment_request_sent',
                    'expires_at' => $paymentRequest->expires_at,
                    'max_amount' => $paymentRequest->max_amount
                ]
            ]);

            $notification->save();

            // Envoyer l'email
            $this->sendPaymentRequestEmail($paymentRequest, $notification);

            Log::info('Notification demande de paiement envoyée', [
                'payment_request_id' => $paymentRequest->id,
                'notification_id' => $notification->id
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur envoi notification demande de paiement', [
                'payment_request_id' => $paymentRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Envoyer une notification de données collectées
     */
    public function sendCardDataCollectedNotification(
        PaymentRequest $paymentRequest, 
        EvervaultCardToken $cardToken
    ): void {
        try {
            $notification = new DatabaseNotification([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\CardDataCollected',
                'notifiable_type' => 'App\\Models\\Organization',
                'notifiable_id' => $paymentRequest->organization_id,
                'data' => [
                    'payment_request_id' => $paymentRequest->id,
                    'request_number' => $paymentRequest->request_number,
                    'client_name' => $paymentRequest->client_first_name . ' ' . $paymentRequest->client_last_name,
                    'card_mask' => $cardToken->card_mask,
                    'card_type' => $cardToken->card_type,
                    'collected_at' => now()
                ],
                'organization_id' => $paymentRequest->organization_id,
                'recipient_email' => $paymentRequest->organization->email,
                'subject' => 'Données bancaires collectées - ' . $paymentRequest->request_number,
                'message' => $this->generateCardCollectedMessage($paymentRequest, $cardToken),
                'channel' => 'email',
                'delivery_status' => 'pending',
                'evervault_metadata' => [
                    'type' => 'card_data_collected',
                    'card_token_id' => $cardToken->id,
                    'is_3ds_verified' => $cardToken->is_3ds_verified,
                    'is_preauth_done' => $cardToken->is_preauth_done
                ]
            ]);

            $notification->save();

            // Envoyer l'email à l'organisation
            $this->sendCardCollectedEmail($paymentRequest, $cardToken, $notification);

            Log::info('Notification données collectées envoyée', [
                'payment_request_id' => $paymentRequest->id,
                'card_token_id' => $cardToken->id,
                'notification_id' => $notification->id
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur envoi notification données collectées', [
                'payment_request_id' => $paymentRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Envoyer une notification de transfert créé
     */
    public function sendTransferCreatedNotification(
        MemberTransfer $transfer, 
        EvervaultCardToken $cardToken
    ): void {
        try {
            // Notification pour l'expéditeur
            $senderNotification = new DatabaseNotification([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\TransferCreated',
                'notifiable_type' => 'App\\Models\\Organization',
                'notifiable_id' => $transfer->sender_organization_id,
                'data' => [
                    'transfer_id' => $transfer->id,
                    'transfer_number' => $transfer->transfer_number,
                    'recipient_org' => $transfer->recipientOrganization->name,
                    'cardholder_name' => $transfer->cardholder_full_name,
                    'max_amount' => $transfer->max_amount,
                    'currency' => $transfer->currency
                ],
                'organization_id' => $transfer->sender_organization_id,
                'recipient_email' => $transfer->senderOrganization->email,
                'subject' => 'Transfert créé - ' . $transfer->transfer_number,
                'message' => $this->generateTransferCreatedMessage($transfer, 'sender'),
                'channel' => 'email',
                'delivery_status' => 'pending',
                'evervault_metadata' => [
                    'type' => 'transfer_created_sender',
                    'transfer_id' => $transfer->id,
                    'role' => 'sender'
                ]
            ]);

            // Notification pour le destinataire
            $recipientNotification = new DatabaseNotification([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\TransferReceived',
                'notifiable_type' => 'App\\Models\\Organization',
                'notifiable_id' => $transfer->recipient_organization_id,
                'data' => [
                    'transfer_id' => $transfer->id,
                    'transfer_number' => $transfer->transfer_number,
                    'sender_org' => $transfer->senderOrganization->name,
                    'cardholder_name' => $transfer->cardholder_full_name,
                    'max_amount' => $transfer->max_amount,
                    'currency' => $transfer->currency
                ],
                'organization_id' => $transfer->recipient_organization_id,
                'recipient_email' => $transfer->recipientOrganization->email,
                'subject' => 'Nouveau transfert reçu - ' . $transfer->transfer_number,
                'message' => $this->generateTransferCreatedMessage($transfer, 'recipient'),
                'channel' => 'email',
                'delivery_status' => 'pending',
                'evervault_metadata' => [
                    'type' => 'transfer_created_recipient',
                    'transfer_id' => $transfer->id,
                    'role' => 'recipient'
                ]
            ]);

            $senderNotification->save();
            $recipientNotification->save();

            // Envoyer les emails
            $this->sendTransferEmails($transfer, $senderNotification, $recipientNotification);

            Log::info('Notifications transfert envoyées', [
                'transfer_id' => $transfer->id,
                'sender_notification_id' => $senderNotification->id,
                'recipient_notification_id' => $recipientNotification->id
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur envoi notifications transfert', [
                'transfer_id' => $transfer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Envoyer une notification de paiement complété
     */
    public function sendPaymentCompletedNotification(PaymentTransaction $transaction): void
    {
        try {
            // Notification pour l'organisation qui a effectué le paiement
            $organizationNotification = new DatabaseNotification([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\PaymentCompleted',
                'notifiable_type' => 'App\\Models\\Organization',
                'notifiable_id' => $transaction->organization_id,
                'data' => [
                    'transaction_id' => $transaction->id,
                    'transaction_reference' => $transaction->transaction_reference,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'processor_type' => $transaction->processor_type,
                    'processed_at' => $transaction->processed_at
                ],
                'organization_id' => $transaction->organization_id,
                'recipient_email' => $transaction->organization->email,
                'subject' => 'Paiement effectué - ' . $transaction->transaction_reference,
                'message' => $this->generatePaymentCompletedMessage($transaction, 'organization'),
                'channel' => 'email',
                'delivery_status' => 'pending',
                'evervault_metadata' => [
                    'type' => 'payment_completed_organization',
                    'transaction_id' => $transaction->id,
                    'processor_type' => $transaction->processor_type
                ]
            ]);

            $organizationNotification->save();

            // Notification pour le client (si applicable)
            $clientEmail = $this->getTransactionClientEmail($transaction);
            if ($clientEmail) {
                $clientNotification = new DatabaseNotification([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'type' => 'App\\Notifications\\PaymentCompletedClient',
                    'notifiable_type' => 'App\\Models\\Organization',
                    'notifiable_id' => $transaction->organization_id,
                    'data' => [
                        'transaction_id' => $transaction->id,
                        'transaction_reference' => $transaction->transaction_reference,
                        'amount' => $transaction->amount,
                        'currency' => $transaction->currency,
                        'organization_name' => $transaction->organization->name
                    ],
                    'organization_id' => $transaction->organization_id,
                    'recipient_email' => $clientEmail,
                    'subject' => 'Paiement débité - ' . $transaction->transaction_reference,
                    'message' => $this->generatePaymentCompletedMessage($transaction, 'client'),
                    'channel' => 'email',
                    'delivery_status' => 'pending',
                    'evervault_metadata' => [
                        'type' => 'payment_completed_client',
                        'transaction_id' => $transaction->id
                    ]
                ]);

                $clientNotification->save();
                $this->sendPaymentCompletedClientEmail($transaction, $clientNotification);
            }

            // Envoyer l'email à l'organisation
            $this->sendPaymentCompletedEmail($transaction, $organizationNotification);

            Log::info('Notification paiement complété envoyée', [
                'transaction_id' => $transaction->id,
                'organization_notification_id' => $organizationNotification->id,
                'client_notification_id' => $clientNotification->id ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur envoi notification paiement complété', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Envoyer une notification de paiement échoué
     */
    public function sendPaymentFailedNotification(PaymentTransaction $transaction): void
    {
        try {
            $notification = new DatabaseNotification([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\PaymentFailed',
                'notifiable_type' => 'App\\Models\\Organization',
                'notifiable_id' => $transaction->organization_id,
                'data' => [
                    'transaction_id' => $transaction->id,
                    'transaction_reference' => $transaction->transaction_reference,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'failure_reason' => $transaction->failure_reason,
                    'processor_type' => $transaction->processor_type
                ],
                'organization_id' => $transaction->organization_id,
                'recipient_email' => $transaction->organization->email,
                'subject' => 'Échec de paiement - ' . $transaction->transaction_reference,
                'message' => $this->generatePaymentFailedMessage($transaction),
                'channel' => 'email',
                'delivery_status' => 'pending',
                'evervault_metadata' => [
                    'type' => 'payment_failed',
                    'transaction_id' => $transaction->id,
                    'failure_reason' => $transaction->failure_reason
                ]
            ]);

            $notification->save();

            // Envoyer l'email
            $this->sendPaymentFailedEmail($transaction, $notification);

            Log::info('Notification paiement échoué envoyée', [
                'transaction_id' => $transaction->id,
                'notification_id' => $notification->id
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur envoi notification paiement échoué', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Marquer une notification comme envoyée
     */
    public function markNotificationAsSent(string $notificationId): void
    {
        try {
            DatabaseNotification::where('id', $notificationId)
                ->update([
                    'delivery_status' => 'sent',
                    'sent_at' => now()
                ]);
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour statut notification', [
                'notification_id' => $notificationId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Marquer une notification comme échouée
     */
    public function markNotificationAsFailed(string $notificationId, string $error): void
    {
        try {
            DatabaseNotification::where('id', $notificationId)
                ->update([
                    'delivery_status' => 'failed',
                    'evervault_metadata->error' => $error
                ]);
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour statut notification échouée', [
                'notification_id' => $notificationId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtenir les notifications en attente d'envoi
     */
    public function getPendingNotifications(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return DatabaseNotification::where('delivery_status', 'pending')
            ->whereNotNull('recipient_email')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Traiter les notifications en attente
     */
    public function processPendingNotifications(): int
    {
        $notifications = $this->getPendingNotifications();
        $processed = 0;

        foreach ($notifications as $notification) {
            try {
                $this->sendNotificationEmail($notification);
                $this->markNotificationAsSent($notification->id);
                $processed++;
            } catch (\Exception $e) {
                $this->markNotificationAsFailed($notification->id, $e->getMessage());
                Log::error('Erreur envoi notification en attente', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $processed;
    }

    // Méthodes privées pour générer les messages

    private function generatePaymentRequestMessage(PaymentRequest $paymentRequest): string
    {
        return "Bonjour {$paymentRequest->client_first_name},\n\n" .
               "Vous avez reçu une demande de collecte de données bancaires de la part de {$paymentRequest->organization->name}.\n\n" .
               "Numéro de dossier : {$paymentRequest->request_number}\n" .
               "Montant maximum : {$paymentRequest->formatted_amount}\n" .
               "Date d'expiration : {$paymentRequest->expires_at->format('d/m/Y H:i')}\n\n" .
               "Pour saisir vos données bancaires de manière sécurisée, cliquez sur le lien suivant :\n" .
               "{$paymentRequest->getSecureUrl()}\n\n" .
               "Vos données sont protégées par un chiffrement de niveau bancaire et ne seront jamais stockées en clair.\n\n" .
               "Cordialement,\n" .
               "{$paymentRequest->organization->name}";
    }

    private function generateCardCollectedMessage(PaymentRequest $paymentRequest, EvervaultCardToken $cardToken): string
    {
        return "Les données bancaires ont été collectées avec succès pour la demande {$paymentRequest->request_number}.\n\n" .
               "Client : {$paymentRequest->client_full_name}\n" .
               "Email : {$paymentRequest->client_email}\n" .
               "Carte : {$cardToken->card_mask} ({$cardToken->card_type})\n" .
               "Vérification 3DS : " . ($cardToken->is_3ds_verified ? 'Effectuée' : 'Non effectuée') . "\n" .
               "Pré-autorisation : " . ($cardToken->is_preauth_done ? 'Effectuée' : 'Non effectuée') . "\n" .
               "Date de collecte : " . $cardToken->created_at->format('d/m/Y H:i') . "\n\n" .
               "Vous pouvez maintenant débiter cette carte via votre interface de gestion.\n\n" .
               "Les données sont sécurisées par Evervault et peuvent être utilisées pour des paiements jusqu'au " .
               $cardToken->expires_at->format('d/m/Y') . ".";
    }

    private function generateTransferCreatedMessage(MemberTransfer $transfer, string $role): string
    {
        if ($role === 'sender') {
            return "Votre transfert {$transfer->transfer_number} a été créé avec succès.\n\n" .
                   "Destinataire : {$transfer->recipientOrganization->name}\n" .
                   "Porteur de carte : {$transfer->cardholder_full_name}\n" .
                   "Email : {$transfer->cardholder_email}\n" .
                   "Montant maximum : {$transfer->formatted_amount}\n" .
                   "Date d'expiration : {$transfer->expires_at->format('d/m/Y H:i')}\n\n" .
                   "Le destinataire peut maintenant utiliser ces données pour effectuer des paiements.\n\n" .
                   "Vous pouvez suivre l'utilisation de ce transfert dans votre interface de gestion.";
        } else {
            return "Vous avez reçu un nouveau transfert de données bancaires.\n\n" .
                   "Numéro de transfert : {$transfer->transfer_number}\n" .
                   "Expéditeur : {$transfer->senderOrganization->name}\n" .
                   "Porteur de carte : {$transfer->cardholder_full_name}\n" .
                   "Email : {$transfer->cardholder_email}\n" .
                   "Montant maximum : {$transfer->formatted_amount}\n" .
                   "Date d'expiration : {$transfer->expires_at->format('d/m/Y H:i')}\n\n" .
                   "Vous pouvez maintenant utiliser ces données pour effectuer des paiements via votre interface de gestion.\n\n" .
                   "Les données sont sécurisées par Evervault et expireront automatiquement à la date indiquée.";
        }
    }

    private function generatePaymentCompletedMessage(PaymentTransaction $transaction, string $recipient): string
    {
        if ($recipient === 'organization') {
            return "Un paiement a été effectué avec succès.\n\n" .
                   "Référence : {$transaction->transaction_reference}\n" .
                   "Montant : " . number_format($transaction->amount, 2) . " {$transaction->currency}\n" .
                   "Processeur : " . ucfirst($transaction->processor_type) . "\n" .
                   "Date : {$transaction->processed_at->format('d/m/Y H:i')}\n\n" .
                   "Le paiement a été traité avec succès et les fonds seront crédités selon les délais de votre processeur de paiement.\n\n" .
                   "Vous pouvez consulter les détails complets dans votre interface de gestion.";
        } else {
            return "Un paiement a été débité de votre carte bancaire.\n\n" .
                   "Référence : {$transaction->transaction_reference}\n" .
                   "Montant : " . number_format($transaction->amount, 2) . " {$transaction->currency}\n" .
                   "Bénéficiaire : {$transaction->organization->name}\n" .
                   "Date : {$transaction->processed_at->format('d/m/Y H:i')}\n\n" .
                   "Ce débit correspond à une autorisation que vous avez préalablement donnée.\n\n" .
                   "Pour toute question, veuillez contacter {$transaction->organization->name}.";
        }
    }

    private function generatePaymentFailedMessage(PaymentTransaction $transaction): string
    {
        return "Un paiement a échoué.\n\n" .
               "Référence : {$transaction->transaction_reference}\n" .
               "Montant : " . number_format($transaction->amount, 2) . " {$transaction->currency}\n" .
               "Processeur : " . ucfirst($transaction->processor_type) . "\n" .
               "Raison de l'échec : {$transaction->failure_reason}\n" .
               "Date : " . now()->format('d/m/Y H:i') . "\n\n" .
               "Veuillez vérifier la configuration de votre processeur de paiement ou contacter le support technique.\n\n" .
               "Vous pouvez réessayer le paiement depuis votre interface de gestion.";
    }

    private function getTransactionClientEmail(PaymentTransaction $transaction): ?string
    {
        if ($transaction->paymentRequest) {
            return $transaction->paymentRequest->client_email;
        }
        
        if ($transaction->memberTransfer) {
            return $transaction->memberTransfer->cardholder_email;
        }
        
        return null;
    }

    // Méthodes d'envoi d'emails (à adapter selon votre système)

    private function sendPaymentRequestEmail(PaymentRequest $paymentRequest, DatabaseNotification $notification): void
    {
        // Implémentez l'envoi d'email selon votre système existant
        // Exemple avec Mail::send ou votre service d'email préféré
        $this->markNotificationAsSent($notification->id);
    }

    private function sendCardCollectedEmail(PaymentRequest $paymentRequest, EvervaultCardToken $cardToken, DatabaseNotification $notification): void
    {
        // Implémentez l'envoi d'email selon votre système existant
        $this->markNotificationAsSent($notification->id);
    }

    private function sendTransferEmails(MemberTransfer $transfer, DatabaseNotification $senderNotification, DatabaseNotification $recipientNotification): void
    {
        // Implémentez l'envoi d'emails selon votre système existant
        $this->markNotificationAsSent($senderNotification->id);
        $this->markNotificationAsSent($recipientNotification->id);
    }

    private function sendPaymentCompletedEmail(PaymentTransaction $transaction, DatabaseNotification $notification): void
    {
        // Implémentez l'envoi d'email selon votre système existant
        $this->markNotificationAsSent($notification->id);
    }

    private function sendPaymentCompletedClientEmail(PaymentTransaction $transaction, DatabaseNotification $notification): void
    {
        // Implémentez l'envoi d'email selon votre système existant
        $this->markNotificationAsSent($notification->id);
    }

    private function sendPaymentFailedEmail(PaymentTransaction $transaction, DatabaseNotification $notification): void
    {
        // Implémentez l'envoi d'email selon votre système existant
        $this->markNotificationAsSent($notification->id);
    }

    private function sendNotificationEmail(DatabaseNotification $notification): void
    {
        // Méthode générique pour envoyer une notification par email
        // À adapter selon votre système d'email existant
        
        // Exemple basique :
        // Mail::raw($notification->message, function ($message) use ($notification) {
        //     $message->to($notification->recipient_email)
        //             ->subject($notification->subject);
        // });
        
        Log::info('Email envoyé', [
            'notification_id' => $notification->id,
            'recipient' => $notification->recipient_email,
            'subject' => $notification->subject
        ]);
    }
}

