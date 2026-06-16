<?php

namespace App\Services\Assinatura;

use App\Models\AssinaturaLog;
use App\Models\DocumentoVersao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Responsável por criar e consultar versões de documentos.
 * Toda nova geração de PDF (rascunho) passa por aqui.
 */
class DocumentoVersaoService
{
    /**
     * Cria uma nova versão (rascunho) vinculada ao documentavel.
     * Calcula o hash do PDF + grava log de criação.
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

        $hash = hash_file('sha256', $caminhoPdf);
        if ($hash === false) {
            throw new \RuntimeException("Falha ao calcular hash de {$caminhoPdf}");
        }

        return DB::transaction(function () use ($documentavel, $caminhoPdf, $geradoPorUserId, $hash) {
            $proximaVersao = $this->proximaVersao($documentavel);

            $versao = DocumentoVersao::create([
                'documentavel_type'  => get_class($documentavel),
                'documentavel_id'    => $documentavel->getKey(),
                'versao'             => $proximaVersao,
                'caminho_pdf'        => $caminhoPdf,
                'hash_sha256'        => $hash,
                'gerado_por_user_id' => $geradoPorUserId,
                'gerado_em'          => now(),
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
