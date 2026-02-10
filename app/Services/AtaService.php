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
            'vencedores' // Carregar vencedores para busca por nome
        ]);

        // Filtro por prefeitura
        if ($prefeituraId) {
            $query->where('prefeitura_id', $prefeituraId);
        }

        // Filtro por processo específico
        if ($processoId) {
            $query->where('id', $processoId);
        }

        // Filtro por pesquisa livre
        if ($search) {
            \Log::info('Search term:', ['search' => $search]);

            $query->where(function($q) use ($search) {
                // Busca por parte do objeto
                $q->where('objeto', 'like', "%{$search}%")
                    // Busca por número do processo
                    ->orWhere('numero_processo', 'like', "%{$search}%")
                    // Busca por número do procedimento
                    ->orWhere('numero_procedimento', 'like', "%{$search}%")
                    // Busca por prefeitura - DEPURAÇÃO
                    ->orWhereHas('prefeitura', function($q2) use ($search) {
                        \Log::info('Searching prefeitura for:', ['search' => $search]);
                        $q2->where('nome', 'like', "%{$search}%")
                            ->orWhere('cidade', 'like', "%{$search}%");
                    })
                    // Busca por nome do contratado (vencedor) - CORRIGIDO AQUI
                    ->orWhereHas('vencedores', function($q3) use ($search) {
                        // REMOVA 'nome' e use apenas 'razao_social'
                        $q3->where('razao_social', 'like', "%{$search}%");
                    });
            });
        }

        // Ordenar do mais recente para o mais antigo
        $query->orderBy('created_at', 'desc');

        return $query->get();
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

        $totalContratacoes = LoteContratado::where('processo_id', $processo->id)->count();
        $valorTotalContratado = LoteContratado::where('processo_id', $processo->id)->sum('valor_total');
        $totalContratos = $documentos->count();

        return compact(
            'processo',
            'dadosAtas',
            'contratacoes',
            'documentos',
            'dadosAta',
            'contrato',
            'totalContratacoes',
            'valorTotalContratado',
            'totalContratos'
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
            $quantidadeContratada = $lote->contratados
                ->where('processo_id', $processo->id)
                ->sum('quantidade_contratada');

            $estoque = EstoqueLote::where('lote_id', $lote->id)
                ->where('processo_id', $processo->id)
                ->first();

            $quantidadeDisponivel = $estoque ? (float) $estoque->quantidade_disponivel : (float) $lote->quantidade;
            $quantidadeUtilizada = $estoque ? (float) $estoque->quantidade_utilizada : 0;

            if ($quantidadeContratada == 0) {
                $quantidadeContratada = (float) $lote->quantidade;
            }

            $dados[] = [
                'vencedor' => $lote->vencedor?->razao_social ?? 'Não definido',
                'id' => $lote->id,
                'item' => $lote->item,
                'descricao' => $lote->descricao,
                'unidade' => $lote->unidade,
                'quantidade_total' => (float) $lote->quantidade,
                'quantidade_contratada' => $quantidadeContratada,
                'quantidade_disponivel' => $quantidadeDisponivel,
                'quantidade_utilizada' => $quantidadeUtilizada,
                'valor_unitario' => (float) $lote->vl_unit,
                'valor_total_contratado' => $quantidadeContratada * (float) $lote->vl_unit,
                'valor_total_disponivel' => $quantidadeDisponivel * (float) $lote->vl_unit,
                'percentual_utilizado' => (float) $lote->quantidade > 0
                    ? round(($quantidadeUtilizada / (float) $lote->quantidade) * 100, 2)
                    : 0,
                'status' => $quantidadeDisponivel > 0 ? 'PARCIAL' : 'ESGOTADO',
                'tem_contratacao' => $quantidadeUtilizada > 0,
            ];
        }

        usort($dados, function($a, $b) {
            if ($a['vencedor'] === $b['vencedor']) {
                return strcmp($a['item'], $b['item']);
            }
            return strcmp($a['vencedor'], $b['vencedor']);
        });

        return $dados;
    }
}
