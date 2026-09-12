<?php

namespace App\Services;

use App\Models\PurchaseItem;

class PurchaseItemService
{

    public function crear(array $validated)
    {
        $purchase = PurchaseItem::create($validated);

        return $purchase;
    }
}
