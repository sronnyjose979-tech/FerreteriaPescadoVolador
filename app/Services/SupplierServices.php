<?php

namespace App\Services;

use App\Models\Supplier;

class SupplierServices
{

    public function crear($supplier)
    {
        $supplier = Supplier::create($supplier);

        return $supplier;
    }
}
