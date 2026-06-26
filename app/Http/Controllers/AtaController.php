<?php

namespace App\Http\Controllers;

use App\Models\Processo;
use App\Models\Prefeitura;
use Illuminate\Http\Request;
use App\Services\AtaService;
use App\Services\AtaDocumentoService;
use App\Services\AtaContratacaoService;
use App\Services\AtaPdfService;

class AtaController extends AbstractController
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

            return $this->jsonOk([
                'itens' => $resultado['itens'],
                'total_contrato' => $resultado['total_contrato'],
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao obter itens do contrato', [
                'processo_id' => $processo->id,
                'documento_id' => $documentoId,
                'erro' => $e->getMessage()
            ]);

            return $this->jsonFail('Erro ao obter itens do contrato.', 500);
        }
    }

    public function getLotesDisponiveis(Processo $processo, $vencedorId)
    {
        return $this->tryJson(function () use ($processo, $vencedorId) {
            $resultado = $this->contratacaoService->getLotesDisponiveis($processo, $vencedorId);

            return $this->jsonOk([
                'lotes' => $resultado['lotes'],
                'vencedor' => $resultado['vencedor'],
            ]);
        }, 'Erro ao obter lotes disponíveis', 'Erro ao obter lotes disponíveis', ['processo_id' => $processo->id, 'vencedor_id' => $vencedorId]);
    }

    public function criarContratacaoDireta(Request $request, Processo $processo)
    {
        return $this->tryJson(function () use ($request, $processo) {
            $resultado = $this->contratacaoService->criarContratacaoDireta($processo, $request->all());

            return $this->jsonOk([
                'message' => 'Contratação criada com sucesso!',
                'contratacao' => $resultado['contratacao'],
            ]);
        }, 'Erro ao criar contratação', 'Erro ao criar contratação', ['processo_id' => $processo->id]);
    }

    public function marcarComoContratado(Request $request, Processo $processo)
    {
        return $this->tryJson(function () use ($request, $processo) {
            $this->contratacaoService->marcarComoContratado($processo, $request->input('contratacoes', []));

            return $this->jsonOk(['message' => 'Contratações marcadas como CONTRATADO!']);
        }, 'Erro ao marcar contratações como CONTRATADO', 'Erro ao atualizar status', ['processo_id' => $processo->id]);
    }

    public function salvarCampoContrato(Request $request, Processo $processo)
    {
        return $this->tryJson(function () use ($request, $processo) {
            $resultado = $this->documentoService->salvarCampoContrato($processo, $request->all());

            return $this->jsonOk([
                'message' => 'Campo salvo com sucesso.',
                'data' => $resultado,
            ]);
        }, 'Erro ao salvar campo da ata', 'Erro ao salvar campo', ['processo_id' => $processo->id]);
    }

    public function getDadosAta(Processo $processo)
    {
        try {
            $dados = $this->documentoService->getDadosContrato($processo);

            return $this->jsonOk(['dados' => $dados]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao obter dados do contrato/ata', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return $this->jsonFail('Erro ao obter dados.', 500);
        }
    }

    public function salvarAssinantesAta(Request $request, Processo $processo)
    {
        return $this->tryJson(function () use ($request, $processo) {
            $this->documentoService->salvarAssinantesContrato($processo, $request->input('assinantes', []));

            return $this->jsonOk(['message' => 'Assinantes salvos com sucesso.']);
        }, 'Erro ao salvar assinantes da ata', 'Erro ao salvar assinantes', ['processo_id' => $processo->id]);
    }

    public function salvarContratacoesSelecionadas(Request $request, Processo $processo)
    {
        return $this->tryJson(function () use ($request, $processo) {
            $this->documentoService->salvarContratacoesSelecionadas($processo, $request->input('contratacoes_selecionadas', []));

            return $this->jsonOk(['message' => 'Contratações selecionadas salvas com sucesso.']);
        }, 'Erro ao salvar contratações selecionadas', 'Erro ao salvar contratações', ['processo_id' => $processo->id]);
    }

    public function gerarESalvarAta(Processo $processo, Request $request)
    {
        return $this->tryJson(function () use ($request, $processo) {
            $resultado = $this->pdfService->gerarESalvarContrato($processo, $request->all());

            return $this->jsonOk([
                'message' => '✅ Contrato gerado com sucesso! O download começará automaticamente.',
                'documento' => 'contrato',
                'download_url' => $resultado['download_url'],
                'refresh' => true,
                'auto_download' => true,
            ]);
        }, 'Erro ao gerar contrato', '❌ Erro ao gerar Contrato', ['processo_id' => $processo->id]);
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

            return $this->jsonOk(['contratacoes' => $contratacoes]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao obter contratações pendentes', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return $this->jsonFail('Erro ao obter contratações pendentes.', 500);
        }
    }

    public function getContratacoesAtualizadas(Processo $processo)
    {
        try {
            $resultado = $this->contratacaoService->getContratacoesAtualizadas($processo);

            return $this->jsonOk([
                'html' => $resultado['html'],
                'totalItens' => $resultado['totalItens'],
                'valorTotal' => $resultado['valorTotal'],
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao obter contratações atualizadas', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return $this->jsonFail('Erro ao obter contratações atualizadas.', 500);
        }
    }

    public function debugContratos(Processo $processo)
    {
        $resultado = $this->documentoService->debugContratos($processo);

        return response()->json($resultado);
    }

    public function editContratacao(Processo $processo, \App\Models\LoteContratado $contratacao)
    {
        return $this->tryJson(function () use ($processo, $contratacao) {
            $resultado = $this->contratacaoService->getDadosParaEdicao($processo, $contratacao);

            return $this->jsonOk([
                'contratacao' => $resultado['contratacao'],
                'estoque' => $resultado['estoque'],
                'max_quantidade' => $resultado['max_quantidade'],
            ]);
        }, 'Erro ao buscar dados para edição de contratação', 'Erro ao carregar dados para edição', ['processo_id' => $processo->id, 'contratacao_id' => $contratacao->id]);
    }

    public function updateContratacao(Request $request, Processo $processo, \App\Models\LoteContratado $contratacao)
    {
        return $this->tryJson(function () use ($request, $processo, $contratacao) {
            $resultado = $this->contratacaoService->atualizarContratacao($processo, $contratacao, $request->all());

            return $this->jsonOk([
                'message' => 'Contratação atualizada com sucesso!',
                'contratacao' => $resultado['contratacao'],
                'estoque' => $resultado['estoque'],
            ]);
        }, 'Erro ao atualizar contratação', 'Erro ao atualizar contratação', ['processo_id' => $processo->id, 'contratacao_id' => $contratacao->id]);
    }

    public function destroyContratacao(Request $request, Processo $processo, \App\Models\LoteContratado $contratacao)
    {
        return $this->tryJson(function () use ($processo, $contratacao) {
            $resultado = $this->contratacaoService->excluirContratacao($processo, $contratacao);

            return $this->jsonOk([
                'message' => 'Contratação excluída com sucesso!',
                'estoque' => $resultado['estoque'],
            ]);
        }, 'Erro ao excluir contratação', 'Erro ao excluir contratação', ['processo_id' => $processo->id, 'contratacao_id' => $contratacao->id]);
    }

    public function excluirTodasContratacoes(Request $request, Processo $processo)
    {
        return $this->tryJson(function () use ($processo) {
            $resultado = $this->contratacaoService->excluirTodasContratacoes($processo);

            return $this->jsonOk([
                'message' => $resultado['mensagem'],
                'excluidas' => $resultado['excluidas'],
                'erros' => $resultado['erros'],
            ]);
        }, 'Erro ao excluir todas as contratações', 'Erro ao excluir todas as contratações', ['processo_id' => $processo->id]);
    }

    public function desfazerContrato(Request $request, Processo $processo, \App\Models\Documento $documento)
    {
        return $this->tryJson(function () use ($processo, $documento) {
            $this->contratacaoService->desfazerContrato($processo, $documento);

            return $this->jsonOk([
                'message' => 'Contrato cancelado com sucesso! Todas as contratações voltaram ao status PENDENTE.',
                'refresh' => true,
            ]);
        }, 'Erro ao desfazer contrato', 'Erro ao desfazer contrato', ['processo_id' => $processo->id, 'documento_id' => $documento->id]);
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
