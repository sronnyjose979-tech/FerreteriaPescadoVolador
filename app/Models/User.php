<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * DER: User (N) — (1) Role via User.id_role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Roles::class, 'id_role', 'id_role');
    }

    /**
     * Ventas registradas por el usuario.
     * DER: Sale.id_user → User.id_User
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sales::class, 'id_user', 'id_User');
    }

    /**
     * Compras registradas por el usuario.
     * DER: Purchase.id_user → User.id_User
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchases::class, 'id_user', 'id_User');
    }

    /**
     * Movimientos de inventario registrados por el usuario (si aplica).
     * DER: Inventory_movement ↔ User
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(Inventory_Movements::class, 'id_user', 'id_User');
    }
}
