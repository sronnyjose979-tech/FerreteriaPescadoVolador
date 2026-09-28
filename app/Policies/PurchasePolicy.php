<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PurchasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view purchases');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Purchase $purchase): Response
    {
        return $this->ownerOrAdmin($user, $purchase, 'view purchases'); // Con esta parte los usuarios pueden ver sus purchases
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create purchases');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Purchase $purchase): Response
    {
        return $this->ownerOrAdmin($user, $purchase, 'update purchases'); // Solo pueden actualizar los purchase sus creadores
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Purchase $purchase): Response
    {
        return $this->ownerOrAdmin($user, $purchase, 'delete purchases'); // Aca lo mismo, solo pueden borrar sus propios purchases
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Purchase $purchase): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Purchase $purchase): bool
    {
        return false;
    }

    private function ownerOrAdmin(User $user, Purchase $purchase, string $permission): Response
    {
        if (! $user->can($permission)) {
            return Response::deny();
        }

        if ($user->hasRole('admin') || $purchase->user_id === $user->id) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }
}
