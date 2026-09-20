<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
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
            'id_Purchase',
            'id_user' => $this->id_user,
            'id_Supplier' => $this->id_Supplier,
            'Purchase_Total' => $this->Purchase_Total,
            'Purchase_status' => $this->Purchase_status,
            'purchaseItems' => new PurchaseItemResource($this->purchaseItems)
        ];
    }
}
