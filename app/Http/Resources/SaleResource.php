<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'id_Customer' => $this->id_Customer,
            //'order_id' => $this->order_id, NO SE DEBE USAR PORQUE ORDER ES DEL ECOMMERCE
            'sale_date' => $this->sale_date,
            'total' => $this->total,
            'tax_amount' => $this->tax_amount,
            'discount' => $this->discount,
            'status' => $this->status,
        ];
    }
}
