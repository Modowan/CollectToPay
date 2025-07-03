<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $table = 'tenants';
    
    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'domain',
        'database_name',
        'address',
        'city',
        'country',
        'phone',
        'email',
        'website',
        'description',
        'manager_id',
        'status'
    ];
    
    /**
     * Obtenir le gestionnaire associé à ce tenant.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    
    /**
     * Obtenir toutes les branches associées à ce tenant.
     */
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
    
    /**
     * Obtenir tous les utilisateurs associés à ce tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
