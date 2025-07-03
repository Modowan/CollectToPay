<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Customer représentant un client d'une branche
 * 
 * Ce modèle est spécifique à chaque tenant et gère les clients
 * associés à une branche particulière.
 */
class Customer extends Model
{
    use HasFactory;

    /**
     * Liste des attributs qui peuvent être assignés en masse.
     *
     * @var array
     */
    protected $fillable = [
        'branch_id',
        'name',
        'email',
        'phone',
        'address',
        'status',
        'accept_tokenisation',
    ];

    /**
     * Obtenir tous les tokens de paiement associés à ce client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paymentTokens()
    {
        return $this->hasMany(PaymentToken::class);
    }

    /**
     * Obtenir toutes les transactions de paiement associées à ce client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Vérifier si le client est actif.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active';
    }
}
