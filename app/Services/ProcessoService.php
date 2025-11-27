<?php

namespace App\Services;

use App\Models\Processo;
use App\enums\ModalidadeEnum;
use App\Models\ProcessoDetalhe;
use Barryvdh\DomPDF\Facade\Pdf;

class ProcessoService
{
    /**
     * Cria um novo processo
     */
    public function create(array $data): Processo
    {
        if (isset($data['modalidade']) && is_int($data['modalidade'])) {
            $data['modalidade'] = ModalidadeEnum::from($data['modalidade']);
        }

        // Adiciona automaticamente o usuário logado
        $data['user_id'] = auth()->id();

        return Processo::create($data);
    }


    public function createDetalhe(array $data): ProcessoDetalhe
    {
        return ProcessoDetalhe::create($data);
    }

    /**
     * Atualiza um processo existente
     */
    public function update(Processo $processo, array $data): Processo
    {
        if (isset($data['modalidade']) && is_int($data['modalidade'])) {
            $data['modalidade'] = ModalidadeEnum::from($data['modalidade']);
        }

        $processo->update($data);

        return $processo;
    }

    /**
     * Remove um processo
     */
    public function delete(Processo $processo): bool
    {
        return $processo->delete();
    }

    /**
     * Gera PDF do processo sem salvar no sistema
     */
    public function gerarPdf(Processo $processo)
    {
        // Carrega os relacionamentos necessários
        $processo->load(['detalhe', 'prefeitura']);

        $data = [
            'processo' => $processo,
            'prefeitura' => $processo->prefeitura,
            'detalhe' => $processo->detalhe,
            'dataGeracao' => now()->format('d/m/Y H:i:s'),
        ];

        // Configurações do PDF
        $pdf = Pdf::loadView('Admin.Processos.pdf.capa', $data)
            ->setPaper('a4', 'portrait')
            ->setOption([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        return $pdf;
    }

    /**
     * Obtém o nome do arquivo PDF
     */
    public function getNomeArquivo(Processo $processo): string
    {
        $numeroProcesso = $processo->numero_processo ?? $processo->id;
        return "processo_{$numeroProcesso}_" . now()->format('Ymd_His') . '.pdf';
    }
}
