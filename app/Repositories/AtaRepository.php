<?php

namespace App\Repositories;

use App\Enums\ModalidadeEnum;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\EstoqueLote;
use App\Models\LoteContratado;
use App\Models\Processo;

/**
 * Acesso a dados do domínio Ata (Registro de Preço). Concentra as consultas
 * Eloquent que viviam no AtaService (processos SRP, contratações, documentos de
 * contrato, estoque), mantendo o service focado no cálculo/montagem dos dados.
 */
class AtaRepository extends AbstractRepository
{
    public function __construct(Processo $model)
    {
        parent::__construct($model);
    }

    /**
     * Processos de Pregão Eletrônico SRP com vencedores, aplicando filtros de
     * prefeitura/processo/busca.
     */
    public function processosFiltrados($prefeituraId = null, $processoId = null, $search = null)
    {
        $query = Processo::with([
            'prefeitura',
            'lotesContratados',
            'lotes',
            'user',
            'vencedores',
        ]);

        $query->where('modalidade', ModalidadeEnum::PREGAO_ELETRONICO->value ?? ModalidadeEnum::PREGAO_ELETRONICO);

        $query->whereHas('detalhe', function ($q) {
            $q->where('tipo_srp', 'sim');
        });

        $query->has('vencedores');

        if ($prefeituraId) {
            $query->where('prefeitura_id', $prefeituraId);
        }

        if ($processoId) {
            $query->where('id', $processoId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('objeto', 'like', "%{$search}%")
                ->orWhere('numero_processo', 'like', "%{$search}%")
                ->orWhere('numero_procedimento', 'like', "%{$search}%")
                ->orWhereHas('prefeitura', function ($q2) use ($search) {
                    $q2->where('nome', 'like', "%{$search}%")
                        ->orWhere('cidade', 'like', "%{$search}%");
                })
                ->orWhereHas('vencedores', function ($q3) use ($search) {
                    $q3->where('razao_social', 'like', "%{$search}%");
                });
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Processos SRP (Pregão Eletrônico) para o dashboard, com lotes contratados.
     */
    public function processosParaDashboard(?string $prefeituraId = null)
    {
        $query = Processo::query()
            ->with([
                'prefeitura',
                'lotesContratados' => function ($query) {
                    $query->whereIn('status', ['PENDENTE', 'CONTRATADO']);
                },
            ]);

        $query->where('modalidade', ModalidadeEnum::PREGAO_ELETRONICO);

        $query->whereHas('detalhe', function ($q) {
            $q->where('tipo_srp', 'sim');
        });

        if ($prefeituraId) {
            $query->where('prefeitura_id', $prefeituraId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Contratações PENDENTES do processo, agrupadas por vencedor.
     */
    public function contratacoesPendentes(Processo $processo)
    {
        return LoteContratado::where('processo_id', $processo->id)
            ->where('status', 'PENDENTE')
            ->with(['lote', 'vencedor'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('vencedor_id');
    }

    /**
     * Documentos do tipo `contrato` do processo (mais recentes primeiro).
     */
    public function documentosContrato(Processo $processo)
    {
        return Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->orderBy('gerado_em', 'desc')
            ->get();
    }

    /**
     * Primeiro documento do tipo `contrato` do processo (ou null).
     */
    public function primeiroDocumentoContrato(Processo $processo): ?Documento
    {
        return Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->first();
    }

    /**
     * Contrato vinculado ao processo (ou null).
     */
    public function contratoDoProcesso(Processo $processo): ?Contrato
    {
        return Contrato::where('processo_id', $processo->id)->first();
    }

    /**
     * Soma de `valor_total` dos lotes contratados do processo por status.
     */
    public function somaLotesContratadosPorStatus(int $processoId, string $status): float
    {
        return (float) LoteContratado::where('processo_id', $processoId)
            ->where('status', $status)
            ->sum('valor_total');
    }

    /**
     * Registro de estoque de um lote no processo (ou null).
     */
    public function estoqueLote(int $loteId, int $processoId): ?EstoqueLote
    {
        return EstoqueLote::where('lote_id', $loteId)
            ->where('processo_id', $processoId)
            ->first();
    }
}
