<?php

namespace App\Http\Requests\Payment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
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
            'sale_id' => 'sometimes|exists:sales,id',
            'payment_method' => 'sometimes|string|in:cash,card,sinpe|max:50',
            'transaction_reference' => 'sometimes|nullable|string|max:255',
            'status' => 'sometimes|string|in:completed,pending,cancelled|max:20',
        ];
    }

    /**
     * Get the custom validation messages.
     */
    public function messages(): array
    {
        return [
            'sale_id.exists' => 'La venta seleccionada no existe.',

            'payment_method.string' => 'El metodo de pago debe ser una cadena de texto.',
            'payment_method.in' => 'El metodo de pago debe ser cash, card o sinpe.',
            'payment_method.max' => 'El metodo de pago no debe exceder los 50 caracteres.',

            'transaction_reference.string' => 'La referencia de transaccion debe ser una cadena de texto.',
            'transaction_reference.max' => 'La referencia de transaccion no debe exceder los 255 caracteres.',

            'status.string' => 'El estado debe ser una cadena de texto.',
            'status.in' => 'El estado debe ser completed, pending o cancelled.',
            'status.max' => 'El estado no debe exceder los 20 caracteres.',
        ];
    }
}
