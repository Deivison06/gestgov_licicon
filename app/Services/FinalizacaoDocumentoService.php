<?php

namespace App\Services;

use App\Models\Processo;
use App\Enums\ModalidadeEnum;
use App\Enums\TipoProcedimentoEnum;

class FinalizacaoDocumentoService
{
    protected array $documentosBase = [
        'atos_sessao' => [
            'titulo' => 'ATOS DA SESSÃO',
            'cor' => 'bg-red-500',
            'campos' => ['anexo_atos_sessao'],
            'requer_assinatura' => false,
        ],
        'proposta' => [
            'titulo' => 'PROPOSTAS',
            'cor' => 'bg-blue-500',
            'campos' => ['anexo_proposta'],
            'requer_assinatura' => false,
        ],
        'proposta_readequada' => [
            'titulo' => 'PROPOSTA VENCEDORA READEQUADA',
            'cor' => 'bg-purple-500',
            'campos' => ['anexo_proposta_readequada'],
            'requer_assinatura' => false,
        ],
        'documento_habilitacao_empresa_vencedora' => [
            'titulo' => 'DOCUMENTOS DE HABILITAÇÃO DA EMPRESA VENCEDORA',
            'cor' => 'bg-green-500',
            'campos' => ['anexo_habilitacao'],
            'requer_assinatura' => false,
        ],
        'recurso_contratacoes_decisao_recursos' => [
            'titulo' => 'RECURSOS, CONTRARRAZÕES E DECISÃO DOS RECURSOS',
            'cor' => 'bg-green-500',
            'campos' => ['anexo_recurso_contratacoes'],
            'requer_assinatura' => false,
        ],
        'termo_adjudicacao' => [
            'titulo' => 'TERMO DE ADJUDICAÇÃO',
            'cor' => 'bg-yellow-500',
            'campos' => [
                'orgao_responsavel',
                'cargo_responsavel',
                'cnpj',
                'endereco',
                'responsavel',
                'cpf_responsavel',
                'merenda_escolar',
                'veiculos',
            ],
            'requer_assinatura' => true,
        ],
        'parecer_controle_interno' => [
            'titulo' => 'PARECER DO CONTROLE INTERNO',
            'cor' => 'bg-orange-500',
            'campos' => ['cargo_controle_interno'],
            'requer_assinatura' => true,
        ],
        'termo_homologacao' => [
            'titulo' => 'TERMO DE HOMOLOGAÇÃO',
            'cor' => 'bg-pink-500',
            'campos' => [],
            'requer_assinatura' => true,
        ],
        'publicacoes' => [
            'titulo' => 'PUBLICAÇÕES',
            'cor' => 'bg-indigo-500',
            'campos' => ['anexo_publicacoes'],
            'requer_assinatura' => false,
        ],
        'ata_registro_precos' => [
            'titulo' => 'ATA DE REGISTRO DE PREÇOS',
            'cor' => 'bg-teal-500',
            'campos' => ['numero_ata_registro_precos'],
            'requer_assinatura' => true,
        ]
    ];

    protected array $mapeamentoAnexos = [
        'atos_sessao' => 'anexo_atos_sessao',
        'ata' => 'anexo_atos_sessao',
        'proposta' => 'anexo_proposta',
        'proposta_vencedora' => 'anexo_proposta_readequada',
        'proposta_readequada' => 'anexo_proposta_readequada',
        'documento_habilitacao' => 'anexo_habilitacao',
        'documento_habilitacao_empresa_vencedora' => 'anexo_habilitacao',
        'recurso_contratacoes_decisao_recursos' => 'anexo_recurso_contratacoes',
        'termo_adjudicacao' => 'anexo_planilha',
        'parecer_juridico' => 'anexo_parecer_juridico',
        'publicacoes' => 'anexo_publicacoes',
    ];

    public function getDocumentosOrganizados(Processo $processo): array
    {
        if ($processo->modalidade === ModalidadeEnum::DISPENSA) {
            return $this->getDocumentosDispensa($processo);
        }

        $isSRP = $processo->detalhe?->tipo_srp === 'sim';
        $hasInversaoFase = $processo->detalhe && $processo->detalhe->inversao_fase === 'sim';
        $isConcorrencia = $processo->modalidade === ModalidadeEnum::CONCORRENCIA;

        if ($isConcorrencia && $hasInversaoFase) {
            return $this->getOrdemConcorrenciaComInversao($isSRP);
        }

        if ($isSRP) {
            return $this->getOrdemSRP($hasInversaoFase);
        }

        return $this->getOrdemPregao($hasInversaoFase);
    }

