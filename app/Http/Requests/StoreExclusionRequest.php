<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExclusionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Aqui verificamos se quem está tentando criar a regra é o dono do grupo.
     */
    public function authorize(): bool
    {
        $group = $this->route('group');
        // Apenas o dono do grupo pode adicionar exclusões
        return $group && $group->owner_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $group = $this->route('group');

        return [
            'user_id' => [
                'required',
                'integer',
                // Garante que o usuário existe E faz parte deste grupo específico
                Rule::exists('group_members', 'user_id')->where(function ($query) use ($group) {
                    return $query->where('group_id', $group->id);
                }),
            ],
            'excluded_id' => [
                'required',
                'integer',
                'different:user_id', // Não pode excluir a si mesmo (regra redundante mas boa pra UX)
                // Garante que o excluído existe E faz parte deste grupo específico
                Rule::exists('group_members', 'user_id')->where(function ($query) use ($group) {
                    return $query->where('group_id', $group->id);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'O participante selecionado não pertence a este grupo.',
            'excluded_id.exists' => 'O participante a ser excluído não pertence a este grupo.',
            'excluded_id.different' => 'Um participante não pode ter uma restrição contra si mesmo.',
        ];
    }
}
