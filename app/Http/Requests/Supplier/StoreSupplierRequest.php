<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
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
            'id_supplier' => 'required|unique:suppliers,id_supplier',
            'supplier_first_name' => 'required|string|max:50',
            'supplier_last_name' => 'required|string|max:50',
            'supplier_phone' => 'required|string|max:20',
            'supplier_address' => 'required|string|max:100',
            'supplier_email' => 'required|email|max:100|unique:suppliers,supplier_email',
        ];
    }

    public function messages(): array
    {
        return [
            'id_supplier.required' => 'El campo id_supplier es obligatorio.',
            'id_supplier.unique' => 'El id_supplier ya está en uso.',
            'supplier_first_name.required' => 'El campo supplier_first_name es obligatorio.',
            'supplier_first_name.string' => 'El campo supplier_first_name debe ser una cadena de texto.',
            'supplier_first_name.max' => 'El campo supplier_first_name no puede tener más de 50 caracteres.',
            'supplier_last_name.required' => 'El campo supplier_last_name es obligatorio.',
            'supplier_last_name.string' => 'El campo supplier_last_name debe ser una cadena de texto.',
            'supplier_last_name.max' => 'El campo supplier_last_name no puede tener más de 50 caracteres.',
            'supplier_phone.required' => 'El campo supplier_phone es obligatorio.',
            'supplier_phone.string' => 'El campo supplier_phone debe ser una cadena de texto.',
            'supplier_phone.max' => 'El campo supplier_phone no puede tener más de 20 caracteres.',
            'supplier_address.required' => 'El campo supplier_address es obligatorio.',
            'supplier_address.string' => 'El campo supplier_address debe ser una cadena de texto.',
            'supplier_address.max' => 'El campo supplier_address no puede tener más de 100 caracteres.',
            'supplier_email.required' => 'El campo supplier_email es obligatorio.',
            'supplier_email.email' => 'El campo supplier_email debe ser un correo electrónico válido.',
            'supplier_email.max' => 'El campo supplier_email no puede tener más de 100 caracteres.',
            'supplier_email.unique' => 'El correo electrónico ya está en uso.',
        ];
    }
}
