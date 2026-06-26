<?php

namespace App\Services;

use App\Models\Processo;
use App\Repositories\AtaRepository;

class AtaService extends AbstractService
{
    public function __construct(
        private AtaRepository $repo
    ) {
    }

    public function getProcessosFiltrados($prefeituraId = null, $processoId = null, $search = null)
    {
        return $this->repo->processosFiltrados($prefeituraId, $processoId, $search);
    }


    public function prepararDadosParaExibicao(Processo $processo): array
    {
        $processo->load([
            'prefeitura',
            'prefeitura.unidades',
            'lotes.vencedor',
            'lotes.contratados' => function ($query) use ($processo) {
                $query->where('processo_id', $processo->id);
            },
            'vencedores',
            'finalizacao'
        ]);

        $dadosAtas = $this->prepararDadosAtaTodosLotes($processo);
        $contratacoes = $this->carregarContratacoesPendentes($processo);

        $documentos = $this->repo->documentosContrato($processo);

        $dadosAta = $this->repo->primeiroDocumentoContrato($processo);

        $contrato = $this->repo->contratoDoProcesso($processo);

        $totalContratos = $documentos->count();

        // Valores para os cards de resumo
        $valorLicitado = (float) $processo->lotes->sum(function($lote) {
            return (float) $lote->quantidade * (float) $lote->vl_unit;
        });

        $valorRealContratado = $this->repo->somaLotesContratadosPorStatus($processo->id, 'CONTRATADO');

        $valorPendenteContratacao = $this->repo->somaLotesContratadosPorStatus($processo->id, 'PENDENTE');

        $saldoAContratar = $valorLicitado - $valorRealContratado;

        $unidadesData = $processo->prefeitura->unidades->map(function($u) {
            $dataPortaria = $u->data_portaria;
            if ($dataPortaria && !($dataPortaria instanceof \Carbon\Carbon)) {
                try {
                    $dataPortaria = \Carbon\Carbon::parse($dataPortaria);
                } catch (\Exception $e) {
                    $dataPortaria = null;
                }
            }

            return [
                'id' => $u->id,
                'nome' => $u->nome,
                'servidor_responsavel' => $u->servidor_responsavel,
                'cargo_responsavel' => $u->cargo_responsavel,
                'numero_portaria' => $u->numero_portaria,
                'data_portaria' => $dataPortaria ? $dataPortaria->format('Y-m-d') : null,
            ];
        })->toArray();

        $itensSaldo = collect($dadosAtas)->filter(fn($i) => $i['quantidade_disponivel'] > 0)->count();
        $itensEsgotados = collect($dadosAtas)->filter(fn($i) => $i['quantidade_disponivel'] <= 0)->count();

        return compact(
            'processo',
            'dadosAtas',
            'contratacoes',
            'documentos',
            'dadosAta',
            'contrato',
            'valorLicitado',
            'valorRealContratado',
            'valorPendenteContratacao',
            'saldoAContratar',
            'totalContratos',
            'unidadesData',
            'itensSaldo',
            'itensEsgotados'
        );
    }

    public function getProcessosParaDashboard(?string $prefeituraId = null)
    {
        return $this->repo->processosParaDashboard($prefeituraId);
    }

    public function calcularEstatisticas($processos): array
    {
        return [
            'total_processos' => $processos->count(),
            'total_contratacoes' => $processos->sum(function($processo) {
                return $processo->lotesContratados->count();
            }),
            'total_valor_contratado' => $processos->sum(function($processo) {
                return $processo->lotesContratados->sum('valor_total');
            }),
            'total_quantidade_contratada' => $processos->sum(function($processo) {
                return $processo->lotesContratados->sum('quantidade_contratada');
            }),
            'total_lotes' => $processos->sum(function($processo) {
                return $processo->lotes->count();
            }),
        ];
    }

    private function carregarContratacoesPendentes(Processo $processo)
    {
        return $this->repo->contratacoesPendentes($processo);
    }

    private function prepararDadosAtaTodosLotes(Processo $processo): array
    {
        $dados = [];

        foreach ($processo->lotes as $lote) {
            // Soma contratações já consolidadas (CONTRATADO)
            $quantidadeAdquirida = $lote->contratados
                ->where('processo_id', $processo->id)
                ->where('status', 'CONTRATADO')
                ->sum('quantidade_contratada');

            // Soma contratações em planejamento (PENDENTE)
            $quantidadePendente = $lote->contratados
                ->where('processo_id', $processo->id)
                ->where('status', 'PENDENTE')
                ->sum('quantidade_contratada');

            $estoque = $this->repo->estoqueLote($lote->id, $processo->id);

            // O Licitado é fixo do Lote
            $quantidadeLicitada = (float) $lote->quantidade;
            
            // O "Adquirido" visual na aba de Itens geralmente inclui o que já foi contratado de fato
            $quantidadeUtilizada = $quantidadeAdquirida;
            
            // O "Saldo" é o que sobra do Licitado subtraindo o que já foi Adquirido E o que está Pendente
            $quantidadeDisponivel = $quantidadeLicitada - ($quantidadeAdquirida + $quantidadePendente);
            
            // Garantir que não fique negativo por erros de arredondamento
            $quantidadeDisponivel = max(0, $quantidadeDisponivel);

            $dados[] = [
                'vencedor' => $lote->vencedor?->razao_social ?? 'Não definido',
                'id' => $lote->id,
                'lote_num' => $lote->lote,
                'lote_nome' => $lote->lote_nome,
                'item' => $lote->item,
                'descricao' => $lote->descricao,
                'unidade' => $lote->unidade,
                'quantidade_total' => $quantidadeLicitada,
                'quantidade_contratada' => $quantidadeAdquirida + $quantidadePendente,
                'quantidade_disponivel' => $quantidadeDisponivel,
                'quantidade_utilizada' => $quantidadeUtilizada,
                'valor_unitario' => (float) $lote->vl_unit,
                'valor_total_item' => $quantidadeLicitada * (float) $lote->vl_unit,
                'valor_total_contratado' => ($quantidadeAdquirida + $quantidadePendente) * (float) $lote->vl_unit,
                'valor_total_disponivel' => $quantidadeDisponivel * (float) $lote->vl_unit,
                'percentual_utilizado' => $quantidadeLicitada > 0
                    ? round(($quantidadeUtilizada / $quantidadeLicitada) * 100, 2)
                    : 0,
                'status' => $quantidadeDisponivel > 0 ? 'PARCIAL' : 'ESGOTADO',
                'tem_contratacao' => ($quantidadeAdquirida + $quantidadePendente) > 0,
            ];
        }

        usort($dados, function($a, $b) {
            if ($a['lote_num'] === $b['lote_num']) {
                if ($a['vencedor'] === $b['vencedor']) {
                    // Ordenação natural para considerar números corretamente (ex: 2 antes de 10)
                    return strnatcmp($a['item'], $b['item']);
                }
                return strcmp($a['vencedor'], $b['vencedor']);
            }
            return (int) $a['lote_num'] - (int) $b['lote_num'];
        });

        return $dados;
    }
}
