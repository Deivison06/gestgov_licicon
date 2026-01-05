<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Processo;
use App\Models\LoteContratado;
use Illuminate\Http\Request;

class IntegracaoAlmoxarifadoController extends Controller
{
    public function listarContratos(Request $request)
    {
        // busca Processos que tenham itens contratados
        $processos = Processo::whereHas('lotesContratados', function($q) {
                $q->where('status', 'CONTRATADO');
            })
            ->with([
                'prefeitura',
                'lotesContratados' => function($q) {
                    $q->where('status', 'CONTRATADO')->with(['lote', 'vencedor']);
                },
            ])
            ->latest()
            ->get();

        $dadosExportacao = [];

        foreach ($processos as $processo) {
            $contratoMacro = \App\Models\Contrato::where('processo_id', $processo->id)->first();

            $itensPorVencedor = $processo->lotesContratados->groupBy('vencedor_id');

            foreach ($itensPorVencedor as $vencedorId => $itens) {
                $vencedor = $itens->first()->vencedor;

                if (!$vencedor) continue;

                $codigoIntegracao = "PROC_{$processo->id}-VENC_{$vencedor->id}";

                $dadosExportacao[] = [
                    'codigo_integracao' => $codigoIntegracao,
                    'origem_processo_id' => $processo->id,

                    // Cabeçalho
                    'numero_processo' => $processo->numero_processo,
                    'numero_contrato' => $contratoMacro->numero_contrato ?? 'S/N',
                    'objeto' => $processo->objeto ?? "Licitação {$processo->numero_processo}",
                    'data_assinatura' => $contratoMacro->data_assinatura_contrato ?? now()->format('Y-m-d'),
                    'valor_total_vencedor' => $itens->sum('valor_total'),

                    // Dados para Validação/Busca no Destino
                    'prefeitura_cnpj' => $this->limparDocumento($processo->prefeitura->cnpj ?? ''),

                    // Fornecedor / cadastrar se não existir
                    'fornecedor' => [
                        'razao_social' => $vencedor->razao_social,
                        'cnpj' => $this->limparDocumento($vencedor->cnpj),
                        'endereco' => $vencedor->endereco,
                        'representante' => $vencedor->representante,
                    ],

                    // Os Itens para preencher o Estoque
                    'itens' => $itens->map(function($itemContratado) {
                        return [
                            'lote_numero' => $itemContratado->lote->item ?? 0,
                            'descricao' => $itemContratado->lote->descricao,
                            'unidade' => $itemContratado->lote->unidade ?? 'UN',
                            'quantidade' => (float) $itemContratado->quantidade_contratada,
                            'valor_unitario' => (float) $itemContratado->valor_unitario,
                            'valor_total' => (float) $itemContratado->valor_total,
                        ];
                    })->values()
                ];
            }
        }

        return response()->json(['data' => $dadosExportacao]);
    }

    private function limparDocumento($doc)
    {
        return preg_replace('/[^0-9]/', '', $doc);
    }
}
