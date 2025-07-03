<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modèle User représentant un utilisateur du système
 * 
 * Ce modèle gère les utilisateurs avec différents rôles (admin, tenant_admin, branch_admin, customer)
 * et leurs relations avec les tenants et les branches.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Liste des attributs qui peuvent être assignés en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'tenant_id',
        'branch_id',
        'email_verified_at',
    ];

    /**
     * Liste des attributs qui doivent être cachés pour la sérialisation.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Liste des attributs qui doivent être convertis en types natifs.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Obtenir le tenant auquel appartient cet utilisateur.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Obtenir la branche à laquelle appartient cet utilisateur.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Vérifier si l'utilisateur est un administrateur global.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifier si l'utilisateur est un administrateur de tenant.
     *
     * @return bool
     */
    public function isTenantAdmin()
    {
        return $this->role === 'tenant_admin';
    }

    /**
     * Vérifier si l'utilisateur est un administrateur de branche.
     *
     * @return bool
     */
    public function isBranchAdmin()
    {
        return $this->role === 'branch_admin';
    }

    /**
     * Vérifier si l'utilisateur est un client.
     *
     * @return bool
     */
    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    /**
     * Obtenir les journaux d'activité associés à cet utilisateur.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
