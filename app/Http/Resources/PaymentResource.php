<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'ID Pago' => $this->id,
            'ID Venta' => $this->sale_id,
            'Metodo de Pago' => $this->payment_method,
            'Referencia de Transaccion' => $this->transaction_reference,
            'Estado' => $this->status,
            'Fecha de Creacion' => $this->created_at,
            'Fecha de Actualizacion' => $this->updated_at,
        ];
    }
}
