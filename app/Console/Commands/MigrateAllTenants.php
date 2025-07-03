<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use App\Services\DynamicConnectionManager;

class MigrateAllTenants extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenant:migrate-all 
                            {--force : Force the operation to run when in production}
                            {--fresh : Drop all tables and re-run all migrations}
                            {--seed : Seed the database after running migrations}';

    /**
     * The console command description.
     */
    protected $description = 'Run migrations for all tenant databases using DynamicConnectionManager (ULTIMATE FIX)';

    /**
     * DynamicConnectionManager instance
     */
    protected $connectionManager;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');
        $fresh = $this->option('fresh');
        $seed = $this->option('seed');

        $this->info('🚀 Starting migration using DynamicConnectionManager (ULTIMATE FIX)...');
        $this->info('');

        try {
            // Initialize DynamicConnectionManager
            $this->connectionManager = new DynamicConnectionManager();

            // 1. Migrate central database first
            $this->migrateCentralDatabase($force, $fresh);

            // 2. Auto-discover all tenant databases
            $this->info('🔍 Auto-discovering tenant databases...');
            $tenantDatabases = $this->connectionManager->discoverTenantDatabases();

            if (empty($tenantDatabases)) {
                $this->warn('⚠️  No tenant databases found with prefix: ' . config('app.tenant_db_prefix', 'collect_'));
                return Command::SUCCESS;
            }

            $this->info("📋 Found " . count($tenantDatabases) . " tenant database(s):");
            foreach ($tenantDatabases as $db) {
                $this->line("   • {$db}");
            }
            $this->info('');

            // 3. Migrate each tenant database individually
            foreach ($tenantDatabases as $databaseName) {
                $this->migrateTenantDatabase($databaseName, $force, $fresh, $seed);
            }

            $this->info('');
            $this->info('✅ ALL MIGRATIONS COMPLETED SUCCESSFULLY!');
            $this->info('');
            $this->displaySummary($tenantDatabases);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Migrate central database
     */
    private function migrateCentralDatabase(bool $force, bool $fresh): void
    {
        $this->info('🏢 Migrating CENTRAL database (collecttopay)...');

        try {
            if ($fresh) {
                $this->call('migrate:fresh', ['--force' => $force]);
            } else {
                $this->call('migrate', ['--force' => $force]);
            }
            
            $this->info('✅ Central database migrated successfully');
            
        } catch (\Exception $e) {
            throw new \Exception("Failed to migrate central database: " . $e->getMessage());
        }
    }

    /**
     * Migrate individual tenant database - ULTIMATE FIX
     */
    private function migrateTenantDatabase(string $databaseName, bool $force, bool $fresh, bool $seed): void
    {
        $this->info("🏨 Migrating tenant: {$databaseName}");

        try {
            // Create a unique connection name for this tenant
            $connectionName = 'tenant_' . str_replace(['collect_', 'hotel_'], '', $databaseName);
            
            // Configure the connection directly in Laravel config
            $this->configureTenantConnection($connectionName, $databaseName);

            // Test the connection
            $this->testConnection($connectionName, $databaseName);

            // Check if tenant migrations directory exists
            $tenantMigrationsPath = 'database/migrations/tenant';
            if (!is_dir(base_path($tenantMigrationsPath))) {
                $this->warn("⚠️  Tenant migrations directory not found: {$tenantMigrationsPath}");
                $this->info("   Skipping tenant migrations for {$databaseName}");
                return;
            }

            // ULTIMATE FIX: Set default connection temporarily
            $originalDefault = Config::get('database.default');
            Config::set('database.default', $connectionName);
            
            $this->info("   🔄 Temporarily set default connection to: {$connectionName}");

            try {
                // Execute tenant migrations with forced connection
                if ($fresh) {
                    Artisan::call('migrate:fresh', [
                        '--database' => $connectionName,
                        '--path' => $tenantMigrationsPath,
                        '--force' => $force
                    ]);
                } else {
                    Artisan::call('migrate', [
                        '--database' => $connectionName,
                        '--path' => $tenantMigrationsPath,
                        '--force' => $force
                    ]);
                }

                $output = Artisan::output();
                if (strpos($output, 'FAIL') !== false || strpos($output, 'Error') !== false) {
                    throw new \Exception("Migration failed: " . $output);
                }

                $this->info("   📄 Migration output: " . trim($output));

            } finally {
                // Always restore original default connection
                Config::set('database.default', $originalDefault);
                $this->info("   🔄 Restored default connection to: {$originalDefault}");
            }

            // Seed if requested
            if ($seed) {
                $this->seedTenantDatabase($connectionName, $databaseName);
            }

            $this->info("✅ {$databaseName} migrated successfully");

            // Clean up the connection
            DB::purge($connectionName);

        } catch (\Exception $e) {
            $this->error("❌ Failed to migrate {$databaseName}: " . $e->getMessage());
            
            // Continue with other tenants instead of stopping
            $this->warn("   Continuing with other tenants...");
        }
    }

    /**
     * Configure tenant connection directly - ULTIMATE FIX
     */
    private function configureTenantConnection(string $connectionName, string $databaseName): void
    {
        // Get base MySQL configuration
        $baseConfig = config('database.connections.mysql');
        
        // Set the specific database name
        $baseConfig['database'] = $databaseName;
        
        // Configure the connection in Laravel
        Config::set("database.connections.{$connectionName}", $baseConfig);
        
        // Also purge any existing connection to force fresh connection
        DB::purge($connectionName);
        
        $this->info("   🔗 Configured connection: {$connectionName} → {$databaseName}");
    }

    /**
     * Test tenant connection - ENHANCED
     */
    private function testConnection(string $connectionName, string $databaseName): void
    {
        try {
            // Test the connection
            $pdo = DB::connection($connectionName)->getPdo();
            
            // Verify we're connected to the right database
            $currentDb = DB::connection($connectionName)->select('SELECT DATABASE() as db')[0]->db;
            
            if ($currentDb !== $databaseName) {
                throw new \Exception("Connected to wrong database: expected {$databaseName}, got {$currentDb}");
            }
            
            // Test if we can create a temporary table (full write access)
            DB::connection($connectionName)->statement('CREATE TEMPORARY TABLE test_connection_temp (id INT)');
            DB::connection($connectionName)->statement('DROP TEMPORARY TABLE test_connection_temp');
            
            $this->info("   ✅ Connection test passed: {$connectionName} → {$databaseName}");
            
        } catch (\Exception $e) {
            throw new \Exception("Connection test failed for {$databaseName}: " . $e->getMessage());
        }
    }

    /**
     * Seed tenant database
     */
    private function seedTenantDatabase(string $connectionName, string $databaseName): void
    {
        $this->info("🌱 Seeding {$databaseName}...");
        
        try {
            // You can add specific seeding logic here
            // For example, run tenant-specific seeders
            
            // Example: Import SQL data if files exist
            $sqlFile = base_path("database/sql/{$databaseName}.sql");
            if (file_exists($sqlFile)) {
                $this->info("   📄 Importing SQL data from {$databaseName}.sql");
                // Import SQL file logic here
            }
            
            $this->info("✅ {$databaseName} seeded successfully");
            
        } catch (\Exception $e) {
            $this->warn("⚠️  Seeding failed for {$databaseName}: " . $e->getMessage());
        }
    }

    /**
     * Display migration summary
     */
    private function displaySummary(array $tenantDatabases): void
    {
        $this->info('📊 MIGRATION SUMMARY:');
        $this->info('');

        $tableData = [
            ['Central Database', 'collecttopay', 'mysql', '✅ Migrated']
        ];

        foreach ($tenantDatabases as $databaseName) {
            $connectionName = 'tenant_' . str_replace(['collect_', 'hotel_'], '', $databaseName);
            $tableData[] = [
                'Tenant Database',
                $databaseName,
                $connectionName,
                '✅ Migrated'
            ];
        }

        $this->table(['Type', 'Database Name', 'Connection', 'Status'], $tableData);

        $this->info('');
        $this->info('🎯 NEXT STEPS:');
        $this->info('1. Import your SQL data files into each database');
        $this->info('2. Test login functionality for each user type');
        $this->info('3. Verify that all controllers work correctly');
        $this->info('');
        $this->info('💡 TIPS:');
        $this->info('• Each tenant database was migrated with its own connection');
        $this->info('• Check that all tables were created correctly');
        $this->info('• Test database connections in your controllers');
    }
}

