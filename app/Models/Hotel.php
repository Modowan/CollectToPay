<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

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
        'status',
    ];

    /**
     * Obtient le gestionnaire associé à cet hôtel.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
