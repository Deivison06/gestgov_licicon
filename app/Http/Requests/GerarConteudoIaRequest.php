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
            // No fluxo automático (geração ao abrir o acordeão) NÃO há instrução do
            // usuário — o contexto vem do processo (nome resumido). Opcional.
            'instrucao'   => ['nullable', 'string', 'max:1000'],
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
