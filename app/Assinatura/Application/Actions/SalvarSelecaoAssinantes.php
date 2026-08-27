<?php

namespace App\Assinatura\Application\Actions;

use App\Models\DocumentoSelecaoAssinantes;
use App\Models\Processo;

/**
 * Persiste (upsert) a seleção de assinantes de um documento específico.
 * Extraído do SelecaoAssinantesService — responsabilidade única de escrita da seleção.
 */
class SalvarSelecaoAssinantes
{
    /**
     * @param array $dados ['modo','prazo_dias','assinantes' => [...]]
     */
    public function executar(
        Processo $processo,
        string $tipoDocumento,
        ?int $homologacaoId,
        ?int $vencedorId,
        ?int $incidenteId,
        array $dados,
        int $atualizadoPorUserId
    ): DocumentoSelecaoAssinantes {
        $assinantes = is_array($dados['assinantes'] ?? null) ? $dados['assinantes'] : [];

        return DocumentoSelecaoAssinantes::updateOrCreate(
            [
                'processo_id'    => $processo->id,
                'tipo_documento' => $tipoDocumento,
                'homologacao_id' => $homologacaoId,
                'vencedor_id'    => $vencedorId,
                'incidente_id'   => $incidenteId,
            ],
            [
                'modo'                   => in_array($dados['modo'] ?? null, ['paralelo', 'sequencial'], true)
                    ? $dados['modo']
                    : 'paralelo',
                'prazo_dias'             => max(1, (int) ($dados['prazo_dias'] ?? 7)),
                'assinantes'             => $assinantes,
                'atualizado_por_user_id' => $atualizadoPorUserId,
            ]
        );
    }
}
