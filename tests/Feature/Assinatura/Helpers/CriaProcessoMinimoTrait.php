<?php

namespace Tests\Feature\Assinatura\Helpers;

/**
 * Helper compartilhado entre testes que precisam criar Processo + Documento
 * sem depender de factories (que não existem para esses models legados).
 */
trait CriaProcessoMinimoTrait
{
    protected function criarProcessoMinimo(): int
    {
        $prefeituraId = \DB::table('prefeituras')->insertGetId(
            $this->placeholdersParaNotNull('prefeituras', ['nome' => 'Pref Teste', 'cidade' => 'Teste'])
        );

        $extras = [
            'prefeitura_id'   => $prefeituraId,
            'numero_processo' => '999/2026',
            'status'          => 'RASCUNHO',
            'modalidade'      => 1,
        ];
        return \DB::table('processos')->insertGetId(
            $this->placeholdersParaNotNull('processos', $extras)
        );
    }

    protected function placeholdersParaNotNull(string $tabela, array $extras = []): array
    {
        $colunas = collect(\DB::select("PRAGMA table_info({$tabela})"));
        $dados = $extras;
        foreach ($colunas as $col) {
            if (!$col->notnull || $col->pk) continue;
            if (array_key_exists($col->name, $dados)) continue;
            $dados[$col->name] = match (true) {
                str_contains($col->name, 'data')   => now()->format('Y-m-d'),
                str_contains($col->name, 'id')     => 1,
                default                             => 'TESTE',
            };
        }
        return $dados;
    }

    protected function criarPdfFake(): string
    {
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Documento de teste', 0, 1, 'C');

        $caminho = tempnam(sys_get_temp_dir(), 'test_') . '.pdf';
        $pdf->Output($caminho, 'F');

        return $caminho;
    }
}
