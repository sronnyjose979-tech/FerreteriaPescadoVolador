<?php

namespace App\Http\Requests\PurchaseItem;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_purchase' => 'required|exists:purchases,id_purchase',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'id_purchase.required' => 'La compra es obligatoria.',
            'id_purchase.exists' => 'La compra indicada no existe.',

            'product_id.required' => 'El producto es obligatorio.',
            'product_id.exists' => 'El producto indicado no existe.',

            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.integer' => 'La cantidad debe ser un numero entero.',
            'quantity.min' => 'La cantidad debe ser mayor que 0.',

            'unit_cost.required' => 'El costo unitario es obligatorio.',
            'unit_cost.numeric' => 'El costo unitario debe ser numerico.',
            'unit_cost.min' => 'El costo unitario no puede ser negativo.',

            'subtotal.required' => 'El subtotal es obligatorio.',
            'subtotal.numeric' => 'El subtotal debe ser numerico.',
            'subtotal.min' => 'El subtotal no puede ser negativo.',
        ];
    }
}
