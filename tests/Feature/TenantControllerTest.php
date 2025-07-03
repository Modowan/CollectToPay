<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TenantControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test l'affichage de la liste des tenants pour un administrateur.
     *
     * @return void
     */
    public function test_admin_can_view_tenants_list()
    {
        // Créer un utilisateur administrateur
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Créer quelques tenants pour le test
        $tenants = Tenant::factory()->count(3)->create();

        // Simuler une connexion en tant qu'administrateur
        $this->actingAs($admin);

        // Faire une requête GET vers la route d'index des tenants
        $response = $this->get(route('tenants.index'));

        // Vérifier que la réponse est réussie
        $response->assertStatus(200);

        // Vérifier que la vue contient les tenants créés
        foreach ($tenants as $tenant) {
            $response->assertSee($tenant->name);
        }
    }

    /**
     * Test qu'un utilisateur non-admin ne peut pas voir la liste des tenants.
     *
     * @return void
     */
    public function test_non_admin_cannot_view_tenants_list()
    {
        // Créer un utilisateur non-administrateur
        $user = User::factory()->create([
            'role' => 'tenant_admin',
        ]);

        // Simuler une connexion en tant qu'utilisateur non-administrateur
        $this->actingAs($user);

        // Faire une requête GET vers la route d'index des tenants
        $response = $this->get(route('tenants.index'));

        // Vérifier que l'accès est refusé
        $response->assertStatus(403);
    }

    /**
     * Test la création d'un nouveau tenant par un administrateur.
     *
     * @return void
     */
    public function test_admin_can_create_tenant()
    {
        // Créer un utilisateur administrateur
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Simuler une connexion en tant qu'administrateur
        $this->actingAs($admin);

        // Données pour le nouveau tenant
        $tenantData = [
            'name' => 'Nouveau Tenant Test',
            'domain' => 'nouveau-tenant-test',
            'database' => 'tenant_nouveau_tenant_test',
            'status' => 'active',
        ];

        // Faire une requête POST vers la route de création de tenant
        $response = $this->post(route('tenants.store'), $tenantData);

        // Vérifier la redirection vers la liste des tenants
        $response->assertRedirect(route('tenants.index'));

        // Vérifier que le tenant a été créé dans la base de données
        $this->assertDatabaseHas('tenants', [
            'name' => 'Nouveau Tenant Test',
            'domain' => 'nouveau-tenant-test',
        ]);
    }

    /**
     * Test la mise à jour d'un tenant existant par un administrateur.
     *
     * @return void
     */
    public function test_admin_can_update_tenant()
    {
        // Créer un utilisateur administrateur
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Créer un tenant pour le test
        $tenant = Tenant::factory()->create([
            'name' => 'Tenant Original',
            'domain' => 'tenant-original',
        ]);

        // Simuler une connexion en tant qu'administrateur
        $this->actingAs($admin);

        // Données pour la mise à jour du tenant
        $updatedData = [
            'name' => 'Tenant Modifié',
            'domain' => 'tenant-modifie',
            'status' => 'active',
        ];

        // Faire une requête PUT vers la route de mise à jour du tenant
        $response = $this->put(route('tenants.update', $tenant), $updatedData);

        // Vérifier la redirection vers la page de détails du tenant
        $response->assertRedirect(route('tenants.show', $tenant));

        // Vérifier que le tenant a été mis à jour dans la base de données
        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name' => 'Tenant Modifié',
            'domain' => 'tenant-modifie',
        ]);
    }

    /**
     * Test la suppression d'un tenant par un administrateur.
     *
     * @return void
     */
    public function test_admin_can_delete_tenant()
    {
        // Créer un utilisateur administrateur
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Créer un tenant pour le test
        $tenant = Tenant::factory()->create();

        // Simuler une connexion en tant qu'administrateur
        $this->actingAs($admin);

        // Faire une requête DELETE vers la route de suppression du tenant
        $response = $this->delete(route('tenants.destroy', $tenant));

        // Vérifier la redirection vers la liste des tenants
        $response->assertRedirect(route('tenants.index'));

        // Vérifier que le tenant a été supprimé de la base de données
        $this->assertDatabaseMissing('tenants', [
            'id' => $tenant->id,
        ]);
    }

    /**
     * Test qu'un administrateur de tenant peut voir les détails de son propre tenant.
     *
     * @return void
     */
    public function test_tenant_admin_can_view_own_tenant_details()
    {
        // Créer un tenant pour le test
        $tenant = Tenant::factory()->create();

        // Créer un utilisateur administrateur de tenant
        $tenantAdmin = User::factory()->create([
            'role' => 'tenant_admin',
            'tenant_id' => $tenant->id,
        ]);

        // Simuler une connexion en tant qu'administrateur de tenant
        $this->actingAs($tenantAdmin);

        // Faire une requête GET vers la route de détails du tenant
        $response = $this->get(route('tenants.show', $tenant));

        // Vérifier que la réponse est réussie
        $response->assertStatus(200);

        // Vérifier que la vue contient le nom du tenant
        $response->assertSee($tenant->name);
    }

    /**
     * Test qu'un administrateur de tenant ne peut pas voir les détails d'un autre tenant.
     *
     * @return void
     */
    public function test_tenant_admin_cannot_view_other_tenant_details()
    {
        // Créer deux tenants pour le test
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        // Créer un utilisateur administrateur du premier tenant
        $tenantAdmin = User::factory()->create([
            'role' => 'tenant_admin',
            'tenant_id' => $tenant1->id,
        ]);

        // Simuler une connexion en tant qu'administrateur du premier tenant
        $this->actingAs($tenantAdmin);

        // Faire une requête GET vers la route de détails du deuxième tenant
        $response = $this->get(route('tenants.show', $tenant2));

        // Vérifier que l'accès est refusé
        $response->assertStatus(403);
    }
}
