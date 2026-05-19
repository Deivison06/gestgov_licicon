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

        $lotesPendentes = $this->homologacaoService->lotesPendentes($processo);
        $temLotesPendentes = $lotesPendentes->isNotEmpty();
        $temVencedores = $processo->vencedores->isNotEmpty();
        $temHomologacao = $this->homologacaoService->temHomologacao($processo);
        $podeCriarNovaHomologacao = $this->homologacaoService->podeCriarNovaHomologacao($processo);

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
            'lotesPendentes',
            'temLotesPendentes',
            'temVencedores',
            'temHomologacao',
            'podeCriarNovaHomologacao',
            'documentosLegados'
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

    public function baixarDocumento(Processo $processo, $tipo)
    {
        return $this->pdfService->baixarDocumento($processo, $tipo);
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