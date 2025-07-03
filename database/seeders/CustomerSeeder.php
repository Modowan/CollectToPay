<?php

namespace Database\Seeders;

use App\Models\Tenant\Customer;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    /**
     * Exécute les seeds de la base de données.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('fr_FR');
        
        // Récupérer l'ID de la branche actuelle dans le contexte du tenant
        $branchId = tenant()->run(function () {
            // Dans un contexte réel, nous récupérerions les branches du tenant actuel
            // Mais comme nous sommes dans un seeder, nous allons simuler cela
            return 1; // ID de la première branche
        });
        
        // Créer 20 clients pour cette branche
        for ($i = 0; $i < 20; $i++) {
            tenant()->run(function () use ($faker, $branchId, $i) {
                Customer::create([
                    'branch_id' => $branchId,
                    'name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'phone' => $faker->phoneNumber,
                    'address' => $faker->address,
                    'status' => $i < 18 ? 'active' : 'inactive', // 90% actifs, 10% inactifs
                    'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                    'updated_at' => now(),
                ]);
            });
        }
    }
}
