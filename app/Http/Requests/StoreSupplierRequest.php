<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest  extends FormRequest
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
            'id_Supplier' => 'required|unique:suppliers,id_Supplier',
            'Supplier_first_name' => 'required|string|max:50',
            'Supplier_last_name' => 'required|string|max:50',
            'Supplier_phone' => 'required|string|max:20',
            'Supplier_address' => 'required|string|max:100',
            'Supplier_email' => 'required|email|max:100|unique:suppliers,Supplier_email',
        ];
    }
    public function messages(): array
    {
        return [
            'id_Supplier.required' => 'El campo id_Supplier es obligatorio.',
            'id_Supplier.unique' => 'El id_Supplier ya está en uso.',
            'Supplier_first_name.required' => 'El campo Supplier_first_name es obligatorio.',
            'Supplier_first_name.string' => 'El campo Supplier_first_name debe ser una cadena de texto.',
            'Supplier_first_name.max' => 'El campo Supplier_first_name no puede tener más de 50 caracteres.',
            'Supplier_last_name.required' => 'El campo Supplier_last_name es obligatorio.',
            'Supplier_last_name.string' => 'El campo Supplier_last_name debe ser una cadena de texto.',
            'Supplier_last_name.max' => 'El campo Supplier_last_name no puede tener más de 50 caracteres.',
            'Supplier_phone.required' => 'El campo Supplier_phone es obligatorio.',
            'Supplier_phone.string' => 'El campo Supplier_phone debe ser una cadena de texto.',
            'Supplier_phone.max' => 'El campo Supplier_phone no puede tener más de 20 caracteres.',
            'Supplier_address.required' => 'El campo Supplier_address es obligatorio.',
            'Supplier_address.string' => 'El campo Supplier_address debe ser una cadena de texto.',
            'Supplier_address.max' => 'El campo Supplier_address no puede tener más de 100 caracteres.',
            'Supplier_email.required' => 'El campo Supplier_email es obligatorio.',
            'Supplier_email.email' => 'El campo Supplier_email debe ser un correo electrónico válido.',
            'Supplier_email.max' => 'El campo Supplier_email no puede tener más de 100 caracteres.',
            'Supplier_email.unique' => 'El correo electrónico ya está en uso.',
        ];
    }
}
