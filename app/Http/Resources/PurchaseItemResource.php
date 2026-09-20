<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        return [
            'id_Purchase'=>$this->id_Purchase,
            'id_product'=>$this->id_Product,
            'quantity'=>$this->quantity,
            'unit_cost'=>$this->unit_cost,
            'subtotal'=>$this->subtotal,
        ];
    }
}
