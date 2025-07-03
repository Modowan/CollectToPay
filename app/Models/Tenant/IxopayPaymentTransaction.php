<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class IxopayPaymentTransaction extends Model
{
    use HasFactory;

    protected $table = 'payment_transactions'; // Table existante

    protected $fillable = [
        'customer_id',
        'token_id',
        'amount',
        'currency',
        'description',
        'status',
        'transaction_id',
        'reference',
    ];

    protected $casts = [
        'max_amount' => 'decimal:2',
    ];

    /**
     * Relation avec l organisation
     **/
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentToken()
    {
        return $this->belongsTo(PaymentToken::class, 'token_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Organization::class);
    }

    /**
     * Relation avec le token de carte Evervault
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
     * Générer un numéro de demande unique
     */
    public static function generateRequestNumber(): string
    {
        do {
            $number = 'REQ_' . date('Ymd') . '_' . Str::upper(Str::random(8));
        } while (self::where('request_number', $number)->exists());

        return $number;
    }

    /**
     * Générer un token sécurisé unique
     */
    public static function generateSecureToken(): string
    {
        do {
            $token = Str::random(64);
        } while (self::where('secure_token', $token)->exists());

        return $token;
    }

    /**
     * Vérifier si la demande est expirée
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Vérifier si la demande est accessible
     */
    public function isAccessible(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Obtenir l'URL sécurisée pour la collecte
     */
    public function getSecureUrl(): string
    {
        return route('payment.collect', ['token' => $this->secure_token]);
    }

    /**
     * Marquer comme complétée
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Marquer comme expirée
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Scope pour les demandes actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'pending')->where('expires_at', '>', now());
    }

    /**
     * Scope pour les demandes expirées
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now())->where('status', 'pending');
    }

    /**
     * Obtenir le nom complet du client
     */
    public function getClientFullNameAttribute(): string
    {
        return $this->client_first_name . ' ' . $this->client_last_name;
    }

    /**
     * Obtenir le montant formaté
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->max_amount, 2) . ' ' . $this->currency;
    }
}