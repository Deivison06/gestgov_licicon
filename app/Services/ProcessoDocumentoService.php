<?php

namespace App\Services;

use App\Enums\TipoContratacaoEnum;
use App\Models\Processo;
use App\Enums\ModalidadeEnum;
use App\Enums\TipoProcedimentoEnum;

class ProcessoDocumentoService
{
    protected array $documentos = [
        'capa' => [
            'titulo' => 'Capa do documento',
            'cor' => '#EF4444',
            'data_id' => 'data_capa',
            'campos' => [''],
        ],
        'formalizacao' => [
            'titulo' => 'DOCUMENTO DE FORMALIZAÇÃO DE DEMANDA',
            'cor' => '#3B82F6',
            'data_id' => 'data_formalizacao',
            'campos' => [
                'secretaria',
                'justificativa',
                'prazo_entrega',
                'local_entrega',
                'contratacoes_anteriores',
                'instrumento_vinculativo',
                'prazo_vigencia',
                'objeto_continuado',
                'descricao_necessidade_autorizacao',
                'descricao_e_quantitativos_itens_xml',
                'responsavel_equipe_planejamento',
            ],
        ],
        'estudo_tecnico' => [
            'titulo' => 'INSTRUMENTOS DE PLANEJAMENTO ETP E MAPA DE RISCOS',
            'cor' => '#A855F7',
            'data_id' => 'data_estudo_tecnico',
            'campos' => [
                'problema_resolvido',
                'descricao_necessidade',
                'inversao_fase',
                'solucoes_disponivel_mercado',
                'incluir_requisito_cada_caso_concreto',
                'solucao_escolhida',
                'justificativa_solucao_escolhida',
                'resultado_pretendidos',
                'impacto_ambiental',
                'riscos_extra',
                'tipo_srp',
                'prevista_plano_anual',
                'encaminhamento_elaborar_editais',
                'encaminhamento_elaborar_projeto_basico',
                'encaminhamento_pesquisa_preco',
                'encaminhamento_doacao_orcamentaria',
                'encaminhamento_elaborar_termo_referencia',
                'itens_e_seus_quantitativos_xml',
            ],
        ],
        'projeto_basico' => [
            'titulo' => 'PROJETO BÁSICO',
            'cor' => '#22C55E',
            'data_id' => 'data_projeto_basico',
            'campos' => ['projeto_basico_pdf'],
        ],
        'analise_mercado' => [
            'titulo' => 'ANÁLISE DE MERCADO (PESQUISA DE PREÇOS)',
            'cor' => '#22C55E',
            'data_id' => 'data_analise_mercado',
            'campos' => ['painel_preco_tce', 'anexo_pdf_analise_mercado'],
        ],
        'disponibilidade_orçamento' => [
            'titulo' => 'DISPONIBILIDADE ORÇAMENTÁRIA',
            'cor' => '#EAB308',
            'data_id' => 'data_disponibilidade_orçamento',
            'campos' => [
                'valor_estimado',
                'dotacao_orcamentaria',
            ],
        ],
        'termo_referencia' => [
            'titulo' => 'TERMO DE REFERÊNCIA',
            'cor' => '#F97316',
            'data_id' => 'data_termo_referencia',
            'campos' => [
                'encaminhamento_autorizacao_abertura',
                'itens_especificaca_quantitativos_xml',
                'info_extras',
                'especificacao_servicos_imovel',
                'razao_escolha_contratado',
                'obrigacoes_contratado_extras',
                'obrigacoes_contratante_extras'
            ],
        ],
        'minutas' => [
            'titulo' => 'MINUTAS',
            'cor' => '#EC4899',
            'data_id' => 'data_minutas',
            'campos' => ['anexar_minuta'],
        ],
        'parecer_juridico' => [
            'titulo' => 'PARECER JURÍDICO',
            'cor' => '#10B981',
            'data_id' => 'data_parecer_juridico',
            'campos' => [''],
        ],
        'autorizacao_abertura_procedimento' => [
            'titulo' => 'AUTORIZAÇÃO ABERTURA PROCEDIMENTO LICITATÓRIO',
            'cor' => '#14B8A6',
            'data_id' => 'data_autorizacao_abertura_procedimento',
            'campos' => ['tratamento_diferenciado_MEs_eEPPs', 'agente_contratacao'],
        ],
        'abertura_fase_externa' => [
            'titulo' => 'ABERTURA FASE EXTERNA',
            'cor' => '#06B6D4',
            'data_id' => 'data_abertura_fase_externa',
            'campos' => [''],
        ],
        'avisos_licitacao' => [
            'titulo' => 'AVISOS DE LICITAÇÃO',
            'cor' => '#6366F1',
            'data_id' => 'data_avisos_licitacao',
            'campos' => ['data_hora', 'portal'],
        ],
        'edital' => [
            'titulo' => 'EDITAL',
            'cor' => '#6366F1',
            'data_id' => 'data_edital',
            'campos' => [
                'exige_atestado',
                'data_hora_limite_edital',
                'data_hora_fase_edital',
                'pregoeiro',
                'intervalo_lances',
                'exigencia_garantia_proposta',
                'exigencia_garantia_contrato',
                'participacao_exclusiva_mei_epp',
                'reserva_cotas_mei_epp',
                'prioridade_contratacao_mei_epp',
                'regularidade_fisica',
                'qualificacao_economica',
                'exigencias_tecnicas',
                'anexo_pdf_minuta_contrato',
                'numero_items',
            ],
        ],

        'minuta_cancelamento' => [
            'titulo' => 'MINUTA DE CANCELAMENTO',
            'cor' => '#EF4444',
            'data_id' => 'data_minuta_cancelamento',
            'campos' => [''],
        ],
        'publicacoes_avisos_licitacao' => [
            'titulo' => 'PUBLICAÇÕES',
            'cor' => '#6366F1',
            'data_id' => 'data_publicacoes_avisos_licitacao',
            'campos' => ['anexo_pdf_publicacoes'],
        ],
        'declaracao_indisponibilidade_imovel' => [
            'titulo' => 'DECLARAÇÃO DE INDISPONIBILIDADE DE IMÓVEL PRÓPRIO',
            'cor' => '#7C3AED', // violeta forte
            'data_id' => 'data_declaracao_indisponibilidade_imovel',
            'campos' => [''],
        ],
        'termo_recebimento' => [
            'titulo' => 'TERMO DE RECEBIMENTO',
            'cor' => '#2563EB', // azul
            'data_id' => 'data_termo_recebimento',
            'campos' => [''],
        ],
        'termo_autuacao' => [
            'titulo' => 'TERMO DE AUTUAÇÃO',
            'cor' => '#0284C7', // azul céu
            'data_id' => 'data_termo_autuacao',
            'campos' => [''],
        ],
        'termo_juntada' => [
            'titulo' => 'TERMO DE JUNTADA',
            'cor' => '#0284C7', // azul céu
            'data_id' => 'data_termo_juntada',
            'campos' => [
                'orgao_responsavel',
                'cnpj',
                'endereco',
                'responsavel',
                'cpf_responsavel',
                'razao_social',
                'cnpj_empresa_vencedora',
                'endereco_empresa_vencedora',
                'representante_legal_empresa',
                'cpf_representante',
                'endereco_imovel',
                'prazo_inicio_prestacao_servico',
                'prazo_final_prestacao_servico',
                'valor_mensal',
                'empresa_vencedora_pdf'

            ],
        ],
        'demonstracao_servico_tecnico_singular' => [
            'titulo' => 'DEMONSTRAÇÃO DE SERVIÇO TÉCNICO SINGULAR',
            'cor' => '#0EA5E9', // azul claro
            'data_id' => 'data_demonstracao_servico_tecnico_singular',
            'campos' => [''],
        ],
        'parecer_tecnico' => [
            'titulo' => 'PARECER TÉCNICO',
            'cor' => '#059669', // verde escuro
            'data_id' => 'data_parecer_tecnico',
            'campos' => [''],
        ],
        'parecer_controle_interno' => [
            'titulo' => 'PARECER DO CONTROLE INTERNO',
            'cor' => '#16A34A', // verde
            'data_id' => 'data_parecer_controle_interno',
            'campos' => [''],
        ],
        'ato_autorizacao' => [
            'titulo' => 'ATO DE AUTORIZAÇÃO DE INEXIGIBILIDADE DE LICITAÇÃO',
            'cor' => '#16A34A', // verde
            'data_id' => 'data_ato_autorizacao',
            'campos' => [''],
        ],
        'contrato' => [
            'titulo' => 'CONTRATO',
            'cor' => '#EA580C', // laranja
            'data_id' => 'data_contrato',
            'campos' => [''],
        ],
    ];

