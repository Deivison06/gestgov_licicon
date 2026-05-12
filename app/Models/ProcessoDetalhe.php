<?php

namespace App\Models;

use App\Enums\TipoContratacaoEnum;
use App\Enums\TipoProcedimentoEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessoDetalhe extends Model
{
    use HasFactory;

    protected $table = 'processo_detalhes';

    protected $fillable = [
        'processo_id',
        'secretaria',
        'data_publicacao',
        'unidade_setor',
        'servidor_responsavel',
        'demanda',
        'justificativa',
        'prazo_entrega',
        'local_entrega',
        'contratacoes_anteriores',
        'fiscais',
        'gestor',
        'instrumento_vinculativo',
        'instrumento_vinculativo_outro',
        'prazo_vigencia',
        'prazo_vigencia_outro',
        'objeto_continuado',
        'itens_e_seus_quantitativos_xml',
        'descricao_e_quantitativos_itens_xml',
        'nome_equipe_planejamento',
        'responsavel_equipe_planejamento',
        'descricao_necessidade',
        'alinhamento_planejamento_anual',
        'problema_resolvido',
        'inversao_fase',
        'descricao_necessidade_autorizacao',
        'solucoes_disponivel_mercado',
        'incluir_requisito_cada_caso_concreto',
        'solucao_escolhida',
        'justificativa_solucao_escolhida',
        'impacto_ambiental',
        'tipo_srp',
        'prevista_plano_anual',
        'encaminhamento_pesquisa_preco',
        'encaminhamento_doacao_orcamentaria',
        'painel_preco_tce',
        'anexo_pdf_analise_mercado',

        'encaminhamento_elaborar_editais',
        'encaminhamento_parecer_juridico',
        'encaminhamento_autorizacao_abertura',
        'valor_estimado',
        '.env',
        'dotacao_orcamentaria',
        'anexar_minuta',
        'riscos_extra',
        'anexo_pdf_publicacoes',
        'data_hora',
        'tratamento_diferenciado_MEs_eEPPs',
        'itens_especificaca_quantitativos_xml',

        'intervalo_lances',
        'portal',
        'exigencia_garantia_proposta',
        'exigencia_garantia_contrato',
        'participacao_exclusiva_mei_epp',
        'reserva_cotas_mei_epp',
        'prioridade_contratacao_mei_epp',
        'exigencias_tecnicas',
        'regularidade_fisica',
        'qualificacao_economica',
        'anexo_pdf_minuta_contrato',
        'anexo_pdf_ata_resgitro_preco',
        'data_hora_limite_edital',
        'data_hora_fase_edital',
        'pregoeiro',
        'numero_items',
        'projeto_basico_pdf',
        'agente_contratacao',
        'encaminhamento_elaborar_projeto_basico',
        'info_extras',
        'exige_atestado',

        'orgao_responsavel',
        'cnpj',
        'endereco',
        'responsavel',
        'cpf_responsavel',
        'endereco_imovel',
        'prazo_inicio_prestacao_servico',
        'prazo_final_prestacao_servico',
        'valor_mensal',

        'razao_social',
        'cnpj_empresa_vencedora',
        'endereco_empresa_vencedora',
        'representante_legal_empresa',
        'cpf_representante',
        'valor_total',
        'especificacao_servicos_imovel',
        'razao_escolha_contratado',
        'obrigacoes_contratado_extras',
        'obrigacoes_contratante_extras',
        'empresa_vencedora_pdf',
        'encaminhamento_elaborar_termo_referencia',
        'encaminhamento_controle_interno'
    ];

    protected $casts = [
        'data_publicacao' => 'date',
        // data_hora, data_hora_limite_edital, data_hora_fase_edital são strings locais
        // (sem cast 'datetime' para evitar conversão UTC pelo Carbon que adicionava +3h)
        'instrumento_vinculativo' => 'array',
        'prazo_vigencia' => 'array',
        'itens_e_seus_quantitativos_xml' => 'array',
        'descricao_e_quantitativos_itens_xml' => 'array',
        'itens_especificaca_quantitativos_xml' => 'array',

    ];

    /**
     * Relação com Processo
     */
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }
}
