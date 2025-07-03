<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'email',
        'phone',
        'address',
        'country_code',
        'currency',
        'is_active',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array'
    ];

    /**
     * Relation avec la configuration Evervault
     */
    public function evervaultConfig(): HasOne
    {
        return $this->hasOne(EvervaultConfiguration::class);
    }

    /**
     * Relation avec les configurations des processeurs de paiement
     */
    public function paymentProcessorConfigs(): HasMany
    {
        return $this->hasMany(PaymentProcessorConfig::class);
    }

    /**
     * Relation avec les demandes de paiement
     */
    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class);
    }

    /**
     * Relation avec les transferts envoyés
     */
    public function sentTransfers(): HasMany
    {
        return $this->hasMany(MemberTransfer::class, 'sender_organization_id');
    }

    /**
     * Relation avec les transferts reçus
     */
    public function receivedTransfers(): HasMany
    {
        return $this->hasMany(MemberTransfer::class, 'recipient_organization_id');
    }

    /**
     * Vérifier si l'organisation a une configuration Evervault active
     */
    public function hasActiveEvervaultConfig(): bool
    {
        return $this->evervaultConfig && $this->evervaultConfig->is_active;
    }

    /**
     * Obtenir la configuration d'un processeur de paiement
     */
    public function getPaymentProcessorConfig(string $processorType): ?PaymentProcessorConfig
    {
        return $this->paymentProcessorConfigs()
            ->where('processor_type', $processorType)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Vérifier si l'organisation peut traiter des paiements
     */
    public function canProcessPayments(): bool
    {
        return $this->hasActiveEvervaultConfig() && 
               $this->paymentProcessorConfigs()->where('is_active', true)->exists();
    }

    /**
     * Scope pour les organisations actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope par type d'organisation
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}