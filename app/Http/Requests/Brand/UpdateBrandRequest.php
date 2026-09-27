<?php

namespace App\Http\Requests\Brand;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
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
            'brand_name' => 'sometimes|required|string|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'brand_name.required' => 'El campo nombre de la marca es obligatorio.',
            'brand_name.string' => 'El campo nombre de la marca debe ser una cadena de texto.',
            'brand_name.max' => 'El campo nombre de la marca no debe exceder los 200 caracteres.',
        ];
    }
}
