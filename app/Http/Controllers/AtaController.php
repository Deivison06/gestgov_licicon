<?php

namespace App\Http\Controllers;

use App\Models\Processo;
use App\Models\Prefeitura;
use Illuminate\Http\Request;
use App\Services\AtaService;
use App\Services\AtaDocumentoService;
use App\Services\AtaContratacaoService;
use App\Services\AtaPdfService;

class AtaController extends Controller
{
    protected AtaService $ataService;
    protected AtaDocumentoService $documentoService;
    protected AtaContratacaoService $contratacaoService;
    protected AtaPdfService $pdfService;

    public function __construct(
        AtaService $ataService,
        AtaDocumentoService $documentoService,
        AtaContratacaoService $contratacaoService,
        AtaPdfService $pdfService
    ) {
        $this->ataService = $ataService;
        $this->documentoService = $documentoService;
        $this->contratacaoService = $contratacaoService;
        $this->pdfService = $pdfService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $prefeituraId = $request->get('prefeitura_id');

        // Se o usuário for de uma prefeitura específica, ele só vê coisas dessa prefeitura
        if ($user->prefeitura_id) {
            $prefeituraId = $user->prefeitura_id;
            $prefeituras = Prefeitura::where('id', $prefeituraId)->get();
        } else {
            $prefeituras = Prefeitura::with(['processos' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])->get();
        }

        $processoId = $request->get('processo_id');
        $search = $request->get('search'); // Novo campo de pesquisa

        $processos = $this->ataService->getProcessosFiltrados($prefeituraId, $processoId, $search);

        return view('Admin.Atas.index', compact('prefeituras', 'processos', 'prefeituraId', 'processoId', 'search'));
    }

    public function show(Processo $processo)
    {
        $dados = $this->ataService->prepararDadosParaExibicao($processo);

        return view('Admin.Atas.show', $dados);
    }

    public function getItensContrato(Processo $processo, $documentoId)
    {
        try {
            $resultado = $this->documentoService->getItensContrato($processo, $documentoId);

            return response()->json([
                'success' => true,
                'itens' => $resultado['itens'],
                'total_contrato' => $resultado['total_contrato']
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao obter itens do contrato', [
                'processo_id' => $processo->id,
                'documento_id' => $documentoId,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter itens do contrato.'
            ], 500);
        }
    }

    public function getLotesDisponiveis(Processo $processo, $vencedorId)
    {
        try {
            $resultado = $this->contratacaoService->getLotesDisponiveis($processo, $vencedorId);

            return response()->json([
                'success' => true,
                'lotes' => $resultado['lotes'],
                'vencedor' => $resultado['vencedor']
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao obter lotes disponíveis', [
                'processo_id' => $processo->id,
                'vencedor_id' => $vencedorId,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter lotes disponíveis: ' . $e->getMessage()
            ], 500);
        }
    }

