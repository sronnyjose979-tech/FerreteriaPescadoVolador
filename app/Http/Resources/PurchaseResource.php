<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PurchaseItemResource;

class PurchaseResource extends JsonResource //esto 
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
            'id_Purchase' => $this->id_Purchase, //falto poner el id de la compra
            'id_user' => $this->id_user,
            'id_Supplier' => $this->id_Supplier,
            'Purchase_Total' => $this->Purchase_Total,
            'Purchase_status' => $this->Purchase_status,
            'purchaseItems' => PurchaseItemResource::collection($this->purchaseItems),
        ];
    }
}
