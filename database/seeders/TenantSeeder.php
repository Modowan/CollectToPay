<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    /**
     * Exécute les seeds de la base de données.
     *
     * @return void
     */
    public function run()
    {
        // Créer quelques tenants de test
        $tenants = [
            [
                'name' => 'Hôtel Royal Palace',
                'domain' => 'royal-palace',
                'database' => 'tenant_royal_palace',
                'status' => 'active',
            ],
            [
                'name' => 'Hôtel Méditerranée',
                'domain' => 'mediterranee',
                'database' => 'tenant_mediterranee',
                'status' => 'active',
            ],
            [
                'name' => 'Resort Oasis',
                'domain' => 'oasis',
                'database' => 'tenant_oasis',
                'status' => 'active',
            ],
            [
                'name' => 'Hôtel Montagne',
                'domain' => 'montagne',
                'database' => 'tenant_montagne',
                'status' => 'inactive',
            ],
        ];

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::create([
                'name' => $tenantData['name'],
                'domain' => $tenantData['domain'],
                'database' => $tenantData['database'],
                'status' => $tenantData['status'],
            ]);

            // Créer un domaine pour ce tenant
            $tenant->domains()->create([
                'domain' => $tenantData['domain'] . '.' . config('app.domain', 'localhost'),
            ]);

            // Initialiser le tenant pour créer sa base de données
            $tenant->initialize();

            // Exécuter les seeders spécifiques au tenant
            $tenant->run(function () {
                // Exécuter les seeders pour ce tenant
                $this->call([
                    \Database\Seeders\CustomerSeeder::class,
                ]);
            });
        }
    }
}
