<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    protected $primaryKey = 'id_customer';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_customer',
        'first_name',
        'second_name',
        'last_name1',
        'last_name2',
        'email',
        'telephone_number',
    ];

    /**
     * Atributos ocultos en la serialización JSON.
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Ventas asociadas al cliente.
     * DER: Sale.id_customer — Customer.id_customer
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'id_customer', 'id_customer');
    }
}
