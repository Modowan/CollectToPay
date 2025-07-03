<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BranchPolicy
{
    use HandlesAuthorization;

    /**
     * Détermine si l'utilisateur peut voir la liste des branches.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tenant  $tenant
     * @return bool
     */
    public function viewAny(User $user, Tenant $tenant = null)
    {
        // L'administrateur global peut voir toutes les branches
        if ($user->role === 'admin') {
            return true;
        }

        // L'administrateur du tenant peut voir les branches de son tenant
        if ($user->role === 'tenant_admin' && $tenant && $user->tenant_id === $tenant->id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut voir une branche spécifique.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Branch  $branch
     * @return bool
     */
    public function view(User $user, Branch $branch)
    {
        // L'administrateur global peut voir n'importe quelle branche
        if ($user->role === 'admin') {
            return true;
        }

        // L'administrateur du tenant peut voir les branches de son tenant
        if ($user->role === 'tenant_admin' && $user->tenant_id === $branch->tenant_id) {
            return true;
        }

        // L'administrateur de branche peut voir sa propre branche
        if ($user->role === 'branch_admin' && $user->branch_id === $branch->id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut créer une branche.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tenant  $tenant
     * @return bool
     */
    public function create(User $user, Tenant $tenant = null)
    {
        // L'administrateur global peut créer des branches pour n'importe quel tenant
        if ($user->role === 'admin') {
            return true;
        }

        // L'administrateur du tenant peut créer des branches pour son tenant
        if ($user->role === 'tenant_admin' && $tenant && $user->tenant_id === $tenant->id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour une branche.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Branch  $branch
     * @return bool
     */
    public function update(User $user, Branch $branch)
    {
        // L'administrateur global peut mettre à jour n'importe quelle branche
        if ($user->role === 'admin') {
            return true;
        }

        // L'administrateur du tenant peut mettre à jour les branches de son tenant
        if ($user->role === 'tenant_admin' && $user->tenant_id === $branch->tenant_id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut supprimer une branche.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Branch  $branch
     * @return bool
     */
    public function delete(User $user, Branch $branch)
    {
        // L'administrateur global peut supprimer n'importe quelle branche
        if ($user->role === 'admin') {
            return true;
        }

        // L'administrateur du tenant peut supprimer les branches de son tenant
        if ($user->role === 'tenant_admin' && $user->tenant_id === $branch->tenant_id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut restaurer une branche supprimée.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Branch  $branch
     * @return bool
     */
    public function restore(User $user, Branch $branch)
    {
        // L'administrateur global peut restaurer n'importe quelle branche
        if ($user->role === 'admin') {
            return true;
        }

        // L'administrateur du tenant peut restaurer les branches de son tenant
        if ($user->role === 'tenant_admin' && $user->tenant_id === $branch->tenant_id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut supprimer définitivement une branche.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Branch  $branch
     * @return bool
     */
    public function forceDelete(User $user, Branch $branch)
    {
        // Seul l'administrateur global peut supprimer définitivement des branches
        return $user->role === 'admin';
    }
}
