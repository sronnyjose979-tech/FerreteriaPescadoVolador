<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductsRequest extends FormRequest //esta clase se encarga de validar los datos que se reciben del formulario de productos
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
    public function rules(): array//aqui se van a crear las reglas de validación para cada campo del formulario
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'name' => 'required|string|max:200',
            'price' => 'required|numeric|min:0',
            // 'sku' => 'required|string|unique:products,sku',
            // 'stock_quantity' => 'required|integer|min:0',
            // 'tax_rate' => 'nullable|numeric',
            // 'minimum_stock' => 'nullable|integer',
            // 'maximum_stock' => 'nullable|integer',
            // 'weight' => 'nullable|numeric',
            // 'image_url' => 'nullable|url',
            // 'is_active' => 'boolean'
        ];
    }
    public function messages(): array// aqui se van a crear los mensajes personalizados para cada regla de validación
    {
        return [
            'category_id.required' => 'El campo categoría es obligatorio.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'brand_id.required' => 'El campo marca es obligatorio.',
            'brand_id.exists' => 'La marca seleccionada no existe.',
            'unit_id.required' => 'El campo unidad es obligatorio.',
            'unit_id.exists' => 'La unidad seleccionada no existe.',
            'name.required' => 'El campo nombre es obligatorio.',
            'name.string' => 'El campo nombre debe ser una cadena de texto.',
            'name.max' => 'El campo nombre no debe exceder los 200 caracteres.',
                'price.required' => 'El campo precio es obligatorio.',
                'price.numeric' => 'El campo precio debe ser un número.',
                // 'price.min' => 'El campo precio debe ser mayor o igual a 0.',
                // 'sku.required' => 'El campo SKU es obligatorio.',
                // 'sku.string' => 'El campo SKU debe ser una cadena de texto.',
                // 'sku.unique' => 'El SKU ingresado ya está en uso.',
                // 'stock_quantity.required' => 'El campo cantidad en stock es obligatorio.',
                // 'stock_quantity.integer' => 'El campo cantidad en stock debe ser un número entero.',
                // 'stock_quantity.min' => 'El campo cantidad en stock debe ser mayor o igual a 0.',
            ];
    }
}
