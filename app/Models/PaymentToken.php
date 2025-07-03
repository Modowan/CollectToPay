<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle PaymentToken représentant un token de carte bancaire tokenisé via ixopay
 * 
 * Ce modèle est spécifique à chaque tenant et gère les tokens de paiement
 * associés à un client particulier.
 */
class PaymentToken extends Model
{
    use HasFactory;

    /**
     * Liste des attributs qui peuvent être assignés en masse.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'token',
        'card_type',
        'last_four',
        'expiry_month',
        'expiry_year',
        'is_default',
        'status',
    ];

    /**
     * Liste des attributs qui doivent être convertis en types natifs.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Obtenir le client auquel appartient ce token.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Obtenir toutes les transactions de paiement associées à ce token.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'token_id');
    }

    /**
     * Vérifier si le token est actif.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Vérifier si le token est expiré.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->status === 'expired';
    }

    /**
     * Vérifier si le token est révoqué.
     *
     * @return bool
     */
    public function isRevoked()
    {
        return $this->status === 'revoked';
    }

    /**
     * Obtenir une représentation masquée du numéro de carte.
     *
     * @return string
     */
    public function getMaskedCardNumber()
    {
        return '************' . $this->last_four;
    }

    /**
     * Obtenir la date d'expiration formatée (MM/YY).
     *
     * @return string
     */
    public function getFormattedExpiryDate()
    {
        return $this->expiry_month . '/' . substr($this->expiry_year, -2);
    }
}