    protected array $mapeamentoAnexos = [
        'analise_mercado' => 'anexo_pdf_analise_mercado',
        'minutas' => 'anexar_minuta',
        'publicacoes_avisos_licitacao' => 'anexo_pdf_publicacoes',
        'edital' => ['anexo_pdf_minuta_contrato'],
        'projeto_basico' => 'projeto_basico_pdf',
        'termo_juntada' => 'empresa_vencedora_pdf',
    ];

    public function getDocumentosPorModalidade(Processo $processo): array
    {
        $ordemDocumentos = $this->getOrdemDocumentosParaView($processo);

        // Remove referências estáticas de edital_republicado (agora é dinâmico)
        $ordemDocumentos = array_values(array_filter($ordemDocumentos, fn($t) => $t !== 'edital_republicado'));

        // Coletar TODAS as republicações do banco, ordenadas por data
        $republicacoes = $processo->relationLoaded('documentos')
            ? $processo->documentos->whereIn('tipo_documento', ['republicacao_edital', 'edital_adiado'])->sortBy('gerado_em')->values()
            : $processo->documentos()->whereIn('tipo_documento', ['republicacao_edital', 'edital_adiado'])->orderBy('gerado_em')->get();

        // Gerar entradas dinâmicas para cada republicação
        $republicacaoEntries = [];
        if ($republicacoes->isNotEmpty()) {
            $i = 1;
            foreach ($republicacoes as $repDoc) {
                $key = "republicacao_edital_{$repDoc->id}";
                $republicacaoEntries[$key] = [
                    'titulo' => "EDITAL REPUBLICADO #{$i}",
                    'cor' => '#7C3AED',
                    'data_id' => "data_{$key}",
                    'campos' => [],
                    'documento_id' => $repDoc->id,
                ];
                $i++;
            }

            // Inserir após publicacoes_avisos_licitacao
            $posPublicacoes = array_search('publicacoes_avisos_licitacao', $ordemDocumentos, true);
            $insertPos = $posPublicacoes !== false ? $posPublicacoes + 1 : count($ordemDocumentos);
            array_splice($ordemDocumentos, $insertPos, 0, array_keys($republicacaoEntries));
        }

        // Exibir "Minuta de Cancelamento" acima de "Minutas" quando existir
        $temMinutaCancelamento = $processo->relationLoaded('documentos')
            ? $processo->documentos->where('tipo_documento', 'minuta_cancelamento')->isNotEmpty()
            : $processo->documentos()->where('tipo_documento', 'minuta_cancelamento')->exists();

        if ($temMinutaCancelamento) {
            $posMinutas = array_search('minutas', $ordemDocumentos, true);
            if ($posMinutas !== false) {
                array_splice($ordemDocumentos, $posMinutas, 0, ['minuta_cancelamento']);
            } else {
                $ordemDocumentos[] = 'minuta_cancelamento';
            }
        }

        // Merge das entradas dinâmicas no lookup de documentos
        $allDocumentos = array_merge($this->documentos, $republicacaoEntries);

        $documentosOrdenados = [];

        foreach ($ordemDocumentos as $tipo) {
            if (!isset($allDocumentos[$tipo])) {
                continue;
            }

            $documentosOrdenados[$tipo] = $allDocumentos[$tipo];

            if ($processo->modalidade === ModalidadeEnum::INEXIGIBILIDADE) {
                // remove do termo de referência
                if ($tipo === 'termo_referencia') {
                    $documentosOrdenados[$tipo]['campos'] = array_values(array_filter(
                        $documentosOrdenados[$tipo]['campos'],
                        fn($campo) => $campo !== 'encaminhamento_parecer_juridico'
                    ));
                }

                // adiciona no parecer técnico
                if ($tipo === 'parecer_tecnico') {
                    $documentosOrdenados[$tipo]['campos'][] = 'encaminhamento_parecer_juridico';
                }

                // adiciona no parecer jurídico apenas para inexigibilidade
                if ($tipo === 'parecer_juridico') {
                    $documentosOrdenados[$tipo]['campos'][] = 'encaminhamento_controle_interno';
                }
            }

            if ($tipo === 'formalizacao') {
                $documentosOrdenados[$tipo]['campos'] = $this->filtrarCamposFormalizacao(
                    $documentosOrdenados[$tipo]['campos'],
                    $processo
                );
            }

            if ($tipo === 'disponibilidade_orçamento' && $processo->modalidade === ModalidadeEnum::DISPENSA) {
                $documentosOrdenados[$tipo]['campos'][] = 'tipo_srp';
            }

            if ($tipo === 'estudo_tecnico' && $processo->modalidade === ModalidadeEnum::DISPENSA) {
                $documentosOrdenados[$tipo]['campos'] = $this->filtrarCamposEstudoTecnicoDispensa(
                    $documentosOrdenados[$tipo]['campos']
                );
            }
        }

        return $documentosOrdenados;
    }

