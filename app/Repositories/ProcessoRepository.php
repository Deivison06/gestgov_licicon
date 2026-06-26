<?php

namespace App\Repositories;

use App\Models\Contrato;
use App\Models\Documento;
use App\Models\Processo;

/**
 * Acesso a dados do domínio Processo. Concentra as consultas Eloquent que viviam
 * no ProcessoService (numeração, upsert de data de documento e de contrato),
 * deixando o service focado na regra (parsing de planilha, arquivos, duplicação).
 */
class ProcessoRepository extends AbstractRepository
{
    public function __construct(Processo $model)
    {
        parent::__construct($model);
    }

    /**
     * Último processo da prefeitura no ano que segue o padrão NNN/AAAA
     * (ordenado pelo maior número sequencial). Null se não houver.
     */
    public function ultimoProcessoNumerado(int $prefeituraId, int $ano): ?Processo
    {
        return Processo::where('prefeitura_id', $prefeituraId)
            ->where('numero_processo', 'like', "%/{$ano}")
            ->orderByRaw('CAST(SUBSTRING_INDEX(numero_processo, "/", 1) AS UNSIGNED) DESC')
            ->first();
    }

    /**
     * Upsert da data selecionada de um documento (nível processo).
     */
    public function upsertDataDocumento(int $processoId, string $tipoDocumento, $dataSelecionada): void
    {
        Documento::updateOrCreate(
            ['processo_id' => $processoId, 'tipo_documento' => $tipoDocumento],
            ['data_selecionada' => $dataSelecionada]
        );
    }

    /**
     * Upsert do Contrato vinculado ao processo (usado na Inexigibilidade).
     */
    public function upsertContrato(int $processoId, array $dados): void
    {
        Contrato::updateOrCreate(['processo_id' => $processoId], $dados);
    }
}
