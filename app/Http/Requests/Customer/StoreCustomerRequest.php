<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
            'id_customer' => 'required|unique:customers,id_customer',
            'first_name' => 'required|string|max:50',
            'second_name' => 'nullable|string|max:50',
            'last_name1' => 'required|string|max:50',
            'last_name2' => 'nullable|string|max:50',
            'email' => 'required|email|max:100|unique:customers,email',
            'telephone_number' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'id_customer.required' => 'El campo id_customer es obligatorio.',
            'id_customer.unique' => 'El id_customer ya está en uso.',

            'first_name.required' => 'El campo first_name es obligatorio.',
            'first_name.string' => 'El campo first_name debe ser una cadena de texto.',
            'first_name.max' => 'El campo first_name no puede tener más de 50 caracteres.',

            'second_name.string' => 'El campo second_name debe ser una cadena de texto.',
            'second_name.max' => 'El campo second_name no puede tener más de 50 caracteres.',

            'last_name1.required' => 'El campo last_name1 es obligatorio.',
            'last_name1.string' => 'El campo last_name1 debe ser una cadena de texto.',
            'last_name1.max' => 'El campo last_name1 no puede tener más de 50 caracteres.',

            'last_name2.string' => 'El campo last_name2 debe ser una cadena de texto.',
            'last_name2.max' => 'El campo last_name2 no puede tener más de 50 caracteres.',

            'email.required' => 'El campo email es obligatorio.',
            'email.email' => 'El campo email debe ser un correo electrónico válido.',
            'email.max' => 'El campo email no puede tener más de 100 caracteres.',
            'email.unique' => 'El correo electrónico ya está en uso.',

            'telephone_number.string' => 'El campo telephone_number debe ser una cadena de texto.',
            'telephone_number.max' => 'El campo telephone_number no puede tener más de 20 caracteres.',
        ];
    }
}
