<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest // esta clase se encarga de validar los datos que se reciben del formulario de unidades
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
            'unit_name' => 'required|string|max:200',
        ];
    }

    public function messages(): array// aqui se van a crear los mensajes personalizados para cada regla de validación
    {
        return [
            'unit_name.required' => 'El campo nombre de la unidad es obligatorio.',
            'unit_name.string' => 'El campo nombre de la unidad debe ser una cadena de texto.',
            'unit_name.max' => 'El campo nombre de la unidad no debe exceder los 200 caracteres.',
        ];
    }
}
