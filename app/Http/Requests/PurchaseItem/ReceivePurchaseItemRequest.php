<?php

namespace App\Http\Requests\PurchaseItem;

use Illuminate\Foundation\Http\FormRequest;

class ReceivePurchaseItemRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $body = json_decode($this->getContent(), true);

        if (is_array($body) && array_key_exists('barcode', $body)) {
            $this->merge(['barcode' => $body['barcode']]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'barcode' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'barcode.required' => 'El código de barras es obligatorio.',
            'quantity.required' => 'La cantidad recibida es obligatoria.',
            'quantity.integer' => 'La cantidad recibida debe ser un número entero.',
            'quantity.min' => 'La cantidad recibida debe ser como mínimo 1.',
        ];
    }
}
