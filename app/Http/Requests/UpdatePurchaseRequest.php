<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('purchase')?->id_Purchase ?? $this->route('purchase');

        return [
            'id_Purchase' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('purchases', 'id_Purchase')->ignore($id, 'id_Purchase')],
            'id_user' => ['sometimes', 'required', 'exists:users,id'],
            'id_Supplier' => ['sometimes', 'required', 'exists:suppliers,id_Supplier'],
            'Purchase_Total' => ['sometimes', 'required', 'numeric', 'min:0', 'max:9999999999.99'],
            'Purchase_status' => ['sometimes', 'required', 'string', 'max:20', 'in:pendiente,confirmada,recibida,cancelada'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.id_product' => ['required_with:items', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1', 'max:100000'],
            'items.*.unit_cost' => ['required_with:items', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_Purchase.required' => 'El campo identificador de compra es obligatorio.',
            'id_Purchase.string' => 'El identificador de compra debe ser una cadena de texto.',
            'id_Purchase.max' => 'El identificador de compra no debe exceder los 50 caracteres.',
            'id_Purchase.unique' => 'El identificador de compra ya está en uso.',
            'id_user.required' => 'El campo usuario es obligatorio.',
            'id_user.exists' => 'El usuario seleccionado no existe.',
            'id_Supplier.required' => 'El campo proveedor es obligatorio.',
            'id_Supplier.exists' => 'El proveedor seleccionado no existe.',
            'Purchase_Total.required' => 'El campo total de compra es obligatorio.',
            'Purchase_Total.numeric' => 'El campo total debe ser un número.',
            'Purchase_Total.min' => 'El campo total debe ser mayor o igual a 0.',
            'Purchase_status.required' => 'El campo estado es obligatorio.',
            'Purchase_status.string' => 'El campo estado debe ser una cadena de texto.',
            'Purchase_status.max' => 'El campo estado no debe exceder los 20 caracteres.',
            'Purchase_status.in' => 'El estado seleccionado no es válido.',
            'items.array' => 'Los detalles deben ser un arreglo.',
            'items.min' => 'Debe incluir al menos un detalle.',
            'items.*.id_product.required_with' => 'El producto es obligatorio.',
            'items.*.id_product.exists' => 'El producto seleccionado no existe.',
            'items.*.quantity.required_with' => 'La cantidad es obligatoria.',
            'items.*.quantity.integer' => 'La cantidad debe ser un número entero.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor que 0.',
            'items.*.unit_cost.required_with' => 'El costo unitario es obligatorio.',
            'items.*.unit_cost.numeric' => 'El costo unitario debe ser numérico.',
            'items.*.unit_cost.min' => 'El costo unitario no puede ser negativo.',
        ];
    }
}
