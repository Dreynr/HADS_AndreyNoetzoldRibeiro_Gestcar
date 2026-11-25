<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Entrada ou saida de estoque (B-12 / RF07 / UC07-HU03).
 *
 * A regra de que a saida nao pode deixar o saldo negativo **nao** esta aqui:
 * ela vive no `Product::registerMovement()`, dentro da transacao. Validar
 * aqui seria conferir o saldo antes, e entre a conferencia e a gravacao o
 * saldo pode ter mudado. O Form Request so garante que os campos sao
 * validos.
 */
class StockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([Product::TYPE_IN, Product::TYPE_OUT])],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:99999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Escolha entre entrada e saida.',
            'type.in' => 'Tipo de movimentacao invalido.',
            'quantity.required' => 'A quantidade e obrigatoria.',
            'quantity.numeric' => 'A quantidade deve ser um numero.',
            'quantity.gt' => 'A quantidade deve ser maior que zero.',
            'quantity.max' => 'A quantidade e grande demais para uma movimentacao.',
            'notes.max' => 'As observacoes nao podem ter mais de 1000 caracteres.',
        ];
    }
}
