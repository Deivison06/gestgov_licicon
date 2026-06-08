<?php

namespace App\Http\Controllers;

use App\Models\Processo;
use Illuminate\Http\Request;
use App\Services\FinalizacaoService;
use App\Services\FinalizacaoDocumentoService;
use App\Services\FinalizacaoPdfService;
use App\Services\FinalizacaoVencedorService;
use App\Services\HomologacaoService;

class FinalizacaoProcessoController extends Controller
{
    protected FinalizacaoService $finalizacaoService;
    protected FinalizacaoDocumentoService $documentoService;
    protected FinalizacaoPdfService $pdfService;
    protected FinalizacaoVencedorService $vencedorService;
    protected HomologacaoService $homologacaoService;

    public function __construct(
        FinalizacaoService $finalizacaoService,
        FinalizacaoDocumentoService $documentoService,
        FinalizacaoPdfService $pdfService,
        FinalizacaoVencedorService $vencedorService,
        HomologacaoService $homologacaoService
    ) {
        $this->finalizacaoService = $finalizacaoService;
        $this->documentoService = $documentoService;
        $this->pdfService = $pdfService;
        $this->vencedorService = $vencedorService;
        $this->homologacaoService = $homologacaoService;
    }

    public function finalizar(Processo $processo)
    {
        $processo->load([
            'prefeitura.unidades',
            'detalhe',
            'vencedores.lotes',
            'homologacoes.lotes.vencedor',
            'homologacoes.documentos',
            'homologacoes.atasRegistroPreco',
            'documentos',
        ]);

        $documentos = $this->documentoService->getDocumentosOrganizados($processo);

        $camposExtras = [
            'razao_social',
            'cnpj_empresa_vencedora',
            'endereco_empresa_vencedora',
            'representante_legal_empresa',
            'cpf_representante',
            'valor_total',
        ];

        $this->documentoService->adicionarCamposExtras($documentos, $processo, $camposExtras);

        $escopos = $this->documentoService->separarDocumentosPorEscopo($processo);
        $documentosProcesso = $escopos['processo'];
        // Sobrescreve com a versão que recebeu camposExtras (mantém termo_adjudicacao etc. já enriquecidos):
        $documentosHomologacao = array_intersect_key($documentos, $escopos['homologacao']);

        // Para cada homologação, expandimos a entrada `ata_registro_precos` em N entradas
        // (uma por vencedor da homologação). Assim o usuário pode gerar/baixar a Ata
        // de cada vencedor independentemente.
        $documentosPorHomologacao = [];
        foreach ($processo->homologacoes as $homol) {
            $documentosPorHomologacao[$homol->id] = $this->documentoService
                ->expandirAtaPorVencedor($documentosHomologacao, $homol);
        }

        $lotesPendentes = $this->homologacaoService->lotesPendentes($processo);
        $temLotesPendentes = $lotesPendentes->isNotEmpty();
        $temVencedores = $processo->vencedores->isNotEmpty();
        $temHomologacao = $this->homologacaoService->temHomologacao($processo);
        $podeCriarNovaHomologacao = $this->homologacaoService->podeCriarNovaHomologacao($processo);

        // "Homologação única" cobre Concorrência, Inexigibilidade (1 homologação atrás dos
        // docs) e Dispensa de Obra (zero homologação — contratação direta).
        // Em todos esses casos, a tela mostra UMA seção com todos os documentos juntos.
        $ehHomologacaoUnica = $this->homologacaoService->ehHomologacaoUnica($processo);
        $homologacaoUnica   = $ehHomologacaoUnica
            ? $processo->homologacoes->sortBy('id')->first()
            : null;

        if ($ehHomologacaoUnica) {
            $documentosProcesso = array_merge($documentosProcesso, $documentosHomologacao);
            if ($homologacaoUnica) {
                $documentosProcesso = $this->documentoService
                    ->expandirAtaPorVencedor($documentosProcesso, $homologacaoUnica);
            }
            // Esvazia para BLOCO 2a/2 não renderizarem
            $documentosHomologacao = [];
        }

        // Documentos legados: tipos por-homologação criados antes da feature, sem homologacao_id.
        $tiposPorHomologacao = $this->homologacaoService->tiposPorHomologacao();
        $documentosLegados = $processo->documentos
            ->whereNull('homologacao_id')
            ->whereIn('tipo_documento', $tiposPorHomologacao);

        return view('Admin.Processos.finalizar', compact(
            'processo',
            'documentos',
            'documentosProcesso',
            'documentosHomologacao',
            'documentosPorHomologacao',
            'lotesPendentes',
            'temLotesPendentes',
            'temVencedores',
            'temHomologacao',
            'podeCriarNovaHomologacao',
            'documentosLegados',
            'ehHomologacaoUnica',
            'homologacaoUnica'
        ));
    }

