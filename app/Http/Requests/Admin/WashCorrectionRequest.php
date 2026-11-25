<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Correcao de uma lavagem ja registrada (B-09 / RF06 / UC06-HU03).
 *
 * So valor e observacoes podem ser alterados. Cliente, veiculo, servico e
 * data definem *qual* atendimento aconteceu — mudar isso nao seria
 * correcao, seria trocar o fato por outro. Erro desses se resolve
 * registrando a lavagem certa, nao editando a errada.
 *
 * Por isso este Form Request nao tem regra nenhuma para esses campos: o
 * `validated()` devolve so o que pode mudar, e o que vier a mais no
 * formulario e simplesmente ignorado.
 */
class WashCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'O valor cobrado e obrigatorio.',
            'amount.numeric' => 'O valor cobrado deve ser um numero.',
            'amount.gt' => 'O valor cobrado deve ser maior que zero.',
            'notes.max' => 'As observacoes nao podem ter mais de 1000 caracteres.',
        ];
    }
}
