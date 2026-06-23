<?php

namespace App\Services;

use App\Models\Processo;
use App\Models\Finalizacao;
use App\Models\Documento;
use App\Models\Homologacao;
use App\Models\Vencedor;
use App\Models\AtaRegistroPreco;
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
        'vencedor_id',
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
     * Campos que pertencem à Ata por vencedor (e portanto vão para AtaRegistroPreco
     * em vez de Homologacao) quando a request inclui vencedor_id.
     */
    protected array $camposDaAta = [
        'numero_ata_registro_precos',
        'cargo_controle_interno',
    ];

    /**
     * Salva campos de finalização.
     *  - Se vier `vencedor_id` + `homologacao_id`, os campos da Ata (número e cargo)
     *    e a data_doc_ata_registro_precos vão para AtaRegistroPreco. Demais campos
     *    seguem a regra padrão (Homologacao).
     *  - Se vier só `homologacao_id`, persiste na Homologacao.
     *  - Caso contrário, persiste na Finalizacao do processo.
     */
    public function salvarFinalizacao(Processo $processo, array $data): Model
    {
        $homologacao = $this->resolverHomologacao($processo, $data);
        $vencedor = $this->resolverVencedor($processo, $data);

        if ($homologacao && $vencedor) {
            $this->salvarNaAtaRegistroPreco($processo, $homologacao, $vencedor, $data);
        }

        $finalizacao = $this->salvarNaFinalizacao($processo, $data);

        if ($homologacao) {
            return $this->salvarNaHomologacao($processo, $homologacao, $data);
        }

        return $finalizacao;
    }

    private function resolverVencedor(Processo $processo, array $data): ?Vencedor
    {
        $vencedorId = $data['vencedor_id'] ?? null;
        if (!$vencedorId) {
            return null;
        }

        return Vencedor::where('processo_id', $processo->id)
            ->where('id', $vencedorId)
            ->first();
    }

    /**
     * Upsert dos campos da Ata na tabela AtaRegistroPreco. Recebe o mesmo $data da
     * request, extrai os campos pertinentes e salva por (homologacao_id, vencedor_id).
     */
    private function salvarNaAtaRegistroPreco(
        Processo $processo,
        Homologacao $homologacao,
        Vencedor $vencedor,
        array $data
    ): void {
        $ata = AtaRegistroPreco::firstOrNew([
            'homologacao_id' => $homologacao->id,
            'vencedor_id' => $vencedor->id,
        ]);
        $ata->processo_id = $processo->id;

        foreach ($this->camposDaAta as $campo) {
            if (array_key_exists($campo, $data)) {
                $ata->{$campo} = $data[$campo];
            }
        }

        // Data específica do tipo `ata_registro_precos` enviada via data_doc_*.
        if (array_key_exists('data_doc_ata_registro_precos', $data)) {
            $ata->data_selecionada = $data['data_doc_ata_registro_precos'];
        }

        $ata->save();
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

            if (!in_array($field, $this->excludedFields) && in_array($field, $finalizacao->getFillable(), true)) {
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