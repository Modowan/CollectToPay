<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateAllMigrations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'generate:all-migrations 
                            {--central-sql= : Path to central database SQL file (default: collecttopay_emails_realistes.sql)}
                            {--tenant-sql= : Path to tenant database SQL file (default: collect_hotel_hilton_paris_emails_realistes.sql)}
                            {--output-dir=database/migrations : Output directory for migrations}
                            {--clean : Remove existing migrations before generating new ones}';

    /**
     * The console command description.
     */
    protected $description = 'Generate ALL Laravel migrations from existing SQL files (Windows compatible)';

    /**
     * Type mappings from MySQL to Laravel
     */
    private $typeMappings = [
        'bigint(20) UNSIGNED' => 'id',
        'bigint(20)' => 'bigInteger',
        'int(11)' => 'integer',
        'varchar(255)' => 'string',
        'varchar(100)' => 'string:100',
        'varchar(45)' => 'string:45',
        'varchar(20)' => 'string:20',
        'text' => 'text',
        'longtext' => 'longText',
        'timestamp' => 'timestamp',
        'date' => 'date',
        'decimal(10,2)' => 'decimal:10,2',
        'decimal(8,2)' => 'decimal:8,2',
        'tinyint(1)' => 'boolean',
        'enum' => 'enum',
        'json' => 'json',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Déterminer les chemins des fichiers SQL
        $centralSqlPath = $this->getCentralSqlPath();
        $tenantSqlPath = $this->getTenantSqlPath();
        $outputDir = $this->option('output-dir');
        $clean = $this->option('clean');

        $this->info('🚀 Generating ALL migrations from SQL files...');
        $this->info("📁 Central SQL: {$centralSqlPath}");
        $this->info("📁 Tenant SQL: {$tenantSqlPath}");

        try {
            // Vérifier que les fichiers existent
            $this->validateSqlFiles($centralSqlPath, $tenantSqlPath);

            // Clean existing migrations if requested
            if ($clean) {
                $this->cleanExistingMigrations($outputDir);
            }

            // Generate central database migrations
            $this->generateCentralMigrations($centralSqlPath, $outputDir);
            
            // Generate tenant database migrations
            $this->generateTenantMigrations($tenantSqlPath, $outputDir);
            
            // Generate summary
            $this->generateSummary($outputDir);
            
            $this->info('✅ ALL migrations generated successfully!');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->info('');
            $this->info('💡 SOLUTIONS:');
            $this->info('1. Vérifiez que les fichiers SQL sont dans le dossier racine de votre projet');
            $this->info('2. Ou spécifiez les chemins complets:');
            $this->info('   php artisan generate:all-migrations --central-sql="C:\\path\\to\\central.sql" --tenant-sql="C:\\path\\to\\tenant.sql"');
            return Command::FAILURE;
        }
    }

    /**
     * Get central SQL file path
     */
    private function getCentralSqlPath(): string
    {
        $customPath = $this->option('central-sql');
        
        if ($customPath) {
            return $customPath;
        }

        // Chemins par défaut à tester (Windows et Linux)
        $defaultPaths = [
            base_path('collecttopay_emails_realistes.sql'),                    // Dossier racine Laravel
            base_path('database/sql/collecttopay_emails_realistes.sql'),       // Dossier database/sql
            'collecttopay_emails_realistes.sql',                               // Dossier courant
            'C:\\xampp\\htdocs\\CollectToPay\\collecttopay_emails_realistes.sql', // XAMPP Windows
        ];

        foreach ($defaultPaths as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        return base_path('collecttopay_emails_realistes.sql'); // Fallback
    }

    /**
     * Get tenant SQL file path
     */
    private function getTenantSqlPath(): string
    {
        $customPath = $this->option('tenant-sql');
        
        if ($customPath) {
            return $customPath;
        }

        // Chemins par défaut à tester (Windows et Linux)
        $defaultPaths = [
            base_path('collect_hotel_hilton_paris_emails_realistes.sql'),                    // Dossier racine Laravel
            base_path('database/sql/collect_hotel_hilton_paris_emails_realistes.sql'),       // Dossier database/sql
            'collect_hotel_hilton_paris_emails_realistes.sql',                               // Dossier courant
            'C:\\xampp\\htdocs\\CollectToPay\\collect_hotel_hilton_paris_emails_realistes.sql', // XAMPP Windows
        ];

        foreach ($defaultPaths as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        return base_path('collect_hotel_hilton_paris_emails_realistes.sql'); // Fallback
    }

    /**
     * Validate SQL files exist
     */
    private function validateSqlFiles(string $centralPath, string $tenantPath): void
    {
        if (!File::exists($centralPath)) {
            throw new \Exception("Central SQL file not found: {$centralPath}");
        }

        if (!File::exists($tenantPath)) {
            throw new \Exception("Tenant SQL file not found: {$tenantPath}");
        }

        $this->info('✅ SQL files found and validated');
    }

    /**
     * Clean existing migrations
     */
    private function cleanExistingMigrations(string $outputDir): void
    {
        $this->warn('🧹 Cleaning existing migrations...');
        
        if (File::exists($outputDir)) {
            $files = File::glob($outputDir . '/*.php');
            foreach ($files as $file) {
                File::delete($file);
                $this->info("🗑️  Deleted: " . basename($file));
            }
        }
        
        // Clean tenant migrations directory
        $tenantDir = $outputDir . '/tenant';
        if (File::exists($tenantDir)) {
            $files = File::glob($tenantDir . '/*.php');
            foreach ($files as $file) {
                File::delete($file);
                $this->info("🗑️  Deleted: tenant/" . basename($file));
            }
        }
    }

    /**
     * Generate migrations for central database
     */
    private function generateCentralMigrations(string $sqlPath, string $outputDir): void
    {
        $this->info('📊 Processing central database...');
        
        $sqlContent = File::get($sqlPath);
        $tables = $this->extractTablesFromSQL($sqlContent);
        
        $this->info("📋 Found " . count($tables) . " tables in central database");
        
        $counter = 1;
        foreach ($tables as $tableName => $tableStructure) {
            $migrationName = sprintf('2024_01_01_%06d_create_%s_table.php', $counter, $tableName);
            $migrationPath = $outputDir . '/' . $migrationName;
            
            $migrationContent = $this->generateMigrationContent($tableName, $tableStructure, false);
            File::put($migrationPath, $migrationContent);
            
            $this->info("✅ Created: {$migrationName}");
            $counter++;
        }
    }

    /**
     * Generate migrations for tenant database
     */
    private function generateTenantMigrations(string $sqlPath, string $outputDir): void
    {
        $this->info('🏨 Processing tenant database...');
        
        $sqlContent = File::get($sqlPath);
        $tables = $this->extractTablesFromSQL($sqlContent);
        
        $this->info("📋 Found " . count($tables) . " tables in tenant database");
        
        // Create tenant migrations directory
        $tenantDir = $outputDir . '/tenant';
        if (!File::exists($tenantDir)) {
            File::makeDirectory($tenantDir, 0755, true);
        }
        
        $counter = 1;
        foreach ($tables as $tableName => $tableStructure) {
            $migrationName = sprintf('tenant_2024_01_01_%06d_create_%s_table.php', $counter, $tableName);
            $migrationPath = $tenantDir . '/' . $migrationName;
            
            $migrationContent = $this->generateMigrationContent($tableName, $tableStructure, true);
            File::put($migrationPath, $migrationContent);
            
            $this->info("✅ Created: tenant/{$migrationName}");
            $counter++;
        }
    }

    /**
     * Extract table structures from SQL content
     */
    private function extractTablesFromSQL(string $sqlContent): array
    {
        $tables = [];
        
        // Match CREATE TABLE statements with better regex
        preg_match_all('/CREATE TABLE `([^`]+)` \((.*?)\) ENGINE=/s', $sqlContent, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $tableName = $match[1];
            $tableContent = $match[2];
            
            $tables[$tableName] = $this->parseTableStructure($tableContent);
        }
        
        return $tables;
    }

    /**
     * Parse table structure from CREATE TABLE content
     */
    private function parseTableStructure(string $tableContent): array
    {
        $structure = [
            'columns' => [],
            'indexes' => [],
            'foreign_keys' => []
        ];
        
        $lines = explode("\n", $tableContent);
        
        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B,");
            
            if (empty($line)) continue;
            
            // Parse column definitions
            if (preg_match('/^`([^`]+)` (.+)$/', $line, $matches)) {
                $columnName = $matches[1];
                $columnDef = $matches[2];
                
                $structure['columns'][$columnName] = $this->parseColumnDefinition($columnDef);
            }
            
            // Parse indexes
            if (preg_match('/^(?:PRIMARY )?KEY `?([^`\s]*)`? \((.+?)\)/', $line, $matches)) {
                $indexName = $matches[1] ?: 'primary';
                $columns = str_replace('`', '', $matches[2]);
                
                $structure['indexes'][$indexName] = $columns;
            }
        }
        
        return $structure;
    }

    /**
     * Parse individual column definition
     */
    private function parseColumnDefinition(string $definition): array
    {
        $column = [
            'type' => 'string',
            'nullable' => true,
            'default' => null,
            'auto_increment' => false,
            'unique' => false,
            'enum_values' => null
        ];
        
        // Extract type
        if (preg_match('/^(\w+(?:\([^)]+\))?(?:\s+UNSIGNED)?)/', $definition, $matches)) {
            $type = $matches[1];
            $column['type'] = $this->mapMySQLTypeToLaravel($type);
            
            // Handle enum values
            if (str_starts_with($type, 'enum(')) {
                preg_match('/enum\((.+?)\)/', $type, $enumMatches);
                if (isset($enumMatches[1])) {
                    $column['enum_values'] = $enumMatches[1];
                }
            }
        }
        
        // Check for NULL/NOT NULL
        $column['nullable'] = !str_contains($definition, 'NOT NULL');
        
        // Check for AUTO_INCREMENT
        $column['auto_increment'] = str_contains($definition, 'AUTO_INCREMENT');
        
        // Check for UNIQUE
        $column['unique'] = str_contains($definition, 'UNIQUE');
        
        // Extract default value
        if (preg_match('/DEFAULT (.+?)(?:\s|$)/', $definition, $matches)) {
            $defaultValue = trim($matches[1]);
            if ($defaultValue !== 'NULL') {
                $column['default'] = $defaultValue;
            }
        }
        
        return $column;
    }

    /**
     * Map MySQL types to Laravel migration methods
     */
    private function mapMySQLTypeToLaravel(string $mysqlType): string
    {
        // Handle specific patterns first
        if (str_contains($mysqlType, 'bigint(20) UNSIGNED') && str_contains($mysqlType, 'AUTO_INCREMENT')) {
            return 'id';
        }
        
        if (str_contains($mysqlType, 'bigint(20) UNSIGNED')) {
            return 'unsignedBigInteger';
        }
        
        if (str_starts_with($mysqlType, 'varchar(')) {
            preg_match('/varchar\((\d+)\)/', $mysqlType, $matches);
            $length = $matches[1] ?? 255;
            return $length == 255 ? 'string' : "string:{$length}";
        }
        
        if (str_starts_with($mysqlType, 'enum(')) {
            return 'enum';
        }
        
        if (str_starts_with($mysqlType, 'decimal(')) {
            preg_match('/decimal\((\d+),(\d+)\)/', $mysqlType, $matches);
            if (isset($matches[1], $matches[2])) {
                return "decimal:{$matches[1]},{$matches[2]}";
            }
            return 'decimal:8,2';
        }
        
        // Standard mappings
        $mappings = [
            'int(11)' => 'integer',
            'text' => 'text',
            'longtext' => 'longText',
            'timestamp' => 'timestamp',
            'date' => 'date',
            'tinyint(1)' => 'boolean',
        ];
        
        foreach ($mappings as $pattern => $laravelType) {
            if (str_contains($mysqlType, $pattern)) {
                return $laravelType;
            }
        }
        
        // Default fallback
        return 'string';
    }

    /**
     * Generate migration file content
     */
    private function generateMigrationContent(string $tableName, array $structure, bool $isTenant): string
    {
        $className = 'Create' . Str::studly($tableName) . 'Table';
        
        $content = "<?php\n\n";
        $content .= "use Illuminate\Database\Migrations\Migration;\n";
        $content .= "use Illuminate\Database\Schema\Blueprint;\n";
        $content .= "use Illuminate\Support\Facades\Schema;\n\n";
        $content .= "return new class extends Migration\n{\n";
        $content .= "    /**\n";
        $content .= "     * Run the migrations.\n";
        if ($isTenant) {
            $content .= "     * This migration will be executed on each tenant database.\n";
        }
        $content .= "     */\n";
        $content .= "    public function up(): void\n";
        $content .= "    {\n";
        
        // Use tenant connection for tenant migrations
        if ($isTenant) {
            $content .= "        Schema::connection('tenant')->create('{$tableName}', function (Blueprint \$table) {\n";
        } else {
            $content .= "        Schema::create('{$tableName}', function (Blueprint \$table) {\n";
        }
        
        // Generate column definitions
        $hasTimestamps = false;
        foreach ($structure['columns'] as $columnName => $columnDef) {
            if (in_array($columnName, ['created_at', 'updated_at'])) {
                $hasTimestamps = true;
                continue;
            }
            $content .= $this->generateColumnDefinition($columnName, $columnDef);
        }
        
        // Add timestamps if found
        if ($hasTimestamps) {
            $content .= "            \$table->timestamps();\n";
        }
        
        // Generate indexes (skip primary key as it's handled by id())
        foreach ($structure['indexes'] as $indexName => $columns) {
            if ($indexName === 'primary' || $indexName === 'PRIMARY') continue;
            
            $cleanColumns = str_replace(['`', ' '], '', $columns);
            $columnsArray = explode(',', $cleanColumns);
            
            if (count($columnsArray) === 1) {
                $content .= "            \$table->index('{$columnsArray[0]}');\n";
            } else {
                $columnsList = "['" . implode("', '", $columnsArray) . "']";
                $content .= "            \$table->index({$columnsList});\n";
            }
        }
        
        $content .= "        });\n";
        $content .= "    }\n\n";
        $content .= "    /**\n";
        $content .= "     * Reverse the migrations.\n";
        $content .= "     */\n";
        $content .= "    public function down(): void\n";
        $content .= "    {\n";
        
        if ($isTenant) {
            $content .= "        Schema::connection('tenant')->dropIfExists('{$tableName}');\n";
        } else {
            $content .= "        Schema::dropIfExists('{$tableName}');\n";
        }
        
        $content .= "    }\n";
        $content .= "};\n";
        
        return $content;
    }

    /**
     * Generate column definition for migration
     */
    private function generateColumnDefinition(string $columnName, array $columnDef): string
    {
        $line = "            ";
        
        // Handle ID column
        if ($columnName === 'id' && $columnDef['auto_increment']) {
            $line .= "\$table->id();\n";
            return $line;
        }
        
        $type = $columnDef['type'];
        
        // Handle enum type
        if ($type === 'enum' && $columnDef['enum_values']) {
            $line .= "\$table->enum('{$columnName}', [{$columnDef['enum_values']}])";
        } else {
            $line .= "\$table->{$type}('{$columnName}')";
        }
        
        // Add modifiers
        if ($columnDef['nullable']) {
            $line .= "->nullable()";
        }
        
        if ($columnDef['default'] !== null) {
            $defaultValue = $columnDef['default'];
            if ($defaultValue === 'current_timestamp()' || $defaultValue === 'CURRENT_TIMESTAMP') {
                $line .= "->useCurrent()";
            } elseif (is_numeric($defaultValue)) {
                $line .= "->default({$defaultValue})";
            } else {
                $line .= "->default('{$defaultValue}')";
            }
        }
        
        if ($columnDef['unique']) {
            $line .= "->unique()";
        }
        
        $line .= ";\n";
        
        return $line;
    }

    /**
     * Generate summary of created migrations
     */
    private function generateSummary(string $outputDir): void
    {
        $this->info('');
        $this->info('📊 MIGRATION GENERATION SUMMARY:');
        $this->info('');
        
        // Count central migrations
        $centralFiles = File::glob($outputDir . '/*.php');
        $centralCount = count($centralFiles);
        
        // Count tenant migrations
        $tenantDir = $outputDir . '/tenant';
        $tenantFiles = File::exists($tenantDir) ? File::glob($tenantDir . '/*.php') : [];
        $tenantCount = count($tenantFiles);
        
        $this->table(['Type', 'Count', 'Location'], [
            ['Central Database', $centralCount, 'database/migrations/'],
            ['Tenant Database', $tenantCount, 'database/migrations/tenant/'],
            ['TOTAL', $centralCount + $tenantCount, 'All migrations']
        ]);
        
        $this->info('');
        $this->info('🚀 NEXT STEPS:');
        $this->info('1. Run: php artisan migrate --force');
        $this->info('2. For tenants: php artisan tenant:migrate {database_name}');
        $this->info('3. Seed data: php artisan db:seed');
        $this->info('');
        $this->info('💡 TIP: Place your SQL files in the project root for automatic detection');
    }
}

