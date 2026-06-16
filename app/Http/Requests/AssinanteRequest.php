<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssinanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $assinanteId = $this->route('assinante'); // {assinante} param do resource
        $userId = is_object($assinanteId) ? $assinanteId->id : $assinanteId;

        return [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'cpf'             => [
                'nullable', 'string', 'max:18',
                Rule::unique('users', 'cpf')->ignore($userId),
            ],
            'prefeitura_id'   => ['required', 'integer', 'exists:prefeituras,id'],
            'unidade_id'      => ['nullable', 'integer', 'exists:unidades,id'],
            'numero_portaria' => ['nullable', 'string', 'max:50'],
            'data_portaria'   => ['nullable', 'date'],
            'password'        => $this->isMethod('post')
                ? ['required', 'string', 'min:8', 'confirmed']
                : ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'         => 'Já existe um usuário com este e-mail.',
            'cpf.unique'           => 'Já existe um usuário com este CPF.',
            'password.min'         => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed'   => 'A confirmação de senha não confere.',
            'prefeitura_id.exists' => 'Prefeitura inválida.',
            'unidade_id.exists'    => 'Unidade inválida.',
        ];
    }
}
