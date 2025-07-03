<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Exécute les seeds de la base de données.
     *
     * @return void
     */
    public function run()
    {
        // Créer un administrateur global
        User::create([
            'name' => 'Admin Global',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'tenant_id' => null,
            'branch_id' => null,
            'status' => 'active',
        ]);

        // Récupérer tous les tenants
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Créer un administrateur pour chaque tenant
            User::create([
                'name' => 'Admin ' . $tenant->name,
                'email' => 'admin@' . $tenant->domain . '.com',
                'password' => Hash::make('password'),
                'role' => 'tenant_admin',
                'tenant_id' => $tenant->id,
                'branch_id' => null,
                'status' => 'active',
            ]);

            // Récupérer toutes les branches du tenant
            $branches = Branch::where('tenant_id', $tenant->id)->get();

            foreach ($branches as $branch) {
                // Créer un administrateur pour chaque branche
                User::create([
                    'name' => 'Admin ' . $branch->name,
                    'email' => 'admin.' . strtolower(str_replace(' ', '', $branch->name)) . '@' . $tenant->domain . '.com',
                    'password' => Hash::make('password'),
                    'role' => 'branch_admin',
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branch->id,
                    'status' => 'active',
                ]);

                // Créer quelques utilisateurs standards pour chaque branche
                for ($i = 1; $i <= 3; $i++) {
                    User::create([
                        'name' => 'Utilisateur ' . $i . ' ' . $branch->name,
                        'email' => 'user' . $i . '.' . strtolower(str_replace(' ', '', $branch->name)) . '@' . $tenant->domain . '.com',
                        'password' => Hash::make('password'),
                        'role' => 'user',
                        'tenant_id' => $tenant->id,
                        'branch_id' => $branch->id,
                        'status' => 'active',
                    ]);
                }
            }
        }
    }
}