    public function gerarNovaHomologacao(Processo $processo)
    {
        try {
            $homologacao = $this->homologacaoService->criarNovaHomologacao($processo);

            return response()->json([
                'success' => true,
                'message' => "Homologação #{$homologacao->numero_sequencial} criada com sucesso.",
                'homologacao' => $homologacao,
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao gerar nova homologação', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar homologação: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function storeFinalizacao(Request $request, Processo $processo)
    {
        try {
            $finalizacao = $this->finalizacaoService->salvarFinalizacao($processo, $request->all());

            return response()->json([
                'success' => true,
                'data' => $finalizacao->toArray()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao salvar finalizacao do processo', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar os dados: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeVencedores(Request $request, Processo $processo)
    {
        try {
            $resultado = $this->vencedorService->salvarVencedores($processo, $request->all());

            $processo->load('vencedores.lotes');

            return response()->json([
                'success' => true,
                'message' => 'Vencedor salvo com sucesso!',
                'vencedores' => $processo->vencedores
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao salvar vencedor', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar vencedor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importarExcel(Request $request, Processo $processo)
    {
        try {
            $resultado = $this->vencedorService->importarExcel($processo, $request->all());

            return response()->json([
                'success' => true,
                'message' => '✅ Arquivo processado com sucesso! ' . count($resultado['lotes']) . ' itens importados.',
                'lotes' => $resultado['lotes'],
                'vencedor' => $resultado['vencedor']
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao importar Excel', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getVencedores(Processo $processo)
    {
        try {
            $vencedores = $this->vencedorService->getVencedores($processo);

            return response()->json([
                'success' => true,
                'vencedores' => $vencedores
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar vencedores: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gerarPdf(Request $request, Processo $processo)
    {
        try {
            $resultado = $this->pdfService->gerarPdf($processo, $request->all());

            return response()->json([
                'success' => true,
                'message' => '✅ PDF gerado com sucesso! Clique em "Download" para visualizar o arquivo.',
                'documento' => $request->query('documento')
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao gerar PDF - Finalização', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ Ocorreu um erro inesperado ao gerar o PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function baixarDocumento(Request $request, Processo $processo, $tipo)
    {
        $homologacaoId = $request->query('homologacao_id') ?: null;
        $vencedorId = $request->query('vencedor_id') ?: null;

        try {
            return $this->pdfService->baixarDocumento(
                $processo,
                $tipo,
                $homologacaoId ? (int) $homologacaoId : null,
                $vencedorId ? (int) $vencedorId : null
            );
        } catch (\DomainException $e) {
            // Erro previsível (documento não gerado / arquivo ausente): volta para a tela
            // de finalização com uma mensagem clara, em vez de servir uma página de erro
            // que o navegador salvaria como "Baixar.html".
            return redirect()
                ->route('admin.processos.finalizacao.finalizar', $processo)
                ->with('error', $e->getMessage());
        }
    }

    public function baixarTodosDocumentos(Processo $processo)
    {
        try {
            $token = \Illuminate\Support\Str::uuid()->toString();
            
            // Coloca a indicação inicial de "na fila"
            \Illuminate\Support\Facades\Cache::put("doc_status_{$token}", ['status' => 'na_fila', 'fase' => 'finalizar'], now()->addHours(2));
            
            // Dispara na fila Default
            \App\Jobs\GerarTodosDocumentosJob::dispatch($processo->id, 'finalizar', $token);

            return response()->json([
                'success' => true,
                'token'   => $token,
                'message' => 'Processamento iniciado em segundo plano.'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao colocar na fila todos os documentos (Finalizacao)', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar download: ' . $e->getMessage()
            ], 500);
        }
    }
}