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
            'ID Compra'=>$this->id_Purchase,
            'ID Producto'=>$this->product_id,
            'Cantidad'=>$this->quantity,
            'Costo Unitario'=>$this->unit_cost,
            'SubTotal'=>$this->subtotal,
        ];
    }
}
