<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('supplier')?->id_supplier ?? $this->route('supplier');

        return [
            'id_supplier' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('suppliers', 'id_supplier')->ignore($id, 'id_supplier')],
            'supplier_first_name' => ['sometimes', 'required', 'string', 'max:50'],
            'supplier_last_name' => ['sometimes', 'required', 'string', 'max:50'],
            'supplier_phone' => ['sometimes', 'required', 'string', 'max:20'],
            'supplier_address' => ['sometimes', 'required', 'string', 'max:100'],
            'supplier_email' => ['sometimes', 'required', 'email', 'max:100', Rule::unique('suppliers', 'supplier_email')->ignore($id, 'id_supplier')],
            'supplier_type' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_supplier.required' => 'El campo identificador del proveedor es obligatorio.',
            'id_supplier.string' => 'El identificador del proveedor debe ser una cadena de texto.',
            'id_supplier.max' => 'El identificador del proveedor no debe exceder los 50 caracteres.',
            'id_supplier.unique' => 'El identificador del proveedor ya está en uso.',
            'supplier_first_name.required' => 'El campo nombre del proveedor es obligatorio.',
            'supplier_first_name.string' => 'El campo nombre debe ser una cadena de texto.',
            'supplier_first_name.max' => 'El campo nombre no puede tener más de 50 caracteres.',
            'supplier_last_name.required' => 'El campo apellido del proveedor es obligatorio.',
            'supplier_last_name.string' => 'El campo apellido debe ser una cadena de texto.',
            'supplier_last_name.max' => 'El campo apellido no puede tener más de 50 caracteres.',
            'supplier_phone.required' => 'El campo teléfono del proveedor es obligatorio.',
            'supplier_phone.string' => 'El campo teléfono debe ser una cadena de texto.',
            'supplier_phone.max' => 'El campo teléfono no puede tener más de 20 caracteres.',
            'supplier_address.required' => 'El campo dirección del proveedor es obligatorio.',
            'supplier_address.string' => 'El campo dirección debe ser una cadena de texto.',
            'supplier_address.max' => 'El campo dirección no puede tener más de 100 caracteres.',
            'supplier_email.required' => 'El campo correo del proveedor es obligatorio.',
            'supplier_email.email' => 'El campo correo debe ser un correo electrónico válido.',
            'supplier_email.max' => 'El campo correo no puede tener más de 100 caracteres.',
            'supplier_email.unique' => 'El correo electrónico ya está en uso.',
            'supplier_type.string' => 'El campo tipo debe ser una cadena de texto.',
            'supplier_type.max' => 'El campo tipo no debe exceder los 50 caracteres.',
        ];
    }
}
