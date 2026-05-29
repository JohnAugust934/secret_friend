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
            'name'         => 'required|string|max:255',
            'event_date'   => 'required|date|after:today',
            'budget'       => 'nullable|numeric|min:0|max:99999.99', // SEGURANÇA: Limite máximo para evitar overflow de coluna
            'budget_limit' => 'nullable|string|max:100',             // Ex: "10€ - 20€" — descrição textual livre
            'location'     => 'nullable|string|max:255',             // Local físico do evento
            'description'  => 'nullable|string|max:2000',            // SEGURANÇA: Limite para prevenir payloads abusivos
        ];
    }

    public function attributes(): array
    {
        return [
            'name'         => 'nome do grupo',
            'event_date'   => 'data da revelação',
            'budget'       => 'valor máximo',
            'budget_limit' => 'intervalo de orçamento',
            'location'     => 'local do evento',
            'description'  => 'descrição',
        ];
    }

}
