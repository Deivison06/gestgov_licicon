<?php

namespace App\Http\Controllers;

use App\Models\Homologacao;
use App\Models\HomologacaoDesistencia;
use App\Models\HomologacaoDesistenciaAnexo;
use App\Models\Processo;
use App\Models\Vencedor;
use App\Services\FinalizacaoDocumentoService;
use App\Services\FinalizacaoPdfService;
use App\Services\FinalizacaoService;
use App\Services\FinalizacaoVencedorService;
use App\Services\HomologacaoDesistenciaService;
use App\Services\HomologacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FinalizacaoProcessoController extends AbstractController
{
    protected FinalizacaoService $finalizacaoService;

    protected FinalizacaoDocumentoService $documentoService;

    protected FinalizacaoPdfService $pdfService;

    protected FinalizacaoVencedorService $vencedorService;

    protected HomologacaoService $homologacaoService;

    protected HomologacaoDesistenciaService $desistenciaService;

    public function __construct(
        FinalizacaoService $finalizacaoService,
        FinalizacaoDocumentoService $documentoService,
        FinalizacaoPdfService $pdfService,
        FinalizacaoVencedorService $vencedorService,
        HomologacaoService $homologacaoService,
        HomologacaoDesistenciaService $desistenciaService
    ) {
        $this->finalizacaoService = $finalizacaoService;
        $this->documentoService = $documentoService;
        $this->pdfService = $pdfService;
        $this->vencedorService = $vencedorService;
        $this->homologacaoService = $homologacaoService;
        $this->desistenciaService = $desistenciaService;
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
            'homologacoes.desistencias.vencedor',
            'homologacoes.desistencias.anexos',
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
        $homologacaoUnica = $ehHomologacaoUnica
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

            return $this->jsonOk([
                'message' => "Homologação #{$homologacao->numero_sequencial} criada com sucesso.",
                'homologacao' => $homologacao,
            ]);
        } catch (\DomainException $e) {
            return $this->jsonFail($e->getMessage(), 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao gerar nova homologação', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
            ]);

            return $this->jsonFail('Erro ao criar homologação: '.$e->getMessage(), 500);
        }
    }

    public function deletarHomologacao(Processo $processo, \App\Models\Homologacao $homologacao)
    {
        try {
            if ($homologacao->processo_id !== $processo->id) {
                return $this->jsonFail('Homologação não pertence a este processo.', 403);
            }

            $this->homologacaoService->deletarHomologacao($homologacao);

            return $this->jsonOk([
                'message' => "Homologação #{$homologacao->numero_sequencial} excluída com sucesso.",
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao excluir homologação', [
                'homologacao_id' => $homologacao->id,
                'erro' => $e->getMessage(),
            ]);

            return $this->jsonFail('Erro ao excluir homologação: '.$e->getMessage(), 500);
        }
    }

    public function registrarDesistencia(Request $request, Processo $processo, Homologacao $homologacao)
    {
        if ($homologacao->processo_id !== $processo->id) {
            return $this->jsonFail('Homologação não pertence a este processo.', 403);
        }

        $validated = $request->validate([
            'vencedor_id' => 'required|exists:vencedores,id',
            'data_solicitacao_assinatura' => 'required|date',
            'data_decisao' => 'required|date',
            'observacao' => 'nullable|string',
            'anexos' => 'required|array|min:1',
            'anexos.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        try {
            $vencedor = Vencedor::where('processo_id', $processo->id)->findOrFail($validated['vencedor_id']);

            $desistencia = $this->desistenciaService->registrar(
                $homologacao,
                $vencedor,
                $validated,
                $request->file('anexos')
            );

            return $this->jsonOk([
                'message' => 'Desistência registrada com sucesso. O saldo dos lotes foi zerado.',
                'desistencia' => $desistencia->load('anexos'),
            ]);
        } catch (\DomainException $e) {
            return $this->jsonFail($e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('Erro ao registrar desistência', [
                'processo_id' => $processo->id,
                'homologacao_id' => $homologacao->id,
                'erro' => $e->getMessage(),
            ]);

            return $this->jsonFail('Erro ao registrar desistência: '.$e->getMessage(), 500);
        }
    }

    public function atualizarDesistencia(Request $request, Processo $processo, Homologacao $homologacao, HomologacaoDesistencia $desistencia)
    {
        if ($homologacao->processo_id !== $processo->id || $desistencia->homologacao_id !== $homologacao->id) {
            return $this->jsonFail('Desistência não pertence a este processo/homologação.', 403);
        }

        $validated = $request->validate([
            'data_solicitacao_assinatura' => 'required|date',
            'data_decisao' => 'required|date',
            'observacao' => 'nullable|string',
        ]);

        try {
            $desistencia = $this->desistenciaService->atualizar($homologacao, $desistencia, $validated);

            return $this->jsonOk([
                'message' => 'Desistência atualizada e Termo de Decisão regerado com sucesso.',
                'desistencia' => $desistencia,
            ]);
        } catch (\DomainException $e) {
            return $this->jsonFail($e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar desistência', [
                'processo_id' => $processo->id,
                'homologacao_id' => $homologacao->id,
                'desistencia_id' => $desistencia->id,
                'erro' => $e->getMessage(),
            ]);

            return $this->jsonFail('Erro ao atualizar desistência: '.$e->getMessage(), 500);
        }
    }

    public function baixarPdfDesistencia(Processo $processo, Homologacao $homologacao, HomologacaoDesistencia $desistencia)
    {
        if ($homologacao->processo_id !== $processo->id || $desistencia->homologacao_id !== $homologacao->id) {
            abort(403, 'Desistência não pertence a este processo/homologação.');
        }

        if (! $desistencia->caminho_pdf || ! file_exists(public_path($desistencia->caminho_pdf))) {
            return redirect()
                ->route('admin.processos.finalizacao.finalizar', $processo)
                ->with('error', 'O Termo de Registro e Decisão Administrativa não foi encontrado no servidor.');
        }

        return response()->download(public_path($desistencia->caminho_pdf));
    }

    public function baixarAnexoDesistencia(Processo $processo, Homologacao $homologacao, HomologacaoDesistencia $desistencia, HomologacaoDesistenciaAnexo $anexo)
    {
        if ($homologacao->processo_id !== $processo->id
            || $desistencia->homologacao_id !== $homologacao->id
            || $anexo->homologacao_desistencia_id !== $desistencia->id) {
            abort(403, 'Anexo não pertence a esta desistência.');
        }

        if (! file_exists(public_path($anexo->caminho))) {
            return redirect()
                ->route('admin.processos.finalizacao.finalizar', $processo)
                ->with('error', 'Anexo não encontrado no servidor.');
        }

        return response()->download(public_path($anexo->caminho), $anexo->nome_original ?: basename($anexo->caminho));
    }

    public function storeFinalizacao(Request $request, Processo $processo)
    {
        return $this->tryJson(
            fn () => $this->jsonOk([
                'data' => $this->finalizacaoService->salvarFinalizacao($processo, $request->all())->toArray(),
            ]),
            'Erro ao salvar finalizacao do processo',
            'Erro ao salvar os dados',
            ['processo_id' => $processo->id],
        );
    }

    public function storeVencedores(Request $request, Processo $processo)
    {
        return $this->tryJson(function () use ($request, $processo) {
            $this->vencedorService->salvarVencedores($processo, $request->all());

            $processo->load('vencedores.lotes');

            return $this->jsonOk([
                'message' => 'Vencedor salvo com sucesso!',
                'vencedores' => $processo->vencedores,
            ]);
        }, 'Erro ao salvar vencedor', 'Erro ao salvar vencedor', ['processo_id' => $processo->id]);
    }

    public function importarExcel(Request $request, Processo $processo)
    {
        return $this->tryJson(function () use ($request, $processo) {
            $resultado = $this->vencedorService->importarExcel($processo, $request->all());

            return $this->jsonOk([
                'message' => '✅ Arquivo processado com sucesso! '.count($resultado['lotes']).' itens importados.',
                'lotes' => $resultado['lotes'],
                'vencedor' => $resultado['vencedor'],
            ]);
        }, 'Erro ao importar Excel', 'Erro ao importar arquivo', ['processo_id' => $processo->id]);
    }

    public function getVencedores(Processo $processo)
    {
        try {
            return $this->jsonOk([
                'vencedores' => $this->vencedorService->getVencedores($processo),
            ]);
        } catch (\Exception $e) {
            return $this->jsonFail('Erro ao buscar vencedores: '.$e->getMessage(), 500);
        }
    }

    public function gerarPdf(Request $request, Processo $processo)
    {
        return $this->tryJson(function () use ($request, $processo) {
            $this->pdfService->gerarPdf($processo, $request->all());

            return $this->jsonOk([
                'message' => '✅ PDF gerado com sucesso! Clique em "Download" para visualizar o arquivo.',
                'documento' => $request->query('documento'),
            ]);
        }, 'Erro ao gerar PDF - Finalização', '❌ Ocorreu um erro inesperado ao gerar o PDF', ['processo_id' => $processo->id]);
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
        return $this->dispararDownloadEmLote($processo->id, 'finalizar');
    }
}
