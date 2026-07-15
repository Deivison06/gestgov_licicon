<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Permission;

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

        // Lógica limpa para determinar a obrigatoriedade dinâmica
        $roleId = $this->input('role');
        $role = $roleId ? Role::find($roleId) : null;

        $isPrefeituraRequired = $role && str_contains(strtolower($role->name), 'prefeitura');

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
                $isPrefeituraRequired ? 'required' : 'nullable',
                'exists:prefeituras,id',
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'unidade_id' => [
                'nullable',
                'exists:unidades,id',
            ],
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
            'prefeitura_id.required' => 'O campo prefeitura é obrigatório para usuários do tipo Prefeitura.',
            'prefeitura_id.exists' => 'A prefeitura selecionada é inválida',
            'unidade_id.exists' => 'A unidade selecionada é inválida',
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
            'unidade_id' => 'secretaria/unidade',
            'permissions' => 'permissões'
        ];
    }

    protected function prepareForValidation()
    {
        if (!$this->has('permissions')) {
            $this->merge([
                'permissions' => []
            ]);
        }
    }
}
