<?php

namespace App\Http\Resources;

use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PurchaseItem */
class PurchaseReceiptResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_purchase' => $this->id_purchase,
            'product_id' => $this->product_id,
            'nombre' => $this->product->name,
            'barcode' => $this->product->barcode,
            'quantity' => (int) $this->quantity,
            'received_at' => $this->received_at?->toISOString(),
        ];
    }
}
