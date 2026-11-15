<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Escolha do mes e do ano do fechamento (B-10 / RF08 / UC08-HU01).
 *
 * Os dois campos sao opcionais: sem nada escolhido, a tela abre no mes
 * corrente. O que a validacao garante e que o que chega pela URL e um mes
 * e um ano de verdade, e nao qualquer coisa digitada na barra de endereco.
 */
class MonthlyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mes' => ['nullable', 'integer', 'between:1,12'],
            'ano' => ['nullable', 'integer', 'between:2020,2100'],
        ];
    }

    public function messages(): array
    {
        return [
            'mes.integer' => 'Mes invalido.',
            'mes.between' => 'O mes deve estar entre 1 e 12.',
            'ano.integer' => 'Ano invalido.',
            'ano.between' => 'O ano deve estar entre 2020 e 2100.',
        ];
    }

    public function mes(): int
    {
        return (int) ($this->validated('mes') ?? today()->month);
    }

    public function ano(): int
    {
        return (int) ($this->validated('ano') ?? today()->year);
    }
}
