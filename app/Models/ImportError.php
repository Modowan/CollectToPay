<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportError extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_import_id',
        'row_number',
        'field_name',
        'field_value',
        'error_type',
        'error_message',
        'row_data'
    ];

    protected $casts = [
        'row_data' => 'array'
    ];

    /**
     * Relation avec l'importation de données
     */
    public function dataImport()
    {
        return $this->belongsTo(DataImport::class);
    }

    /**
     * Types d'erreurs possibles
     */
    const ERROR_TYPES = [
        'validation' => 'Erreur de validation des données',
        'duplicate' => 'Données en double',
        'missing_required' => 'Champ obligatoire manquant',
        'invalid_format' => 'Format invalide',
        'database_error' => 'Erreur de base de données',
        'unknown' => 'Erreur inconnue'
    ];

    /**
     * Obtenir la description du type d'erreur
     */
    public function getErrorTypeDescriptionAttribute()
    {
        return self::ERROR_TYPES[$this->error_type] ?? 'Erreur inconnue';
    }

    /**
     * Créer une nouvelle erreur
     */
    public static function createError($dataImportId, $rowNumber, $errorType, $errorMessage, $fieldName = null, $fieldValue = null, $rowData = null)
    {
        return self::create([
            'data_import_id' => $dataImportId,
            'row_number' => $rowNumber,
            'field_name' => $fieldName,
            'field_value' => $fieldValue,
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            'row_data' => $rowData
        ]);
    }
}