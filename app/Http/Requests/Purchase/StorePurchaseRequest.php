<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
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
            'id_purchase' => 'required|string|max:50|unique:purchases,id_purchase',
            'id_supplier' => 'required|string|max:50|exists:suppliers,id_supplier',
            'purchase_total' => 'nullable|numeric|min:0|max:9999999999.99',
            'purchase_status' => 'required|string|max:20|in:pendiente,confirmada,recibida,cancelada',
            'items' => 'present|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:100000',
            'items.*.unit_cost' => 'required|numeric|min:0|max:9999999999.99',
            'items.*.subtotal' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'id_purchase.required' => 'El campo id_purchase es obligatorio.',
            'id_purchase.string' => 'El campo id_purchase debe ser una cadena de texto.',
            'id_purchase.max' => 'El campo id_purchase no puede tener más de 50 caracteres.',
            'id_purchase.unique' => 'El id_purchase ya está en uso.',
            'id_supplier.required' => 'El campo id_supplier es obligatorio.',
            'id_supplier.string' => 'El campo id_supplier debe ser una cadena de texto.',
            'id_supplier.max' => 'El campo id_supplier no puede tener más de 50 caracteres.',
            'id_supplier.exists' => 'El proveedor indicado no existe.',
            'purchase_total.numeric' => 'El campo purchase_total debe ser un número.',
            'purchase_total.min' => 'El campo purchase_total debe ser mayor o igual a 0.',
            'purchase_total.max' => 'El campo purchase_total excede el máximo permitido.',
            'purchase_status.required' => 'El campo purchase_status es obligatorio.',
            'purchase_status.string' => 'El campo purchase_status debe ser una cadena de texto.',
            'purchase_status.max' => 'El campo purchase_status no puede tener más de 20 caracteres.',
            'purchase_status.in' => 'El estado debe ser pendiente, confirmada, recibida o cancelada.',
            'items.present' => 'Debe enviar el detalle de la compra en el campo items.',
            'items.array' => 'Los detalles deben ser un arreglo.',
            'items.*.product_id.required' => 'El producto es obligatorio.',
            'items.*.product_id.exists' => 'El producto seleccionado no existe.',
            'items.*.quantity.required' => 'La cantidad es obligatoria.',
            'items.*.quantity.integer' => 'La cantidad debe ser un número entero.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor que 0.',
            'items.*.quantity.max' => 'La cantidad excede el máximo permitido.',
            'items.*.unit_cost.required' => 'El costo unitario es obligatorio.',
            'items.*.unit_cost.numeric' => 'El costo unitario debe ser numérico.',
            'items.*.unit_cost.min' => 'El costo unitario no puede ser negativo.',
            'items.*.unit_cost.max' => 'El costo unitario excede el máximo permitido.',
            'items.*.subtotal.numeric' => 'El subtotal debe ser numérico.',
            'items.*.subtotal.min' => 'El subtotal no puede ser negativo.',
        ];
    }
}
