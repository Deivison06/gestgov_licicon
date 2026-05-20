<?php

namespace App\Http\Requests;

use App\Services\IA\IaContextoService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GerarConteudoIaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $camposPermitidos = (new IaContextoService())->camposPermitidos();

        return [
            'campo'       => ['required', 'string', Rule::in($camposPermitidos)],
            'instrucao'   => ['required', 'string', 'min:3', 'max:1000'],
            'processo_id' => ['nullable', 'integer', 'exists:processos,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'campo.in'         => 'Campo não autorizado para geração via IA.',
            'instrucao.min'    => 'A instrução é curta demais — descreva melhor o que você precisa.',
            'instrucao.max'    => 'A instrução é muito longa (máximo 1000 caracteres).',
            'processo_id.exists' => 'Processo informado não encontrado.',
        ];
    }
}
