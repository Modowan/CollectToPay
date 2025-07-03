<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\DynamicConnectionManager;

class TenantMigrationManager extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenant:migration-manager 
                            {--analyze : Analyser l\'état actuel des migrations}
                            {--reset-migrations : Supprimer seulement la table migrations}
                            {--mark-existing : Marquer les migrations existantes comme appliquées}
                            {--selective : Migrer seulement les nouvelles tables}
                            {--backup-data : Sauvegarder les données avant migration}
                            {--dry-run : Voir ce qui serait fait sans l\'exécuter}';

    /**
     * The console command description.
     */
    protected $description = 'Gestionnaire avancé de migrations tenant avec options intelligentes';

    protected DynamicConnectionManager $connectionManager;

    public function __construct(DynamicConnectionManager $connectionManager)
    {
        parent::__construct();
        $this->connectionManager = $connectionManager;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Gestionnaire Avancé de Migrations Tenant');
        $this->info('==========================================');
        $this->newLine();

        // Découvrir les bases tenant
        $tenantDatabases = $this->connectionManager->discoverTenantDatabases();
        
        if (empty($tenantDatabases)) {
            $this->error('❌ Aucune base tenant trouvée !');
            return 1;
        }

        $this->info("📊 Bases tenant détectées : " . count($tenantDatabases));
        $this->newLine();

        // Analyser l'état actuel
        if ($this->option('analyze')) {
            return $this->analyzeMigrationState($tenantDatabases);
        }

        // Reset des tables migrations
        if ($this->option('reset-migrations')) {
            return $this->resetMigrationTables($tenantDatabases);
        }

        // Marquer les migrations existantes
        if ($this->option('mark-existing')) {
            return $this->markExistingMigrations($tenantDatabases);
        }

        // Migration sélective
        if ($this->option('selective')) {
            return $this->selectiveMigration($tenantDatabases);
        }

        // Sauvegarde des données
        if ($this->option('backup-data')) {
            return $this->backupTenantData($tenantDatabases);
        }

        // Afficher les options disponibles
        $this->showAvailableOptions();
        return 0;
    }

    /**
     * Analyser l'état des migrations pour chaque tenant
     */
    protected function analyzeMigrationState(array $tenantDatabases): int
    {
        $this->info('🔍 ANALYSE DE L\'ÉTAT DES MIGRATIONS');
        $this->info('===================================');
        $this->newLine();

        foreach ($tenantDatabases as $database) {
            $this->line("📁 Analyse de : {$database}");
            
            try {
                $connectionName = $this->connectionManager->createDynamicConnection($database);
                
                // Vérifier si la table migrations existe
                $hasMigrationsTable = Schema::connection($connectionName)->hasTable('migrations');
                
                if ($hasMigrationsTable) {
                    // Compter les migrations appliquées
                    $appliedCount = DB::connection($connectionName)->table('migrations')->count();
                    $this->info("   ✅ Table migrations : OUI ({$appliedCount} migrations appliquées)");
                    
                    // Lister les migrations appliquées
                    $appliedMigrations = DB::connection($connectionName)
                        ->table('migrations')
                        ->pluck('migration')
                        ->toArray();
                    
                    $this->line("   📄 Migrations appliquées :");
                    foreach (array_slice($appliedMigrations, 0, 5) as $migration) {
                        $this->line("      - {$migration}");
                    }
                    if (count($appliedMigrations) > 5) {
                        $this->line("      ... et " . (count($appliedMigrations) - 5) . " autres");
                    }
                } else {
                    $this->warn("   ⚠️ Table migrations : NON");
                }

                // Vérifier les tables existantes
                $tables = $this->getTables($connectionName);
                $this->info("   📊 Tables existantes : " . count($tables));
                
                // Afficher quelques tables importantes
                $importantTables = ['customers', 'branches', 'tenant_users', 'payment_transactions'];
                $existingImportant = array_intersect($tables, $importantTables);
                if (!empty($existingImportant)) {
                    $this->line("   🏗️ Tables importantes : " . implode(', ', $existingImportant));
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Erreur : " . $e->getMessage());
            }

            $this->newLine();
        }

        $this->info('💡 RECOMMANDATIONS :');
        $this->line('• Si toutes les bases ont des tables migrations : utilisez --reset-migrations');
        $this->line('• Si vous voulez garder les données : utilisez --mark-existing');
        $this->line('• Si vous voulez tout recommencer : utilisez migrate:fresh');
        $this->line('• Pour voir les options : php artisan tenant:migration-manager');

        return 0;
    }

    /**
     * Supprimer seulement les tables migrations
     */
    protected function resetMigrationTables(array $tenantDatabases): int
    {
        $this->warn('⚠️ SUPPRESSION DES TABLES MIGRATIONS');
        $this->warn('===================================');
        $this->newLine();

        if (!$this->option('dry-run') && !$this->confirm('Êtes-vous sûr de vouloir supprimer les tables migrations ?')) {
            $this->info('❌ Opération annulée');
            return 0;
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($tenantDatabases as $database) {
            $this->line("🗑️ Suppression table migrations : {$database}");
            
            try {
                $connectionName = $this->connectionManager->createDynamicConnection($database);
                
                if ($this->option('dry-run')) {
                    $this->info("   🔍 [DRY-RUN] Supprimerait la table migrations");
                } else {
                    Schema::connection($connectionName)->dropIfExists('migrations');
                    $this->info("   ✅ Table migrations supprimée");
                }
                
                $successCount++;
            } catch (\Exception $e) {
                $this->error("   ❌ Erreur : " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("📊 Résumé : {$successCount} réussies, {$errorCount} erreurs");
        
        if (!$this->option('dry-run') && $successCount > 0) {
            $this->info('🚀 Vous pouvez maintenant exécuter : php artisan tenants:migrate-dynamic --force');
        }

        return $errorCount > 0 ? 1 : 0;
    }

    /**
     * Marquer les migrations existantes comme appliquées
     */
    protected function markExistingMigrations(array $tenantDatabases): int
    {
        $this->info('📝 MARQUAGE DES MIGRATIONS EXISTANTES');
        $this->info('====================================');
        $this->newLine();

        // Obtenir la liste des fichiers de migration
        $migrationFiles = glob(database_path('migrations/tenant/*.php'));
        $migrationNames = [];
        
        foreach ($migrationFiles as $file) {
            $filename = basename($file, '.php');
            $migrationNames[] = $filename;
        }

        $this->info("📄 Migrations à marquer : " . count($migrationNames));
        $this->newLine();

        foreach ($tenantDatabases as $database) {
            $this->line("📝 Marquage : {$database}");
            
            try {
                $connectionName = $this->connectionManager->createDynamicConnection($database);
                
                // Créer la table migrations si elle n'existe pas
                if (!Schema::connection($connectionName)->hasTable('migrations')) {
                    if ($this->option('dry-run')) {
                        $this->info("   🔍 [DRY-RUN] Créerait la table migrations");
                    } else {
                        Schema::connection($connectionName)->create('migrations', function ($table) {
                            $table->id();
                            $table->string('migration');
                            $table->integer('batch');
                        });
                        $this->info("   ✅ Table migrations créée");
                    }
                }

                // Marquer toutes les migrations comme appliquées
                if ($this->option('dry-run')) {
                    $this->info("   🔍 [DRY-RUN] Marquerait " . count($migrationNames) . " migrations");
                } else {
                    $batch = 1;
                    foreach ($migrationNames as $migration) {
                        DB::connection($connectionName)->table('migrations')->updateOrInsert(
                            ['migration' => $migration],
                            ['migration' => $migration, 'batch' => $batch]
                        );
                    }
                    $this->info("   ✅ " . count($migrationNames) . " migrations marquées");
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Erreur : " . $e->getMessage());
            }
        }

        return 0;
    }

    /**
     * Migration sélective des nouvelles tables
     */
    protected function selectiveMigration(array $tenantDatabases): int
    {
        $this->info('🎯 MIGRATION SÉLECTIVE');
        $this->info('======================');
        $this->newLine();

        // Cette fonctionnalité nécessiterait une analyse plus poussée
        $this->warn('⚠️ Fonctionnalité en développement');
        $this->info('Pour l\'instant, utilisez les autres options disponibles.');

        return 0;
    }

    /**
     * Sauvegarder les données des tenants
     */
    protected function backupTenantData(array $tenantDatabases): int
    {
        $this->info('💾 SAUVEGARDE DES DONNÉES TENANT');
        $this->info('===============================');
        $this->newLine();

        $backupDir = storage_path('app/tenant-backups/' . date('Y-m-d_H-i-s'));
        
        if (!$this->option('dry-run')) {
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
        }

        foreach ($tenantDatabases as $database) {
            $this->line("💾 Sauvegarde : {$database}");
            
            try {
                $backupFile = $backupDir . "/{$database}.sql";
                
                if ($this->option('dry-run')) {
                    $this->info("   🔍 [DRY-RUN] Sauvegarderait vers : {$backupFile}");
                } else {
                    // Commande mysqldump (nécessite mysqldump installé)
                    $command = sprintf(
                        'mysqldump -h %s -u %s -p%s %s > %s',
                        config('database.connections.mysql.host'),
                        config('database.connections.mysql.username'),
                        config('database.connections.mysql.password'),
                        $database,
                        $backupFile
                    );
                    
                    exec($command, $output, $returnCode);
                    
                    if ($returnCode === 0) {
                        $this->info("   ✅ Sauvegardé vers : {$backupFile}");
                    } else {
                        $this->error("   ❌ Erreur de sauvegarde");
                    }
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Erreur : " . $e->getMessage());
            }
        }

        if (!$this->option('dry-run')) {
            $this->info("📁 Sauvegardes stockées dans : {$backupDir}");
        }

        return 0;
    }

    /**
     * Afficher les options disponibles
     */
    protected function showAvailableOptions(): void
    {
        $this->info('🎯 OPTIONS DISPONIBLES :');
        $this->info('========================');
        $this->newLine();

        $this->table(
            ['Option', 'Description', 'Sécurité'],
            [
                ['--analyze', 'Analyser l\'état actuel des migrations', '✅ Sûr'],
                ['--reset-migrations', 'Supprimer seulement les tables migrations', '⚠️ Modéré'],
                ['--mark-existing', 'Marquer les migrations comme appliquées', '✅ Sûr'],
                ['--backup-data', 'Sauvegarder les données avant action', '✅ Sûr'],
                ['--dry-run', 'Voir ce qui serait fait (avec toute option)', '✅ Sûr'],
            ]
        );

        $this->newLine();
        $this->info('💡 EXEMPLES D\'UTILISATION :');
        $this->line('• php artisan tenant:migration-manager --analyze');
        $this->line('• php artisan tenant:migration-manager --reset-migrations --dry-run');
        $this->line('• php artisan tenant:migration-manager --mark-existing');
        $this->line('• php artisan tenant:migration-manager --backup-data');
    }

    /**
     * Obtenir la liste des tables d'une connexion
     */
    protected function getTables(string $connectionName): array
    {
        $tables = DB::connection($connectionName)->select('SHOW TABLES');
        $tableKey = 'Tables_in_' . DB::connection($connectionName)->getDatabaseName();
        
        return array_map(function($table) use ($tableKey) {
            return $table->$tableKey;
        }, $tables);
    }
}

