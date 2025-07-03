<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Branch représentant une branche (succursale) d'un hôtel ou d'une entreprise touristique
 * 
 * Ce modèle gère les différentes branches associées à un tenant spécifique.
 */
class Branch extends Model
{
    use HasFactory;

    /**
     * Liste des attributs qui peuvent être assignés en masse.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'address',
        'city',
        'country',
        'phone',
        'email',
        'status',
    ];

    /**
     * Obtenir le tenant auquel appartient cette branche.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Obtenir les utilisateurs associés à cette branche.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Obtenir les clients associés à cette branche.
     * Cette relation traverse les bases de données tenant.
     *
     * @return mixed
     */
    public function customers()
    {
        // Cette méthode nécessite une implémentation spéciale pour accéder aux données du tenant
        // car les clients sont stockés dans la base de données du tenant, pas dans la base centrale
        return $this->tenant->run(function ($tenant) {
            return \App\Models\Tenant\Customer::where('branch_id', $this->id)->get();
        });
    }

    /**
     * Obtenir les importations de clients associées à cette branche.
     * Cette relation traverse les bases de données tenant.
     *
     * @return mixed
     */
    public function customerImports()
    {
        // Cette méthode nécessite une implémentation spéciale pour accéder aux données du tenant
        return $this->tenant->run(function ($tenant) {
            return \App\Models\Tenant\CustomerImport::where('branch_id', $this->id)->get();
        });
    }
}
