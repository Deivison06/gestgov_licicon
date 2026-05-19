<?php

namespace App\Services;

use App\Models\Processo;
use App\Models\Finalizacao;
use App\Models\Documento;
use App\Models\Homologacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class FinalizacaoService
{
    protected array $arquivosConfig = [
        'anexo_atos_sessao' => 'salvarAnexo',
        'anexo_proposta' => 'salvarAnexo',
        'anexo_proposta_readequada' => 'salvarAnexo',
        'anexo_habilitacao' => 'salvarAnexo',
        'anexo_recurso_contratacoes' => 'salvarAnexo',
        'anexo_planilha' => 'salvarAnexo',
        'anexo_publicacoes' => 'salvarAnexo',
        'anexo_parecer_juridico' => 'salvarAnexo'
    ];

    protected array $excludedFields = [
        '_token',
        'processo_id',
        'homologacao_id',
        'anexo_atos_sessao',
        'anexo_proposta',
        'anexo_proposta_readequada',
        'anexo_habilitacao',
        'anexo_recurso_contratacoes',
        'anexo_planilha',
        'anexo_publicacoes',
        'anexo_parecer_juridico'
    ];

    /**
     * Salva campos de finalização. Se `homologacao_id` for informado, persiste na
     * `Homologacao` correspondente; caso contrário, na `Finalizacao` do processo.
     */
    public function salvarFinalizacao(Processo $processo, array $data): Model
    {
        $homologacao = $this->resolverHomologacao($processo, $data);

        if ($homologacao) {
            return $this->salvarNaHomologacao($processo, $homologacao, $data);
        }

        return $this->salvarNaFinalizacao($processo, $data);
    }

    private function resolverHomologacao(Processo $processo, array $data): ?Homologacao
    {
        $homologacaoId = $data['homologacao_id'] ?? null;

        if (!$homologacaoId) {
            return null;
        }

        $homologacao = Homologacao::where('processo_id', $processo->id)
            ->where('id', $homologacaoId)
            ->first();

        if (!$homologacao) {
            throw new \DomainException('Homologação não pertence a este processo.');
        }

        return $homologacao;
    }

    private function salvarNaFinalizacao(Processo $processo, array $data): Finalizacao
    {
        $finalizacao = $processo->finalizacao ?? new Finalizacao();
        $finalizacao->processo_id = $processo->id;

        $this->processarArquivos($data, $finalizacao);

        foreach ($data as $field => $value) {
            if (strpos($field, 'data_doc_') === 0) {
                $tipoDocumento = substr($field, 9);
                Documento::updateOrCreate(
                    ['processo_id' => $processo->id, 'tipo_documento' => $tipoDocumento, 'homologacao_id' => null],
                    ['data_selecionada' => $value]
                );
                continue;
            }

            if (!in_array($field, $this->excludedFields)) {
                $finalizacao->{$field} = $value;
            }
        }

        $finalizacao->save();
        return $finalizacao;
    }

    private function salvarNaHomologacao(Processo $processo, Homologacao $homologacao, array $data): Homologacao
    {
        $this->processarArquivos($data, $homologacao);

        foreach ($data as $field => $value) {
            if (strpos($field, 'data_doc_') === 0) {
                $tipoDocumento = substr($field, 9);
                Documento::updateOrCreate(
                    [
                        'processo_id' => $processo->id,
                        'homologacao_id' => $homologacao->id,
                        'tipo_documento' => $tipoDocumento,
                    ],
                    ['data_selecionada' => $value]
                );
                continue;
            }

            if (!in_array($field, $this->excludedFields) && in_array($field, $homologacao->getFillable(), true)) {
                $homologacao->{$field} = $value;
            }
        }

        $homologacao->save();
        return $homologacao;
    }

    private function processarArquivos(array $data, Model $alvo): void
    {
        foreach ($this->arquivosConfig as $campo => $metodo) {
            if (isset($data[$campo]) && $data[$campo] instanceof \Illuminate\Http\UploadedFile) {
                $this->{$metodo}($data[$campo], $alvo, $campo);
            }
        }
    }

    private function salvarAnexo($file, Model $alvo, string $campo): void
    {
        if (!in_array($campo, $alvo->getFillable(), true)) {
            return;
        }

        $filename = $campo . '_' . time() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('uploads/anexos_finalizacao');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $file->move($destinationPath, $filename);
        $alvo->{$campo} = 'uploads/anexos_finalizacao/' . $filename;

        Log::info("Arquivo salvo - Finalização: {$alvo->{$campo}}");
    }
}