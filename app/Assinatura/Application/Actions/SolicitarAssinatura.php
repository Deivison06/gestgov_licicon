<?php

namespace App\Assinatura\Application\Actions;

use App\Assinatura\Application\Queries\StatusDocumentoAssinatura;
use App\Assinatura\Application\Support\AssinanteResolver;
use App\Models\Processo;
use App\Services\Assinatura\DocumentoVersaoService;
use App\Services\Assinatura\PdfWatermarkService;
use App\Services\Assinatura\SolicitacaoService;
use Illuminate\Support\Facades\DB;

/**
 * Dispara a rodada de assinatura a partir da seleção salva:
 *  - lê a seleção persistida
 *  - resolve responsável → user_id
 *  - aplica marca d'água e cria DocumentoVersao (rascunho)
 *  - cria as SolicitacaoAssinatura via SolicitacaoService
 *
 * Extraído do SelecaoAssinantesService — orquestração isolada e testável.
 */
class SolicitarAssinatura
{
    public function __construct(
        private readonly StatusDocumentoAssinatura $consulta,
        private readonly AssinanteResolver $assinanteResolver,
        private readonly DocumentoVersaoService $versaoService,
        private readonly SolicitacaoService $solicitacaoService,
        private readonly PdfWatermarkService $watermarkService
    ) {}

    /**
     * @return array{versao_id:int, total_solicitacoes:int, modo:string, prazo_dias:int, ignorados:int}
     */
    public function executar(
        Processo $processo,
        string $tipoDocumento,
        ?int $homologacaoId,
        ?int $vencedorId,
        int $solicitadoPorUserId
    ): array {
        $selecao = $this->consulta->obterSelecao($processo, $tipoDocumento, $homologacaoId, $vencedorId);

        if (!$selecao) {
            throw new \DomainException(
                'Nenhuma seleção de assinantes salva para este documento. '
                . 'Selecione os assinantes e clique em "Salvar Seleção" antes de solicitar a assinatura.'
            );
        }

        if (empty($selecao->assinantes)) {
            throw new \DomainException('A seleção salva está vazia.');
        }

        $listaResolvida = $this->assinanteResolver->resolverParaRodada(
            $selecao->assinantes,
            $selecao->modo
        );

        if (empty($listaResolvida)) {
            throw new \DomainException(
                'Nenhum assinante da seleção foi encontrado como usuário ativo. '
                . 'Verifique se os Responsáveis selecionados estão cadastrados como Assinantes do sistema.'
            );
        }

        $ignorados = count($selecao->assinantes) - count($listaResolvida);

        // Localiza o Documento gerado (PDF rascunho atual)
        $documento = $this->consulta->localizarDocumentoGerado($processo, $tipoDocumento, $homologacaoId);

        if (!$documento) {
            throw new \DomainException(
                'PDF do documento ainda não foi gerado. Clique em "Gerar PDF" antes de solicitar a assinatura.'
            );
        }

        $caminhoAbsoluto = $this->resolverCaminhoAbsoluto($documento->caminho);

        if (!file_exists($caminhoAbsoluto)) {
            throw new \DomainException(
                'Arquivo PDF não encontrado no servidor. Tente gerar o PDF novamente.'
            );
        }

        // Bloqueia disparo duplicado de rodada ativa para o mesmo documento
        if ($this->consulta->existeRodadaAtiva($documento)) {
            throw new \DomainException(
                'Já existe uma rodada de assinatura em andamento para este documento. '
                . 'Cancele ou conclua a rodada atual antes de iniciar outra.'
            );
        }

        return DB::transaction(function () use (
            $documento,
            $caminhoAbsoluto,
            $listaResolvida,
            $selecao,
            $solicitadoPorUserId,
            $ignorados
        ) {
            // 1) Marca d'água
            $caminhoRascunho = $this->watermarkService->aplicarMarcaDagua(
                $caminhoAbsoluto,
                'AGUARDANDO ASSINATURAS'
            );

            // 2) Versão
            $versao = $this->versaoService->criarRascunho(
                $documento,
                $caminhoRascunho,
                $solicitadoPorUserId
            );

            // 3) Rodada
            $solicitacoes = $this->solicitacaoService->criarRodada(
                $versao,
                $listaResolvida,
                $solicitadoPorUserId,
                now()->addDays($selecao->prazo_dias)
            );

            return [
                'versao_id'          => $versao->id,
                'total_solicitacoes' => $solicitacoes->count(),
                'modo'               => $selecao->modo,
                'prazo_dias'         => $selecao->prazo_dias,
                'ignorados'          => $ignorados,
            ];
        });
    }

    /**
     * Devolve sempre caminho absoluto. Documento->caminho é relativo a public/.
     * Crítico: TCPDF/FPDI convertem caminhos relativos em URIs file://host/path,
     * que PHP rejeita com "Remote host file access not supported".
     */
    private function resolverCaminhoAbsoluto(string $caminho): string
    {
        if (str_starts_with($caminho, '/')) {
            return $caminho;
        }
        return public_path($caminho);
    }
}
