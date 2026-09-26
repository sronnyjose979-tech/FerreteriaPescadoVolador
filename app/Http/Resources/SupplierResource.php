<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_supplier,
            'supplier_first_name' => $this->supplier_first_name,
            'supplier_last_name' => $this->supplier_last_name,
            'supplier_phone' => $this->supplier_phone,
            'supplier_address' => $this->supplier_address,
            'supplier_email' => $this->supplier_email,
            'supplier_type' => $this->supplier_type,
            'purchases' => PurchaseResource::collection($this->purchases),
        ];
    }
}
