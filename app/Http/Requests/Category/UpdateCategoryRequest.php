<?php

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
            'category_name' => 'sometimes|required|string|max:200',
            'description' => 'sometimes|required|string|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'category_name.required' => 'El campo nombre de la categoría es obligatorio.',
            'category_name.string' => 'El campo nombre de la categoría debe ser una cadena de texto.',
            'category_name.max' => 'El campo nombre de la categoría no debe exceder los 200 caracteres.',
            'description.required' => 'El campo descripción es obligatorio.',
            'description.string' => 'El campo descripción debe ser una cadena de texto.',
            'description.max' => 'El campo descripción no debe exceder los 200 caracteres.',
        ];
    }
}
