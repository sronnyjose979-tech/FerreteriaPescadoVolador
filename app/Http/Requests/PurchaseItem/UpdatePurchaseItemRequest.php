<?php

namespace App\Http\Requests\PurchaseItem;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_Purchase' => ['sometimes', 'required', 'exists:purchases,id_Purchase'],
            'id_product' => ['sometimes', 'required', 'exists:products,id'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1', 'max:100000'],
            'unit_cost' => ['sometimes', 'required', 'numeric', 'min:0'],
            'subtotal' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_Purchase.required' => 'La compra es obligatoria.',
            'id_Purchase.exists' => 'La compra indicada no existe.',
            'id_product.required' => 'El producto es obligatorio.',
            'id_product.exists' => 'El producto indicado no existe.',
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'La cantidad debe ser mayor que 0.',
            'quantity.max' => 'La cantidad excede el máximo permitido.',
            'unit_cost.required' => 'El costo unitario es obligatorio.',
            'unit_cost.numeric' => 'El costo unitario debe ser numérico.',
            'unit_cost.min' => 'El costo unitario no puede ser negativo.',
            'subtotal.required' => 'El subtotal es obligatorio.',
            'subtotal.numeric' => 'El subtotal debe ser numérico.',
            'subtotal.min' => 'El subtotal no puede ser negativo.',
        ];
    }
}
