<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|exists:users,id',
            'id_Customer' => 'sometimes|nullable|exists:customers,id_Customer',
            'order_id' => 'sometimes|nullable|exists:orders,id',
            'sale_date' => 'sometimes|required|date',
            'total' => 'sometimes|required|numeric|min:0',
            'tax_amount' => 'sometimes|nullable|numeric|min:0',
            'discount' => 'sometimes|nullable|numeric|min:0',
            'status' => 'sometimes|required|string|max:20',
        ];
    }
    public function messages(): array
    {
        return [
            'user_id.required' => 'El usuario es obligatorio.',
            'user_id.exists' => 'El usuario seleccionado no existe.',

            'id_Customer.exists' => 'El cliente seleccionado no existe.',

            'order_id.exists' => 'La orden seleccionada no existe.',

            'sale_date.required' => 'La fecha de venta es obligatoria.',
            'sale_date.date' => 'La fecha de venta debe tener un formato válido.',

            'total.required' => 'El total es obligatorio.',
            'total.numeric' => 'El total debe ser un número.',
            'total.min' => 'El total no puede ser negativo.',

            'tax_amount.numeric' => 'El impuesto debe ser un número.',
            'tax_amount.min' => 'El impuesto no puede ser negativo.',

            'discount.numeric' => 'El descuento debe ser un número.',
            'discount.min' => 'El descuento no puede ser negativo.',

            'status.required' => 'El estado es obligatorio.',
            'status.string' => 'El estado debe ser texto.',
            'status.max' => 'El estado no puede tener más de 20 caracteres.',
        ];
    }
}
