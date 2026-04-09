<?php

namespace App\Services;

use App\Models\Processo;
use App\Models\Prefeitura;
use App\Models\Documento;
use App\Models\LoteContratado;
use App\Models\EstoqueLote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AtaService
{
    public function getProcessosFiltrados($prefeituraId = null, $processoId = null, $search = null)
    {
        $query = Processo::with([
            'prefeitura',
            'lotesContratados',
            'lotes',
            'user',
            'vencedores'
        ]);

        // 1. FILTROS GLOBAIS (Sempre aplicados com AND)
        // Use ->value se for um Backed Enum (PHP 8.1+)
        $query->where('modalidade', \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO->value ?? \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO);

        $query->whereHas('detalhe', function($q) {
            $q->where('tipo_srp', 'sim');
        });

        $query->has('vencedores');

        // 2. FILTROS ESPECÍFICOS
        if ($prefeituraId) {
            $query->where('prefeitura_id', $prefeituraId);
        }

        if ($processoId) {
            $query->where('id', $processoId);
        }

        // 3. FILTRO DE PESQUISA (Agrupado para não quebrar os filtros acima)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('objeto', 'like', "%{$search}%")
                ->orWhere('numero_processo', 'like', "%{$search}%")
                ->orWhere('numero_procedimento', 'like', "%{$search}%")
                ->orWhereHas('prefeitura', function($q2) use ($search) {
                    $q2->where('nome', 'like', "%{$search}%")
                        ->orWhere('cidade', 'like', "%{$search}%");
                })
                ->orWhereHas('vencedores', function($q3) use ($search) {
                    $q3->where('razao_social', 'like', "%{$search}%");
                });
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
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

        $documentos = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->orderBy('gerado_em', 'desc')
            ->get();

        $dadosAta = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->first();

        $contrato = \App\Models\Contrato::where('processo_id', $processo->id)->first();

        $totalContratos = $documentos->count();

        // Valores para os cards de resumo
        $valorLicitado = (float) $processo->lotes->sum(function($lote) {
            return (float) $lote->quantidade * (float) $lote->vl_unit;
        });

        $valorRealContratado = (float) LoteContratado::where('processo_id', $processo->id)
            ->where('status', 'CONTRATADO')
            ->sum('valor_total');
            
        $valorPendenteContratacao = (float) LoteContratado::where('processo_id', $processo->id)
            ->where('status', 'PENDENTE')
            ->sum('valor_total');

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
        $query = Processo::query()
            ->with([
                'prefeitura',
                'lotesContratados' => function($query) {
                    $query->whereIn('status', ['PENDENTE', 'CONTRATADO']);
                }
            ]);

        // FILTRO OBRIGATÓRIO: Apenas Pregão Eletrônico
        $query->where('modalidade', \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO);

        // FILTRO OBRIGATÓRIO: Apenas do tipo SRP
        $query->whereHas('detalhe', function($q) {
            $q->where('tipo_srp', 'sim');
        });

        if ($prefeituraId) {
            $query->where('prefeitura_id', $prefeituraId);
        }

        return $query->orderBy('created_at', 'desc')->get();
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
        return LoteContratado::where('processo_id', $processo->id)
            ->where('status', 'PENDENTE')
            ->with(['lote', 'vencedor'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('vencedor_id');
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

            $estoque = EstoqueLote::where('lote_id', $lote->id)
                ->where('processo_id', $processo->id)
                ->first();

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
