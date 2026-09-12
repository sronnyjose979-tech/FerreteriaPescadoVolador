<?php

namespace App\Http\Requests;

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
            'id_Purchase' => 'required|unique:purchases,id_Purchase',
            'id_user' => 'required|string|max:50',
            'id_Supplier' => 'required|string|max:50',
            'Purchase_Total' => 'required|numeric|min:0',
            'Purchase_status' => 'required|string|max:20',
        ];
    }
    public function messages(): array
    {
        return [
            'id_Purchase.required' => 'El campo id_Purchase es obligatorio.',
            'id_Purchase.unique' => 'El id_Purchase ya está en uso.',
            'id_user.required' => 'El campo id_user es obligatorio.',
            'id_user.string' => 'El campo id_user debe ser una cadena de texto.',
            'id_user.max' => 'El campo id_user no puede tener más de 50 caracteres.',
            'id_Supplier.required' => 'El campo id_Supplier es obligatorio.',
            'id_Supplier.string' => 'El campo id_Supplier debe ser una cadena de texto.',
            'id_Supplier.max' => 'El campo id_Supplier no puede tener más de 50 caracteres.',
            'Purchase_Total.required' => 'El campo Purchase_Total es obligatorio.',
            'Purchase_Total.numeric' => 'El campo Purchase_Total debe ser un número.',
            'Purchase_Total.min' => 'El campo Purchase_Total debe ser mayor o igual a 0.',
            'Purchase_status.required' => 'El campo Purchase_status es obligatorio.',
            'Purchase_status.string' => 'El campo Purchase_status debe ser una cadena de texto.',
            'Purchase_status.max' => 'El campo Purchase_status no puede tener más de 20 caracteres.',
        ];
    }
}
