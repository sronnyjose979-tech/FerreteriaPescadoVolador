<?php

namespace App\Http\Requests\InventaryMovement;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInventaryMovementRequest extends FormRequest
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
            'product_id' => 'sometimes|exists:products,id',
            'movementable_type' => 'sometimes|string|max:255',
            'movementable_id' => 'sometimes|integer',
            'type' => 'sometimes|string|max:20',
            'quantity' => 'sometimes|integer',
            'stock_after' => 'sometimes|integer',
        ];
    }

    /**
     * Get the custom validation messages.
     */
    public function messages(): array
    {
        return [
            'product_id.exists' => 'El producto seleccionado no existe.',

            'movementable_type.string' => 'El tipo de origen debe ser una cadena de texto.',
            'movementable_type.max' => 'El tipo de origen no debe exceder los 255 caracteres.',

            'movementable_id.integer' => 'El ID de origen debe ser un numero entero.',

            'type.string' => 'El tipo de movimiento debe ser una cadena de texto.',
            'type.max' => 'El tipo de movimiento no debe exceder los 20 caracteres.',

            'quantity.integer' => 'La cantidad debe ser un numero entero.',

            'stock_after.integer' => 'El stock posterior debe ser un numero entero.',
        ];
    }
}