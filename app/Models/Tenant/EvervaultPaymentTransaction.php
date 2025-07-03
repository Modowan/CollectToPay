<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvervaultPaymentTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evervault_payment_transactions';

    protected $fillable = [
        'transaction_reference',
        'organization_id',
        'evervault_card_token_id',
        'payment_request_id',
        'member_transfer_id',
        'processor_type',
        'processor_transaction_id',
        'amount',
        'currency',
        'status',
        'description',
        'processor_response',
        'failure_reason',
        'processed_at',
        'refunded_at',
        'refund_amount',
        'refund_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'processor_response' => 'array',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime'
    ];

    protected $dates = [
        'processed_at',
        'refunded_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relation avec l'organisation
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Organization::class);
    }

    /**
     * Relation avec le token de carte Evervault
     */
    public function cardToken(): BelongsTo
    {
        return $this->belongsTo(EvervaultCardToken::class, 'evervault_card_token_id');
    }

    /**
     * Relation avec la demande de paiement
     */
    public function paymentRequest(): BelongsTo
    {
        return $this->belongsTo(PaymentRequest::class);
    }

    /**
     * Relation avec le transfert entre membres
     */
    public function memberTransfer(): BelongsTo
    {
        return $this->belongsTo(MemberTransfer::class);
    }

    /**
     * Obtenir le montant formaté avec la devise
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Obtenir le montant de remboursement formaté
     */
    public function getFormattedRefundAmountAttribute(): ?string
    {
        if (!$this->refund_amount) {
            return null;
        }
        
        return number_format($this->refund_amount, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Vérifier si la transaction est complétée
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Vérifier si la transaction a échoué
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Vérifier si la transaction est en attente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Vérifier si la transaction est remboursée
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Vérifier si la transaction peut être remboursée
     */
    public function canBeRefunded(): bool
    {
        return $this->isCompleted() && !$this->isRefunded();
    }

    /**
     * Obtenir le montant disponible pour remboursement
     */
    public function getAvailableRefundAmount(): float
    {
        if (!$this->canBeRefunded()) {
            return 0.0;
        }

        return $this->amount - ($this->refund_amount ?? 0.0);
    }

    /**
     * Obtenir le statut formaté pour l'affichage
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'completed' => 'Complétée',
            'failed' => 'Échouée',
            'refunded' => 'Remboursée',
            'cancelled' => 'Annulée',
            default => ucfirst($this->status)
        };
    }

    /**
     * Obtenir la couleur du statut pour l'affichage
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'completed' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
            'cancelled' => 'secondary',
            default => 'primary'
        };
    }

    /**
     * Obtenir le type de processeur formaté
     */
    public function getProcessorLabelAttribute(): string
    {
        return match($this->processor_type) {
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'ixopay' => 'Ixopay',
            default => ucfirst($this->processor_type)
        };
    }

    /**
     * Obtenir les détails de la source de la transaction
     */
    public function getSourceDetailsAttribute(): array
    {
        if ($this->paymentRequest) {
            return [
                'type' => 'payment_request',
                'label' => 'Demande de paiement',
                'reference' => $this->paymentRequest->request_number,
                'client_name' => $this->paymentRequest->client_full_name,
                'client_email' => $this->paymentRequest->client_email
            ];
        }

        if ($this->memberTransfer) {
            return [
                'type' => 'member_transfer',
                'label' => 'Transfert entre membres',
                'reference' => $this->memberTransfer->transfer_number,
                'sender' => $this->memberTransfer->senderOrganization->name,
                'cardholder_name' => $this->memberTransfer->cardholder_full_name
            ];
        }

        return [
            'type' => 'unknown',
            'label' => 'Source inconnue',
            'reference' => null
        ];
    }

    /**
     * Obtenir les frais de transaction (si applicable)
     */
    public function getTransactionFeesAttribute(): ?array
    {
        $response = $this->processor_response;
        
        if (!$response) {
            return null;
        }

        // Extraire les frais selon le processeur
        switch ($this->processor_type) {
            case 'stripe':
                if (isset($response['charges'][0]['balance_transaction'])) {
                    $balanceTransaction = $response['charges'][0]['balance_transaction'];
                    return [
                        'fee' => $balanceTransaction['fee'] / 100, // Stripe utilise les centimes
                        'net' => $balanceTransaction['net'] / 100,
                        'currency' => strtoupper($balanceTransaction['currency'])
                    ];
                }
                break;
                
            case 'paypal':
                if (isset($response['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown'])) {
                    $breakdown = $response['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown'];
                    return [
                        'fee' => $breakdown['paypal_fee']['value'] ?? 0,
                        'net' => $breakdown['net_amount']['value'] ?? 0,
                        'currency' => $breakdown['gross_amount']['currency_code'] ?? $this->currency
                    ];
                }
                break;
        }

        return null;
    }

    /**
     * Obtenir l'URL de la transaction dans le dashboard du processeur
     */
    public function getProcessorDashboardUrlAttribute(): ?string
    {
        if (!$this->processor_transaction_id) {
            return null;
        }

        switch ($this->processor_type) {
            case 'stripe':
                $mode = $this->isTestMode() ? 'test/' : '';
                return "https://dashboard.stripe.com/{$mode}payments/{$this->processor_transaction_id}";
                
            case 'paypal':
                $env = $this->isTestMode() ? 'sandbox.' : '';
                return "https://www.{$env}paypal.com/activity/payment/{$this->processor_transaction_id}";
                
            default:
                return null;
        }
    }

    /**
     * Vérifier si la transaction est en mode test
     */
    public function isTestMode(): bool
    {
        // Vérifier dans la configuration du processeur
        $config = \App\Models\PaymentProcessorConfig::where('organization_id', $this->organization_id)
            ->where('processor_type', $this->processor_type)
            ->first();
            
        return $config ? $config->test_mode : false;
    }

    /**
     * Obtenir un résumé de la transaction pour les logs
     */
    public function getLogSummaryAttribute(): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->transaction_reference,
            'amount' => $this->formatted_amount,
            'status' => $this->status,
            'processor' => $this->processor_type,
            'organization_id' => $this->organization_id,
            'source' => $this->source_details['type'],
            'created_at' => $this->created_at->toISOString()
        ];
    }

    /**
     * Marquer la transaction comme complétée
     */
    public function markAsCompleted(array $processorResponse = []): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
            'processor_response' => array_merge($this->processor_response ?? [], $processorResponse)
        ]);

        // Logger l'événement
        AuditLog::logPaymentProcessed(
            $this->id,
            $this->toArray(),
            $this->organization_id,
            ['processor_response' => $processorResponse]
        );
    }

    /**
     * Marquer la transaction comme échouée
     */
    public function markAsFailed(string $reason, array $processorResponse = []): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'processor_response' => array_merge($this->processor_response ?? [], $processorResponse)
        ]);

        // Logger l'événement
        AuditLog::logPaymentFailed(
            $this->id,
            $reason,
            $this->organization_id,
            ['processor_response' => $processorResponse]
        );
    }

    /**
     * Marquer la transaction comme remboursée
     */
    public function markAsRefunded(float $refundAmount = null, string $reason = null, array $processorResponse = []): void
    {
        $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
            'refund_amount' => $refundAmount ?? $this->amount,
            'refund_reason' => $reason,
            'processor_response' => array_merge($this->processor_response ?? [], ['refund' => $processorResponse])
        ]);

        // Logger l'événement
        AuditLog::createLog([
            'organization_id' => $this->organization_id,
            'action' => 'payment_refunded',
            'resource_type' => 'evervault_payment_transaction',
            'resource_id' => $this->id,
            'new_values' => [
                'refund_amount' => $refundAmount ?? $this->amount,
                'refund_reason' => $reason
            ],
            'metadata' => ['processor_response' => $processorResponse]
        ]);
    }

    /**
     * Scopes pour les requêtes courantes
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeByProcessor($query, string $processor)
    {
        return $query->where('processor_type', $processor);
    }

    public function scopeByOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeByAmountRange($query, float $minAmount, float $maxAmount)
    {
        return $query->whereBetween('amount', [$minAmount, $maxAmount]);
    }

    public function scopeFromPaymentRequests($query)
    {
        return $query->whereNotNull('payment_request_id');
    }

    public function scopeFromMemberTransfers($query)
    {
        return $query->whereNotNull('member_transfer_id');
    }
}

