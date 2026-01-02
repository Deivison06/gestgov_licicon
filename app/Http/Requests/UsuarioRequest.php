<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class UsuarioRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->route('usuario')?->id;
        $isUpdate = !is_null($userId);

        return [
            'name' => 'required|string|max:255',

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'cpf' => [
                'nullable',
                'string',
                'max:18',
                Rule::unique('users', 'cpf')->ignore($userId),
            ],

            'password' => $isUpdate
                ? 'nullable|min:8|confirmed'
                : 'required|min:8|confirmed',

            'role' => 'required|exists:roles,id',

            'prefeitura_id' => [
                'nullable',
                'exists:prefeituras,id',
                function ($attribute, $value, $fail) {
                    $roleId = $this->input('role');
                    if ($roleId) {
                        $role = Role::find($roleId);
                        if ($role && $role->name === 'prefeitura' && empty($value)) {
                            $fail('O campo prefeitura é obrigatório para usuários do tipo Prefeitura.');
                        }
                    }
                }
            ],

            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ];
    }


    public function messages()
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'O e-mail deve ser válido',
            'email.unique' => 'Este e-mail já está em uso',
            'cpf.unique' => 'Este CPF já está em uso',
            'password.required' => 'A senha é obrigatória',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres',
            'password.confirmed' => 'A confirmação de senha não corresponde',
            'role.required' => 'A função é obrigatória',
            'role.exists' => 'A função selecionada é inválida',
            'prefeitura_id.exists' => 'A prefeitura selecionada é inválida',
            'permissions.*.exists' => 'A permissão selecionada é inválida'
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'password' => 'senha',
            'role' => 'função',
            'prefeitura_id' => 'prefeitura',
            'permissions' => 'permissões'
        ];
    }

    // Método para preparar os dados antes da validação
    protected function prepareForValidation()
    {
        // Se não for envio de permissões, define como array vazio
        if (!$this->has('permissions')) {
            $this->merge([
                'permissions' => []
            ]);
        }
    }
}