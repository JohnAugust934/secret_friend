<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // A autorização de "dono" será feita no Controller
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'event_date'  => 'required|date|after:today',
            'budget'      => 'nullable|numeric|min:0|max:99999.99', // SEGURANÇA: Limite máximo para evitar overflow de coluna
            'description' => 'nullable|string|max:2000',            // SEGURANÇA: Limite para prevenir payloads abusivos
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome do grupo',
            'event_date' => 'data da revelação',
            'budget' => 'valor máximo',
            'description' => 'descrição',
        ];
    }
}
