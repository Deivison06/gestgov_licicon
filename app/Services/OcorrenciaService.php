<?php

namespace App\Services;

use App\Enums\StatusOcorrenciaEnum;
use App\Models\Ocorrencia;
use App\Models\OcorrenciaAnexo;
use App\Support\FileStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Regras de negócio do registro de Ocorrência contratual: criação com
 * anexos do fato, transições de status (rascunho → registrada → concluída),
 * anexação de resposta da empresa e do Atesto de Correção.
 *
 * Segue o mesmo espírito de HomologacaoDesistenciaService: nada é apagado
 * silenciosamente, e uma vez "Concluída" a ocorrência vira fato consumado.
 */
class OcorrenciaService
{
    /**
     * @param  UploadedFile[]  $anexosFato
     */
    public function registrar(array $dados, array $anexosFato = []): Ocorrencia
    {
        return DB::transaction(function () use ($dados, $anexosFato) {
            $ocorrencia = Ocorrencia::create($dados);

            foreach ($anexosFato as $arquivo) {
                $this->salvarAnexo($ocorrencia, 'fato', $arquivo);
            }

            return $ocorrencia->refresh();
        });
    }

    /**
     * Enquanto RASCUNHO, tudo pode ser alterado (inclusive a transição direta
     * para Registrada, via o próprio campo `status` do formulário). A partir
     * de REGISTRADA, os campos estruturais do fato já foram formalizados —
     * só a situação segue editável; a promoção para Concluída passa por
     * concluir(), nunca por aqui.
     */
    public function atualizar(Ocorrencia $ocorrencia, array $dados): Ocorrencia
    {
        if ($ocorrencia->status === StatusOcorrenciaEnum::CONCLUIDA) {
            throw new \DomainException('Esta ocorrência já foi concluída e não pode mais ser editada.');
        }

        if ($ocorrencia->status === StatusOcorrenciaEnum::RASCUNHO) {
            $ocorrencia->update($dados);

            return $ocorrencia->refresh();
        }

        $ocorrencia->update([
            'situacao' => $dados['situacao'] ?? $ocorrencia->situacao?->value,
        ]);

        return $ocorrencia->refresh();
    }

    /**
     * @param  UploadedFile[]  $arquivos
     */
    public function anexar(Ocorrencia $ocorrencia, string $categoria, array $arquivos): void
    {
        foreach ($arquivos as $arquivo) {
            $this->salvarAnexo($ocorrencia, $categoria, $arquivo);
        }

        if ($categoria === 'resposta' && ! $ocorrencia->resposta_registrada_em) {
            $ocorrencia->update(['resposta_registrada_em' => now()]);
        }
    }

    public function removerAnexo(OcorrenciaAnexo $anexo): void
    {
        FileStorage::remover($anexo->caminho);
        $anexo->delete();
    }

    /**
     * Grava os dados do Atesto de Correção. Não conclui a ocorrência sozinho
     * — a conclusão é uma ação explícita e separada do fiscal (concluir()).
     *
     * @param  array{correcao_descricao: string, correcao_data: string, correcao_elementos_comprobatorios?: string|null}  $dados
     * @param  UploadedFile[]  $anexos
     */
    public function registrarAtesto(Ocorrencia $ocorrencia, array $dados, array $anexos = []): Ocorrencia
    {
        if ($ocorrencia->status === StatusOcorrenciaEnum::RASCUNHO) {
            throw new \DomainException('Registre a ocorrência antes de lançar o atesto de correção.');
        }

        return DB::transaction(function () use ($ocorrencia, $dados, $anexos) {
            $ocorrencia->update([
                'correcao_descricao' => $dados['correcao_descricao'],
                'correcao_data' => $dados['correcao_data'],
                'correcao_elementos_comprobatorios' => $dados['correcao_elementos_comprobatorios'] ?? null,
            ]);

            foreach ($anexos as $arquivo) {
                $this->salvarAnexo($ocorrencia, 'correcao', $arquivo);
            }

            return $ocorrencia->refresh();
        });
    }

    public function concluir(Ocorrencia $ocorrencia): Ocorrencia
    {
        if (! $ocorrencia->status->podeTransicionarPara(StatusOcorrenciaEnum::CONCLUIDA)) {
            throw new \DomainException('Esta ocorrência não pode ser concluída no estado atual.');
        }

        if (! $ocorrencia->correcao_descricao || ! $ocorrencia->correcao_data) {
            throw new \DomainException('Preencha o Atesto de Correção (descrição e data da correção) antes de concluir.');
        }

        $ocorrencia->update(['status' => StatusOcorrenciaEnum::CONCLUIDA->value]);

        return $ocorrencia->refresh();
    }

    private function salvarAnexo(Ocorrencia $ocorrencia, string $categoria, UploadedFile $arquivo): void
    {
        $caminho = FileStorage::salvar(
            $arquivo,
            "uploads/ocorrencias/{$ocorrencia->id}",
            $categoria
        );

        $ocorrencia->anexos()->create([
            'categoria' => $categoria,
            'caminho' => $caminho,
            'nome_original' => $arquivo->getClientOriginalName(),
        ]);
    }
}
