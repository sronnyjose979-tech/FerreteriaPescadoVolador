<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /** @use HasFactory<CustomersFactory> */
    use HasFactory;
    
    protected $primaryKey = 'id_Customer';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_Customer',
        'first_name',
        'second_name',
        'last_name1',
        'last_name2',
        'email',
        'telephone_number',
    ];
   
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

 
}
