<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'id_customer' => $this->id_customer,
            'first_name' => $this->first_name,
            'second_name' => $this->second_name,
            'last_name1' => $this->last_name1,
            'last_name2' => $this->last_name2,
            'email' => $this->email,
            'telephone_number' => $this->telephone_number,
            // 'purchase'=> new SaleResource($this->purchase)
        ];
    }
}
// Tengo que trabajar en sale para hacer la relacion
