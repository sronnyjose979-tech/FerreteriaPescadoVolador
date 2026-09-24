<?php

namespace App\Http\Requests\SaleItem;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleItemRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'sale_id' => 'required|exists:sales,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get the custom validation messages.
     */
    public function messages(): array
    {
        return [
            'sale_id.required' => 'La venta es obligatoria.',
            'sale_id.exists' => 'La venta seleccionada no existe.',

            'product_id.required' => 'El producto es obligatorio.',
            'product_id.exists' => 'El producto seleccionado no existe.',

            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'La cantidad debe ser como mínimo 1.',

            'unit_price.required' => 'El precio unitario es obligatorio.',
            'unit_price.numeric' => 'El precio unitario debe ser un número.',
            'unit_price.min' => 'El precio unitario no puede ser negativo.',

            'subtotal.required' => 'El subtotal es obligatorio.',
            'subtotal.numeric' => 'El subtotal debe ser un número.',
            'subtotal.min' => 'El subtotal no puede ser negativo.',
        ];
    }
}