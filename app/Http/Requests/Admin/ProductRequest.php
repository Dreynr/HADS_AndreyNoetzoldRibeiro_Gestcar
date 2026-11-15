<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Cadastro e edicao de produto (B-12 / RF07 / UC07-HU01 e HU02).
 */
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'active' => $this->boolean('active'),
        ]);
    }

    public function rules(): array
    {
        return [
            // Nome unico e a regra do UC07-HU01. O `ignore` solta a propria
            // linha na edicao, senao o produto reprovaria por si mesmo.
            'name' => [
                'required', 'string', 'max:80',
                Rule::unique('products', 'name')->ignore($this->route('product')),
            ],
            'unit' => ['required', 'string', 'max:10'],

            // Quantidade so e lida no cadastro, como saldo inicial: depois
            // disso o saldo nunca e digitado, so muda por movimentacao
            // (UC07-HU03), para todo numero ter historico que o explique.
            'quantity' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],

            'min_quantity' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do produto e obrigatorio.',
            'name.unique' => 'Ja existe um produto com esse nome.',
            'name.max' => 'O nome nao pode ter mais de 80 caracteres.',
            'unit.required' => 'A unidade e obrigatoria (un, L, ml, kg).',
            'unit.max' => 'A unidade nao pode ter mais de 10 caracteres.',
            'quantity.numeric' => 'A quantidade inicial deve ser um numero.',
            'quantity.min' => 'A quantidade inicial nao pode ser negativa.',
            'min_quantity.required' => 'O estoque minimo e obrigatorio.',
            'min_quantity.numeric' => 'O estoque minimo deve ser um numero.',
            'min_quantity.min' => 'O estoque minimo nao pode ser negativo.',
        ];
    }
}
