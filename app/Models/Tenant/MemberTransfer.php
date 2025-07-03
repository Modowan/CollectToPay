<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MemberTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'sender_organization_id',
        'recipient_organization_id',
        'cardholder_first_name',
        'cardholder_last_name',
        'cardholder_phone',
        'cardholder_email',
        'max_amount',
        'currency',
        'status',
        'notes',
        'expires_at'
    ];

    protected $casts = [
        'max_amount' => 'decimal:2',
        'expires_at' => 'datetime'
    ];

    /**
     * Relation avec l'organisation expéditrice
     */
    public function senderOrganization(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Organization::class, 'sender_organization_id');
    }

    /**
     * Relation avec l'organisation destinataire
     */
    public function recipientOrganization(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Organization::class, 'recipient_organization_id');
    }

    /**
     * Relation avec le token de carte
     */
    public function cardToken(): HasOne
    {
        return $this->hasOne(EvervaultCardToken::class);
    }

    /**
     * Relation avec les transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Générer un numéro de transfert unique
     */
    public static function generateTransferNumber(): string
    {
        do {
            $number = 'TRF_' . date('Ymd') . '_' . Str::upper(Str::random(8));
        } while (self::where('transfer_number', $number)->exists());

        return $number;
    }

    /**
     * Marquer comme transféré
     */
    public function markAsTransferred(): void
    {
        $this->update(['status' => 'transferred']);
    }

    /**
     * Marquer comme utilisé
     */
    public function markAsUsed(): void
    {
        $this->update(['status' => 'used']);
    }

    /**
     * Vérifier si le transfert est expiré
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Vérifier si le transfert peut être utilisé
     */
    public function canBeUsed(): bool
    {
        return $this->status === 'transferred' && !$this->isExpired();
    }

    /**
     * Obtenir le nom complet du porteur de carte
     */
    public function getCardholderFullNameAttribute(): string
    {
        return $this->cardholder_first_name . ' ' . $this->cardholder_last_name;
    }

    /**
     * Obtenir le montant formaté
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->max_amount, 2) . ' ' . $this->currency;
    }

    /**
     * Scope pour les transferts actifs
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'transferred']);
    }

    /**
     * Scope pour les transferts utilisables
     */
    public function scopeUsable($query)
    {
        return $query->where('status', 'transferred')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }
}