<?php

namespace App\Models;

use App\Enums\NaturezaAlteracaoAditivoEnum;
use Illuminate\Database\Eloquent\Model;

class IncidenteContratual extends Model
{
    protected $table = 'incidentes_contratuais';

    protected $fillable = [
        'contratavel_id',
        'contratavel_type',
        'tipo',
        'categoria',
        'origem',
        'data_aditivo',
        'data_finalizacao_base',
        'valor_total_base',
        'meses_prorrogacao',
        'percentual_valor',
        'justificativa',
        'status',
        'arquivo_solicitacao_path',
        'arquivo_orcamento_obra_path',
        'arquivo_aditivo_externo_path',
        'nome_solicitante',
        'cargo_solicitante',
        'nome_parecerista',
        'cargo_parecerista',
        'oab_parecerista',

        // Decisões de abertura do aditivo (atualização TCU)
        'tipo_objeto_contrato',
        'contrato_continuo',

        // Natureza explícita da alteração de valor
        'natureza_alteracao',
        'percentual_acrescimo',
        'percentual_supressao',

        // Compras e Serviços — Prazo e Valor
        'tem_valor_periodo_prorrogado',
        'valor_periodo_prorrogado',

        // Obras — Solicitação / limite legal
        'percentual_execucao_fisica',
        'limite_legal_aditivo_obras',

        // Ato: Aceite e Documentação de Regularidade
        'arquivo_aceite_documentacao_path',

        // Ato: Parecer Técnico (só Obras)
        'nome_engenheiro_parecer_tecnico',
        'cargo_engenheiro_parecer_tecnico',
        'registro_profissional_parecer_tecnico',
        'situacao_execucao_contratual',
        'fatores_atraso',
        'descricao_fatores_atraso',
        'responsabilidade_atraso',
        'texto_vantajosidade_tecnica',
        'conclusao_tecnica',
    ];

    protected $casts = [
        'data_aditivo' => 'date',
        'data_finalizacao_base' => 'date',
        'valor_total_base' => 'decimal:2',
        'contrato_continuo' => 'boolean',
        'percentual_acrescimo' => 'decimal:2',
        'percentual_supressao' => 'decimal:2',
        'tem_valor_periodo_prorrogado' => 'boolean',
        'valor_periodo_prorrogado' => 'decimal:2',
        'percentual_execucao_fisica' => 'decimal:2',
        'fatores_atraso' => 'array',
    ];

    public function ehExterno(): bool
    {
        return $this->origem === 'externo';
    }

    /**
     * O contrato ao qual este aditivo pertence — pode ser um Contrato do Sistema
     * (App\Models\Contrato) ou um Contrato Manual/Externo (App\Models\ContratoManual).
     * Mesmo padrão morphTo usado por Fiscalizacao/Ocorrencia ('fiscalizavel').
     */
    public function contratavel()
    {
        return $this->morphTo();
    }

    public function itens()
    {
        return $this->hasMany(IncidenteContratualItem::class, 'incidente_contratual_id');
    }

    /**
     * Snapshot estruturado da dotação orçamentária informada para este
     * aditivo (ato "Informação de Disponibilidade Orçamentária"). hasMany em
     * vez de 1:1 para permitir rateio entre mais de uma fonte de recurso.
     */
    public function dotacoes()
    {
        return $this->hasMany(IncidenteContratualDotacao::class, 'incidente_contratual_id');
    }

    public function naturezaAlteracaoEnum(): ?NaturezaAlteracaoAditivoEnum
    {
        return $this->natureza_alteracao ? NaturezaAlteracaoAditivoEnum::from($this->natureza_alteracao) : null;
    }

    /**
     * Se este aditivo tem acréscimo de valor (natureza explícita) ou valor
     * estimado para o período prorrogado — dispara a exigência do ato
     * "Informação de Disponibilidade Orçamentária".
     */
    public function temAcrescimoDeValor(): bool
    {
        if ($this->tem_valor_periodo_prorrogado) {
            return true;
        }

        return in_array($this->tipo, ['valor', 'prazo_valor'])
            && $this->naturezaAlteracaoEnum()?->temAcrescimo() === true;
    }

    /**
     * Campos de ABERTURA exigidos pela atualização TCU (tipo_objeto_contrato,
     * contrato_continuo, natureza_alteracao) — são capturados uma única vez,
     * no modal "Iniciar Novo Aditivo" (store()). Um aditivo criado a partir
     * desta atualização SEMPRE os tem preenchidos desde o momento da criação;
     * só ficam vazios em aditivos LEGADOS (criados antes desta atualização).
     * Por isso servem para distinguir "aditivo legado, precisa complementar
     * dados antigos" de "aditivo novo, ainda no meio do preenchimento normal".
     *
     * @return list<string>
     */
    public function camposAberturaTceFaltantes(): array
    {
        $faltantes = [];

        if ($this->categoria === 'compras_servicos' && ! $this->tipo_objeto_contrato) {
            $faltantes[] = 'tipo_objeto_contrato';
        }

        if (in_array($this->tipo, ['prazo', 'prazo_valor']) && is_null($this->contrato_continuo)) {
            $faltantes[] = 'contrato_continuo';
        }

        if (in_array($this->tipo, ['valor', 'prazo_valor']) && ! $this->natureza_alteracao) {
            $faltantes[] = 'natureza_alteracao';
        }

        return $faltantes;
    }

    /**
     * true somente para aditivos criados ANTES da atualização TCU (sem os
     * campos de abertura). Dispara o aviso/formulário de "dados pendentes" —
     * nunca aparece para um aditivo criado a partir de agora, mesmo que ele
     * ainda esteja no meio do preenchimento normal do fluxo (percentuais,
     * limite legal etc., que são preenchidos em etapas posteriores).
     */
    public function ehAditivoLegado(): bool
    {
        return ! empty($this->camposAberturaTceFaltantes());
    }

    /**
     * Todos os campos novos (atualização TCU) ainda faltando para este
     * aditivo, incluindo os de etapas posteriores do fluxo normal
     * (percentuais na Solicitação, limite legal no Parecer Técnico). Usado
     * só para bloquear a geração de PDF — um aditivo novo e em dia com a
     * abertura pode perfeitamente ter itens aqui enquanto o usuário ainda
     * está preenchendo o formulário, sem que isso seja um "aditivo legado".
     *
     * @return list<string>
     */
    public function camposTceFaltantes(): array
    {
        $faltantes = $this->camposAberturaTceFaltantes();

        if (in_array($this->tipo, ['valor', 'prazo_valor'])) {
            $natureza = $this->naturezaAlteracaoEnum();
            if ($natureza?->temAcrescimo() && $this->percentual_acrescimo === null) {
                $faltantes[] = 'percentual_acrescimo';
            }
            if ($natureza?->temSupressao() && $this->percentual_supressao === null) {
                $faltantes[] = 'percentual_supressao';
            }

            if ($this->categoria === 'obras' && ! $this->limite_legal_aditivo_obras) {
                $faltantes[] = 'limite_legal_aditivo_obras';
            }
        }

        return $faltantes;
    }

    public function dadosTceCompletos(): bool
    {
        return empty($this->camposTceFaltantes());
    }
}
