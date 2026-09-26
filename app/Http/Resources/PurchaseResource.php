<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource // esto
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id_purchase' => $this->id_purchase, // falto poner el id de la compra
            'user_id' => $this->user_id,
            'id_supplier' => $this->id_supplier,
            'purchase_total' => $this->purchase_total,
            'purchase_status' => $this->purchase_status,
            'purchaseItems' => PurchaseItemResource::collection($this->purchaseItems),
        ];
    }
}
