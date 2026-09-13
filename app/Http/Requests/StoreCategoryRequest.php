<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest // esta clase se encarga de validar los datos que se reciben del formulario de categorias
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
    public function rules(): array// aqui se van a crear las reglas de validación para cada campo del formulario
    {
        return [
            'category_name' => 'required|string|max:200',
            'description' => 'required|string|max:200',
        ];
    }

    public function messages(): array// aqui se van a crear los mensajes personalizados para cada regla de validación
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
