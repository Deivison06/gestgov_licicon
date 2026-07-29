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
        'tipo_relatorio_analise_mercado',
        'fornecedor_local_precos',
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
        'is_oriundo_fracassado',
        'processo_fracassado_id',
        'motivo_fracasso',
        'motivos_fracasso',
        'anexo_pdf_ata_sessao_fracassada',
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
        // Formato explícito evita conversão UTC na serialização JSON (+3h)
        'data_hora' => 'datetime:Y-m-d H:i:s',
        'data_hora_limite_edital' => 'datetime:Y-m-d H:i:s',
        'data_hora_fase_edital' => 'datetime:Y-m-d H:i:s',
        'prazo_inicio_prestacao_servico' => 'datetime:Y-m-d H:i:s',
        'prazo_final_prestacao_servico' => 'datetime:Y-m-d H:i:s',
        'instrumento_vinculativo' => 'array',
        'prazo_vigencia' => 'array',
        'fornecedor_local_precos' => 'array',
        'painel_preco_tce' => 'array',
        'itens_e_seus_quantitativos_xml' => 'array',
        'descricao_e_quantitativos_itens_xml' => 'array',
        'itens_especificaca_quantitativos_xml' => 'array',
        'is_oriundo_fracassado' => 'boolean',
        'motivos_fracasso' => 'array',

    ];

    /**
     * Relação com Processo
     */
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }

    public function processoFracassado()
    {
        return $this->belongsTo(Processo::class, 'processo_fracassado_id');
    }

    /**
     * Texto amigável do prazo de vigência (12/24/36 meses, exercício financeiro ou outro).
     */
    public function getPrazoVigenciaTextoAttribute(): string
    {
        $vigencia = is_array($this->prazo_vigencia) ? $this->prazo_vigencia : ['12_meses'];

        if (in_array('exercicio_financeiro', $vigencia)) {
            return 'até 31/12 do exercício financeiro da contratação';
        }

        if ($meses = $this->prazo_vigencia_meses) {
            return "{$meses} meses";
        }

        if (in_array('outro', $vigencia)) {
            return $this->prazo_vigencia_outro ?? '________________.';
        }

        return '________________';
    }

    /**
     * Quantidade de meses do prazo de vigência.
     * Retorna null quando o prazo não é expresso em meses (exercício financeiro / outro).
     */
    public function getPrazoVigenciaMesesAttribute(): ?int
    {
        $vigencia = is_array($this->prazo_vigencia) ? $this->prazo_vigencia : ['12_meses'];

        foreach ([12, 24, 36] as $meses) {
            if (in_array("{$meses}_meses", $vigencia, true)) {
                return $meses;
            }
        }

        return null;
    }

    /**
     * Calcula o término da vigência a partir da data de assinatura do contrato.
     * Ex.: assinatura em 22/06/2026 com prazo de 12 meses => 22/06/2027.
     *
     * Retorna null quando não há data de início ou quando o prazo é livre ("outro"),
     * casos em que não há como derivar a data com segurança.
     */
    public function calcularFimVigencia($dataInicio): ?\Carbon\Carbon
    {
        if (! $dataInicio) {
            return null;
        }

        $inicio = $dataInicio instanceof \Carbon\Carbon
            ? $dataInicio->copy()
            : \Carbon\Carbon::parse($dataInicio);

        $vigencia = is_array($this->prazo_vigencia) ? $this->prazo_vigencia : ['12_meses'];

        // Exercício financeiro: encerra em 31/12 do ano da assinatura.
        if (in_array('exercicio_financeiro', $vigencia, true)) {
            return $inicio->copy()->endOfYear()->startOfDay();
        }

        $meses = $this->prazo_vigencia_meses;

        // addMonthsNoOverflow evita que 31/01 + 1 mês vire 03/03 em fevereiro.
        return $meses ? $inicio->copy()->addMonthsNoOverflow($meses) : null;
    }

    /**
     * Serializa datas sem converter para UTC (mantém horário local de Brasília).
     * Resolve o problema de +3h que ocorria ao passar dados via JSON para o frontend.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
