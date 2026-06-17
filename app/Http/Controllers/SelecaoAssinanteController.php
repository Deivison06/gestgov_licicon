<?php

namespace App\Http\Controllers;

use App\Models\Processo;
use App\Services\Assinatura\SelecaoAssinantesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Endpoints HTTP para gerenciar a seleção de assinantes e disparar a rodada.
 *
 *  GET   /admin/processos/{processo}/selecao-assinantes        — lê seleção atual
 *  POST  /admin/processos/{processo}/selecao-assinantes        — salva (upsert)
 *  POST  /admin/processos/{processo}/solicitar-assinatura      — dispara rodada
 *  GET   /admin/processos/{processo}/rodada-assinatura/status  — status atual
 */
class SelecaoAssinanteController extends Controller
{
    public function __construct(
        private readonly SelecaoAssinantesService $service
    ) {}

    /**
     * GET — Recupera a seleção salva
     */
    public function obter(Request $request, Processo $processo)
    {
        $request->validate([
            'tipo_documento' => ['required', 'string', 'max:80'],
            'homologacao_id' => ['nullable', 'integer', 'exists:homologacoes,id'],
            'vencedor_id'    => ['nullable', 'integer', 'exists:vencedores,id'],
        ]);

        $selecao = $this->service->obter(
            $processo,
            $request->input('tipo_documento'),
            $request->integer('homologacao_id') ?: null,
            $request->integer('vencedor_id') ?: null
        );

        return response()->json([
            'success' => true,
            'data'    => $selecao ? [
                'modo'       => $selecao->modo,
                'prazo_dias' => $selecao->prazo_dias,
                'assinantes' => $selecao->assinantes,
                'atualizado_em' => optional($selecao->updated_at)->toIso8601String(),
            ] : null,
        ]);
    }

    /**
     * POST — Salva (upsert) a seleção
     */
    public function salvar(Request $request, Processo $processo)
    {
        $request->validate([
            'tipo_documento'        => ['required', 'string', 'max:80'],
            'homologacao_id'        => ['nullable', 'integer', 'exists:homologacoes,id'],
            'vencedor_id'           => ['nullable', 'integer', 'exists:vencedores,id'],
            'modo'                  => ['nullable', 'in:paralelo,sequencial'],
            'prazo_dias'            => ['nullable', 'integer', 'min:1', 'max:60'],
            'assinantes'            => ['required', 'array'],
            'assinantes.*.responsavel' => ['nullable', 'string', 'max:255'],
            'assinantes.*.user_id'  => ['nullable', 'integer'],
            'assinantes.*.unidade_nome'    => ['nullable', 'string', 'max:255'],
            'assinantes.*.numero_portaria' => ['nullable', 'string', 'max:50'],
            'assinantes.*.data_portaria'   => ['nullable', 'string', 'max:30'],
            'assinantes.*.ordem'    => ['nullable', 'integer'],
        ]);

        try {
            $selecao = $this->service->salvar(
                $processo,
                $request->input('tipo_documento'),
                $request->integer('homologacao_id') ?: null,
                $request->integer('vencedor_id') ?: null,
                [
                    'modo'       => $request->input('modo', 'paralelo'),
                    'prazo_dias' => (int) $request->input('prazo_dias', 7),
                    'assinantes' => $request->input('assinantes', []),
                ],
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Seleção de assinantes salva.',
                'data'    => [
                    'modo'       => $selecao->modo,
                    'prazo_dias' => $selecao->prazo_dias,
                    'total'      => count($selecao->assinantes),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao salvar seleção de assinantes', [
                'processo_id' => $processo->id,
                'erro'        => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar seleção: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET — Status agregado da rodada (para badges/UI)
     */
    public function status(Request $request, Processo $processo)
    {
        $request->validate([
            'tipo_documento' => ['required', 'string', 'max:80'],
            'homologacao_id' => ['nullable', 'integer', 'exists:homologacoes,id'],
            'vencedor_id'    => ['nullable', 'integer', 'exists:vencedores,id'],
        ]);

        $status = $this->service->statusDocumento(
            $processo,
            $request->input('tipo_documento'),
            $request->integer('homologacao_id') ?: null,
            $request->integer('vencedor_id') ?: null
        );

        return response()->json(['success' => true, 'data' => $status]);
    }

    /**
     * POST — Solicita a assinatura (dispara a rodada)
     */
    public function solicitar(Request $request, Processo $processo)
    {
        $request->validate([
            'tipo_documento' => ['required', 'string', 'max:80'],
            'homologacao_id' => ['nullable', 'integer', 'exists:homologacoes,id'],
            'vencedor_id'    => ['nullable', 'integer', 'exists:vencedores,id'],
        ]);

        try {
            $info = $this->service->solicitarAssinatura(
                $processo,
                $request->input('tipo_documento'),
                $request->integer('homologacao_id') ?: null,
                $request->integer('vencedor_id') ?: null,
                $request->user()->id
            );

            $msg = "Rodada de assinatura iniciada — {$info['total_solicitacoes']} solicitação(ões), modo {$info['modo']}.";
            if ($info['ignorados'] > 0) {
                $msg .= " ({$info['ignorados']} assinante(s) ignorado(s) por não estar(em) cadastrado(s) como usuário do sistema)";
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'data'    => $info,
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Erro ao solicitar assinatura', [
                'processo_id' => $processo->id,
                'erro'        => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro inesperado: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST — Cancela a rodada de assinatura ativa do documento
     */
    public function cancelar(Request $request, Processo $processo)
    {
        $request->validate([
            'tipo_documento' => ['required', 'string', 'max:80'],
            'homologacao_id' => ['nullable', 'integer', 'exists:homologacoes,id'],
            'vencedor_id'    => ['nullable', 'integer', 'exists:vencedores,id'],
            'motivo'         => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $afetadas = $this->service->cancelarRodada(
                $processo,
                $request->input('tipo_documento'),
                $request->integer('homologacao_id') ?: null,
                $request->integer('vencedor_id') ?: null,
                $request->user()->id,
                $request->input('motivo')
            );

            return response()->json([
                'success' => true,
                'message' => "Rodada de assinatura cancelada — {$afetadas} solicitação(ões) afetada(s).",
                'data'    => ['afetadas' => $afetadas],
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Erro ao cancelar rodada de assinatura', [
                'processo_id' => $processo->id,
                'erro'        => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro inesperado: ' . $e->getMessage(),
            ], 500);
        }
    }
}
