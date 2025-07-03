<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle ActivityLog représentant un journal d'activité dans le système
 * 
 * Ce modèle enregistre toutes les actions importantes effectuées par les utilisateurs
 * pour assurer la traçabilité et la sécurité du système.
 */
class ActivityLog extends Model
{
    use HasFactory;

    /**
     * Indique si les horodatages du modèle doivent être mis à jour.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Liste des attributs qui peuvent être assignés en masse.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'tenant_id',
        'action',
        'entity_type',
        'entity_id',
        'details',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * Liste des attributs qui doivent être convertis en types natifs.
     *
     * @var array
     */
    protected $casts = [
        'details' => 'json',
        'created_at' => 'datetime',
    ];

    /**
     * Obtenir l'utilisateur qui a effectué l'action.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir le tenant associé à cette activité.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
