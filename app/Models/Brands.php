<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brands extends Model
{
    use HasFactory;

    protected $table = 'brands';

    protected $primaryKey = 'id_Brand';

    public $timestamps = false;

    protected $fillable = [
        'Brand_name'
    ];
}
