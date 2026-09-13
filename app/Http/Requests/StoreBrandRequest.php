<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest // esta clase se encarga de validar los datos que se reciben del formulario de marcas
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
            'brand_name' => 'required|string|max:200',
        ];
    }

    public function messages(): array// aqui se van a crear los mensajes personalizados para cada regla de validación
    {
        return [
            'brand_name.required' => 'El campo nombre de la marca es obligatorio.',
            'brand_name.string' => 'El campo nombre de la marca debe ser una cadena de texto.',
            'brand_name.max' => 'El campo nombre de la marca no debe exceder los 200 caracteres.',
        ];
    }
}
