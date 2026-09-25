<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteSupplierRequest extends FormRequest
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
            'id_Supplier' => [
                'required',
                'string',
                Rule::exists('suppliers', 'id_Supplier'),
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'id_Supplier.required' => 'El proveedor es obligatorio.',
            'id_Supplier.exists' => 'El proveedor no existe.',
        ];
    }
}