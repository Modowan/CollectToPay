<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * مدير الاتصالات الديناميكي لقواعد بيانات المستأجرين - VERSION FINALE CORRIGÉE
 * 
 * يوفر إمكانيات متقدمة لإنشاء وإدارة اتصالات قواعد البيانات
 * في وقت التشغيل دون الحاجة لتعديل ملفات التكوين
 */
class DynamicConnectionManager
{
    /**
     * بادئة أسماء قواعد بيانات المستأجرين
     *
     * @var string
     */
    protected $tenantDatabasePrefix;

    /**
     * قالب إعدادات الاتصال الأساسي
     *
     * @var array
     */
    protected $baseConnectionConfig;

    /**
     * قائمة الاتصالات المُنشأة ديناميكياً
     *
     * @var array
     */
    protected $dynamicConnections = [];

    /**
     * إنشاء مثيل جديد من مدير الاتصالات
     */
    public function __construct()
    {
        $this->tenantDatabasePrefix = config('app.tenant_db_prefix', 'collect_');
        $this->baseConnectionConfig = $this->getBaseConnectionConfig();
        
        Log::info('DynamicConnectionManager: Initialisé avec préfixe: ' . $this->tenantDatabasePrefix);
    }

    /**
     * اكتشاف جميع قواعد بيانات المستأجرين تلقائياً
     *
     * @return array قائمة بأسماء قواعد بيانات المستأجرين
     */
    public function discoverTenantDatabases(): array
    {
        try {
            Log::info('DynamicConnectionManager: بدء اكتشاف قواعد بيانات المستأجرين');

            // الحصول على قائمة جميع قواعد البيانات
            $databases = DB::select('SHOW DATABASES');
            $tenantDatabases = [];

            foreach ($databases as $database) {
                $dbName = $database->Database;
                
                // التحقق من أن اسم قاعدة البيانات يبدأ بالبادئة المحددة
                if (Str::startsWith($dbName, $this->tenantDatabasePrefix)) {
                    $tenantDatabases[] = $dbName;
                    Log::debug("DynamicConnectionManager: تم اكتشاف قاعدة بيانات مستأجر: {$dbName}");
                }
            }

            Log::info('DynamicConnectionManager: تم اكتشاف ' . count($tenantDatabases) . ' قاعدة بيانات مستأجر');
            return $tenantDatabases;

        } catch (\Exception $e) {
            Log::error('DynamicConnectionManager: خطأ في اكتشاف قواعد البيانات', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \RuntimeException('فشل في اكتشاف قواعد بيانات المستأجرين: ' . $e->getMessage());
        }
    }

    /**
     * إنشاء اتصال ديناميكي لقاعدة بيانات مستأجر - VERSION FINALE CORRIGÉE
     *
     * @param string $databaseName اسم قاعدة البيانات
     * @return string اسم الاتصال المُنشأ
     */
    public function createDynamicConnection(string $databaseName): string
    {
        $connectionName = $this->generateConnectionName($databaseName);

        // التحقق من وجود الاتصال مسبقاً
        if (isset($this->dynamicConnections[$connectionName])) {
            Log::debug("DynamicConnectionManager: الاتصال موجود مسبقاً: {$connectionName}");
            
            // CORRECTION FINALE: Vérifier que la connexion fonctionne encore
            if ($this->testConnection($connectionName)) {
                return $connectionName;
            } else {
                // Si le test échoue, recréer la connexion
                Log::warning("DynamicConnectionManager: Connexion défaillante, recréation: {$connectionName}");
                unset($this->dynamicConnections[$connectionName]);
            }
        }

        try {
            // CORRECTION FINALE: Créer la configuration avec la bonne base de données
            $connectionConfig = $this->buildConnectionConfig($databaseName);

            // CORRECTION FINALE: Nettoyer complètement toute connexion existante
            $this->forceCleanConnection($connectionName);

            // CORRECTION FINALE: Configurer la connexion dans Laravel
            Config::set("database.connections.{$connectionName}", $connectionConfig);

            // CORRECTION FINALE: Forcer Laravel à recharger la configuration
            $this->forceConnectionReload($connectionName, $connectionConfig);

            // Enregistrer la connexion
            $this->dynamicConnections[$connectionName] = [
                'database' => $databaseName,
                'created_at' => now(),
                'config' => $connectionConfig
            ];

            Log::info("DynamicConnectionManager: Connexion créée: {$connectionName} -> {$databaseName}");

            // CORRECTION FINALE: Test de connexion obligatoire
            if (!$this->testConnection($connectionName)) {
                throw new \Exception("Test de connexion échoué pour {$connectionName}");
            }

            return $connectionName;

        } catch (\Exception $e) {
            Log::error('DynamicConnectionManager: Erreur création connexion', [
                'database' => $databaseName,
                'connection' => $connectionName,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException("Échec création connexion {$databaseName}: " . $e->getMessage());
        }
    }

    /**
     * CORRECTION FINALE: Nettoyage forcé d'une connexion
     */
    protected function forceCleanConnection(string $connectionName): void
    {
        try {
            // Purger la connexion de Laravel
            DB::purge($connectionName);
            
            // Supprimer de la configuration
            Config::set("database.connections.{$connectionName}", null);
            
            // Nettoyer le cache de connexions
            if (app()->bound('db')) {
                $dbManager = app('db');
                if (method_exists($dbManager, 'forgetConnection')) {
                    $dbManager->forgetConnection($connectionName);
                }
            }
            
            Log::debug("DynamicConnectionManager: Connexion nettoyée: {$connectionName}");
            
        } catch (\Exception $e) {
            Log::warning("DynamicConnectionManager: Erreur nettoyage {$connectionName}: " . $e->getMessage());
        }
    }

    /**
     * CORRECTION FINALE: Forcer le rechargement d'une connexion
     */
    protected function forceConnectionReload(string $connectionName, array $connectionConfig): void
    {
        try {
            // Méthode 1: Extension directe
            app('db')->extend($connectionName, function($config, $name) use ($connectionConfig) {
                return app('db.factory')->make($connectionConfig, $name);
            });
            
            // Méthode 2: Purge et reconfiguration
            DB::purge($connectionName);
            Config::set("database.connections.{$connectionName}", $connectionConfig);
            
            // Méthode 3: Force la création d'une nouvelle instance
            $connection = app('db.factory')->make($connectionConfig, $connectionName);
            app('db')->setConnection($connectionName, $connection);
            
            Log::debug("DynamicConnectionManager: Rechargement forcé: {$connectionName}");
            
        } catch (\Exception $e) {
            Log::warning("DynamicConnectionManager: Erreur rechargement {$connectionName}: " . $e->getMessage());
        }
    }

    /**
     * إنشاء اتصالات ديناميكية لجميع قواعد بيانات المستأجرين
     *
     * @return array قائمة بأسماء الاتصالات المُنشأة
     */
    public function createAllDynamicConnections(): array
    {
        $tenantDatabases = $this->discoverTenantDatabases();
        $connections = [];

        foreach ($tenantDatabases as $database) {
            try {
                $connectionName = $this->createDynamicConnection($database);
                $connections[$connectionName] = $database;
            } catch (\Exception $e) {
                Log::warning("DynamicConnectionManager: تخطي قاعدة البيانات {$database} بسبب خطأ: " . $e->getMessage());
            }
        }

        Log::info('DynamicConnectionManager: تم إنشاء ' . count($connections) . ' اتصال ديناميكي');
        return $connections;
    }

    /**
     * إزالة اتصال ديناميكي
     *
     * @param string $connectionName اسم الاتصال
     * @return bool
     */
    public function removeDynamicConnection(string $connectionName): bool
    {
        try {
            $this->forceCleanConnection($connectionName);
            unset($this->dynamicConnections[$connectionName]);

            Log::info("DynamicConnectionManager: تم إزالة الاتصال الديناميكي: {$connectionName}");
            return true;

        } catch (\Exception $e) {
            Log::error("DynamicConnectionManager: خطأ في إزالة الاتصال {$connectionName}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * تنظيف جميع الاتصالات الديناميكية
     *
     * @return void
     */
    public function cleanupDynamicConnections(): void
    {
        foreach (array_keys($this->dynamicConnections) as $connectionName) {
            $this->removeDynamicConnection($connectionName);
        }

        Log::info('DynamicConnectionManager: تم تنظيف جميع الاتصالات الديناميكية');
    }

    /**
     * الحصول على قائمة الاتصالات الديناميكية النشطة
     *
     * @return array
     */
    public function getActiveDynamicConnections(): array
    {
        return $this->dynamicConnections;
    }

    /**
     * التحقق من صحة اتصال قاعدة بيانات - VERSION FINALE AMÉLIORÉE
     *
     * @param string $connectionName اسم الاتصال
     * @return bool
     */
    public function testConnection(string $connectionName): bool
    {
        try {
            // Test 1: Connexion PDO
            $pdo = DB::connection($connectionName)->getPdo();
            
            // Test 2: Vérifier la base de données actuelle
            $result = DB::connection($connectionName)->select('SELECT DATABASE() as current_db');
            $currentDb = $result[0]->current_db;
            
            // Test 3: Vérifier contre la base attendue
            $expectedDb = $this->dynamicConnections[$connectionName]['database'] ?? null;
            
            if ($currentDb !== $expectedDb) {
                Log::error("DynamicConnectionManager: Base incorrecte pour {$connectionName}", [
                    'expected' => $expectedDb,
                    'actual' => $currentDb
                ]);
                return false;
            }
            
            // Test 4: Test d'écriture simple
            DB::connection($connectionName)->statement('SELECT 1');
            
            Log::debug("DynamicConnectionManager: Test connexion réussi: {$connectionName} -> {$currentDb}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("DynamicConnectionManager: Test connexion échoué: {$connectionName}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * توليد اسم اتصال فريد لقاعدة البيانات
     *
     * @param string $databaseName اسم قاعدة البيانات
     * @return string
     */
    protected function generateConnectionName(string $databaseName): string
    {
        // إنشاء اسم اتصال فريد بناءً على اسم قاعدة البيانات
        $cleanName = Str::slug(str_replace($this->tenantDatabasePrefix, '', $databaseName), '_');
        return "dynamic_tenant_{$cleanName}";
    }

    /**
     * بناء إعدادات الاتصال لقاعدة بيانات محددة - VERSION FINALE CORRIGÉE
     *
     * @param string $databaseName اسم قاعدة البيانات
     * @return array
     */
    protected function buildConnectionConfig(string $databaseName): array
    {
        // CORRECTION FINALE: Partir de la configuration MySQL complète
        $config = config('database.connections.mysql');
        
        // CORRECTION FINALE: Forcer le nom de la base de données
        $config['database'] = $databaseName;
        
        // CORRECTION FINALE: S'assurer que tous les paramètres sont corrects
        $config['charset'] = $config['charset'] ?? 'utf8mb4';
        $config['collation'] = $config['collation'] ?? 'utf8mb4_unicode_ci';
        $config['strict'] = $config['strict'] ?? true;
        $config['prefix'] = '';
        $config['prefix_indexes'] = true;
        
        // CORRECTION FINALE: Ajouter des options PDO spécifiques
        if (!isset($config['options'])) {
            $config['options'] = [];
        }
        
        Log::debug("DynamicConnectionManager: Configuration créée pour {$databaseName}", [
            'host' => $config['host'],
            'database' => $config['database'],
            'username' => $config['username'],
            'charset' => $config['charset']
        ]);
        
        return $config;
    }

    /**
     * الحصول على إعدادات الاتصال الأساسية - VERSION FINALE CORRIGÉE
     *
     * @return array
     */
    protected function getBaseConnectionConfig(): array
    {
        // CORRECTION FINALE: Conserver la configuration complète
        $baseConfig = config('database.connections.mysql');
        
        // CORRECTION FINALE: Ne PAS supprimer 'database' ici
        // Il sera remplacé dans buildConnectionConfig()
        
        return $baseConfig;
    }

    /**
     * التحقق من وجود قاعدة بيانات
     *
     * @param string $databaseName اسم قاعدة البيانات
     * @return bool
     */
    public function databaseExists(string $databaseName): bool
    {
        try {
            $result = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);
            return !empty($result);
        } catch (\Exception $e) {
            Log::error("DynamicConnectionManager: خطأ في التحقق من وجود قاعدة البيانات {$databaseName}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * create new database for tenant
     *
     * @param string $tenantName name of tenant
     * @return string name of created database
     */
    public function createTenantDatabase(string $tenantName): string
    {
        $databaseName = $this->tenantDatabasePrefix . Str::slug($tenantName, '_');

        try {
            if ($this->databaseExists($databaseName)) {
                throw new \RuntimeException("قاعدة البيانات موجودة مسبقاً: {$databaseName}");
            }

            // create the database
            DB::statement("CREATE DATABASE `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            Log::info("DynamicConnectionManager:database created {$databaseName}");
            return $databaseName;

        } catch (\Exception $e) {
            Log::error("DynamicConnectionManager:Erroe when create database{$databaseName}", [
                'tenant' => $tenantName,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException("error while create database{$tenantName}: " . $e->getMessage());
        }
    }
}

