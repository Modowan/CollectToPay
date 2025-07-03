<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Exécute les seeds de la base de données.
     *
     * @return void
     */
    public function run()
    {
        // Récupérer tous les tenants actifs
        $tenants = Tenant::where('status', 'active')->get();

        foreach ($tenants as $tenant) {
            // Créer des branches pour chaque tenant
            $branches = [
                [
                    'name' => 'Siège Principal',
                    'address' => '123 Avenue Centrale',
                    'city' => 'Paris',
                    'country' => 'France',
                    'phone' => '+33 1 23 45 67 89',
                    'email' => 'siege@' . $tenant->domain . '.com',
                    'status' => 'active',
                ],
                [
                    'name' => 'Succursale Nord',
                    'address' => '45 Rue du Nord',
                    'city' => 'Lille',
                    'country' => 'France',
                    'phone' => '+33 3 20 12 34 56',
                    'email' => 'nord@' . $tenant->domain . '.com',
                    'status' => 'active',
                ],
                [
                    'name' => 'Succursale Sud',
                    'address' => '78 Boulevard Méditerranéen',
                    'city' => 'Nice',
                    'country' => 'France',
                    'phone' => '+33 4 93 12 34 56',
                    'email' => 'sud@' . $tenant->domain . '.com',
                    'status' => 'active',
                ],
            ];

            // Si le tenant est "Hôtel Montagne", ajouter une branche inactive
            if ($tenant->name === 'Hôtel Montagne') {
                $branches[] = [
                    'name' => 'Succursale Temporaire',
                    'address' => '12 Chemin des Pistes',
                    'city' => 'Chamonix',
                    'country' => 'France',
                    'phone' => '+33 4 50 12 34 56',
                    'email' => 'temp@' . $tenant->domain . '.com',
                    'status' => 'inactive',
                ];
            }

            // Créer les branches pour ce tenant
            foreach ($branches as $branchData) {
                Branch::create([
                    'tenant_id' => $tenant->id,
                    'name' => $branchData['name'],
                    'address' => $branchData['address'],
                    'city' => $branchData['city'],
                    'country' => $branchData['country'],
                    'phone' => $branchData['phone'],
                    'email' => $branchData['email'],
                    'status' => $branchData['status'],
                ]);
            }
        }
    }
}
