<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plate' => ['required', 'string', 'max:10'],
            'model' => ['required', 'string', 'max:100'],
            'color' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'plate.required' => 'A placa é obrigatória.',
            'model.required' => 'O modelo é obrigatório.',
            'color.required' => 'A cor é obrigatória.',
        ];
    }
}
