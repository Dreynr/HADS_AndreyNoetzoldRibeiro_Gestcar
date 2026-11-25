<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validacao do registro de lavagem (B-09 / RF06).
 *
 * O mesmo Form Request atende as duas telas. Na lavagem que vem de um
 * agendamento, cliente, veiculo e servico ja estao definidos pelo proprio
 * agendamento; na lavagem avulsa eles vem do formulario, e por isso so sao
 * exigidos quando a rota nao tem agendamento.
 */
class WashRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // O valor vem sugerido pelo sistema, mas e editavel: desconto,
            // ajuste, cortesia. Por isso `min:0` e nao `min:0.01`.
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999.99'],
            // Lavagem e fato: so se registra o que ja aconteceu.
            'performed_at' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if (!$this->route('appointment')) {
            $rules['user_id'] = ['required', Rule::exists('users', 'id')->where('role', 'cliente')];
            // O veiculo precisa ser do cliente escolhido: a regra no banco
            // impede registrar a lavagem no carro de outra pessoa.
            $rules['vehicle_id'] = ['required', Rule::exists('vehicles', 'id')->where('user_id', $this->input('user_id'))];
            $rules['service_id'] = ['required', Rule::exists('services', 'id')];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'O valor cobrado e obrigatorio.',
            'amount.numeric' => 'O valor cobrado deve ser um numero.',
            'amount.gt' => 'O valor cobrado deve ser maior que zero.',
            'performed_at.required' => 'A data do atendimento e obrigatoria.',
            'performed_at.before_or_equal' => 'A data do atendimento nao pode ser no futuro.',
            'notes.max' => 'As observacoes nao podem ter mais de 1000 caracteres.',
            'user_id.required' => 'Escolha o cliente.',
            'vehicle_id.required' => 'Escolha o veiculo.',
            'vehicle_id.exists' => 'O veiculo escolhido nao pertence a esse cliente.',
            'service_id.required' => 'Escolha o servico.',
        ];
    }
}
