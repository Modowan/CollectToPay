<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvervaultCardToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_request_id',
        'member_transfer_id',
        'evervault_token_id',
        'card_mask',
        'card_type',
        'expiry_month',
        'expiry_year',
        'cardholder_name',
        'is_3ds_verified',
        'is_preauth_done',
        'preauth_amount',
        'preauth_reference',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'is_3ds_verified' => 'boolean',
        'is_preauth_done' => 'boolean',
        'preauth_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array'
    ];

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
     * Relation avec les transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Vérifier si la carte est expirée
     */
    public function isExpired(): bool
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');

        return $this->expiry_year < $currentYear || 
               ($this->expiry_year == $currentYear && $this->expiry_month < $currentMonth);
    }

    /**
     * Vérifier si la carte peut être utilisée
     */
    public function canBeUsed(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Obtenir le montant disponible pour débit
     */
    public function getAvailableAmount(): float
    {
        if ($this->paymentRequest) {
            $usedAmount = $this->transactions()
                ->where('status', 'completed')
                ->sum('amount');
            return $this->paymentRequest->max_amount - $usedAmount;
        }

        if ($this->memberTransfer) {
            $usedAmount = $this->transactions()
                ->where('status', 'completed')
                ->sum('amount');
            return $this->memberTransfer->max_amount - $usedAmount;
        }

        return 0;
    }

    /**
     * Marquer la vérification 3DS comme effectuée
     */
    public function mark3DSVerified(): void
    {
        $this->update(['is_3ds_verified' => true]);
    }

    /**
     * Marquer la pré-autorisation comme effectuée
     */
    public function markPreAuthDone(float $amount, string $reference): void
    {
        $this->update([
            'is_preauth_done' => true,
            'preauth_amount' => $amount,
            'preauth_reference' => $reference
        ]);
    }

    /**
     * Désactiver le token
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Scope pour les tokens actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les tokens non expirés
     */
    public function scopeNotExpired($query)
    {
        $currentYear = date('Y');
        $currentMonth = date('n');

        return $query->where(function($q) use ($currentYear, $currentMonth) {
            $q->where('expiry_year', '>', $currentYear)
              ->orWhere(function($q2) use ($currentYear, $currentMonth) {
                  $q2->where('expiry_year', '=', $currentYear)
                     ->where('expiry_month', '>=', $currentMonth);
              });
        });
    }

    /**
     * Obtenir la date d'expiration formatée
     */
    public function getFormattedExpiryAttribute(): string
    {
        return sprintf('%02d/%d', $this->expiry_month, $this->expiry_year);
    }
}