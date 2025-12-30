<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Retorna true porque verificamos o login via middleware nas rotas
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'event_date' => 'required|date|after:today',
            'budget' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'wishlist' => 'nullable|string|max:1000',
        ];
    }

    /**
     * (Opcional) Mensagens personalizadas ou tradução de atributos
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome do grupo',
            'event_date' => 'data da revelação',
            'budget' => 'valor máximo',
            'description' => 'descrição',
            'wishlist' => 'lista de desejos',
        ];
    }
}
