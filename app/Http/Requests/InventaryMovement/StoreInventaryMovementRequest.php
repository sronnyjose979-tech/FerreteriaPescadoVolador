<?php

namespace App\Http\Requests\InventaryMovement;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInventaryMovementRequest extends FormRequest
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
            'product_id' => 'required|exists:products,id',
            'movementable_type' => 'required|string|max:255',
            'movementable_id' => 'required|integer',
            'type' => 'required|string|max:20',
            'quantity' => 'required|integer',
            'stock_after' => 'required|integer',
        ];
    }

    /**
     * Get the custom validation messages.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'El producto es obligatorio.',
            'product_id.exists' => 'El producto seleccionado no existe.',

            'movementable_type.required' => 'El tipo de origen es obligatorio.',
            'movementable_type.string' => 'El tipo de origen debe ser una cadena de texto.',
            'movementable_type.max' => 'El tipo de origen no debe exceder los 255 caracteres.',

            'movementable_id.required' => 'El ID de origen es obligatorio.',
            'movementable_id.integer' => 'El ID de origen debe ser un numero entero.',

            'type.required' => 'El tipo de movimiento es obligatorio.',
            'type.string' => 'El tipo de movimiento debe ser una cadena de texto.',
            'type.max' => 'El tipo de movimiento no debe exceder los 20 caracteres.',

            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.integer' => 'La cantidad debe ser un numero entero.',

            'stock_after.required' => 'El stock posterior es obligatorio.',
            'stock_after.integer' => 'El stock posterior debe ser un numero entero.',
        ];
    }
}
