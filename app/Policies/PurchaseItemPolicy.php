<?php

namespace App\Policies;

use App\Models\PurchaseItem;
use App\Models\User;

class PurchaseItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view purchase-items');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseItem $purchaseItem): bool
    {
        return $user->can('view purchase-items');
        
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create purchase-items');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseItem $purchaseItem): bool
    {
        return $user->can('update purchase-items');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseItem $purchaseItem): bool
    {
        return $user->can('delete purchase-items');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PurchaseItem $purchaseItem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PurchaseItem $purchaseItem): bool
    {
        return false;
    }
}
