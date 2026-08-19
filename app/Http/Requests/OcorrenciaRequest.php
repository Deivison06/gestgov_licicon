<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OcorrenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fiscalizavel_id' => 'required|integer',
            'fiscalizavel_type' => 'required|string|in:App\\Models\\Contrato,App\\Models\\ContratoManual',
            'numero_ocorrencia' => 'required|string|max:100',
            'data_ocorrencia' => 'required|date',
            'local' => 'nullable|string|max:255',
            'descricao_fato' => 'required|string',
            'obrigacao_descumprida' => 'nullable|string',
            'prazo_resposta' => 'nullable|string|max:255',
            'tipo_comprovacao' => 'nullable|array',
            'tipo_comprovacao.*' => 'boolean',
            'tipo_comprovacao_outro' => 'nullable|string|max:255',
            'situacao' => 'nullable|string|in:regularizada,nao_regularizada,encaminhada_gestor',
            'status' => 'required|string|in:rascunho,registrada',
            'anexos_fato' => 'nullable|array',
            'anexos_fato.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'fiscalizavel_id.required' => 'É necessário selecionar um contrato.',
            'fiscalizavel_type.required' => 'Tipo do contrato não identificado.',
            'fiscalizavel_type.in' => 'Tipo do contrato inválido.',
            'numero_ocorrencia.required' => 'O número da ocorrência é obrigatório.',
            'data_ocorrencia.required' => 'A data da ocorrência é obrigatória.',
            'data_ocorrencia.date' => 'A data da ocorrência deve ser uma data válida.',
            'descricao_fato.required' => 'A descrição do fato é obrigatória.',
            'situacao.in' => 'Selecione uma situação válida.',
            'status.required' => 'Selecione se a ocorrência será salva como rascunho ou registrada.',
            'status.in' => 'Status inválido.',
            'anexos_fato.*.mimes' => 'Os anexos devem estar nos formatos PDF, JPEG, JPG, PNG ou WEBP.',
            'anexos_fato.*.max' => 'Cada anexo não pode ultrapassar 10MB.',
        ];
    }
}
