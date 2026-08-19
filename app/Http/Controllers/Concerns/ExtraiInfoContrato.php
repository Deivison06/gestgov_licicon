<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Contrato;
use App\Models\ContratoManual;

/**
 * Normaliza os dados de um contrato polimórfico (Contrato do sistema ou
 * ContratoManual) num array único, usado tanto pelo módulo de Fiscalização
 * quanto pelo de Ocorrências para montar o cabeçalho do contrato nos
 * formulários, telas de detalhe e PDFs.
 */
trait ExtraiInfoContrato
{
    /**
     * Extrai informações do contrato para exibição unificada.
     */
    private function extrairInfoContrato($contrato): array
    {
        if (! $contrato) {
            return [
                'numero_contrato' => '—',
                'objeto' => '—',
                'numero_processo' => '—',
                'modalidade' => '—',
                'secretaria' => '—',
                'razao_social' => '—',
                'cnpj' => '—',
                'endereco' => '—',
                'representante' => '—',
                'origem' => '—',
            ];
        }

        if ($contrato instanceof ContratoManual) {
            return [
                'numero_contrato' => $contrato->numero_contrato ?? '—',
                'objeto' => $this->limparHtml($contrato->objeto),
                'numero_processo' => $contrato->numero_processo ?? '—',
                'modalidade' => $contrato->modalidade?->getDisplayName() ?? '—',
                'secretaria' => $contrato->secretaria->nome ?? '—',
                'razao_social' => $contrato->empresa->razao_social ?? '—',
                'cnpj' => $contrato->empresa->cnpj_formatado ?? '—',
                'endereco' => $contrato->empresa->endereco ?? '—',
                'representante' => $contrato->empresa->representante ?? '—',
                'origem' => 'Contrato Manual',
            ];
        }

        if ($contrato instanceof Contrato) {
            $processo = $contrato->processo;
            $vencedor = $processo?->vencedores?->first();

            return [
                'numero_contrato' => $contrato->numero_contrato ?? '—',
                'objeto' => $this->limparHtml($processo->objeto),
                'numero_processo' => $processo->numero_processo ?? '—',
                'modalidade' => $processo->modalidade?->getDisplayName() ?? '—',
                'secretaria' => $processo->unidade_numeracao ?? $processo->detalhe?->secretaria ?? '—',
                'razao_social' => $vencedor?->razao_social ?? '—',
                'cnpj' => $vencedor?->cnpj_formatado ?? $vencedor?->cpf_formatado ?? '—',
                'endereco' => $vencedor?->endereco ?? '—',
                'representante' => $vencedor?->representante ?? '—',
                'origem' => 'Contrato do Sistema',
            ];
        }

        return [
            'numero_contrato' => '—',
            'objeto' => '—',
            'numero_processo' => '—',
            'modalidade' => '—',
            'secretaria' => '—',
            'razao_social' => '—',
            'cnpj' => '—',
            'endereco' => '—',
            'representante' => '—',
            'origem' => '—',
        ];
    }

    /**
     * Remove tags HTML e decodifica entidades para exibir texto puro.
     */
    private function limparHtml(?string $texto): string
    {
        if (! $texto) {
            return '—';
        }

        $texto = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = strip_tags($texto);

        return trim(preg_replace('/\s+/', ' ', $texto));
    }
}
