<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest // esta clase se encarga de validar los datos que se reciben del formulario de productos
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
    public function rules(): array // aqui se van a crear las reglas de validación para cada campo del formulario
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'sku' => 'required|string|max:50|unique:products,sku',
            'barcode' => 'nullable|string|max:50|unique:products,barcode',
            'stock_quantity' => 'required|integer|min:0|max:1000000',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'minimum_stock' => 'nullable|integer|min:0|max:1000000',
            'maximum_stock' => 'nullable|integer|min:0|max:1000000',
            'weight' => 'nullable|numeric|min:0|max:100000',
            'image_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array // aqui se van a crear los mensajes personalizados para cada regla de validación
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
            'name.max' => 'El campo nombre no debe exceder los 150 caracteres.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no debe exceder los 1000 caracteres.',
            'price.required' => 'El campo precio es obligatorio.',
            'price.numeric' => 'El campo precio debe ser un número.',
            'price.min' => 'El campo precio debe ser mayor o igual a 0.',
            'price.max' => 'El campo precio excede el máximo permitido.',
            'sku.required' => 'El campo SKU es obligatorio.',
            'sku.string' => 'El campo SKU debe ser una cadena de texto.',
            'sku.max' => 'El campo SKU no debe exceder los 50 caracteres.',
            'sku.unique' => 'El SKU ingresado ya está en uso.',
            'barcode.string' => 'El código de barras debe ser una cadena de texto.',
            'barcode.max' => 'El código de barras no debe exceder los 50 caracteres.',
            'barcode.unique' => 'El código de barras ya está en uso.',
            'stock_quantity.required' => 'El campo cantidad en stock es obligatorio.',
            'stock_quantity.integer' => 'El campo cantidad en stock debe ser un número entero.',
            'stock_quantity.min' => 'El campo cantidad en stock debe ser mayor o igual a 0.',
            'stock_quantity.max' => 'El campo cantidad en stock excede el máximo permitido.',
            'tax_rate.numeric' => 'La tasa de impuesto debe ser un número.',
            'tax_rate.min' => 'La tasa de impuesto debe ser mayor o igual a 0.',
            'tax_rate.max' => 'La tasa de impuesto no debe exceder 100.',
            'minimum_stock.integer' => 'El stock mínimo debe ser un número entero.',
            'minimum_stock.min' => 'El stock mínimo debe ser mayor o igual a 0.',
            'minimum_stock.max' => 'El stock mínimo excede el máximo permitido.',
            'maximum_stock.integer' => 'El stock máximo debe ser un número entero.',
            'maximum_stock.min' => 'El stock máximo debe ser mayor o igual a 0.',
            'maximum_stock.max' => 'El stock máximo excede el máximo permitido.',
            'weight.numeric' => 'El peso debe ser un número.',
            'weight.min' => 'El peso debe ser mayor o igual a 0.',
            'weight.max' => 'El peso excede el máximo permitido.',
            'image_url.url' => 'La URL de la imagen debe ser una URL válida.',
            'image_url.max' => 'La URL de la imagen no debe exceder los 255 caracteres.',
            'is_active.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}
