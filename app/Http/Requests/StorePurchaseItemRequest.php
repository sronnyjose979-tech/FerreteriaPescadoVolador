<?php

namespace App\Http\Requests;

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
            'id_Purchase' => 'required|exists:purchases,id_Purchase',
            'id_product' => 'required|exists:products,id_Product',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
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
