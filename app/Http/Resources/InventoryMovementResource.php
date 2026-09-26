<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryMovementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ID Movimiento' => $this->id,
            'ID Producto' => $this->product_id,
            'Tipo' => $this->type,
            'Cantidad' => $this->quantity,
            'Stock Despues' => $this->stock_after,
            'Tipo de Origen' => $this->movementable_type,
            'ID de Origen' => $this->movementable_id,
        ];
    }
}
