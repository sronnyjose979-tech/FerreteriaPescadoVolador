<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('product')?->id ?? $this->route('product');

        return [
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'brand_id' => ['sometimes', 'required', 'exists:brands,id'],
            'unit_id' => ['sometimes', 'required', 'exists:units,id'],
            'name' => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'sku' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($id)],
            'barcode' => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($id)],
            'price' => ['sometimes', 'required', 'numeric', 'min:0', 'max:9999999999.99'],
            'tax_rate' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'stock_quantity' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000000'],
            'minimum_stock' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:1000000'],
            'maximum_stock' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:1000000'],
            'weight' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100000'],
            'image_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
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
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no debe exceder los 1000 caracteres.',
            'sku.required' => 'El campo SKU es obligatorio.',
            'sku.string' => 'El campo SKU debe ser una cadena de texto.',
            'sku.max' => 'El campo SKU no debe exceder los 50 caracteres.',
            'sku.unique' => 'El SKU ingresado ya está en uso.',
            'barcode.string' => 'El código de barras debe ser una cadena de texto.',
            'barcode.max' => 'El código de barras no debe exceder los 100 caracteres.',
            'barcode.unique' => 'El código de barras ya está en uso.',
            'price.required' => 'El campo precio es obligatorio.',
            'price.numeric' => 'El campo precio debe ser un número.',
            'price.min' => 'El campo precio debe ser mayor o igual a 0.',
            'price.max' => 'El campo precio excede el máximo permitido.',
            'tax_rate.numeric' => 'La tasa de impuesto debe ser un número.',
            'tax_rate.min' => 'La tasa de impuesto debe ser mayor o igual a 0.',
            'tax_rate.max' => 'La tasa de impuesto no debe exceder 100.',
            'stock_quantity.required' => 'El campo cantidad en stock es obligatorio.',
            'stock_quantity.integer' => 'El campo cantidad en stock debe ser un número entero.',
            'stock_quantity.min' => 'El campo cantidad en stock debe ser mayor o igual a 0.',
            'stock_quantity.max' => 'El campo cantidad en stock excede el máximo permitido.',
            'minimum_stock.integer' => 'El stock mínimo debe ser un número entero.',
            'minimum_stock.min' => 'El stock mínimo debe ser mayor o igual a 0.',
            'maximum_stock.integer' => 'El stock máximo debe ser un número entero.',
            'maximum_stock.min' => 'El stock máximo debe ser mayor o igual a 0.',
            'weight.numeric' => 'El peso debe ser un número.',
            'weight.min' => 'El peso debe ser mayor o igual a 0.',
            'image_url.url' => 'La URL de la imagen debe ser una URL válida.',
            'image_url.max' => 'La URL de la imagen no debe exceder los 255 caracteres.',
            'is_active.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}
