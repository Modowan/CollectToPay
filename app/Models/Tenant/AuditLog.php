<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'metadata'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Relation avec l'organisation
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Organization::class);
    }

    /**
     * Relation avec l'utilisateur (si applicable)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Obtenir le libellé de l'action
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => 'Créé',
            'updated' => 'Modifié',
            'deleted' => 'Supprimé',
            'viewed' => 'Consulté',
            'exported' => 'Exporté',
            'imported' => 'Importé',
            'sent' => 'Envoyé',
            'received' => 'Reçu',
            'processed' => 'Traité',
            'cancelled' => 'Annulé',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            'payment_processed' => 'Paiement traité',
            'payment_failed' => 'Paiement échoué',
            'payment_refunded' => 'Paiement remboursé',
            'card_data_collected' => 'Données bancaires collectées',
            'transfer_created' => 'Transfert créé',
            'transfer_used' => 'Transfert utilisé',
            'token_created' => 'Token créé',
            'token_expired' => 'Token expiré',
            'token_revoked' => 'Token révoqué',
            'config_updated' => 'Configuration mise à jour',
            'webhook_received' => 'Webhook reçu',
            'notification_sent' => 'Notification envoyée',
            default => ucfirst($this->action)
        };
    }

    /**
     * Obtenir le libellé du type de ressource
     */
    public function getResourceTypeLabelAttribute(): string
    {
        return match($this->resource_type) {
            'payment_request' => 'Demande de paiement',
            'payment_transaction' => 'Transaction de paiement',
            'member_transfer' => 'Transfert entre membres',
            'evervault_card_token' => 'Token de carte',
            'payment_processor_config' => 'Configuration processeur',
            'organization' => 'Organisation',
            'user' => 'Utilisateur',
            'notification' => 'Notification',
            'webhook' => 'Webhook',
            default => ucfirst(str_replace('_', ' ', $this->resource_type))
        ];
    }

    /**
     * Obtenir la couleur de l'action pour l'affichage
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created', 'approved', 'payment_processed' => 'success',
            'updated', 'config_updated' => 'info',
            'deleted', 'cancelled', 'rejected', 'payment_failed' => 'danger',
            'viewed', 'exported', 'received' => 'secondary',
            'sent', 'notification_sent' => 'primary',
            'payment_refunded', 'token_expired', 'token_revoked' => 'warning',
            default => 'dark'
        };
    }

    /**
     * Obtenir l'icône de l'action
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'created' => 'plus-circle',
            'updated', 'config_updated' => 'edit',
            'deleted' => 'trash',
            'viewed' => 'eye',
            'exported' => 'download',
            'imported' => 'upload',
            'sent', 'notification_sent' => 'send',
            'received' => 'inbox',
            'processed', 'payment_processed' => 'check-circle',
            'cancelled' => 'x-circle',
            'approved' => 'check',
            'rejected', 'payment_failed' => 'x',
            'payment_refunded' => 'arrow-left-circle',
            'card_data_collected' => 'credit-card',
            'transfer_created', 'transfer_used' => 'arrow-right',
            'token_created' => 'key',
            'token_expired', 'token_revoked' => 'clock',
            'webhook_received' => 'globe',
            default => 'activity'
        ];
    }

    /**
     * Obtenir un résumé des changements
     */
    public function getChangesSummaryAttribute(): array
    {
        $summary = [];

        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $field => $newValue) {
                $oldValue = $this->old_values[$field] ?? null;
                
                if ($oldValue !== $newValue) {
                    $summary[] = [
                        'field' => $field,
                        'field_label' => $this->getFieldLabel($field),
                        'old_value' => $this->formatValue($oldValue),
                        'new_value' => $this->formatValue($newValue)
                    ];
                }
            }
        }

        return $summary;
    }

    /**
     * Obtenir le libellé d'un champ
     */
    private function getFieldLabel(string $field): string
    {
        return match($field) {
            'status' => 'Statut',
            'amount' => 'Montant',
            'currency' => 'Devise',
            'description' => 'Description',
            'expires_at' => 'Date d\'expiration',
            'processed_at' => 'Date de traitement',
            'client_email' => 'Email client',
            'client_phone' => 'Téléphone client',
            'max_amount' => 'Montant maximum',
            'processor_type' => 'Type de processeur',
            'is_active' => 'Actif',
            'test_mode' => 'Mode test',
            'failure_reason' => 'Raison de l\'échec',
            'refund_amount' => 'Montant remboursé',
            'refund_reason' => 'Raison du remboursement',
            default => ucfirst(str_replace('_', ' ', $field))
        ];
    }

    /**
     * Formater une valeur pour l'affichage
     */
    private function formatValue($value): string
    {
        if ($value === null) {
            return 'N/A';
        }

        if (is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        if (is_string($value) && strlen($value) > 100) {
            return substr($value, 0, 100) . '...';
        }

        return (string) $value;
    }

    /**
     * Obtenir la description complète de l'audit
     */
    public function getDescriptionAttribute(): string
    {
        $description = "{$this->action_label} {$this->resource_type_label}";
        
        if ($this->resource_id) {
            $description .= " (ID: {$this->resource_id})";
        }

        if ($this->user) {
            $description .= " par {$this->user->name}";
        }

        return $description;
    }

    /**
     * Créer un log d'audit
     */
    public static function createLog(array $data): self
    {
        // Ajouter automatiquement l'IP et le User-Agent si disponibles
        if (request()) {
            $data['ip_address'] = $data['ip_address'] ?? request()->ip();
            $data['user_agent'] = $data['user_agent'] ?? request()->userAgent();
        }

        // Ajouter l'utilisateur connecté si disponible
        if (auth()->check() && !isset($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }

        return self::create($data);
    }

    /**
     * Logger une création de ressource
     */
    public static function logCreated(string $resourceType, int $resourceId, array $values, int $organizationId, array $metadata = []): self
    {
        return self::createLog([
            'organization_id' => $organizationId,
            'action' => 'created',
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'new_values' => $values,
            'metadata' => $metadata
        ]);
    }

    /**
     * Logger une modification de ressource
     */
    public static function logUpdated(string $resourceType, int $resourceId, array $oldValues, array $newValues, int $organizationId, array $metadata = []): self
    {
        return self::createLog([
            'organization_id' => $organizationId,
            'action' => 'updated',
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata
        ]);
    }

    /**
     * Logger une suppression de ressource
     */
    public static function logDeleted(string $resourceType, int $resourceId, array $values, int $organizationId, array $metadata = []): self
    {
        return self::createLog([
            'organization_id' => $organizationId,
            'action' => 'deleted',
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'old_values' => $values,
            'metadata' => $metadata
        ]);
    }

    /**
     * Logger un paiement traité
     */
    public static function logPaymentProcessed(int $transactionId, array $transactionData, int $organizationId, array $metadata = []): self
    {
        return self::createLog([
            'organization_id' => $organizationId,
            'action' => 'payment_processed',
            'resource_type' => 'payment_transaction',
            'resource_id' => $transactionId,
            'new_values' => $transactionData,
            'metadata' => $metadata
        ]);
    }

    /**
     * Logger un paiement échoué
     */
    public static function logPaymentFailed(int $transactionId, string $reason, int $organizationId, array $metadata = []): self
    {
        return self::createLog([
            'organization_id' => $organizationId,
            'action' => 'payment_failed',
            'resource_type' => 'payment_transaction',
            'resource_id' => $transactionId,
            'new_values' => ['failure_reason' => $reason],
            'metadata' => $metadata
        ]);
    }

    /**
     * Logger une collecte de données bancaires
     */
    public static function logCardDataCollected(int $paymentRequestId, int $cardTokenId, int $organizationId, array $metadata = []): self
    {
        return self::createLog([
            'organization_id' => $organizationId,
            'action' => 'card_data_collected',
            'resource_type' => 'payment_request',
            'resource_id' => $paymentRequestId,
            'new_values' => ['card_token_id' => $cardTokenId],
            'metadata' => $metadata
        ]);
    }

    /**
     * Logger un transfert créé
     */
    public static function logTransferCreated(int $transferId, array $transferData, int $organizationId, array $metadata = []): self
    {
        return self::createLog([
            'organization_id' => $organizationId,
            'action' => 'transfer_created',
            'resource_type' => 'member_transfer',
            'resource_id' => $transferId,
            'new_values' => $transferData,
            'metadata' => $metadata
        ]);
    }

    /**
     * Logger l'utilisation d'un transfert
     */
    public static function logTransferUsed(int $transferId, float $amount, int $organizationId, array $metadata = []): self
    {
        return self::createLog([
            'organization_id' => $organizationId,
            'action' => 'transfer_used',
            'resource_type' => 'member_transfer',
            'resource_id' => $transferId,
            'new_values' => ['amount_used' => $amount],
            'metadata' => $metadata
        ]);
    }

    /**
     * Logger une réception de webhook
     */
    public static function logWebhookReceived(string $webhookType, array $webhookData, int $organizationId, array $metadata = []): self
    {
        return self::createLog([
            'organization_id' => $organizationId,
            'action' => 'webhook_received',
            'resource_type' => 'webhook',
            'new_values' => ['webhook_type' => $webhookType],
            'metadata' => array_merge($metadata, ['webhook_data' => $webhookData])
        ]);
    }

    /**
     * Scopes pour les requêtes courantes
     */
    public function scopeByOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByResourceType($query, string $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopePaymentActions($query)
    {
        return $query->whereIn('action', [
            'payment_processed',
            'payment_failed',
            'payment_refunded',
            'card_data_collected'
        ]);
    }

    public function scopeTransferActions($query)
    {
        return $query->whereIn('action', [
            'transfer_created',
            'transfer_used'
        ]);
    }

    public function scopeConfigActions($query)
    {
        return $query->whereIn('action', [
            'config_updated',
            'created',
            'updated'
        ])->where('resource_type', 'payment_processor_config');
    }
}