    public function getOrdemDocumentosParaView(Processo $processo): array
    {
        return match ($processo->modalidade) {
            ModalidadeEnum::INEXIGIBILIDADE => match ($processo->tipo_contratacao) {
                TipoContratacaoEnum::TECNICO => $this->getOrdemDocumentosInexigibilidadeTecnico(),
                TipoContratacaoEnum::ARTISTICO => $this->getOrdemDocumentosInexigibilidadeArtistico(),
                TipoContratacaoEnum::IMOVEL => $this->getOrdemDocumentosInexigibilidadeImovel(),
                TipoContratacaoEnum::FORNECEDOR => $this->getOrdemDocumentosInexigibilidadeFornecedor(),
                default => $this->getOrdemDocumentosPadrao(),
            },

            ModalidadeEnum::DISPENSA => $this->getOrdemDocumentosDispensa($processo),
            ModalidadeEnum::PREGAO_ELETRONICO => $this->getOrdemDocumentosPregaoEletronico(),
            ModalidadeEnum::CONCORRENCIA => $this->getOrdemDocumentosConcorrencia(),
            default => $this->getOrdemDocumentosPadrao(),
        };
    }

    public function getOrdemDocumentosParaDownload(Processo $processo): array
    {
        return $this->getOrdemDocumentosParaView($processo);
    }

