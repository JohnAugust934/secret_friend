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
            'name' => 'required|string|max:255',
            'event_date' => 'required|date|after:today',
            'budget' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
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
