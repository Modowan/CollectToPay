<?php

namespace App\Console\Commands;

use App\Services\DynamicConnectionManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

/**
 * Commande pour gérer les migrations des locataires de manière dynamique
 */
class DynamicTenantMigrations extends Command
{
    /**
     * Signature et options de la commande
     * 
     * @var string
     */
    protected $signature = 'tenants:migrate-dynamic 
                            {--discover : Détecter seulement les bases de données des locataires}
                            {--tenant= : Exécuter les migrations pour un locataire spécifique}
                            {--fresh : Réinitialiser complètement la base de données}
                            {--seed : Exécuter les seeders après les migrations} 
                            {--force : Forcer l\'exécution en production}
                            {--path= : Chemin personnalisé pour les migrations}
                            {--dry-run : Simulation sans exécution réelle}';

    /**
     * Description de la commande
     *
     * @var string 
     */
    protected $description = 'Exécuter les migrations dynamiquement pour tous les locataires';

    /**
     * Gestionnaire de connexion dynamique
     */
    protected $connectionManager;

    /**
     * Statistiques d'exécution
     */
    protected $stats = [
        'discovered' => 0,  // Nombre de BDD découvertes
        'processed' => 0,    // Nombre de BDD traitées
        'successful' => 0,   // Migrations réussies
        'failed' => 0,       // Migrations échouées
    ];

    /**
     * Liste des erreurs rencontrées
     */
    protected $errors = [];

    /**
     * Constructeur
     */
    public function __construct(DynamicConnectionManager $connectionManager)
    {
        parent::__construct();
        $this->connectionManager = $connectionManager;
    }

    /**
     * Exécution principale de la commande
     */
    public function handle()
    {
        $this->info('🚀 Gestionnaire de migrations pour locataires');
        $this->info('=====================================');
        $this->newLine();

        try {
            // Mode simulation
            if ($this->option('dry-run')) {
                return $this->performDryRun();
            }

            // Mode détection seule
            if ($this->option('discover')) {
                return $this->performDiscovery();
            }

            // Migration d'un seul locataire
            if ($this->option('tenant')) {
                return $this->migrateSingleTenant($this->option('tenant'));
            }

            // Migration de tous les locataires
            return $this->migrateAllTenants();

        } catch (\Exception $e) {
            $this->error("❌ Erreur : " . $e->getMessage());
            Log::error('Erreur dans DynamicTenantMigrations', [
                'error' => $e->getMessage()
            ]);
            return 1;
        } finally {
            // Nettoyage des connexions
            $this->connectionManager->cleanupDynamicConnections();
        }
    }

    /**
     * Exécute une simulation de migration
     */
    protected function performDryRun(): int
    {
        $this->info('🔍 Simulation (dry-run) : affichage des actions');
        $this->newLine();

        // Détection des bases de données
        $databases = $this->connectionManager->discoverTenantDatabases();
        $this->stats['discovered'] = count($databases);

        if (empty($databases)) {
            $this->warn('⚠️  Aucune base de données trouvée');
            return 0;
        }

        $this->info("📊 Bases détectées : {$this->stats['discovered']}");
        
        // Affichage des actions qui seraient exécutées
        foreach ($databases as $database) {
            $this->line("  📁 {$database}");
            $connectionName = "dynamic_tenant_" . str_replace('collect_', '', $database);
            $this->line("    🔗 Connexion créée : {$connectionName}");
            
            $migrationsPath = $this->option('path') ?: 'database/migrations/tenant';
            if (File::exists(base_path($migrationsPath))) {
                $migrations = File::files(base_path($migrationsPath));
                $this->line("    📋 Nombre de migrations : " . count($migrations));
            }
        }

        $this->newLine();
        $this->info('✅ Simulation terminée');
        return 0;
    }

    /**
     * Détecte simplement les bases de données
     */
    protected function performDiscovery(): int
    {
        $this->info('🔍 Détection des bases de données...');
        $this->newLine();

        $databases = $this->connectionManager->discoverTenantDatabases();
        $this->stats['discovered'] = count($databases);

        if (empty($databases)) {
            $this->warn('⚠️  Aucune base de données trouvée');
            return 0;
        }

        $this->info("📊 Bases détectées : {$this->stats['discovered']}");
        $this->newLine();

        // Affichage sous forme de tableau
        $headers = ['#', 'Base de données', 'Statut'];
        $rows = [];

        foreach ($databases as $index => $database) {
            $connectionStatus = $this->testDatabaseConnection($database);
            
            $rows[] = [
                $index + 1,
                $database,
                $connectionStatus ? '✅ Connecté' : '❌ Non connecté',
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();
        $this->info('✅ Détection terminée');
        
        return 0;
    }

    /**
     * Migre un seul locataire
     */
    protected function migrateSingleTenant(string $databaseName): int
    {
        $this->info("🎯 Migration du locataire : {$databaseName}");
        $this->newLine();

        // Vérifie l'existence de la base
        if (!$this->connectionManager->databaseExists($databaseName)) {
            $this->error("❌ Base inexistante : {$databaseName}");
            return 1;
        }

        // Exécute les migrations
        $success = $this->processTenantMigrations($databaseName);
        
        if ($success) {
            $this->info("✅ Migrations réussies : {$databaseName}");
            return 0;
        } else {
            $this->error("❌ Échec des migrations : {$databaseName}");
            return 1;
        }
    }

    /**
     * Migre tous les locataires
     */
    protected function migrateAllTenants(): int
    {
        $this->info('🌐 Migration de tous les locataires...');
        $this->newLine();

        $databases = $this->connectionManager->discoverTenantDatabases();
        $this->stats['discovered'] = count($databases);

        if (empty($databases)) {
            $this->warn('⚠️  Aucune base de données trouvée');
            return 0;
        }

        $this->info("📊 Bases à migrer : {$this->stats['discovered']}");
        $this->newLine();

        // Barre de progression
        $progressBar = $this->output->createProgressBar(count($databases));
        $progressBar->start();

        // Traitement de chaque base
        foreach ($databases as $database) {
            try {
                $this->stats['processed']++;
                
                if ($this->processTenantMigrations($database)) {
                    $this->stats['successful']++;
                } else {
                    $this->stats['failed']++;
                }
                
            } catch (\Exception $e) {
                $this->stats['failed']++;
                $this->errors[] = [
                    'database' => $database,
                    'error' => $e->getMessage()
                ];
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Affichage du rapport
        $this->displayFinalReport();

        return $this->stats['failed'] > 0 ? 1 : 0;
    }

    /**
     * Exécute les migrations pour une base donnée
     */
    protected function processTenantMigrations(string $databaseName): bool
    {
        try {
            // Crée une connexion dynamique
            $connectionName = $this->connectionManager->createDynamicConnection($databaseName);

            // Vérifie le chemin des migrations
            $migrationsPath = $this->option('path') ?: 'database/migrations/tenant';
            if (!File::exists(base_path($migrationsPath))) {
                throw new \RuntimeException("Dossier introuvable : {$migrationsPath}");
            }

            // Construit les commandes à exécuter
            $commands = $this->buildMigrationCommands($connectionName, $migrationsPath);

            // Exécute les commandes
            foreach ($commands as $commandName => $commandOptions) {
                $exitCode = Artisan::call($commandName, $commandOptions);
                
                if ($exitCode !== 0) {
                    $output = Artisan::output();
                    throw new \RuntimeException("Échec de {$commandName} : {$output}");
                }
            }

            // Exécute les seeders si demandé
            if ($this->option('seed')) {
                $this->runSeeders($connectionName);
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Erreur avec {$databaseName}", [
                'database' => $databaseName,
                'error' => $e->getMessage()
            ]);
            
            $this->errors[] = [
                'database' => $databaseName,
                'error' => $e->getMessage()
            ];
            
            return false;
        }
    }

    /**
     * Construit les commandes de migration
     */
    protected function buildMigrationCommands(string $connectionName, string $migrationsPath): array
    {
        $commands = [];

        if ($this->option('fresh')) {
            $commands['migrate:fresh'] = [
                '--database' => $connectionName,
                '--path' => $migrationsPath,
                '--force' => $this->option('force') || app()->environment('local', 'testing'),
            ];
        } else {
            $commands['migrate'] = [
                '--database' => $connectionName,
                '--path' => $migrationsPath,
                '--force' => $this->option('force') || app()->environment('local', 'testing'),
            ];
        }

        return $commands;
    }

    /**
     * Exécute les seeders
     */
    protected function runSeeders(string $connectionName): void
    {
        try {
            $exitCode = Artisan::call('db:seed', [
                '--database' => $connectionName,
                '--force' => $this->option('force') || app()->environment('local', 'testing'),
            ]);

            if ($exitCode !== 0) {
                throw new \RuntimeException('Échec des seeders : ' . Artisan::output());
            }

        } catch (\Exception $e) {
            Log::warning("Erreur de seeders pour {$connectionName}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Teste une connexion à une base
     */
    protected function testDatabaseConnection(string $databaseName): bool
    {
        try {
            $connectionName = $this->connectionManager->createDynamicConnection($databaseName);
            return $this->connectionManager->testConnection($connectionName);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Affiche le rapport final
     */
    protected function displayFinalReport(): void
    {
        $this->info('📊 Rapport final :');
        $this->info('================');
        
        $this->table(
            ['Statistique', 'Valeur'],
            [
                ['Bases détectées', $this->stats['discovered']],
                ['Bases traitées', $this->stats['processed']],
                ['Réussites', $this->stats['successful']],
                ['Échecs', $this->stats['failed']],
            ]
        );

        if (!empty($this->errors)) {
            $this->newLine();
            $this->error('❌ Erreurs rencontrées :');
            
            foreach ($this->errors as $error) {
                $this->line("  📁 {$error['database']}: {$error['error']}");
            }
        }

        $this->newLine();
        if ($this->stats['failed'] === 0) {
            $this->info('🎉 Toutes les migrations ont réussi !');
        } else {
            $this->error("⚠️  {$this->stats['successful']}/{$this->stats['processed']} migrations réussies");
        }
    }
}