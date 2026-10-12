<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'service_id' => ['required', 'exists:services,id'],
            'date' => ['required', 'date', 'after:today'],
            'period' => ['required', 'in:manha,tarde,noite'],
            'extras' => ['nullable', 'array'],
            'extras.*' => ['exists:services,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Selecione um veiculo.',
            'vehicle_id.exists' => 'Veiculo invalido.',
            'service_id.required' => 'Selecione um servico.',
            'service_id.exists' => 'Servico invalido.',
            'date.required' => 'Selecione a data.',
            'date.after' => 'O agendamento deve ser para uma data futura.',
            'period.required' => 'Selecione o periodo.',
            'period.in' => 'Periodo invalido.',
            'notes.max' => 'As observacoes nao podem ter mais de 500 caracteres.',
        ];
    }
}
