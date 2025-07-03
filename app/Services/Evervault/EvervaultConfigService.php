<?php

namespace App\Services\Evervault;

use Evervault\Evervault;
use App\Models\Organization;
use App\Models\EvervaultConfiguration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class EvervaultConfigService
{
    private $configurations = [];

    /**
     * Obtenir une instance Evervault configurée pour une organisation
     */
    public function getEvervaultInstance(int $organizationId): Evervault
    {
        if (!isset($this->configurations[$organizationId])) {
            $config = $this->loadConfiguration($organizationId);
            
            $this->configurations[$organizationId] = new Evervault(
                Crypt::decryptString($config->api_key),
                $config->app_id
            );

            // Configuration des options selon l'environnement
            if ($config->test_mode) {
                $this->configurations[$organizationId]->enableDebugMode();
            }
        }

        return $this->configurations[$organizationId];
    }

    /**
     * Charger la configuration depuis la base de données avec cache
     */
    private function loadConfiguration(int $organizationId): EvervaultConfiguration
    {
        $cacheKey = "evervault_config_{$organizationId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($organizationId) {
            $config = EvervaultConfiguration::where('organization_id', $organizationId)
                ->where('is_active', true)
                ->first();
                
            if (!$config) {
                throw new \Exception("Configuration Evervault non trouvée pour l'organisation {$organizationId}");
            }
            
            return $config;
        });
    }

    /**
     * Créer ou mettre à jour une configuration Evervault
     */
    public function createOrUpdateConfiguration(
        int $organizationId,
        string $apiKey,
        string $appId,
        bool $testMode = true,
        array $features = []
    ): EvervaultConfiguration {
        $config = EvervaultConfiguration::updateOrCreate(
            ['organization_id' => $organizationId],
            [
                'api_key' => Crypt::encryptString($apiKey),
                'app_id' => $appId,
                'test_mode' => $testMode,
                'is_active' => true,
                'features_enabled' => $features
            ]
        );

        // Invalider le cache
        Cache::forget("evervault_config_{$organizationId}");

        Log::info('Configuration Evervault mise à jour', [
            'organization_id' => $organizationId,
            'test_mode' => $testMode
        ]);

        return $config;
    }

    /**
     * Vérifier si une fonctionnalité est activée pour une organisation
     */
    public function isFeatureEnabled(int $organizationId, string $feature): bool
    {
        $config = $this->loadConfiguration($organizationId);
        $features = $config->features_enabled ?? [];
        
        return in_array($feature, $features);
    }

    /**
     * Tester la connexion Evervault pour une organisation
     */
    public function testConnection(int $organizationId): array
    {
        try {
            $evervault = $this->getEvervaultInstance($organizationId);
            
            // Test simple de chiffrement/déchiffrement
            $testData = 'test_connection_' . time();
            $encrypted = $evervault->encrypt($testData);
            $decrypted = $evervault->decrypt($encrypted);
            
            $success = $decrypted === $testData;
            
            Log::info('Test connexion Evervault', [
                'organization_id' => $organizationId,
                'success' => $success
            ]);
            
            return [
                'success' => $success,
                'message' => $success ? 'Connexion réussie' : 'Échec de la connexion'
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur test connexion Evervault', [
                'organization_id' => $organizationId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage()
            ];
        }
    }
}