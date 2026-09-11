<?php

namespace App\Services\Assinatura;

use App\Assinatura\Infrastructure\Pdf\PaginaAutenticacaoRenderer;
use App\Models\AssinaturaLog;
use App\Models\DocumentoVersao;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Responsável por criar e consultar versões de documentos.
 * Toda nova geração de PDF (rascunho) passa por aqui.
 */
class DocumentoVersaoService
{
    public function __construct(
        private readonly PaginaAutenticacaoRenderer $autenticacaoRenderer,
        private readonly CodigoVerificadorService $codigoVerificadorService
    ) {}

    /**
     * Cria uma nova versão (rascunho) vinculada ao documentavel.
     * Carimba o selo de autenticação (código + QR) no PDF, calcula o hash do
     * resultado + grava log de criação.
     *
     * @param Model  $documentavel       Ex.: Contrato, AtaRegistroPreco, Processo
     * @param string $caminhoPdf         Path absoluto ou relativo do PDF rascunho
     * @param int    $geradoPorUserId    User que disparou a geração
     * @return DocumentoVersao
     */
    public function criarRascunho(
        Model $documentavel,
        string $caminhoPdf,
        int $geradoPorUserId
    ): DocumentoVersao {
        if (!file_exists($caminhoPdf)) {
            throw new \InvalidArgumentException("PDF não encontrado: {$caminhoPdf}");
        }

        $proximaVersao = $this->proximaVersao($documentavel);
        $codigoVerificador = $this->codigoVerificadorService->gerarUnico();
        $geradoEm = now();
        $nomeGerador = User::find($geradoPorUserId)?->name ?? '—';

        // Carimba o selo de autenticação no arquivo recebido — vira o rascunho público.
        $caminhoAutenticado = $this->caminhoComSufixo($caminhoPdf, '_autenticado');
        $this->autenticacaoRenderer->gerar(
            $caminhoPdf, $caminhoAutenticado, $codigoVerificador, $nomeGerador, $geradoEm, $proximaVersao
        );

        // Se o arquivo recebido já tinha marca d'água ("AGUARDANDO ASSINATURAS"), carimba
        // também uma cópia autenticada do PDF limpo — usada depois pela consolidação para
        // gerar o documento assinado final sem o "aguardando", mas mantendo o selo.
        $caminhoLimpo = preg_replace('/_watermark\.pdf$/i', '.pdf', $caminhoPdf);
        if ($caminhoLimpo !== $caminhoPdf && file_exists($caminhoLimpo)) {
            $this->autenticacaoRenderer->gerar(
                $caminhoLimpo,
                $this->caminhoComSufixo($caminhoLimpo, '_autenticado'),
                $codigoVerificador,
                $nomeGerador,
                $geradoEm,
                $proximaVersao
            );
        }

        $hash = hash_file('sha256', $caminhoAutenticado);
        if ($hash === false) {
            throw new \RuntimeException("Falha ao calcular hash de {$caminhoAutenticado}");
        }

        return DB::transaction(function () use (
            $documentavel, $caminhoAutenticado, $hash, $codigoVerificador, $geradoPorUserId, $proximaVersao, $geradoEm
        ) {
            $versao = DocumentoVersao::create([
                'documentavel_type'  => get_class($documentavel),
                'documentavel_id'    => $documentavel->getKey(),
                'versao'             => $proximaVersao,
                'caminho_pdf'        => $caminhoAutenticado,
                'hash_sha256'        => $hash,
                'codigo_verificador' => $codigoVerificador,
                'gerado_por_user_id' => $geradoPorUserId,
                'gerado_em'          => $geradoEm,
            ]);

            AssinaturaLog::create([
                'acao'                => AssinaturaLog::ACAO_CRIADA,
                'documento_versao_id' => $versao->id,
                'user_id'             => $geradoPorUserId,
                'metadados'           => [
                    'documentavel' => class_basename($documentavel),
                    'versao'       => $proximaVersao,
                    'hash'         => substr($hash, 0, 16),
                ],
            ]);

            return $versao;
        });
    }

    private function caminhoComSufixo(string $caminho, string $sufixo): string
    {
        return preg_replace('/\.pdf$/i', $sufixo . '.pdf', $caminho);
    }

    /**
     * Marca a versão como regerada (substituída por uma nova). Não deleta.
     * Útil quando o operador cancela uma rodada e cria nova versão.
     */
    public function marcarComoRegerada(DocumentoVersao $versao, int $userId): void
    {
        AssinaturaLog::create([
            'acao'                => AssinaturaLog::ACAO_REGERADA,
            'documento_versao_id' => $versao->id,
            'user_id'             => $userId,
            'metadados'           => ['versao' => $versao->versao],
        ]);
    }

    /**
     * Retorna a próxima versão sequencial para este documentavel.
     * Protegido contra race condition via SELECT ... FOR UPDATE.
     */
    private function proximaVersao(Model $documentavel): int
    {
        $maximo = DocumentoVersao::query()
            ->where('documentavel_type', get_class($documentavel))
            ->where('documentavel_id', $documentavel->getKey())
            ->lockForUpdate()
            ->max('versao');

        return (int) ($maximo ?? 0) + 1;
    }
}
