<?php

namespace App\Assinatura\Application\Queries;

use App\Models\Documento;
use App\Models\DocumentoSelecaoAssinantes;
use App\Models\DocumentoVersao;
use App\Models\Processo;
use App\Models\SolicitacaoAssinatura;
use Illuminate\Support\Collection;

/**
 * Read model do estado de assinatura de um documento: seleção salva, rodada ativa,
 * status agregado e caminho do PDF assinado. Sem efeitos colaterais.
 *
 * Extraído do antigo SelecaoAssinantesService (god service) para separar leitura
 * de escrita/orquestração.
 */
class StatusDocumentoAssinatura
{
    /**
     * Recupera a seleção de assinantes salva, se existir.
     */
    public function obterSelecao(
        Processo $processo,
        string $tipoDocumento,
        ?int $homologacaoId = null,
        ?int $vencedorId = null
    ): ?DocumentoSelecaoAssinantes {
        return DocumentoSelecaoAssinantes::query()
            ->where('processo_id', $processo->id)
            ->where('tipo_documento', $tipoDocumento)
            ->where('homologacao_id', $homologacaoId)
            ->where('vencedor_id', $vencedorId)
            ->first();
    }

    /**
     * Localiza o Documento (PDF rascunho) gerado para o tipo/homologação.
     */
    public function localizarDocumentoGerado(
        Processo $processo,
        string $tipoDocumento,
        ?int $homologacaoId
    ): ?Documento {
        return Documento::query()
            ->where('processo_id', $processo->id)
            ->where('tipo_documento', $tipoDocumento)
            ->when(
                $homologacaoId,
                fn ($q) => $q->where('homologacao_id', $homologacaoId),
                fn ($q) => $q->whereNull('homologacao_id')
            )
            ->latest('id')
            ->first();
    }

    /**
     * Lista as solicitações da rodada ATIVA do documento (se houver).
     */
    public function rodadaAtiva(Documento $documento): Collection
    {
        $versaoAtiva = $this->ultimaVersaoComRodadaAtiva($documento);
        if (!$versaoAtiva) {
            return collect();
        }

        return SolicitacaoAssinatura::query()
            ->where('documento_versao_id', $versaoAtiva->id)
            ->with('assinante')
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();
    }

    public function existeRodadaAtiva(Documento $documento): bool
    {
        return $this->ultimaVersaoComRodadaAtiva($documento) !== null;
    }

    public function versaoConsolidada(Documento $documento): ?DocumentoVersao
    {
        return DocumentoVersao::query()
            ->where('documentavel_type', Documento::class)
            ->where('documentavel_id', $documento->id)
            ->whereNotNull('assinaturas_consolidadas_em')
            ->whereNotNull('caminho_pdf_assinado')
            ->latest('id')
            ->first();
    }

    /**
     * Status agregado da rodada de assinatura de um documento.
     *
     * @return array{
     *   estado: string, pendentes: int, assinadas: int, total: int,
     *   versao_id: ?int, pode_solicitar: bool, mensagem: ?string,
     * }
     */
    public function status(
        Processo $processo,
        string $tipoDocumento,
        ?int $homologacaoId = null,
        ?int $vencedorId = null
    ): array {
        // $vencedorId está no contrato público pra simetria com obter/salvar,
        // mas Documento não tem coluna vencedor_id — fluxos por-vencedor (ata_registro_precos)
        // usam AtaRegistroPreco em tabela separada.
        unset($vencedorId);
        $documento = $this->localizarDocumentoGerado($processo, $tipoDocumento, $homologacaoId);

        if (!$documento) {
            return [
                'estado'         => 'sem_pdf',
                'pendentes'      => 0,
                'assinadas'      => 0,
                'total'          => 0,
                'versao_id'      => null,
                'pode_solicitar' => false,
                'mensagem'       => 'Gere o PDF antes de solicitar assinaturas.',
            ];
        }

        $consolidada = $this->versaoConsolidada($documento);
        if ($consolidada) {
            $total = SolicitacaoAssinatura::query()->where('documento_versao_id', $consolidada->id)->count();
            return [
                'estado'         => 'assinado',
                'pendentes'      => 0,
                'assinadas'      => $total,
                'total'          => $total,
                'versao_id'      => $consolidada->id,
                'pode_solicitar' => false,
                'mensagem'       => 'Documento totalmente assinado.',
            ];
        }

        $versaoAtiva = $this->ultimaVersaoComRodadaAtiva($documento);
        if ($versaoAtiva) {
            $solicitacoes = SolicitacaoAssinatura::query()->where('documento_versao_id', $versaoAtiva->id);
            $total        = (clone $solicitacoes)->count();
            $assinadas    = (clone $solicitacoes)->where('status', SolicitacaoAssinatura::STATUS_ASSINADA)->count();
            $pendentes    = (clone $solicitacoes)->where('status', SolicitacaoAssinatura::STATUS_PENDENTE)->count();

            return [
                'estado'         => $assinadas > 0 ? 'parcialmente_assinado' : 'aguardando',
                'pendentes'      => $pendentes,
                'assinadas'      => $assinadas,
                'total'          => $total,
                'versao_id'      => $versaoAtiva->id,
                'pode_solicitar' => false,
                'mensagem'       => "{$pendentes} assinatura(s) pendente(s) de {$total}.",
            ];
        }

        return [
            'estado'         => 'pronto_para_solicitar',
            'pendentes'      => 0,
            'assinadas'      => 0,
            'total'          => 0,
            'versao_id'      => null,
            'pode_solicitar' => true,
            'mensagem'       => null,
        ];
    }

    /**
     * Caminho absoluto do PDF assinado mais recente, ou null se ainda não houver
     * versão consolidada com o arquivo válido em disco.
     */
    public function caminhoPdfAssinado(?Documento $documento): ?string
    {
        if (!$documento) {
            return null;
        }
        $versao = $this->versaoConsolidada($documento);
        if (!$versao || !$versao->caminho_pdf_assinado) {
            return null;
        }
        return file_exists($versao->caminho_pdf_assinado)
            ? $versao->caminho_pdf_assinado
            : null;
    }

    private function ultimaVersaoComRodadaAtiva(Documento $documento): ?DocumentoVersao
    {
        return DocumentoVersao::query()
            ->where('documentavel_type', Documento::class)
            ->where('documentavel_id', $documento->id)
            ->whereHas('solicitacoes', function ($q) {
                $q->where('status', SolicitacaoAssinatura::STATUS_PENDENTE);
            })
            ->latest('id')
            ->first();
    }
}