    public function adicionarCamposExtras(array &$documentos, Processo $processo, array $campos): void
    {
        if ($processo->modalidade === ModalidadeEnum::CONCORRENCIA) {
            $this->adicionarCampos($documentos, 'termo_adjudicacao', $campos);
        }

        if ($processo->modalidade === ModalidadeEnum::DISPENSA
            && $processo->tipo_procedimento === TipoProcedimentoEnum::OBRA) {
            $this->adicionarCampos($documentos, 'ata', $campos);
        }
    }

    public function getOrdemDocumentos(Processo $processo): array
    {
        $documentosOrganizados = $this->getDocumentosOrganizados($processo);
        return array_keys($documentosOrganizados);
    }

    /**
     * Filtra os documentos retornados por getDocumentosOrganizados em dois grupos:
     *  - 'processo': documentos únicos do processo (atos_sessao, proposta etc.).
     *  - 'homologacao': documentos gerados por homologação (recursos, adjudicação, parecer,
     *    homologação, ata RP, publicações).
     */
    public function separarDocumentosPorEscopo(Processo $processo): array
    {
        $documentos = $this->getDocumentosOrganizados($processo);
        $tiposPorHomologacao = HomologacaoService::TIPOS_POR_HOMOLOGACAO;

        $processoDocs = [];
        $homologacaoDocs = [];

        foreach ($documentos as $tipo => $config) {
            if (in_array($tipo, $tiposPorHomologacao, true)) {
                $homologacaoDocs[$tipo] = $config;
            } else {
                $processoDocs[$tipo] = $config;
            }
        }

        return [
            'processo' => $processoDocs,
            'homologacao' => $homologacaoDocs,
        ];
    }

    public function getMapeamentoAnexos(): array
    {
        return $this->mapeamentoAnexos;
    }

    /**
     * Expande a entrada estática `ata_registro_precos` em N entradas — uma por vencedor
     * da homologação. Cada entrada nova é chaveada como `ata_registro_precos_v{vencedor_id}`
     * e tem o título "ATA DE REGISTRO DE PREÇOS — {razão social}".
     *
     * Quando a homologação não tem vencedores vinculados, a entrada original é mantida.
     *
     * @param array $documentosHomologacao  mapa tipo => config (já filtrado para escopo de homologação)
     * @param \App\Models\Homologacao $homologacao
     * @return array
     */
    public function expandirAtaPorVencedor(array $documentosHomologacao, \App\Models\Homologacao $homologacao): array
    {
        if (!isset($documentosHomologacao['ata_registro_precos'])) {
            return $documentosHomologacao;
        }

        // Vencedores únicos com base nos lotes vinculados à homologação.
        $homologacao->loadMissing(['lotes.vencedor']);
        $vencedores = $homologacao->lotes
            ->map(fn($lote) => $lote->vencedor)
            ->filter()
            ->unique('id')
            ->values();

        if ($vencedores->isEmpty()) {
            return $documentosHomologacao;
        }

        $configBase = $documentosHomologacao['ata_registro_precos'];
        $expandido = [];

        foreach ($documentosHomologacao as $tipo => $config) {
            if ($tipo !== 'ata_registro_precos') {
                $expandido[$tipo] = $config;
                continue;
            }

            // Substitui a entrada única por uma por vencedor.
            foreach ($vencedores as $vencedor) {
                $key = "ata_registro_precos_v{$vencedor->id}";
                $expandido[$key] = array_merge($configBase, [
                    'titulo' => 'ATA DE REGISTRO DE PREÇOS — ' . ($vencedor->razao_social ?? 'Vencedor #' . $vencedor->id),
                    'vencedor_id' => $vencedor->id,
                    'tipo_base' => 'ata_registro_precos',
                ]);
            }
        }

        return $expandido;
    }

    public function getDocumentoConfig(string $tipo): ?array
    {
        return $this->documentosBase[$tipo] ?? null;
    }

    private function getDocumentosDispensa(Processo $processo): array
    {
        $tipoProcedimento = $processo->tipo_procedimento ?? null;

        if ($tipoProcedimento == TipoProcedimentoEnum::OBRA) {
            return $this->getOrdemDispensaObra();
        }

        return $this->getOrdemDispensaComprasServicos();
    }