    public function getMapeamentoAnexos(): array
    {
        return $this->mapeamentoAnexos;
    }

    public function getDocumentoConfig(string $tipo): ?array
    {
        return $this->documentos[$tipo] ?? null;
    }

    private function getOrdemDocumentosDispensa(Processo $processo): array
    {
        if ($processo->tipo_procedimento === TipoProcedimentoEnum::OBRA) {
            return [
                'capa',
                'formalizacao',
                'projeto_basico',
                'disponibilidade_orçamento',
                'autorizacao_abertura_procedimento',
                'abertura_fase_externa',
                'avisos_licitacao',
                'edital',
                'publicacoes_avisos_licitacao',
            ];
        }

        return [
            'capa',
            'formalizacao',
            'analise_mercado',
            'disponibilidade_orçamento',
            'termo_referencia',
            'autorizacao_abertura_procedimento',
            'abertura_fase_externa',
            'avisos_licitacao',
            'edital',
            'publicacoes_avisos_licitacao',
        ];
    }

    private function getOrdemDocumentosPregaoEletronico(): array
    {
        return [
            'capa',
            'formalizacao',
            'estudo_tecnico',
            'analise_mercado',
            'disponibilidade_orçamento',
            'termo_referencia',
            'minutas',
            'parecer_juridico',
            'autorizacao_abertura_procedimento',
            'abertura_fase_externa',
            'avisos_licitacao',
            'edital',
            'publicacoes_avisos_licitacao',
        ];
    }

