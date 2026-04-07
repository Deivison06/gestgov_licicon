<?php

namespace App\Services;

use App\Models\Processo;
use App\Models\Documento;
use App\Models\LoteContratado;
use Illuminate\Support\Facades\Log;

class AtaDocumentoService
{
    protected array $camposPermitidos = [
        'numero_contrato',
        'data_assinatura_contrato',
        'numero_extrato',
        'comarca',
        'fonte_recurso',
        'subcontratacao'
    ];

    public function getItensContrato(Processo $processo, $documentoId): array
    {
        $documento = Documento::where('id', $documentoId)
            ->where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->firstOrFail();

        $contratacoesIds = $documento->contratacoes_selecionadas ?? [];

        $contratacoes = LoteContratado::whereIn('id', $contratacoesIds)
            ->where('processo_id', $processo->id)
            ->with(['lote', 'vencedor'])
            ->get();

        $itens = [];
        $totalContrato = 0;

        foreach ($contratacoes as $contratacao) {
            if ($contratacao->lote) {
                $totalContrato += $contratacao->valor_total;
                $itens[] = [
                    'item' => $contratacao->lote->item,
                    'descricao' => $contratacao->lote->descricao,
                    'vencedor' => $contratacao->vencedor->razao_social,
                    'quantidade' => number_format($contratacao->quantidade_contratada, 2, ',', '.'),
                    'valor_unitario' => 'R$ ' . number_format($contratacao->valor_unitario, 2, ',', '.'),
                    'valor_total' => 'R$ ' . number_format($contratacao->valor_total, 2, ',', '.'),
                ];
            }
        }

        return [
            'itens' => $itens,
            'total_contrato' => 'R$ ' . number_format($totalContrato, 2, ',', '.')
        ];
    }

    public function salvarCampoContrato(Processo $processo, array $data): array
    {
        $campo = $data['campo'] ?? '';
        $valor = $data['valor'] ?? '';

        if (!in_array($campo, $this->camposPermitidos)) {
            throw new \Exception('Campo não permitido.');
        }

        $contrato = \App\Models\Contrato::where('processo_id', $processo->id)->first();

        if (!$contrato) {
            $contrato = \App\Models\Contrato::create([
                'processo_id' => $processo->id
            ]);
        }

        if ($campo === 'data_assinatura_contrato' && $valor) {
            $valor = \Carbon\Carbon::parse($valor)->format('Y-m-d');
        }

        $contrato->update([$campo => $valor]);

        Log::info('Campo da ata salvo com sucesso', [
            'processo_id' => $processo->id,
            'campo' => $campo,
            'valor' => $valor
        ]);

        return [$campo => $valor];
    }

    public function getDadosContrato(Processo $processo): array
    {
        $contrato = \App\Models\Contrato::where('processo_id', $processo->id)->first();

        if (!$contrato) {
            return [];
        }

        return [
            'numero_contrato' => $contrato->numero_contrato,
            'data_assinatura_contrato' => $contrato->data_assinatura_contrato,
            'numero_extrato' => $contrato->numero_extrato,
            'comarca' => $contrato->comarca,
            'fonte_recurso' => $contrato->fonte_recurso,
            'subcontratacao' => $contrato->subcontratacao,
        ];
    }

    public function salvarAssinantesContrato(Processo $processo, array $assinantes): void
    {
        $documento = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->first();

        if ($documento) {
            $documento->update([
                'assinantes' => $assinantes
            ]);
        } else {
            Documento::create([
                'processo_id' => $processo->id,
                'tipo_documento' => 'contrato',
                'assinantes' => $assinantes,
                'gerado_em' => now()
            ]);
        }

        Log::info('Assinantes da ata salvos com sucesso', [
            'processo_id' => $processo->id,
            'quantidade' => count($assinantes)
        ]);
    }

    public function salvarContratacoesSelecionadas(Processo $processo, array $contratacoesSelecionadas): void
    {
        $documento = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->first();

        if ($documento) {
            $documento->update([
                'contratacoes_selecionadas' => $contratacoesSelecionadas
            ]);
        } else {
            Documento::create([
                'processo_id' => $processo->id,
                'tipo_documento' => 'contrato',
                'contratacoes_selecionadas' => $contratacoesSelecionadas,
                'gerado_em' => now()
            ]);
        }

        Log::info('Contratações selecionadas salvas com sucesso', [
            'processo_id' => $processo->id,
            'quantidade' => count($contratacoesSelecionadas)
        ]);
    }

    public function debugContratos(Processo $processo): array
    {
        $documentos = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato')
            ->get();

        $contratos = \App\Models\Contrato::where('processo_id', $processo->id)->get();

        $requestData = request()->all();

        return [
            'documentos' => $documentos->map(function($doc) {
                $camposJson = $doc->campos ?? [];
                return [
                    'id' => $doc->id,
                    'campos' => $doc->campos,
                    'campos_json' => $camposJson,
                    'numero_contrato_campos' => $camposJson['numero_contrato'] ?? 'não encontrado em JSON',
                    'valor_total' => $doc->valor_total,
                    'quantidade_itens' => $doc->quantidade_itens,
                    'gerado_em' => $doc->gerado_em,
                    'caminho' => $doc->caminho,
                ];
            }),
            'contratos' => $contratos->map(function($contrato) {
                return [
                    'id' => $contrato->id,
                    'numero_contrato' => $contrato->numero_contrato,
                    'data_assinatura_contrato' => $contrato->data_assinatura_contrato,
                    'processo_id' => $contrato->processo_id,
                ];
            }),
            'request_data' => $requestData,
            'total_documentos' => $documentos->count(),
            'total_contratos' => $contratos->count(),
        ];
    }
}