    private function getOrdemDispensaComprasServicos(): array
    {
        return [
            'atos_sessao' => [
                'titulo' => 'ATOS DA SESSÃO',
                'cor' => 'bg-red-500',
                'campos' => [
                    'anexo_atos_sessao',
                    'orgao_responsavel',
                    'cargo_responsavel',
                    'cnpj',
                    'endereco',
                    'responsavel',
                    'cpf_responsavel',
                    'merenda_escolar',
                    'veiculos',
                    'empresas_participantes',
                ],
                'requer_assinatura' => true,
            ],
            'documento_habilitacao' => [
                'titulo' => 'DOCUMENTOS DE HABILITAÇÃO',
                'cor' => 'bg-green-500',
                'campos' => ['anexo_habilitacao'],
                'requer_assinatura' => false,
            ],
            'proposta' => [
                'titulo' => 'PROPOSTAS',
                'cor' => 'bg-blue-500',
                'campos' => ['anexo_proposta'],
                'requer_assinatura' => false,
            ],
            'proposta_vencedora' => [
                'titulo' => 'PROPOSTA VENCEDORA',
                'cor' => 'bg-purple-500',
                'campos' => ['anexo_proposta_readequada'],
                'requer_assinatura' => false,
            ],
            'recurso_contratacoes_decisao_recursos' => [
                'titulo' => 'RECURSOS, CONTRARRAZÕES E DECISÃO DOS RECURSOS',
                'cor' => 'bg-green-500',
                'campos' => ['anexo_recurso_contratacoes'],
                'requer_assinatura' => false,
            ],
            'parecer_juridico' => [
                'titulo' => 'PARECER JURÍDICO',
                'cor' => 'bg-orange-500',
                'campos' => [],
                'requer_assinatura' => true,
            ],
            'atos_autorizacao_dispensa' => [
                'titulo' => 'ATO DE AUTORIZAÇÃO DE DISPENSA DE LICITAÇÃO',
                'cor' => 'bg-yellow-500',
                'campos' => [],
                'requer_assinatura' => true,
            ],
            'publicacoes' => [
                'titulo' => 'PUBLICAÇÕES',
                'cor' => 'bg-indigo-500',
                'campos' => ['anexo_publicacoes'],
                'requer_assinatura' => false,
            ]
        ];
    }

    private function getOrdemDispensaObra(): array
    {
        return [
            'ata' => [
                'titulo' => 'ATA',
                'cor' => 'bg-red-500',
                'campos' => [
                    'anexo_atos_sessao',
                    'orgao_responsavel',
                    'cargo_responsavel',
                    'cnpj',
                    'endereco',
                    'responsavel',
                    'cpf_responsavel',
                    'razao_social',
                    'cnpj_empresa_vencedora',
                    'endereco_empresa_vencedora',
                    'representante_legal_empresa',
                    'cpf_representante',
                    'valor_total',
                    'merenda_escolar',
                    'veiculos',
                    'empresas_participantes',
                ],
                'requer_assinatura' => true,
            ],
            'documento_habilitacao' => [
                'titulo' => 'DOCUMENTOS DE HABILITAÇÃO',
                'cor' => 'bg-green-500',
                'campos' => ['anexo_habilitacao'],
                'requer_assinatura' => false,
            ],
            'proposta' => [
                'titulo' => 'PROPOSTAS',
                'cor' => 'bg-blue-500',
                'campos' => ['anexo_proposta'],
                'requer_assinatura' => false,
            ],
            'proposta_vencedora' => [
                'titulo' => 'PROPOSTAS DA EMPRESA VENCEDORA',
                'cor' => 'bg-purple-500',
                'campos' => ['anexo_proposta_readequada'],
                'requer_assinatura' => false,
            ],
            'recurso_contratacoes_decisao_recursos' => [
                'titulo' => 'RECURSOS, CONTRARRAZÕES E DECISÃO DOS RECURSOS',
                'cor' => 'bg-green-500',
                'campos' => ['anexo_recurso_contratacoes'],
                'requer_assinatura' => false,
            ],
            'parecer_juridico' => [
                'titulo' => 'PARECER JURÍDICO',
                'cor' => 'bg-orange-500',
                'campos' => [],
                'requer_assinatura' => true,
            ],
            'atos_autorizacao_dispensa' => [
                'titulo' => 'ATO DE AUTORIZAÇÃO DE DISPENSA DE LICITAÇÃO',
                'cor' => 'bg-yellow-500',
                'campos' => [],
                'requer_assinatura' => true,
            ],
            'publicacoes' => [
                'titulo' => 'PUBLICAÇÕES',
                'cor' => 'bg-indigo-500',
                'campos' => ['anexo_publicacoes'],
                'requer_assinatura' => false,
            ]
        ];
    }

