<?php

namespace App\Services;

use App\Models\Processo;
use App\Models\Finalizacao;
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
        'anexo_atos_sessao',
        'anexo_proposta',
        'anexo_proposta_readequada',
        'anexo_habilitacao',
        'anexo_recurso_contratacoes',
        'anexo_planilha',
        'anexo_publicacoes',
        'anexo_parecer_juridico'
    ];

    public function salvarFinalizacao(Processo $processo, array $data): Finalizacao
    {
        $finalizacao = $processo->finalizacao ?? new Finalizacao();
        $finalizacao->processo_id = $processo->id;

        $this->processarArquivos($data, $finalizacao);

        foreach ($data as $field => $value) {
            if (!in_array($field, $this->excludedFields)) {
                $finalizacao->{$field} = $value;
            }
        }

        $finalizacao->save();
        return $finalizacao;
    }

    private function processarArquivos(array $data, Finalizacao $finalizacao): void
    {
        foreach ($this->arquivosConfig as $campo => $metodo) {
            if (isset($data[$campo]) && $data[$campo] instanceof \Illuminate\Http\UploadedFile) {
                $this->{$metodo}($data[$campo], $finalizacao, $campo);
            }
        }
    }

    private function salvarAnexo($file, Finalizacao $finalizacao, string $campo): void
    {
        $filename = $campo . '_' . time() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('uploads/anexos_finalizacao');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $file->move($destinationPath, $filename);
        $finalizacao->{$campo} = 'uploads/anexos_finalizacao/' . $filename;

        Log::info("Arquivo salvo - Finalização: {$finalizacao->{$campo}}");
    }
}