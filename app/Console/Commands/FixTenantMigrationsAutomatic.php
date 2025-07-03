<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixTenantMigrationsAutomatic extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenant:fix-migrations 
                            {--path=database/migrations/tenant : Path to tenant migrations}
                            {--dry-run : Show what would be changed without making changes}
                            {--backup : Create backup files before modification}';

    /**
     * The console command description.
     */
    protected $description = 'Fix tenant migrations to use dynamic connections instead of hardcoded "tenant" connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Correction automatique des migrations tenant...');
        $this->newLine();

        $migrationsPath = base_path($this->option('path'));
        $dryRun = $this->option('dry-run');
        $backup = $this->option('backup');

        if (!File::exists($migrationsPath)) {
            $this->error("❌ Le dossier {$migrationsPath} n'existe pas !");
            return 1;
        }

        $migrationFiles = File::glob($migrationsPath . '/*.php');
        
        if (empty($migrationFiles)) {
            $this->warn("⚠️ Aucun fichier de migration trouvé dans {$migrationsPath}");
            return 0;
        }

        $this->info("📁 Dossier analysé : {$migrationsPath}");
        $this->info("📄 Fichiers trouvés : " . count($migrationFiles));
        $this->newLine();

        $fixedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($migrationFiles as $filePath) {
            $fileName = basename($filePath);
            $this->line("🔍 Analyse : {$fileName}");

            try {
                $content = File::get($filePath);
                $originalContent = $content;

                // Rechercher et remplacer les connexions codées en dur
                $patterns = [
                    // Schema::connection('tenant')->
                    "/Schema::connection\s*\(\s*['\"]tenant['\"]\s*\)\s*->/i" => "Schema::connection(\$this->getConnection())->",
                    
                    // Schema::connection("tenant")->
                    "/Schema::connection\s*\(\s*[\"']tenant[\"']\s*\)\s*->/i" => "Schema::connection(\$this->getConnection())->",
                ];

                $hasChanges = false;
                foreach ($patterns as $pattern => $replacement) {
                    if (preg_match($pattern, $content)) {
                        $content = preg_replace($pattern, $replacement, $content);
                        $hasChanges = true;
                    }
                }

                if ($hasChanges) {
                    if ($dryRun) {
                        $this->info("   ✅ Serait corrigé (dry-run)");
                        $this->showDiff($originalContent, $content);
                    } else {
                        // Créer une sauvegarde si demandé
                        if ($backup) {
                            $backupPath = $filePath . '.backup.' . date('Y-m-d_H-i-s');
                            File::copy($filePath, $backupPath);
                            $this->line("   💾 Sauvegarde : " . basename($backupPath));
                        }

                        // Écrire le fichier corrigé
                        File::put($filePath, $content);
                        $this->info("   ✅ Corrigé avec succès");
                    }
                    $fixedCount++;
                } else {
                    $this->line("   ⏭️ Aucune correction nécessaire");
                    $skippedCount++;
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Erreur : " . $e->getMessage());
                $errorCount++;
            }

            $this->newLine();
        }

        // Résumé
        $this->info('📊 RÉSUMÉ :');
        $this->table(
            ['Statut', 'Nombre', 'Description'],
            [
                ['✅ Corrigés', $fixedCount, 'Fichiers modifiés avec succès'],
                ['⏭️ Ignorés', $skippedCount, 'Fichiers sans modification nécessaire'],
                ['❌ Erreurs', $errorCount, 'Fichiers avec erreurs'],
                ['📄 Total', count($migrationFiles), 'Total des fichiers analysés']
            ]
        );

        if ($dryRun) {
            $this->warn('🔍 Mode dry-run activé - Aucune modification effectuée');
            $this->info('💡 Relancez sans --dry-run pour appliquer les corrections');
        } elseif ($fixedCount > 0) {
            $this->info('🎉 Corrections appliquées avec succès !');
            $this->info('🚀 Vous pouvez maintenant tester vos migrations :');
            $this->line('   php artisan tenants:migrate-dynamic --force');
        }

        return $errorCount > 0 ? 1 : 0;
    }

    /**
     * Affiche les différences entre l'ancien et le nouveau contenu
     */
    private function showDiff(string $original, string $modified): void
    {
        $originalLines = explode("\n", $original);
        $modifiedLines = explode("\n", $modified);

        for ($i = 0; $i < max(count($originalLines), count($modifiedLines)); $i++) {
            $originalLine = $originalLines[$i] ?? '';
            $modifiedLine = $modifiedLines[$i] ?? '';

            if ($originalLine !== $modifiedLine) {
                if (!empty($originalLine)) {
                    $this->line("      <fg=red>- {$originalLine}</>");
                }
                if (!empty($modifiedLine)) {
                    $this->line("      <fg=green>+ {$modifiedLine}</>");
                }
            }
        }
    }

    /**
     * Ajoute la méthode getConnection() si elle n'existe pas
     */
    private function addGetConnectionMethod(string $content): string
    {
        // Vérifier si la méthode getConnection() existe déjà
        if (strpos($content, 'getConnection()') !== false) {
            return $content;
        }

        // Chercher la fin de la classe pour ajouter la méthode
        $pattern = '/(\s*)(}\s*;?\s*)$/';
        
        $method = '
    /**
     * Get the migration connection name.
     */
    public function getConnection()
    {
        return $this->connection;
    }
';

        return preg_replace($pattern, $method . '$1$2', $content);
    }
}

