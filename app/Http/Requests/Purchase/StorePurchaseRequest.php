<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
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
            'id_purchase' => 'required|unique:purchases,id_purchase',
            'user_id' => 'required|string|max:50',
            'id_supplier' => 'required|string|max:50',
            'purchase_total' => 'required|numeric|min:0',
            'purchase_status' => 'required|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'id_purchase.required' => 'El campo id_purchase es obligatorio.',
            'id_purchase.unique' => 'El id_purchase ya está en uso.',
            'user_id.required' => 'El campo user_id es obligatorio.',
            'user_id.string' => 'El campo user_id debe ser una cadena de texto.',
            'user_id.max' => 'El campo user_id no puede tener más de 50 caracteres.',
            'id_supplier.required' => 'El campo id_supplier es obligatorio.',
            'id_supplier.string' => 'El campo id_supplier debe ser una cadena de texto.',
            'id_supplier.max' => 'El campo id_supplier no puede tener más de 50 caracteres.',
            'purchase_total.required' => 'El campo purchase_total es obligatorio.',
            'purchase_total.numeric' => 'El campo purchase_total debe ser un número.',
            'purchase_total.min' => 'El campo purchase_total debe ser mayor o igual a 0.',
            'purchase_status.required' => 'El campo purchase_status es obligatorio.',
            'purchase_status.string' => 'El campo purchase_status debe ser una cadena de texto.',
            'purchase_status.max' => 'El campo purchase_status no puede tener más de 20 caracteres.',
        ];
    }
}
