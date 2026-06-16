<?php

namespace App\Http\Controllers;

use App\Models\AssinaturaLog;
use App\Models\SolicitacaoAssinatura;
use App\Services\Assinatura\AssinaturaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Central de Assinaturas — interface do assinante para listar pendências,
 * visualizar documentos e executar ações (assinar / recusar).
 *
 * Toda a lógica de domínio fica em AssinaturaService. Aqui só HTTP.
 */
class AssinaturaController extends Controller
{
    /**
     * GET /minhas-assinaturas
     * Lista pendências do user logado + histórico recente.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $pendentes = SolicitacaoAssinatura::query()
            ->where('assinante_user_id', $user->id)
            ->where('status', SolicitacaoAssinatura::STATUS_PENDENTE)
            ->with(['versao.documentavel', 'solicitadoPor'])
            ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END') // sem prazo vai pro fim
            ->orderBy('expires_at')
            ->paginate(15, ['*'], 'pendentes');

        $historico = SolicitacaoAssinatura::query()
            ->where('assinante_user_id', $user->id)
            ->whereIn('status', SolicitacaoAssinatura::STATUSES_FINALIZADOS)
            ->with(['versao.documentavel', 'assinatura'])
            ->orderByDesc('processada_em')
            ->paginate(10, ['*'], 'historico');

        return view('Assinaturas.index', compact('pendentes', 'historico'));
    }

    /**
     * GET /minhas-assinaturas/{solicitacao}
     * Visualizar documento + ações.
     */
    public function show(Request $request, SolicitacaoAssinatura $solicitacao)
    {
        $this->autorizarAcesso($request, $solicitacao);

        $solicitacao->load([
            'versao.documentavel',
            'versao.assinaturas.assinante',
            'solicitadoPor',
            'assinatura',
        ]);

        // Log de visualização (best-effort — não bloqueia)
        try {
            AssinaturaLog::create([
                'acao'                      => AssinaturaLog::ACAO_VISUALIZADA,
                'solicitacao_assinatura_id' => $solicitacao->id,
                'documento_versao_id'       => $solicitacao->documento_versao_id,
                'user_id'                   => $request->user()->id,
                'ip'                        => $request->ip(),
                'user_agent'                => substr((string) $request->userAgent(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao registrar log de visualização', ['erro' => $e->getMessage()]);
        }

        return view('Assinaturas.show', [
            'solicitacao'           => $solicitacao,
            'versao'                => $solicitacao->versao,
            'assinaturasAnteriores' => $solicitacao->versao->assinaturas->sortBy('assinado_em')->values(),
            'urlPdf'                => route('minhas-assinaturas.pdf', $solicitacao->id),
        ]);
    }

    /**
     * GET /minhas-assinaturas/{solicitacao}/pdf
     * Serve o PDF inline pro <embed>/<iframe>.
     */
    public function visualizarPdf(Request $request, SolicitacaoAssinatura $solicitacao)
    {
        $this->autorizarAcesso($request, $solicitacao);

        $caminho = $solicitacao->versao->caminho_pdf;
        if (!$caminho || !file_exists($caminho)) {
            abort(404, 'PDF não encontrado no servidor.');
        }

        return response()->file($caminho, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="documento.pdf"',
        ]);
    }

    /**
     * POST /minhas-assinaturas/{solicitacao}/assinar
     */
    public function assinar(
        Request $request,
        SolicitacaoAssinatura $solicitacao,
        AssinaturaService $service
    ) {
        $this->autorizarAcesso($request, $solicitacao);

        try {
            $assinatura = $service->assinar(
                $solicitacao,
                $request->user(),
                $request->ip() ?? '0.0.0.0',
                substr((string) $request->userAgent(), 0, 500)
            );

            return redirect()->route('minhas-assinaturas.index')
                ->with('success', "Assinatura registrada com sucesso. Código verificador: {$assinatura->codigo_verificador}");
        } catch (\DomainException $e) {
            return redirect()->route('minhas-assinaturas.show', $solicitacao->id)
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Erro ao assinar', [
                'solicitacao_id' => $solicitacao->id,
                'user_id'        => $request->user()->id,
                'erro'           => $e->getMessage(),
            ]);
            return redirect()->route('minhas-assinaturas.show', $solicitacao->id)
                ->with('error', 'Erro ao registrar assinatura. Tente novamente.');
        }
    }

    /**
     * POST /minhas-assinaturas/{solicitacao}/recusar
     */
    public function recusar(
        Request $request,
        SolicitacaoAssinatura $solicitacao,
        AssinaturaService $service
    ) {
        $this->autorizarAcesso($request, $solicitacao);

        $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'motivo.required' => 'Informe o motivo da recusa.',
            'motivo.min'      => 'O motivo precisa ter ao menos 5 caracteres.',
            'motivo.max'      => 'O motivo é muito longo (máx. 500 caracteres).',
        ]);

        try {
            $service->recusar(
                $solicitacao,
                $request->user(),
                $request->input('motivo'),
                $request->ip() ?? '0.0.0.0',
                substr((string) $request->userAgent(), 0, 500)
            );

            return redirect()->route('minhas-assinaturas.index')
                ->with('success', 'Solicitação recusada. A rodada de assinatura foi cancelada.');
        } catch (\DomainException $e) {
            return redirect()->route('minhas-assinaturas.show', $solicitacao->id)
                ->with('error', $e->getMessage());
        }
    }

    // ====================================================================
    // Helpers
    // ====================================================================

    /**
     * Garante que o user logado é dono da solicitação.
     * Admin pode visualizar (auditoria), mas não pode assinar pelo outro.
     */
    private function autorizarAcesso(Request $request, SolicitacaoAssinatura $solicitacao): void
    {
        if ($solicitacao->assinante_user_id !== $request->user()->id) {
            abort(403, 'Esta solicitação de assinatura não é sua.');
        }
    }
}
