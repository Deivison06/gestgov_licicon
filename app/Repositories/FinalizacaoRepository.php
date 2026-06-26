<?php

namespace App\Repositories;

use App\Models\AtaRegistroPreco;
use App\Models\Documento;
use App\Models\Finalizacao;
use App\Models\Homologacao;
use App\Models\Processo;
use App\Models\Vencedor;

/**
 * Acesso a dados do fluxo de Finalização. Concentra as consultas Eloquent que
 * antes viviam espalhadas no FinalizacaoService (Finalizacao, Homologacao,
 * Vencedor, AtaRegistroPreco, Documento), mantendo o service focado na regra.
 */
class FinalizacaoRepository extends AbstractRepository
{
    public function __construct(Finalizacao $model)
    {
        parent::__construct($model);
    }

    /**
     * Finalização existente do processo ou uma nova instância (NÃO persiste).
     */
    public function finalizacaoDoProcesso(Processo $processo): Finalizacao
    {
        return $processo->finalizacao ?? new Finalizacao();
    }

    /**
     * Vencedor do processo pelo id (ou null).
     */
    public function acharVencedor(int $processoId, $vencedorId): ?Vencedor
    {
        return Vencedor::where('processo_id', $processoId)
            ->where('id', $vencedorId)
            ->first();
    }

    /**
     * Homologação do processo pelo id (ou null).
     */
    public function acharHomologacao(int $processoId, $homologacaoId): ?Homologacao
    {
        return Homologacao::where('processo_id', $processoId)
            ->where('id', $homologacaoId)
            ->first();
    }

    /**
     * Ata (registro de preço) por (homologação, vencedor): existente ou nova,
     * já com o processo_id setado. NÃO persiste — o service preenche e salva.
     */
    public function ataParaUpsert(int $processoId, int $homologacaoId, int $vencedorId): AtaRegistroPreco
    {
        $ata = AtaRegistroPreco::firstOrNew([
            'homologacao_id' => $homologacaoId,
            'vencedor_id' => $vencedorId,
        ]);
        $ata->processo_id = $processoId;

        return $ata;
    }

    /**
     * Upsert da data selecionada de um documento (nível processo quando
     * $homologacaoId é null, ou por homologação).
     */
    public function upsertDataDocumento(int $processoId, ?int $homologacaoId, string $tipoDocumento, $dataSelecionada): void
    {
        Documento::updateOrCreate(
            [
                'processo_id' => $processoId,
                'tipo_documento' => $tipoDocumento,
                'homologacao_id' => $homologacaoId,
            ],
            ['data_selecionada' => $dataSelecionada]
        );
    }
}
