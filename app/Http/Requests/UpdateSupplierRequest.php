<?php

namespace App\Http\Requests;

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
        $id = $this->route('supplier')?->id_Supplier ?? $this->route('supplier');

        return [
            'id_Supplier' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('suppliers', 'id_Supplier')->ignore($id, 'id_Supplier')],
            'Supplier_First_name' => ['sometimes', 'required', 'string', 'max:50'],
            'Supplier_Last_name' => ['sometimes', 'required', 'string', 'max:50'],
            'Supplier_Phone' => ['sometimes', 'required', 'string', 'max:20'],
            'Supplier_Address' => ['sometimes', 'required', 'string', 'max:100'],
            'Supplier_Email' => ['sometimes', 'required', 'email', 'max:100', Rule::unique('suppliers', 'Supplier_Email')->ignore($id, 'id_Supplier')],
            'Supplier_Type' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_Supplier.required' => 'El campo identificador del proveedor es obligatorio.',
            'id_Supplier.string' => 'El identificador del proveedor debe ser una cadena de texto.',
            'id_Supplier.max' => 'El identificador del proveedor no debe exceder los 50 caracteres.',
            'id_Supplier.unique' => 'El identificador del proveedor ya está en uso.',
            'Supplier_First_name.required' => 'El campo nombre del proveedor es obligatorio.',
            'Supplier_First_name.string' => 'El campo nombre debe ser una cadena de texto.',
            'Supplier_First_name.max' => 'El campo nombre no puede tener más de 50 caracteres.',
            'Supplier_Last_name.required' => 'El campo apellido del proveedor es obligatorio.',
            'Supplier_Last_name.string' => 'El campo apellido debe ser una cadena de texto.',
            'Supplier_Last_name.max' => 'El campo apellido no puede tener más de 50 caracteres.',
            'Supplier_Phone.required' => 'El campo teléfono del proveedor es obligatorio.',
            'Supplier_Phone.string' => 'El campo teléfono debe ser una cadena de texto.',
            'Supplier_Phone.max' => 'El campo teléfono no puede tener más de 20 caracteres.',
            'Supplier_Address.required' => 'El campo dirección del proveedor es obligatorio.',
            'Supplier_Address.string' => 'El campo dirección debe ser una cadena de texto.',
            'Supplier_Address.max' => 'El campo dirección no puede tener más de 100 caracteres.',
            'Supplier_Email.required' => 'El campo correo del proveedor es obligatorio.',
            'Supplier_Email.email' => 'El campo correo debe ser un correo electrónico válido.',
            'Supplier_Email.max' => 'El campo correo no puede tener más de 100 caracteres.',
            'Supplier_Email.unique' => 'El correo electrónico ya está en uso.',
            'Supplier_Type.string' => 'El campo tipo debe ser una cadena de texto.',
            'Supplier_Type.max' => 'El campo tipo no debe exceder los 50 caracteres.',
        ];
    }
}
