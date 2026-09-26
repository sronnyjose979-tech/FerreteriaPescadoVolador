<?php

namespace App\Http\Requests\Purchase;

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
        $id = $this->route('purchase')?->id_purchase ?? $this->route('purchase');

        return [
            'id_purchase' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('purchases', 'id_purchase')->ignore($id, 'id_purchase')],
            'user_id' => ['sometimes', 'required', 'exists:users,id'],
            'id_supplier' => ['sometimes', 'required', 'exists:suppliers,id_supplier'],
            'purchase_total' => ['sometimes', 'required', 'numeric', 'min:0', 'max:9999999999.99'],
            'purchase_status' => ['sometimes', 'required', 'string', 'max:20', 'in:pendiente,confirmada,recibida,cancelada'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1', 'max:100000'],
            'items.*.unit_cost' => ['required_with:items', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_purchase.required' => 'El campo identificador de compra es obligatorio.',
            'id_purchase.string' => 'El identificador de compra debe ser una cadena de texto.',
            'id_purchase.max' => 'El identificador de compra no debe exceder los 50 caracteres.',
            'id_purchase.unique' => 'El identificador de compra ya está en uso.',
            'user_id.required' => 'El campo usuario es obligatorio.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'id_supplier.required' => 'El campo proveedor es obligatorio.',
            'id_supplier.exists' => 'El proveedor seleccionado no existe.',
            'purchase_total.required' => 'El campo total de compra es obligatorio.',
            'purchase_total.numeric' => 'El campo total debe ser un número.',
            'purchase_total.min' => 'El campo total debe ser mayor o igual a 0.',
            'purchase_status.required' => 'El campo estado es obligatorio.',
            'purchase_status.string' => 'El campo estado debe ser una cadena de texto.',
            'purchase_status.max' => 'El campo estado no debe exceder los 20 caracteres.',
            'purchase_status.in' => 'El estado seleccionado no es válido.',
            'items.array' => 'Los detalles deben ser un arreglo.',
            'items.min' => 'Debe incluir al menos un detalle.',
            'items.*.product_id.required_with' => 'El producto es obligatorio.',
            'items.*.product_id.exists' => 'El producto seleccionado no existe.',
            'items.*.quantity.required_with' => 'La cantidad es obligatoria.',
            'items.*.quantity.integer' => 'La cantidad debe ser un número entero.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor que 0.',
            'items.*.unit_cost.required_with' => 'El costo unitario es obligatorio.',
            'items.*.unit_cost.numeric' => 'El costo unitario debe ser numérico.',
            'items.*.unit_cost.min' => 'El costo unitario no puede ser negativo.',
        ];
    }
}
