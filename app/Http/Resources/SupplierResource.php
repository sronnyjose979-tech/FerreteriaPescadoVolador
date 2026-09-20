<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PurchaseResource;

class SupplierResource extends JsonResource
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
            'id' => $this->id_Supplier,
            'Supplier_First_name' => $this->Supplier_First_name,
            'Supplier_Last_name' => $this->Supplier_Last_name,
            'Supplier_Phone' => $this->Supplier_Phone,
            'Supplier_Address' => $this->Supplier_Address,
            'Supplier_Email' => $this->Supplier_Email,
            'Supplier_Type' => $this->Supplier_Type,
            'purchases' => PurchaseResource::collection($this->purchases)
        ];
    }
}
