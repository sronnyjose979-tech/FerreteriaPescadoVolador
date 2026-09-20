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
     return  [ 'id' => $this->id,

            // Identificadores de Catálogos
            'id_categoria' => $this->category_id,
            'id_marca' => $this->brand_id,
            'id_unidad' => $this->unit_id,

            // Información Comercial
            'nombre' => $this->name,
            'descripcion' => $this->description,
            'sku' => $this->sku,
            'codigo_barras' => $this->barcode,

            // Precios e Impuestos
            'precio' => (float) $this->price,
            'tasa_impuesto' => (float) $this->tax_rate,

            // Control de Inventario
            'cantidad_stock' => (int) $this->stock_quantity,
            'stock_minimo' => (int) $this->minimum_stock,
            'stock_maximo' => $this->maximum_stock !== null ? (int) $this->maximum_stock : null,
            'peso' => $this->weight !== null ? (float) $this->weight : null,

            // Estado y Multimedia
            'url_imagen' => $this->image_url,
            'esta_activo' => (bool) $this->is_active,

            // Relaciones anidadas (solo si se incluyen mediante with())
          //  'categoria' => new CategoryResource($this->whenLoaded('category')),
           // 'marca' => new BrandResource($this->whenLoaded('brand')),
          //  'unidad' => new UnitResource($this->whenLoaded('unit')),
    ];
    }
}
