<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FiscalizacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fiscalizavel_id'              => 'required|integer',
            'fiscalizavel_type'            => 'required|string|in:App\\Models\\Contrato,App\\Models\\ContratoManual',
            'tipo_contrato'                => 'required|string|in:compras,servicos,obras',
            'data_fiscalizacao'            => 'required|date',
            'numero_fiscalizacao'          => 'required|string|max:100',
            'pontualidade_prazos'          => 'nullable|string',
            'regularidade_fiscal_trabalhista' => 'nullable|string',
            'comunicacao_atendimento'      => 'nullable|string',
            'irregularidade_observada'     => 'nullable|string',
            'recomendacoes_gestor'         => 'nullable|string',
            'recomendacoes_empresa'        => 'nullable|string',
            'conclusao_fiscal'             => 'required|integer|in:1,2,3',
            'execucao_objeto'              => 'nullable|string',
            'qualidade_entregas'           => 'nullable|string',
            'observacoes_servidor'         => 'nullable|string',
            'metodologia_fiscalizacao'     => 'nullable|string',
            'relatorio_fotografico'        => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'fiscalizavel_id.required'     => 'É necessário selecionar um contrato.',
            'fiscalizavel_type.required'   => 'Tipo do contrato não identificado.',
            'fiscalizavel_type.in'         => 'Tipo do contrato inválido.',
            'tipo_contrato.required'       => 'Selecione o tipo de contrato (Compras, Serviços ou Obras).',
            'tipo_contrato.in'             => 'Tipo de contrato inválido.',
            'data_fiscalizacao.required'   => 'A data da fiscalização é obrigatória.',
            'data_fiscalizacao.date'       => 'A data da fiscalização deve ser uma data válida.',
            'numero_fiscalizacao.required' => 'O número da fiscalização é obrigatório.',
            'conclusao_fiscal.required'    => 'A conclusão do fiscal é obrigatória.',
            'conclusao_fiscal.in'          => 'A conclusão do fiscal deve ser uma das opções disponíveis.',
            'relatorio_fotografico.image'  => 'O relatório fotográfico deve ser uma imagem.',
            'relatorio_fotografico.mimes'  => 'A imagem deve estar nos formatos: JPEG, JPG, PNG ou WEBP.',
            'relatorio_fotografico.max'    => 'A imagem não pode ultrapassar 5MB.',
        ];
    }
}
