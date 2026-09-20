<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        return  [
            'id' => $this->id,
            'id_categoria' => $this->category_id,
            'id_marca' => $this->brand_id,
            'id_unidad' => $this->unit_id,
            'nombre' => $this->name,
            'descripcion' => $this->description,
            'sku' => $this->sku,
            'codigo_barras' => $this->barcode,
            'precio' => (float) $this->price,
            'tasa_impuesto' => (float) $this->tax_rate,
            'cantidad_stock' => (int) $this->stock_quantity,
            'stock_minimo' => (int) $this->minimum_stock,
            'stock_maximo' => $this->maximum_stock,
            'peso' => $this->weight,
            'esta_activo' => (bool) $this->is_active,

        ];
    }
}
