<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view sales');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sale $sale): Response
    {
        return $this->ownerOrAdmin($user, $sale, 'view sales'); // En esta parte solo pueden verlas sales los usuarios que las crearon
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create sales');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sale $sale): Response
    {
        return $this->ownerOrAdmin($user, $sale, 'update sales'); // Solo quienes crearon la sale la pueden actualizar
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sale $sale): Response
    {
        return $this->ownerOrAdmin($user, $sale, 'delete sales'); // Tmbien solo quien la crea puede borrarla
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Sale $Sale): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Sale $Sale): bool
    {
        return true;
    }

    private function ownerOrAdmin(User $user, Sale $sale, string $permission): Response
    {
        if (! $user->can($permission)) {
            return Response::deny();
        }

        if ($user->hasRole('admin') || $sale->user_id === $user->id) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }
}
