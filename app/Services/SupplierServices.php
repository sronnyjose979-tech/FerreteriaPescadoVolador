<?php

namespace App\Services;

use App\Models\Suppliers;

class SupplierServices
{

    public function crear($supplier)
    {
        $supplier = Suppliers::create($supplier);

        return $supplier;
    }
}
