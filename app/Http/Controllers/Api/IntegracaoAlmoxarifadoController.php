<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Processo;
use Illuminate\Http\Request;

class IntegracaoAlmoxarifadoController extends Controller
{
    public function listarContratos(Request $request)
    {
        $processos = Processo::whereHas('lotesContratados', function($q) {
            $q->where('status', 'CONTRATADO')->orWhereNotNull('contrato_id');
        })
        ->with([
            'prefeitura',
            'detalhe',
            'lotesContratados' => function($q) {
                $q->where('status', 'CONTRATADO')->orWhereNotNull('contrato_id');
                $q->with(['lote', 'vencedor']);
            },
        ])
        ->latest()
        ->get();

        $dadosExportacao = [];

        foreach ($processos as $processo) {
            $contratoMacro = \App\Models\Contrato::where('processo_id', $processo->id)->first();

            // Pega o valor inteiro do Enum ou o próprio valor se não for Enum
            $tipoContratacaoValor = $processo->tipo_contratacao?->value ?? $processo->tipo_contratacao;

            // Regra de Negócio: 2 = Por Item (Agrupar tudo), 1 = Por Lote (Manter separado)
            $isPorItem = ($tipoContratacaoValor == 2);

            $itensPorVencedor = $processo->lotesContratados->groupBy('vencedor_id');

            foreach ($itensPorVencedor as $vencedorId => $itens) {
                $vencedor = $itens->first()->vencedor;

                if (!$vencedor) continue;

                $codigoIntegracao = "PROC_{$processo->id}-VENC_{$vencedor->id}";

                $nomeContratante = $processo->detalhe->secretaria
                    ?? $processo->detalhe->unidade_setor
                    ?? 'Não Informado';

                $dadosExportacao[] = [
                    'codigo_integracao' => $codigoIntegracao,
                    'origem_processo_id' => $processo->id,
                    'contratante_origem' => $nomeContratante,

                    'prefeitura_nome' => $processo->prefeitura->nome ?? 'Prefeitura Não Identificada',
                    'numero_processo' => $processo->numero_processo,
                    'numero_contrato' => $contratoMacro->numero_contrato ?? 'S/N',

                    'modalidade' => $this->getModalidadeLabel($processo->modalidade),

                    // Envia o nome legível do tipo de contratação para o modal
                    'tipo_contratacao' => $this->getTipoContratacaoLabel($tipoContratacaoValor),

                    'objeto' => $processo->objeto ?? "Licitação {$processo->numero_processo}",
                    'data_assinatura' => $contratoMacro->data_assinatura_contrato ?? now()->format('Y-m-d'),
                    'valor_total_vencedor' => $itens->sum('valor_total'),

                    'prefeitura_cnpj' => $this->limparDocumento($processo->prefeitura->cnpj ?? ''),

                    'fornecedor' => [
                        'razao_social' => $vencedor->razao_social,
                        'cnpj' => $this->limparDocumento($vencedor->cnpj),
                        'endereco' => $vencedor->endereco,
                        'representante' => $vencedor->representante,
                    ],

                    // A Mágica acontece aqui: passamos a variável $isPorItem
                    'itens' => $itens->map(function($itemContratado) use ($isPorItem) {

                        if ($isPorItem) {
                            // SE FOR POR ITEM: Força ser lote 0 (Lote Único)
                            $numeroLote = 0;
                        } else {
                            // SE FOR POR LOTE: Mantém a numeração original
                            $numeroLote = $itemContratado->lote->lote ?? $itemContratado->lote->numero_lote ?? 1;
                        }

                        return [
                            'lote_numero' => $numeroLote,
                            'descricao' => $itemContratado->lote->descricao,
                            'unidade' => $itemContratado->lote->unidade ?? 'UND',
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

    private function getModalidadeLabel($modalidade)
    {
        $valor = (is_object($modalidade) && property_exists($modalidade, 'value'))
            ? $modalidade->value
            : $modalidade;

        return match ($valor) {
            1 => 'Concorrência',
            2 => 'Dispensa',
            3 => 'Inexigibilidade',
            4 => 'Pregão Eletrônico',
            default => 'Outra Modalidade',
        };
    }

    // Novo helper para traduzir o Tipo de Contratação
    private function getTipoContratacaoLabel($valor)
    {
        return match ($valor) {
            1 => 'Por Lote',
            2 => 'Por Item',
            3 => 'Global', // Caso exista no futuro
            default => 'Não Informado',
        };
    }
}