    private function getOrdemPregao(bool $hasInversaoFase = false): array
    {
        $documentos = $this->documentosBase;

        if ($hasInversaoFase) {
            $ordem = [
                'atos_sessao',
                'documento_habilitacao_empresa_vencedora',
                'proposta',
                'proposta_readequada',
                'recurso_contratacoes_decisao_recursos',
                'termo_adjudicacao',
                'parecer_controle_interno',
                'termo_homologacao',
                'publicacoes'
            ];
        } else {
            $ordem = [
                'atos_sessao',
                'proposta',
                'proposta_readequada',
                'documento_habilitacao_empresa_vencedora',
                'recurso_contratacoes_decisao_recursos',
                'termo_adjudicacao',
                'parecer_controle_interno',
                'termo_homologacao',
                'publicacoes'
            ];
        }

        return $this->ordenarDocumentos($documentos, $ordem);
    }

    private function getOrdemSRP(bool $hasInversaoFase = false): array
    {
        $documentos = $this->documentosBase;

        $documentos['ata_registro_precos'] = [
            'titulo' => 'ATA DE REGISTRO DE PREÇOS',
            'cor' => 'bg-teal-500',
            'campos' => ['numero_ata_registro_precos', 'cargo_controle_interno'],
            'requer_assinatura' => true,
        ];

        if ($hasInversaoFase) {
            $ordem = [
                'atos_sessao',
                'documento_habilitacao_empresa_vencedora',
                'proposta',
                'proposta_readequada',
                'recurso_contratacoes_decisao_recursos',
                'termo_adjudicacao',
                'parecer_controle_interno',
                'termo_homologacao',
                'ata_registro_precos',
                'publicacoes'
            ];
        } else {
            $ordem = [
                'atos_sessao',
                'proposta',
                'proposta_readequada',
                'documento_habilitacao_empresa_vencedora',
                'recurso_contratacoes_decisao_recursos',
                'termo_adjudicacao',
                'parecer_controle_interno',
                'termo_homologacao',
                'ata_registro_precos',
                'publicacoes'
            ];
        }

        return $this->ordenarDocumentos($documentos, $ordem);
    }

    private function getOrdemConcorrenciaComInversao(bool $isSRP = false): array
    {
        $documentos = $this->documentosBase;

        $documentos['documento_habilitacao_empresa_vencedora']['titulo'] = 'DOCUMENTOS DE HABILITAÇÃO';

        if ($isSRP) {
            $ordem = [
                'atos_sessao',
                'documento_habilitacao_empresa_vencedora',
                'proposta',
                'proposta_readequada',
                'recurso_contratacoes_decisao_recursos',
                'termo_adjudicacao',
                'parecer_controle_interno',
                'termo_homologacao',
                'ata_registro_precos',
                'publicacoes'
            ];

            $documentos['ata_registro_precos'] = [
                'titulo' => 'ATA DE REGISTRO DE PREÇOS',
                'cor' => 'bg-teal-500',
                'campos' => ['numero_ata_registro_precos', 'cargo_controle_interno'],
                'requer_assinatura' => true,
            ];
        } else {
            $ordem = [
                'atos_sessao',
                'documento_habilitacao_empresa_vencedora',
                'proposta',
                'proposta_readequada',
                'recurso_contratacoes_decisao_recursos',
                'termo_adjudicacao',
                'parecer_controle_interno',
                'termo_homologacao',
                'publicacoes'
            ];
        }

        return $this->ordenarDocumentos($documentos, $ordem);
    }

    private function adicionarCampos(array &$documentos, string $documento, array $campos): void
    {
        if (!isset($documentos[$documento])) {
            return;
        }

        $documentos[$documento]['campos'] = array_merge(
            $documentos[$documento]['campos'],
            $campos
        );
    }

    private function ordenarDocumentos(array $documentos, array $ordem): array
    {
        $documentosOrdenados = [];
        foreach ($ordem as $key) {
            if (isset($documentos[$key])) {
                $documentosOrdenados[$key] = $documentos[$key];
            }
        }

        return $documentosOrdenados;
    }
}