    private function getOrdemDocumentosConcorrencia(): array
    {
        return [
            'capa',
            'formalizacao',
            'estudo_tecnico',
            'projeto_basico',
            'disponibilidade_orçamento',
            'minutas',
            'parecer_juridico',
            'autorizacao_abertura_procedimento',
            'abertura_fase_externa',
            'avisos_licitacao',
            'edital',
            'publicacoes_avisos_licitacao',
        ];
    }

    private function getOrdemDocumentosInexigibilidadeTecnico(): array
    {
        return [
            'capa',
            'formalizacao',
            'estudo_tecnico',
            'minutas',
            'disponibilidade_orçamento',
            'termo_referencia',
            'autorizacao_abertura_procedimento',
            'termo_recebimento',
            'termo_juntada',
            'demonstracao_servico_tecnico_singular',
            'parecer_tecnico',
            'parecer_juridico',
            'parecer_controle_interno',
            'ato_autorizacao',
            'contrato',
            'publicacoes_avisos_licitacao'
        ];
    }

    private function getOrdemDocumentosInexigibilidadeArtistico(): array
    {
        return [
            'capa',
            'formalizacao',
            'estudo_tecnico',
            'minutas',
            'disponibilidade_orçamento',
            'termo_referencia',
            'autorizacao_abertura_procedimento',
            'termo_recebimento',
            'termo_juntada',
            'parecer_tecnico',
            'parecer_juridico',
            'parecer_controle_interno',
            'ato_autorizacao',
            'contrato',
            'publicacoes_avisos_licitacao'
        ];
    }

    private function getOrdemDocumentosInexigibilidadeImovel(): array
    {
        return [
            'capa',
            'formalizacao',
            'estudo_tecnico',
            'minutas',
            'disponibilidade_orçamento',
            'termo_referencia',
            'autorizacao_abertura_procedimento',
            'termo_juntada',
            'termo_recebimento',
            'parecer_tecnico',
            'parecer_juridico',
            'parecer_controle_interno',
            'ato_autorizacao',
            'contrato',
            'publicacoes_avisos_licitacao'
        ];
    }

    private function getOrdemDocumentosInexigibilidadeFornecedor(): array
    {
        return [
            'capa',
            'formalizacao',
            'estudo_tecnico',
            'minutas',
            'disponibilidade_orçamento',
            'termo_referencia',
            'autorizacao_abertura_procedimento',
            'termo_recebimento',
            'termo_autuacao',
            'parecer_tecnico',
            'parecer_juridico',
            'parecer_controle_interno',
            'ato_autorizacao',
            'contrato',
            'publicacoes_avisos_licitacao'
        ];
    }

    private function getOrdemDocumentosPadrao(): array
    {
        return [
            'capa',
            'formalizacao',
            'estudo_tecnico',
            'projeto_basico',
            'analise_mercado',
            'disponibilidade_orçamento',
            'termo_referencia',
            'minutas',
            'parecer_juridico',
            'autorizacao_abertura_procedimento',
            'termo_recebimento',
            'termo_juntada',
            'abertura_fase_externa',
            'avisos_licitacao',
            'publicacoes_avisos_licitacao',
            'edital',
        ];
    }

    private function filtrarCamposFormalizacao(array $campos, Processo $processo): array
    {
        return array_filter($campos, function ($campo) use ($processo) {
            if ($processo->modalidade === ModalidadeEnum::DISPENSA) {
                if ($campo === 'descricao_necessidade_autorizacao') {
                    return false;
                }

                if ($processo->tipo_procedimento === TipoProcedimentoEnum::OBRA &&
                    $campo === 'descricao_e_quantitativos_itens_xml') {
                    return false;
                }
            }

            return true;
        });
    }

    private function filtrarCamposEstudoTecnicoDispensa(array $campos): array
    {
        return array_filter(
            $campos,
            fn($campo) => !in_array($campo, [
                'encaminhamento_pesquisa_preco',
                'encaminhamento_doacao_orcamentaria'
            ])
        );
    }
}
