<?php

namespace App\Services;

use App\Models\Purchase;

class PurchaseService
{

    public function crear($purchase)
    {
        $purchase = Purchase::create($purchase);

        return $purchase;
    }
}
