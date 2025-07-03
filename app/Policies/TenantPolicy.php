<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TenantPolicy
{
    use HandlesAuthorization;

    /**
     * Détermine si l'utilisateur peut voir la liste des tenants.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        // Seul l'administrateur global peut voir tous les tenants
        return $user->role === 'admin';
    }

    /**
     * Détermine si l'utilisateur peut voir un tenant spécifique.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tenant  $tenant
     * @return bool
     */
    public function view(User $user, Tenant $tenant)
    {
        // L'administrateur global peut voir n'importe quel tenant
        if ($user->role === 'admin') {
            return true;
        }

        // L'administrateur du tenant peut voir son propre tenant
        if ($user->role === 'tenant_admin' && $user->tenant_id === $tenant->id) {
            return true;
        }

        // L'administrateur d'une branche peut voir le tenant auquel sa branche appartient
        if ($user->role === 'branch_admin') {
            $branch = $user->branch;
            if ($branch && $branch->tenant_id === $tenant->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut créer un tenant.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        // Seul l'administrateur global peut créer des tenants
        return $user->role === 'admin';
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour un tenant.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tenant  $tenant
     * @return bool
     */
    public function update(User $user, Tenant $tenant)
    {
        // L'administrateur global peut mettre à jour n'importe quel tenant
        if ($user->role === 'admin') {
            return true;
        }

        // L'administrateur du tenant peut mettre à jour son propre tenant
        if ($user->role === 'tenant_admin' && $user->tenant_id === $tenant->id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut supprimer un tenant.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tenant  $tenant
     * @return bool
     */
    public function delete(User $user, Tenant $tenant)
    {
        // Seul l'administrateur global peut supprimer des tenants
        return $user->role === 'admin';
    }

    /**
     * Détermine si l'utilisateur peut restaurer un tenant supprimé.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tenant  $tenant
     * @return bool
     */
    public function restore(User $user, Tenant $tenant)
    {
        // Seul l'administrateur global peut restaurer des tenants
        return $user->role === 'admin';
    }

    /**
     * Détermine si l'utilisateur peut supprimer définitivement un tenant.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tenant  $tenant
     * @return bool
     */
    public function forceDelete(User $user, Tenant $tenant)
    {
        // Seul l'administrateur global peut supprimer définitivement des tenants
        return $user->role === 'admin';
    }
}
