<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\Tenant\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    /**
     * Détermine si l'utilisateur peut voir la liste des clients.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Branch  $branch
     * @return bool
     */
    public function viewAny(User $user, Branch $branch)
    {
        // L'administrateur global peut voir tous les clients
        if ($user->role === 'admin') {
            return true;
        }

        // L'administrateur du tenant peut voir les clients des branches de son tenant
        if ($user->role === 'tenant_admin' && $user->tenant_id === $branch->tenant_id) {
            return true;
        }

        // L'administrateur de branche peut voir les clients de sa branche
        if ($user->role === 'branch_admin' && $user->branch_id === $branch->id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut voir un client spécifique.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tenant\Customer  $customer
     * @param  \App\Models\Branch  $branch
     * @return bool
     */
    public function view(User $user, Customer $customer, Branch $branch)
    {
        // L'administrateur global peut voir n'importe quel client
        if ($user->role === 'admin') {
            return true;
        }

        // L'administrateur du tenant peut voir les clients des branches de son tenant
        if ($user->role === 'tenant_admin' && $user->tenant_id === $branch->tenant_id) {
            return true;
        }

        // L'administrateur de branche peut voir les clients de sa branche
        if ($user->role === 'branch_admin' && $user->branch_id === $branch->id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut créer un client.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Branch  $branch
     * @return bool
     */
    public function create(User $user, Branch $branch)
    {
        // L'administrateur global peut créer des clients pour n'importe quelle branche
        if ($user->role === 'admin') {
            return true;
        }

        // L'administrateur du tenant peut créer des clients pour les branches de son tenant
        if ($user->role === 'tenant_admin' && $user->tenant_id === $branch->tenant_id) {
            return true;
        }

        // L'administrateur de branche peut créer des clients pour sa branche
        if ($user->role === 'branch_admin' && $user->branch_id === $branch->id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour un client.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tenant\Customer  $customer
     * @param  \App\Models\Branch  $branch
     * @return bool
     */
    public function update(User $user, Customer $customer, Branch $branch)
    {
        // L'administrateur global peut mettre à jour n'importe quel client
        if ($user->role === 'admin') {
            return true;
        }

        // L'administrateur du tenant peut mettre à jour les clients des branches de son tenant
        if ($user->role === 'tenant_admin' && $user->tenant_id === $branch->tenant_id) {
            return true;
        }

        // L'administrateur de branche peut mettre à jour les clients de sa branche
        if ($user->role === 'branch_admin' && $user->branch_id === $branch->id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut supprimer un client.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tenant\Customer  $customer
     * @param  \App\Models\Branch  $branch
     * @return bool
     */
    public function delete(User $user, Customer $customer, Branch $branch)
    {
        // L'administrateur global peut supprimer n'importe quel client
        if ($user->role === 'admin') {
            return true;
        }

        // L'administrateur du tenant peut supprimer les clients des branches de son tenant
        if ($user->role === 'tenant_admin' && $user->tenant_id === $branch->tenant_id) {
            return true;
        }

        // L'administrateur de branche peut supprimer les clients de sa branche
        if ($user->role === 'branch_admin' && $user->branch_id === $branch->id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut importer des clients.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Branch  $branch
     * @return bool
     */
    public function import(User $user, Branch $branch)
    {
        // L'administrateur global peut importer des clients pour n'importe quelle branche
        if ($user->role === 'admin') {
            return true;
        }

        // L'administrateur du tenant peut importer des clients pour les branches de son tenant
        if ($user->role === 'tenant_admin' && $user->tenant_id === $branch->tenant_id) {
            return true;
        }

        // L'administrateur de branche peut importer des clients pour sa branche
        if ($user->role === 'branch_admin' && $user->branch_id === $branch->id) {
            return true;
        }

        return false;
    }
}