    public function criarContratacaoDireta(Request $request, Processo $processo)
    {
        try {
            $resultado = $this->contratacaoService->criarContratacaoDireta($processo, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Contratação criada com sucesso!',
                'contratacao' => $resultado['contratacao']
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao criar contratação', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar contratação: ' . $e->getMessage()
            ], 500);
        }
    }

    public function marcarComoContratado(Request $request, Processo $processo)
    {
        try {
            $this->contratacaoService->marcarComoContratado($processo, $request->input('contratacoes', []));

            return response()->json([
                'success' => true,
                'message' => 'Contratações marcadas como CONTRATADO!'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao marcar contratações como CONTRATADO', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function salvarCampoContrato(Request $request, Processo $processo)
    {
        try {
            $resultado = $this->documentoService->salvarCampoContrato($processo, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Campo salvo com sucesso.',
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao salvar campo da ata', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar campo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDadosAta(Processo $processo)
    {
        try {
            $dados = $this->documentoService->getDadosContrato($processo);

            return response()->json([
                'success' => true,
                'dados' => $dados
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao obter dados do contrato/ata', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados.'
            ], 500);
        }
    }

    public function salvarAssinantesAta(Request $request, Processo $processo)
    {
        try {
            $this->documentoService->salvarAssinantesContrato($processo, $request->input('assinantes', []));

            return response()->json([
                'success' => true,
                'message' => 'Assinantes salvos com sucesso.'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao salvar assinantes da ata', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar assinantes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function salvarContratacoesSelecionadas(Request $request, Processo $processo)
    {
        try {
            $this->documentoService->salvarContratacoesSelecionadas($processo, $request->input('contratacoes_selecionadas', []));

            return response()->json([
                'success' => true,
                'message' => 'Contratações selecionadas salvas com sucesso.'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao salvar contratações selecionadas', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar contratações: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gerarESalvarAta(Processo $processo, Request $request)
    {
        try {
            $resultado = $this->pdfService->gerarESalvarContrato($processo, $request->all());

            return response()->json([
                'success' => true,
                'message' => '✅ Contrato gerado com sucesso! O download começará automaticamente.',
                'documento' => 'contrato',
                'download_url' => $resultado['download_url'],
                'refresh' => true,
                'auto_download' => true
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao gerar contrato', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ Erro ao gerar Contrato: ' . $e->getMessage()
            ], 500);
        }
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $prefeituraId = $request->get('prefeitura_id');

        if ($user->prefeitura_id) {
            $prefeituraId = $user->prefeitura_id;
            $prefeituras = Prefeitura::where('id', $prefeituraId)->get();
        } else {
            $prefeituras = Prefeitura::all();
        }

        $processos = $this->ataService->getProcessosParaDashboard($prefeituraId);
        $estatisticas = $this->ataService->calcularEstatisticas($processos);

        return view('Admin.Atas.dashboard', compact('processos', 'estatisticas', 'prefeituras', 'prefeituraId'));
    }

    public function downloadAta(Processo $processo, $nomeArquivo = null)
    {
        try {
            return $this->pdfService->downloadAta($processo, $nomeArquivo);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao baixar Ata', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return back()->with('error', 'Erro ao baixar Ata: ' . $e->getMessage());
        }
    }

    public function getContratacoesPendentes(Processo $processo)
    {
        try {
            // Usar o método que filtra contratações já usadas
            $contratacoes = $this->contratacaoService->getContratacoesPendentesNaoUsadas($processo);

            return response()->json([
                'success' => true,
                'contratacoes' => $contratacoes
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao obter contratações pendentes', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter contratações pendentes.'
            ], 500);
        }
    }

    public function getContratacoesAtualizadas(Processo $processo)
    {
        try {
            $resultado = $this->contratacaoService->getContratacoesAtualizadas($processo);

            return response()->json([
                'success' => true,
                'html' => $resultado['html'],
                'totalItens' => $resultado['totalItens'],
                'valorTotal' => $resultado['valorTotal']
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao obter contratações atualizadas', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter contratações atualizadas.'
            ], 500);
        }
    }

    public function debugContratos(Processo $processo)
    {
        $resultado = $this->documentoService->debugContratos($processo);

        return response()->json($resultado);
    }

    public function editContratacao(Processo $processo, \App\Models\LoteContratado $contratacao)
    {
        try {
            $resultado = $this->contratacaoService->getDadosParaEdicao($processo, $contratacao);

            return response()->json([
                'success' => true,
                'contratacao' => $resultado['contratacao'],
                'estoque' => $resultado['estoque'],
                'max_quantidade' => $resultado['max_quantidade']
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar dados para edição de contratação', [
                'processo_id' => $processo->id,
                'contratacao_id' => $contratacao->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados para edição: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateContratacao(Request $request, Processo $processo, \App\Models\LoteContratado $contratacao)
    {
        try {
            $resultado = $this->contratacaoService->atualizarContratacao($processo, $contratacao, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Contratação atualizada com sucesso!',
                'contratacao' => $resultado['contratacao'],
                'estoque' => $resultado['estoque']
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao atualizar contratação', [
                'processo_id' => $processo->id,
                'contratacao_id' => $contratacao->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar contratação: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyContratacao(Request $request, Processo $processo, \App\Models\LoteContratado $contratacao)
    {
        try {
            $resultado = $this->contratacaoService->excluirContratacao($processo, $contratacao);

            return response()->json([
                'success' => true,
                'message' => 'Contratação excluída com sucesso!',
                'estoque' => $resultado['estoque']
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao excluir contratação', [
                'processo_id' => $processo->id,
                'contratacao_id' => $contratacao->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir contratação: ' . $e->getMessage()
            ], 500);
        }
    }

    public function excluirTodasContratacoes(Request $request, Processo $processo)
    {
        try {
            $resultado = $this->contratacaoService->excluirTodasContratacoes($processo);

            return response()->json([
                'success' => true,
                'message' => $resultado['mensagem'],
                'excluidas' => $resultado['excluidas'],
                'erros' => $resultado['erros']
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao excluir todas as contratações', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir todas as contratações: ' . $e->getMessage()
            ], 500);
        }
    }

    public function desfazerContrato(Request $request, Processo $processo, \App\Models\Documento $documento)
    {
        try {
            $this->contratacaoService->desfazerContrato($processo, $documento);

            return response()->json([
                'success' => true,
                'message' => 'Contrato cancelado com sucesso! Todas as contratações voltaram ao status PENDENTE.',
                'refresh' => true
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao desfazer contrato', [
                'processo_id' => $processo->id,
                'documento_id' => $documento->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao desfazer contrato: ' . $e->getMessage()
            ], 500);
        }
    }
    public function exportarSaldoPdf(Processo $processo)
    {
        try {
            return $this->pdfService->gerarPdfSaldoDisponivel($processo);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao exportar PDF de saldo', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return back()->with('error', 'Erro ao gerar PDF: ' . $e->getMessage());
        }
    }
}
