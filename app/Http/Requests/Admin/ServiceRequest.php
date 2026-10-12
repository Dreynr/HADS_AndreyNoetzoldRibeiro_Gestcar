<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'blocks_period' => $this->boolean('blocks_period'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:principal,extra'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'blocks_period' => ['sometimes', 'boolean'],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do servico e obrigatorio.',
            'name.max' => 'O nome nao pode ter mais de 100 caracteres.',
            'price.required' => 'O preco e obrigatorio.',
            'price.min' => 'O preco deve ser maior que zero.',
            'duration_minutes.required' => 'A duracao e obrigatoria.',
            'duration_minutes.min' => 'A duracao minima e de 15 minutos.',
            'duration_minutes.max' => 'A duracao maxima e de 480 minutos (8 horas).',
        ];
    }
}
