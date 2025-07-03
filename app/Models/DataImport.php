<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'original_filename',
        'tenant_id',
        'branch_id',
        'status',
        'total_records',
        'successful_records',
        'failed_records',
        'preview_data',
        'notes',
        'imported_by',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'preview_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Relation avec le locataire
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relation avec la succursale
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relation avec l'utilisateur qui a effectué l'importation
     */
    public function importedBy()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    /**
     * Relation avec les erreurs d'importation
     */
    public function errors()
    {
        return $this->hasMany(ImportError::class);
    }

    /**
     * Calculer le taux de réussite
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_records == 0) {
            return 0;
        }
        
        return round(($this->successful_records / $this->total_records) * 100, 2);
    }

    /**
     * Vérifier si l'importation est terminée
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Vérifier si l'importation a échoué
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Vérifier si l'importation est en cours de traitement
     */
    public function isProcessing()
    {
        return $this->status === 'processing';
    }

    /**
     * Démarrer le processus d'importation
     */
    public function start()
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now()
        ]);
    }

    /**
     * Terminer le processus d'importation avec succès
     */
    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    /**
     * Terminer le processus d'importation avec échec
     */
    public function fail($notes = null)
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'notes' => $notes
        ]);
    }
}