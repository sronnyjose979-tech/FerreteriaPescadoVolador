<?php

namespace App\Http\Requests\Unit;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
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
            'unit_name' => 'sometimes|string|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'unit_name.string' => 'El campo nombre de la unidad debe ser una cadena de texto.',
            'unit_name.max' => 'El campo nombre de la unidad no debe exceder los 200 caracteres.',
        ];
    }
}
