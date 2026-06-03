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
            // Permite manter a data original inalterada (inclusive se já passou),
            // mas rejeita qualquer nova data no passado — evita que o owner fique
            // preso sem conseguir editar outros campos após a data do evento.
            'event_date'   => [
                'required',
                'date',
                function (string $attr, mixed $value, \Closure $fail) {
                    $group = $this->route('group');
                    if ($group && $group->event_date?->toDateString() === \Carbon\Carbon::parse($value)->toDateString()) {
                        return;
                    }
                    if (\Carbon\Carbon::parse($value)->isBefore(now()->startOfDay())) {
                        $fail('A data da revelação deve ser hoje ou uma data futura.');
                    }
                },
            ],
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
