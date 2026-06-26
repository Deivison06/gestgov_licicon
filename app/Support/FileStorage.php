<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Helper de armazenamento de arquivos em public/uploads.
 *
 * Centraliza o padrão repetido (17 ocorrências de `public_path('uploads…')`):
 * monta o nome `<prefixo>_<timestamp>.<ext>`, garante o diretório e move o
 * arquivo, devolvendo o caminho RELATIVO (para gravar no banco). Mesma lógica já
 * usada em FinalizacaoService::salvarAnexo.
 */
class FileStorage
{
    /**
     * Move o upload para public/$dirRelativo e devolve o caminho relativo salvo.
     */
    public static function salvar(UploadedFile $file, string $dirRelativo, string $prefixo): string
    {
        $dirRelativo = trim($dirRelativo, '/');
        $filename = $prefixo . '_' . time() . '.' . $file->getClientOriginalExtension();
        $destino = public_path($dirRelativo);

        if (!file_exists($destino)) {
            mkdir($destino, 0777, true);
        }

        $file->move($destino, $filename);

        return $dirRelativo . '/' . $filename;
    }

    /**
     * Remove um arquivo a partir do caminho relativo (no-op se vazio/inexistente).
     */
    public static function remover(?string $caminhoRelativo): void
    {
        if ($caminhoRelativo && is_file(public_path($caminhoRelativo))) {
            @unlink(public_path($caminhoRelativo));
        }
    }

    /**
     * Resolve o caminho absoluto a partir de um caminho relativo a public/.
     */
    public static function caminhoAbsoluto(string $caminhoRelativo): string
    {
        return public_path($caminhoRelativo);
    }
}
