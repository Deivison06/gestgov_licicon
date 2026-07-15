<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Homologacao;
use App\Models\Processo;
use App\Models\Documento;
use Illuminate\Http\Request;
use App\Enums\ModalidadeEnum;
use setasign\Fpdi\Tcpdf\Fpdi;
use App\Models\LoteContratado;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Enums\TipoProcedimentoEnum;
use Illuminate\Support\Facades\Log;
use \App\Services\Assinatura\ResolveLegacyAssinantesTrait;

class ContratoProcessoController extends Controller
{
    // Campos do snapshot de contratante persistidos por contrato (sobrescrevem os
    // dados globais do processo apenas para aquele contrato específico).
    private const CAMPOS_CONTRATANTE = [
        'orgao_responsavel',
        'cargo_responsavel',
        'cnpj',
        'endereco',
        'responsavel',
        'cpf_responsavel',
        'razao_social',
    ];
    

    // Configuração única para contrato
    protected $documentoConfig = [
        'contrato' => [
            'titulo' => 'CONTRATO',
            'cor' => 'bg-blue-500',
            'campos' => ['numero_contrato', 'data_assinatura_contrato', 'numero_extrato', 'comarca', 'fonte_recurso', 'subcontratacao'],
            'requer_assinatura' => true,
        ]
    ];

    public function contrato(Processo $processo)
    {
        $processo->load([
            'prefeitura.unidades',
            'detalhe',
            'vencedores.lotes.contratados',
            'homologacoes.lotes.contratados',
            'homologacoes.contrato',
            'contratos',
            'finalizacao',
        ]);

        // Homologações já concluídas — únicas que liberam o painel de contrato.
        $homologacoesHomologadas = $processo->homologacoes
            ->where('status', Homologacao::STATUS_HOMOLOGADA)
            ->values();

        // Contrato legado: registro sem vínculo a homologação (processos antigos).
        $contratoLegado = Contrato::where('processo_id', $processo->id)
            ->whereNull('homologacao_id')
            ->first();

        // Múltiplos contratos por processo (um por secretaria), agrupados por homologação
        // (chave string do id, ou 'legado' para os sem homologação) para a listagem na tela.
        $contratosPorHomologacao = $processo->contratos
            ->sortBy('numero_sequencial')
            ->groupBy(fn ($c) => $c->homologacao_id ? (string) $c->homologacao_id : 'legado');

        // Dados padrão do contratante por homologação, para pré-preencher o formulário de
        // "novo contrato" (o usuário pode editar antes de gerar). Inclui a chave 'legado'.
        $contratantePadraoPorHomologacao = [];
        foreach ($homologacoesHomologadas as $homol) {
            $contratantePadraoPorHomologacao[(string) $homol->id] = $this->montarContratantePadrao($processo, $homol);
        }
        $contratantePadraoPorHomologacao['legado'] = $this->montarContratantePadrao($processo, null);

        // Contratações agrupadas por vencedor — modo legado / topo da tela.
        $contratacoes = LoteContratado::where('processo_id', $processo->id)
            ->with(['lote', 'vencedor'])
            ->get()
            ->groupBy('vencedor_id');

        // Para cada homologação concluída, prepara a lista de contratações
        // restringida aos lotes daquela homologação.
        $contratacoesPorHomologacao = [];
        foreach ($homologacoesHomologadas as $homol) {
            $loteIds = $homol->lotes->pluck('id');
            $contratacoesPorHomologacao[$homol->id] = LoteContratado::where('processo_id', $processo->id)
                ->whereIn('lote_id', $loteIds)
                ->with(['lote', 'vencedor'])
                ->get()
                ->groupBy('vencedor_id');
        }

        $documentos = $this->documentoConfig;

        return view('Admin.Processos.contrato', compact(
            'processo',
            'documentos',
            'contratoLegado',
            'contratacoes',
            'homologacoesHomologadas',
            'contratacoesPorHomologacao',
            'contratosPorHomologacao',
            'contratantePadraoPorHomologacao'
        ));
    }

    /**
     * Resolve a homologação alvo a partir da request (query string
     * `?homologacao_id=N`). Valida que pertence ao processo informado.
     */
    private function resolverHomologacao(Processo $processo, Request $request): ?Homologacao
    {
        $homologacaoId = $request->input('homologacao_id') ?: $request->query('homologacao_id');

        if (!$homologacaoId) {
            return null;
        }

        $homologacao = Homologacao::where('processo_id', $processo->id)
            ->where('id', $homologacaoId)
            ->first();

        if (!$homologacao) {
            throw new \DomainException('Homologação não pertence a este processo.');
        }

        if ($homologacao->status !== Homologacao::STATUS_HOMOLOGADA) {
            throw new \DomainException('Só é possível gerar contrato de uma homologação já HOMOLOGADA.');
        }

        return $homologacao;
    }

    /**
     * Salvar campo individual do contrato
     */
    public function salvarCampoContrato(Request $request, Processo $processo)
    {
        try {
            $request->validate([
                'campo' => 'required|string',
                'valor' => 'nullable|string'
            ]);

            $campo = $request->input('campo');
            $valor = $request->input('valor');
            $homologacao = $this->resolverHomologacao($processo, $request);
            $homologacaoId = $homologacao?->id;

            // Verificar se o campo é válido
            $camposPermitidos = [
                'numero_contrato',
                'data_assinatura_contrato',
                'numero_extrato',
                'comarca',
                'fonte_recurso',
                'subcontratacao'
            ];

            if (strpos($campo, 'data_doc_') === 0) {
                $tipoDocumento = substr($campo, 9);
                Documento::updateOrCreate(
                    [
                        'processo_id' => $processo->id,
                        'tipo_documento' => $tipoDocumento,
                        'homologacao_id' => $homologacaoId,
                    ],
                    ['data_selecionada' => $valor]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Data do documento salva com sucesso.',
                    'data' => [$campo => $valor]
                ]);
            }

            if (!in_array($campo, $camposPermitidos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campo não permitido.'
                ], 400);
            }

            // Verificar se já existe um contrato para este processo+homologação
            $contrato = Contrato::where('processo_id', $processo->id)
                ->where('homologacao_id', $homologacaoId)
                ->first();

            if (!$contrato) {
                $contrato = Contrato::create([
                    'processo_id' => $processo->id,
                    'homologacao_id' => $homologacaoId,
                ]);
            }

            // Processar campo específico
            if ($campo === 'data_assinatura_contrato' && $valor) {
                $valor = \Carbon\Carbon::parse($valor)->format('Y-m-d');
            }

            // Não atualizar campos únicos em contratos já existentes (evita sobrescrever o número do Contrato 1 ao tentar gerar o Contrato 2)
            $camposUnicos = ['numero_contrato', 'numero_extrato', 'data_assinatura_contrato'];
            if (!in_array($campo, $camposUnicos) || $contrato->wasRecentlyCreated) {
                // Atualizar o campo
                $contrato->update([$campo => $valor]);
            }

            Log::info('Campo do contrato salvo com sucesso', [
                'processo_id' => $processo->id,
                'campo' => $campo,
                'valor' => $valor
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Campo salvo com sucesso.',
                'data' => [$campo => $valor]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao salvar campo do contrato', [
                'processo_id' => $processo->id,
                'campo' => $request->input('campo'),
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar campo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter dados salvos do contrato
     */
    public function obterDadosContrato(Request $request, Processo $processo)
    {
        try {
            $homologacao = $this->resolverHomologacao($processo, $request);
            $homologacaoId = $homologacao?->id;

            $contrato = Contrato::where('processo_id', $processo->id)
                ->where('homologacao_id', $homologacaoId)
                ->first();

            if (!$contrato) {
                return response()->json([
                    'success' => true,
                    'dados' => []
                ]);
            }

            return response()->json([
                'success' => true,
                'dados' => [
                    // Campos únicos removidos para não pré-preencher o novo contrato com os dados do anterior
                    'comarca' => $contrato->comarca,
                    'fonte_recurso' => $contrato->fonte_recurso,
                    'subcontratacao' => $contrato->subcontratacao,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter dados do contrato', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados do contrato.'
            ], 500);
        }
    }

    /**
     * Exclui um contrato gerado: remove o registro e o PDF próprio. NÃO altera as
     * contratações/saldo — os itens são devolvidos pela opção "remover contratação".
     */
    public function destroyContrato(Processo $processo, Contrato $contrato)
    {
        try {
            if ($contrato->processo_id != $processo->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrato não pertence a este processo.'
                ], 403);
            }

            if ($contrato->caminho) {
                $arquivo = public_path($contrato->caminho);
                if (file_exists($arquivo)) {
                    unlink($arquivo);
                }

                // Remove também o registro em `documentos` (montagem do processo) que
                // aponte para o mesmo arquivo, para não referenciar um PDF inexistente.
                Documento::where('processo_id', $processo->id)
                    ->where('tipo_documento', 'contrato')
                    ->where('caminho', $contrato->caminho)
                    ->delete();
            }

            // Desvincula as contratações deste contrato (mantém os itens e o saldo;
            // apenas libera o vínculo para que possam ser reaproveitados em um novo
            // contrato). NÃO altera EstoqueLote — isso é feito por "remover contratação".
            \App\Models\LoteContratado::where('processo_id', $processo->id)
                ->where('contrato_id', $contrato->id)
                ->update(['contrato_id' => null]);

            $numero = $contrato->numero_sequencial;
            $contrato->delete();

            Log::info('Contrato excluído', [
                'processo_id' => $processo->id,
                'contrato_id' => $contrato->id,
                'numero_sequencial' => $numero,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Contrato {$numero} excluído com sucesso.",
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir contrato', [
                'processo_id' => $processo->id,
                'contrato_id' => $contrato->id ?? null,
                'erro' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir contrato: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Gera o PDF do contrato
     */
    public function gerarPdf(
        Request $request,
        Processo $processo,
        \App\Services\Assinatura\PdfWatermarkService $watermarkService,
        \App\Services\Assinatura\DocumentoVersaoService $versaoService,
        \App\Services\Assinatura\SolicitacaoService $solicitacaoService
    ) {
        try {
            Log::info('Iniciando geração de PDF - Contrato', [
                'processo_id' => $processo->id,
                'request_data' => $request->all()
            ]);

            $homologacao = $this->resolverHomologacao($processo, $request);
            $validatedData = $this->validarRequisicaoPdf($request, $processo, $homologacao);
            $validatedData['homologacao'] = $homologacao;

            // Cada geração cria um NOVO contrato (Contrato 1, 2, 3...), salvo quando o
            // frontend envia contrato_id para regerar um existente. O snapshot do
            // contratante e os campos preenchidos no modal são persistidos no contrato.
            $contratoId = $request->input('contrato_id') ?: $request->query('contrato_id');
            $contrato = $this->resolverOuCriarContrato($processo, $homologacao, $contratoId ? (int) $contratoId : null);
            $contratoNovo = $contrato->wasRecentlyCreated;
            $this->persistirDadosContrato($contrato, $processo, $homologacao, $validatedData['campos'], $validatedData['contratante']);

            // Vincula a este contrato APENAS as contratações escolhidas na tela (quando
            // enviadas) — isolando o escopo de itens por contrato. Sem seleção, mantém o
            // comportamento legado (contratações do primeiro vencedor da homologação).
            $this->vincularContratacoesAoContrato($processo, $homologacao, $contrato, $contratoNovo, $validatedData['contratacoes'] ?? []);

            $validatedData['contrato'] = $contrato;

            $data = $this->prepararDadosPdf($processo, $validatedData);

            $view = $this->determinarViewContrato($processo);

            Log::info('View selecionada para contrato', ['view' => $view]);

            $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');

            $caminhoCompleto = $this->salvarDocumento($processo, $pdf, $validatedData);

            Log::info('Contrato gerado com sucesso', [
                'processo_id' => $processo->id,
                'homologacao_id' => $homologacao?->id,
                'contrato_id' => $contrato->id,
                'numero_sequencial' => $contrato->numero_sequencial,
                'caminho' => $caminhoCompleto
            ]);

            return response()->json([
                'success' => true,
                'message' => '✅ Contrato gerado com sucesso! Clique em "Download" para visualizar o arquivo.',
                'documento' => 'contrato',
                'contrato_id' => $contrato->id,
                'numero_sequencial' => $contrato->numero_sequencial,
            ]);
            // =====================================================================
            // Auto-trigger DESATIVADO — a rodada de assinatura é disparada apenas
            // via "Solicitar Assinatura" (endpoint /solicitar-assinatura).
            // Gerar PDF agora SEMPRE só renderiza o arquivo.
            // =====================================================================
            $assinantes = [];
            $infoAssinatura = null;

            if (count($assinantes) > 0) {
                $infoAssinatura = $this->iniciarRodadaAssinatura(
                    $processo,
                    $homologacao?->id,
                    $caminhoCompleto,
                    $request->input('modo', 'paralelo'),
                    (int) $request->input('prazo_dias', 7),
                    $assinantes,
                    $request->user()->id,
                    $watermarkService,
                    $versaoService,
                    $solicitacaoService
                );
            }

            return response()->json(array_filter([
                'success'   => true,
                'message'   => $infoAssinatura
                    ? '✅ Contrato gerado e enviado para assinatura.'
                    : '✅ Contrato gerado com sucesso! Clique em "Download" para visualizar o arquivo.',
                'documento' => 'contrato',
                'assinatura' => $infoAssinatura,
            ], fn ($v) => $v !== null));

        } catch (\DomainException $e) {
            Log::warning('Geração de contrato bloqueada', [
                'processo_id' => $processo->id,
                'motivo' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Erro ao gerar contrato', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'linha' => $e->getLine(),
                'arquivo' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ Ocorreu um erro inesperado ao gerar o contrato: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Orquestra: aplica marca d'água no PDF, cria versão, cria rodada de solicitações.
     * Idempotente em caso de exceção parcial (transação no Service).
     *
     * @return array{versao_id:int, total_solicitacoes:int, modo:string, prazo_dias:int}
     */
    private function iniciarRodadaAssinatura(
        Processo $processo,
        ?int $homologacaoId,
        string $caminhoPdfOriginal,
        string $modo,
        int $prazoDias,
        array $assinantes,
        int $solicitadoPorUserId,
        \App\Services\Assinatura\PdfWatermarkService $watermarkService,
        \App\Services\Assinatura\DocumentoVersaoService $versaoService,
        \App\Services\Assinatura\SolicitacaoService $solicitacaoService
    ): array {
        // 1) Marca d'água
        $caminhoRascunho = $watermarkService->aplicarMarcaDagua(
            $caminhoPdfOriginal,
            'AGUARDANDO ASSINATURAS'
        );

        // 2) Documentavel: usamos o Contrato persistido (não o processo) para que cada
        //    contrato tenha sua própria árvore de versões.
        $contrato = Contrato::where('processo_id', $processo->id)
            ->where('homologacao_id', $homologacaoId)
            ->latest('id')
            ->firstOrFail();

        // 3) Cria versão
        $versao = $versaoService->criarRascunho(
            $contrato,
            $caminhoRascunho,
            $solicitadoPorUserId
        );

        // 4) Monta lista normalizada para o SolicitacaoService
        $listaAssinantes = collect($assinantes)
            ->map(function ($a, $idx) use ($modo) {
                return [
                    'user_id' => (int) ($a['id'] ?? $a['user_id'] ?? 0),
                    'ordem'   => $modo === 'sequencial' ? (int) ($a['ordem'] ?? $idx + 1) : 0,
                ];
            })
            ->filter(fn ($a) => $a['user_id'] > 0)
            ->values()
            ->all();

        // 5) Cria rodada
        $solicitacoes = $solicitacaoService->criarRodada(
            $versao,
            $listaAssinantes,
            $solicitadoPorUserId,
            now()->addDays(max(1, $prazoDias))
        );

        return [
            'versao_id'          => $versao->id,
            'total_solicitacoes' => $solicitacoes->count(),
            'modo'               => $modo,
            'prazo_dias'         => $prazoDias,
        ];
    }

    /**
     * Salva ou atualiza os campos do contrato no banco de dados.
     * Quando $homologacaoId é informado, o upsert é feito por (processo, homologação).
     */
    private function salvarCamposContrato($processoId, array $campos, ?int $homologacaoId = null): void
    {
        try {
            $contrato = Contrato::where('processo_id', $processoId)
                ->where('homologacao_id', $homologacaoId)
                ->first();

            $dadosContrato = [
                'processo_id' => $processoId,
                'homologacao_id' => $homologacaoId,
                'numero_contrato' => $campos['numero_contrato'] ?? null,
                'data_assinatura_contrato' => !empty($campos['data_assinatura_contrato'])
                    ? \Carbon\Carbon::parse($campos['data_assinatura_contrato'])->format('Y-m-d')
                    : null,
                'numero_extrato' => $campos['numero_extrato'] ?? null,
                'comarca' => $campos['comarca'] ?? null,
                'fonte_recurso' => $campos['fonte_recurso'] ?? null,
                'subcontratacao' => $campos['subcontratacao'] ?? null,
            ];

            if ($contrato) {
                $contrato->update($dadosContrato);
                Log::info('Contrato atualizado com sucesso', [
                    'processo_id' => $processoId,
                    'homologacao_id' => $homologacaoId,
                    'campos' => $dadosContrato
                ]);
            } else {
                Contrato::create($dadosContrato);
                Log::info('Contrato criado com sucesso', [
                    'processo_id' => $processoId,
                    'homologacao_id' => $homologacaoId,
                    'campos' => $dadosContrato
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao salvar campos do contrato', [
                'processo_id' => $processoId,
                'homologacao_id' => $homologacaoId,
                'erro' => $e->getMessage(),
                'campos' => $campos
            ]);
            throw $e;
        }
    }

    /**
     * Carrega o contrato informado (validando o vínculo ao processo) ou cria um novo
     * com o próximo numero_sequencial do processo.
     */
    private function resolverOuCriarContrato(Processo $processo, ?Homologacao $homologacao, ?int $contratoId): Contrato
    {
        if ($contratoId) {
            $contrato = Contrato::where('processo_id', $processo->id)
                ->where('id', $contratoId)
                ->first();

            if (!$contrato) {
                throw new \DomainException('Contrato não pertence a este processo.');
            }

            return $contrato;
        }

        $proximoSequencial = (int) Contrato::where('processo_id', $processo->id)->max('numero_sequencial') + 1;

        return Contrato::create([
            'processo_id' => $processo->id,
            'homologacao_id' => $homologacao?->id,
            'numero_sequencial' => $proximoSequencial,
        ]);
    }

    /**
     * Persiste os campos e o snapshot do contratante no contrato. O snapshot usa os
     * valores informados no modal e cai para os dados padrão do processo quando vazios.
     */
    private function persistirDadosContrato(
        Contrato $contrato,
        Processo $processo,
        ?Homologacao $homologacao,
        array $campos,
        array $contratante
    ): void {
        $padrao = $this->montarContratantePadrao($processo, $homologacao);

        $snapshot = [];
        foreach (self::CAMPOS_CONTRATANTE as $campo) {
            $valor = $contratante[$campo] ?? null;
            $snapshot[$campo] = ($valor !== null && $valor !== '')
                ? $valor
                : ($padrao[$campo] ?? null);
        }

        $contrato->update([
            'numero_contrato' => $campos['numero_contrato'] ?? $contrato->numero_contrato,
            'data_assinatura_contrato' => !empty($campos['data_assinatura_contrato'])
                ? \Carbon\Carbon::parse($campos['data_assinatura_contrato'])->format('Y-m-d')
                : $contrato->data_assinatura_contrato,
            'numero_extrato' => $campos['numero_extrato'] ?? $contrato->numero_extrato,
            'comarca' => $campos['comarca'] ?? $contrato->comarca,
            'fonte_recurso' => $campos['fonte_recurso'] ?? $contrato->fonte_recurso,
            'subcontratacao' => $campos['subcontratacao'] ?? $contrato->subcontratacao,
            'dados_contratante' => $snapshot,
        ]);
    }

    /**
     * Vincula as contratações (itens) a um contrato específico, isolando o escopo de
     * itens por contrato.
     *
     * - Contrato NOVO: "reivindica" as contratações ainda não vinculadas a nenhum
     *   contrato (as que foram contratadas para esta geração), escopadas à homologação.
     * - Regeração (contrato já existente): mantém exatamente os itens já vinculados —
     *   não captura novos, para não roubar itens preparados para um próximo contrato.
     */
    private function vincularContratacoesAoContrato(
        Processo $processo,
        ?Homologacao $homologacao,
        Contrato $contrato,
        bool $contratoNovo,
        array $contratacaoIds = []
    ): void {
        if (!$contratoNovo) {
            return;
        }

        // Novo comportamento: quando a tela envia as contratações selecionadas, vincula
        // APENAS essas (e só as que ainda não pertencem a nenhum contrato). Assim cada
        // contrato contém exatamente as contratações escolhidas.
        if (!empty($contratacaoIds)) {
            LoteContratado::where('processo_id', $processo->id)
                ->whereIn('id', $contratacaoIds)
                ->whereNull('contrato_id')
                ->update(['contrato_id' => $contrato->id, 'status' => 'CONTRATADO']);
            return;
        }

        // Compatibilidade (sem seleção): comportamento legado.
        $query = LoteContratado::where('processo_id', $processo->id)
            ->whereNull('contrato_id');

        if ($homologacao) {
            $homologacao->loadMissing('lotes');
            $query->whereIn('lote_id', $homologacao->lotes->pluck('id'));
        }

        $primeiroVencedorId = (clone $query)->value('vencedor_id');

        if ($primeiroVencedorId) {
            $query->where('vencedor_id', $primeiroVencedorId)->update(['contrato_id' => $contrato->id, 'status' => 'CONTRATADO']);
        } else {
            $query->update(['contrato_id' => $contrato->id, 'status' => 'CONTRATADO']);
        }
    }

    /**
     * Monta os dados padrão do contratante (7 campos) a partir da fonte de cabeçalho
     * (Homologação > Finalização) com fallback para a Prefeitura. Usado para pré-preencher
     * o formulário e para completar o snapshot quando o usuário deixa campos em branco.
     */
    private function montarContratantePadrao(Processo $processo, ?Homologacao $homologacao): array
    {
        $fonte = $this->mesclarFonteCabecalho($homologacao, $processo->finalizacao);
        $prefeitura = $processo->prefeitura;

        return [
            'orgao_responsavel' => $fonte->orgao_responsavel ?? $prefeitura?->cidade,
            'cargo_responsavel' => $fonte->cargo_responsavel ?? 'Prefeito Municipal',
            'cnpj' => $fonte->cnpj ?? $prefeitura?->cnpj,
            'endereco' => $fonte->endereco ?? $prefeitura?->endereco,
            'responsavel' => $fonte->responsavel ?? $prefeitura?->autoridade_competente,
            'cpf_responsavel' => $fonte->cpf_responsavel,
            'razao_social' => $fonte->razao_social,
        ];
    }

    /**
     * Download do contrato COM CARIMBO
     */
    public function baixarContrato(Request $request, Processo $processo)
    {
        try {
            // Download por contrato específico (novo fluxo de múltiplos contratos).
            $contratoId = $request->query('contrato_id');
            $documento = null; // Inicializa para evitar "Undefined variable" no fluxo por contrato_id
            if ($contratoId) {
                $contrato = Contrato::where('processo_id', $processo->id)
                    ->where('id', $contratoId)
                    ->firstOrFail();

                if (!$contrato->caminho) {
                    throw new \Exception('Este contrato ainda não possui PDF gerado.');
                }

                $caminhoOriginal = public_path($contrato->caminho);
            } else {
                // Compatibilidade: download do contrato registrado em `documentos` por homologação.
                $homologacao = $this->resolverHomologacao($processo, $request);

                $query = Documento::where('processo_id', $processo->id)
                    ->where('tipo_documento', 'contrato');

                if ($homologacao) {
                    $query->where('homologacao_id', $homologacao->id);
                } else {
                    $query->whereNull('homologacao_id');
                }

                $documento = $query->firstOrFail();
                $caminhoOriginal = public_path($documento->caminho);
            }

            if (!file_exists($caminhoOriginal)) {
                throw new \Exception('Arquivo do contrato não encontrado.');
            }

            // Se já existe versão assinada consolidada, serve direto o PDF assinado
            // (não aplica o carimbo legacy — o PDF assinado já contém as assinaturas).
            $caminhoAssinado = app(\App\Services\Assinatura\SelecaoAssinantesService::class)
                ->caminhoPdfAssinado($documento);

            if ($caminhoAssinado) {
                Log::info('Download de contrato servindo versão assinada', [
                    'processo_id' => $processo->id,
                    'caminho_assinado' => $caminhoAssinado,
                ]);
                return response()->download(
                    $caminhoAssinado,
                    $this->gerarNomeDownload($processo, 'contrato_assinado.pdf')
                );
            }

            Log::info('Iniciando download com carimbo - Contrato', [
                'processo_id' => $processo->id,
                'caminho_original' => $caminhoOriginal
            ]);

            // Criar uma cópia carimbada para download
            $caminhoCarimbado = $this->criarContratoCarimbado($caminhoOriginal, $processo);

            if ($caminhoCarimbado && file_exists($caminhoCarimbado)) {
                Log::info('Download com carimbo realizado com sucesso', [
                    'processo_id' => $processo->id,
                    'caminho_carimbado' => $caminhoCarimbado
                ]);

                // Fazer download do arquivo carimbado e depois excluí-lo
                return response()->download($caminhoCarimbado,
                    $this->gerarNomeDownload($processo, 'contrato_carimbado.pdf'))
                    ->deleteFileAfterSend(true);
            } else {
                // Se não conseguiu carimbar, baixa o original
                Log::warning('Falha ao carimbar contrato, baixando original', [
                    'processo_id' => $processo->id
                ]);
                return response()->download($caminhoOriginal,
                    $this->gerarNomeDownload($processo, 'contrato.pdf'));
            }

        } catch (\Exception $e) {
            Log::error('Erro ao baixar contrato', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao baixar contrato: ' . $e->getMessage()
            ], 500);
        }
    }

    private function criarContratoCarimbado(string $caminhoOriginal, Processo $processo): ?string
    {
        $paginasTemp = [];

        try {
            $pageCount = $this->contarPaginasPdf($caminhoOriginal);

            if ($pageCount === 0) {
                Log::error('PDF vazio ou inválido para carimbo - Contrato', ['caminho' => $caminhoOriginal]);
                return null;
            }

            // Criar arquivo temporário para o resultado carimbado
            $caminhoCarimbado = tempnam(sys_get_temp_dir(), 'contrato_carimbado_') . '.pdf';

            // OBTER PÁGINA INICIAL DO CONTRATO
            $paginaInicial = ($processo->contTotalPagePhase1 ?? 0) + ($processo->contTotalPagePhase2 ?? 0);

            for ($pagina = 1; $pagina <= $pageCount; $pagina++) {
                $paginaAtual = $pagina;

                $pdf = new Fpdi();
                $this->configurarFonte($pdf);

                $pdf->setSourceFile($caminhoOriginal);
                $tplId = $pdf->importPage($pagina);
                $pdf->AddPage();
                $pdf->useTemplate($tplId);

                // Aplicar carimbo em todas as páginas (incluindo a primeira)
                // Passar $paginaInicial para calcular página absoluta corretamente
                $this->adicionarCarimbo($pdf, $processo, $paginaAtual, $pageCount, $paginaInicial);

                $tempPath = sys_get_temp_dir() . "/pagina_contrato_{$pagina}_" . uniqid() . '.pdf';
                $pdf->Output($tempPath, 'F');
                $paginasTemp[] = $tempPath;
            }

            $sucesso = $this->mesclarPdfsComGhostscript($paginasTemp, $caminhoCarimbado);

            if ($sucesso && file_exists($caminhoCarimbado) && filesize($caminhoCarimbado) > 0) {
                Log::info('Contrato carimbado criado com sucesso', [
                    'caminho_carimbado' => $caminhoCarimbado,
                    'tamanho' => filesize($caminhoCarimbado),
                    'paginas' => $pageCount,
                    'pagina_inicial' => $paginaInicial,
                    'pagina_final' => $paginaInicial + $pageCount
                ]);
                // Atualizar o contador da fase 3
                $processo->contTotalPagePhase3 = $pageCount;
                \App\Models\Processo::where('id', $processo->id)
                    ->update(['contTotalPagePhase3' => $pageCount]);

                return $caminhoCarimbado;
            } else {
                Log::error('Falha ao criar contrato carimbado');
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao criar contrato carimbado', [
                'caminho_original' => $caminhoOriginal,
                'erro' => $e->getMessage()
            ]);
            return null;
        } finally {
            // Limpar arquivos temporários
            foreach ($paginasTemp as $tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }
        }
    }

    /**
     * Gera nome do arquivo para download
     */
    private function gerarNomeDownload(Processo $processo, string $sufixo = 'contrato.pdf'): string
    {
        $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
        return "contrato_{$numeroProcessoLimpo}_{$sufixo}";
    }

    // =========================================================
    // MÉTODOS PRIVADOS - GERAÇÃO DE PDF
    // =========================================================

    private function validarRequisicaoPdf(Request $request, Processo $processo, ?Homologacao $homologacao = null): array
    {
        // Data não é mais obrigatória - usa data atual se não for fornecida
        $dataSelecionada = $request->query('data', now()->format('Y-m-d'));

        // Assinantes não são mais obrigatórios - processa se existirem
        $assinantes = $this->processarAssinantes($request);

        // Campos do contrato (busca persistido por processo+homologação se não vier na request)
        $campos = $this->processarCamposContrato($request, $processo, $homologacao);

        // Dados do contratante editados no modal (snapshot deste contrato).
        $contratante = $this->processarContratante($request);

        // Contratações (itens) escolhidas na tela para entrar NESTE contrato.
        $contratacoes = $this->processarContratacoesSelecionadas($request);

        return [
            'documento' => 'contrato',
            'dataSelecionada' => $dataSelecionada,
            'assinantes' => $assinantes,
            'campos' => $campos,
            'contratante' => $contratante,
            'contratacoes' => $contratacoes,
        ];
    }

    /**
     * IDs das contratações (LoteContratado) selecionadas na tela para entrar neste
     * contrato. Enviadas como JSON no parâmetro `contratacoes`.
     *
     * @return array<int,int>
     */
    private function processarContratacoesSelecionadas(Request $request): array
    {
        $json = $request->input('contratacoes') ?: $request->query('contratacoes');

        if (!$json) {
            return [];
        }

        $decoded = json_decode(urldecode($json), true);

        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $decoded), fn ($id) => $id > 0));
    }

    /**
     * Decodifica o JSON `contratante` (7 campos do snapshot) enviado pelo modal.
     */
    private function processarContratante(Request $request): array
    {
        $json = $request->input('contratante') ?: $request->query('contratante');

        if (!$json) {
            return [];
        }

        $decoded = json_decode(urldecode($json), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Erro ao decodificar JSON de contratante - Contrato: ' . json_last_error_msg());
            return [];
        }

        // Mantém apenas as chaves esperadas.
        return array_intersect_key($decoded, array_flip(self::CAMPOS_CONTRATANTE));
    }

    private function processarCamposContrato(Request $request, ?Processo $processo = null, ?Homologacao $homologacao = null): array
    {
        $camposJson = $request->query('campos');

        if (!$camposJson) {
            $processoId = $processo?->id ?? $request->route('processo')?->id;
            $contrato = Contrato::where('processo_id', $processoId)
                ->where('homologacao_id', $homologacao?->id)
                ->first();

            if ($contrato) {
                return [
                    'numero_contrato' => $contrato->numero_contrato,
                    'data_assinatura_contrato' => $contrato->data_assinatura_contrato,
                    'numero_extrato' => $contrato->numero_extrato,
                    'comarca' => $contrato->comarca,
                    'fonte_recurso' => $contrato->fonte_recurso,
                    'subcontratacao' => $contrato->subcontratacao,
                ];
            }

            return [];
        }

        $camposDecoded = urldecode($camposJson);
        $campos = json_decode($camposDecoded, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning("Erro ao decodificar JSON de campos - Contrato: " . json_last_error_msg());
            return [];
        }

        return $campos;
    }

    private function processarAssinantes(Request $request): array
    {
        $assinantesJson = $request->query('assinantes');

        if (!$assinantesJson) {
            return [];
        }

        $assinantesDecoded = urldecode($assinantesJson);
        $assinantes = json_decode($assinantesDecoded, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning("Erro ao decodificar JSON de assinantes - Contrato: " . json_last_error_msg());
            return [];
        }

        return $assinantes;
    }

    private function prepararDadosPdf(Processo $processo, array $validatedData): array
    {
        $processo->load(['prefeitura', 'vencedores.lotes.contratados', 'finalizacao']);

        $homologacao = $validatedData['homologacao'] ?? null;
        $contratoAtual = $validatedData['contrato'] ?? null;

        // Carregar contratações — ISOLA estritamente os itens deste contrato.
        $contratacoesQuery = LoteContratado::where('processo_id', $processo->id)
            ->with(['lote', 'vencedor'])
            ->whereIn('status', ['PENDENTE', 'CONTRATADO']);

        if ($contratoAtual && $contratoAtual->id) {
            $temVinculadas = LoteContratado::where('processo_id', $processo->id)
                ->where('contrato_id', $contratoAtual->id)
                ->exists();

            if ($temVinculadas) {
                // Cada contrato lista apenas as contratações vinculadas a ELE (contrato_id).
                $contratacoesQuery->where('contrato_id', $contratoAtual->id);
            } elseif ($homologacao) {
                // Compatibilidade (contratos sem vínculo ainda): usa os lotes da homologação,
                // mas só as contratações ainda não vinculadas a nenhum contrato.
                $homologacao->loadMissing('lotes');
                $contratacoesQuery
                    ->whereIn('lote_id', $homologacao->lotes->pluck('id'))
                    ->whereNull('contrato_id');
            }
        } elseif ($homologacao) {
            $homologacao->loadMissing('lotes');
            $contratacoesQuery->whereIn('lote_id', $homologacao->lotes->pluck('id'));
        }

        $contratacoes = $contratacoesQuery->get();

        // Fonte de leitura do cabeçalho:
        //  - Pregão/Dispensa: usa a homologação resolvida pela request (usuário seleciona qual).
        //  - Concorrência/Inexigibilidade (homologação única): se não veio pela request
        //    (ex.: ainda está EM_EDICAO e o frontend não manda), pega automaticamente a
        //    única homologação do processo como fonte de leitura.
        $homologacaoLeitura = $homologacao;
        if (!$homologacaoLeitura) {
            $ehHomologacaoUnica = !in_array(
                $processo->modalidade,
                [ModalidadeEnum::PREGAO_ELETRONICO, ModalidadeEnum::DISPENSA],
                true
            );
            if ($ehHomologacaoUnica) {
                $homologacaoLeitura = $processo->homologacoes->sortBy('id')->first();
            }
        }

        // Fonte unificada do cabeçalho com fallback campo-a-campo:
        // Homologação preenchida → Finalização (legado / dado salvo antes da homologação existir).
        $fonteCabecalho = $this->mesclarFonteCabecalho($homologacaoLeitura, $processo->finalizacao);

        // ==============================================
        // SNAPSHOT DO CONTRATANTE (por contrato)
        // ==============================================
        // Quando o contrato tem dados de contratante próprios, eles sobrescrevem os dados
        // globais APENAS para este PDF. Os templates leem de $processo->finalizacao->* e
        // $processo->prefeitura->*, então sobrescrevemos os modelos EM MEMÓRIA (sem persistir).
        // Faz $processo->contrato (hasOne) apontar para o contrato sendo gerado: vários
        // templates leem $processo->contrato->{numero_contrato, comarca, numero_extrato,
        // data_assinatura_contrato, fonte_recurso, subcontratacao}, e o hasOne retornaria
        // o primeiro contrato do processo (errado quando há múltiplos contratos).
        $contratoAtual = $validatedData['contrato'] ?? null;
        if ($contratoAtual) {
            $processo->setRelation('contrato', $contratoAtual);
        }

        $snapshot = $contratoAtual?->dados_contratante ?? [];
        if (!empty($snapshot)) {
            foreach (['orgao_responsavel', 'cargo_responsavel', 'cnpj', 'responsavel', 'cpf_responsavel'] as $campo) {
                if (!empty($snapshot[$campo])) {
                    $fonteCabecalho->{$campo} = $snapshot[$campo];
                    if ($processo->finalizacao) {
                        $processo->finalizacao->{$campo} = $snapshot[$campo];
                    }
                }
            }
            if (!empty($snapshot['endereco'])) {
                $fonteCabecalho->endereco = $snapshot['endereco'];
                if ($processo->prefeitura) {
                    $processo->prefeitura->endereco = $snapshot['endereco'];
                }
            }
            // razao_social: persistido no snapshot e exposto em $dadosContratante abaixo,
            // mas NÃO sobrescreve $processo->finalizacao->razao_social — em
            // Concorrência/Dispensa-Obra esse campo representa o CONTRATADO.
        }

        // ==============================================
        // DADOS DO CONTRATANTE (PREFEITURA)
        // ==============================================
        $dadosContratante = [
            'orgao' => $fonteCabecalho->orgao_responsavel ?? $processo->prefeitura->cidade,
            'cidade' => $processo->prefeitura->cidade,
            'uf' => $processo->prefeitura->uf,
            'endereco' => $processo->prefeitura->endereco,
            'cnpj' => $fonteCabecalho->cnpj ?? $processo->prefeitura->cnpj,
            'responsavel' => $fonteCabecalho->responsavel ?? $processo->prefeitura->autoridade_competente,
            'cargo_responsavel' => $fonteCabecalho->cargo_responsavel ?? 'Prefeito Municipal',
            'cpf_responsavel' => $fonteCabecalho->cpf_responsavel ?? null,
            'razao_social' => $snapshot['razao_social'] ?? $fonteCabecalho->razao_social ?? null,
        ];
        
        // Formatando CNPJ e CPF
        $dadosContratante['cnpj_formatado'] = $this->formatarCNPJ($dadosContratante['cnpj']);
        $dadosContratante['cpf_responsavel_formatado'] = $dadosContratante['cpf_responsavel'] 
            ? $this->formatarCPF($dadosContratante['cpf_responsavel'])
            : null;

        // ==============================================
        // DADOS DO CONTRATADO (EMPRESA VENCEDORA)
        // ==============================================
        $dadosContratado = $this->prepararDadosContratado($processo, $contratacoes, $homologacao);

        // ==============================================
        // DADOS DA TABELA DE ITENS
        // ==============================================
        $itensTabela = $this->prepararItensParaTabela($processo, $contratacoes);

        // Calcular totais
        $valorTotalContrato = $contratacoes->sum('valor_total');
        $quantidadeTotalContrato = $contratacoes->sum('quantidade_contratada');

        // Dados salvos do contrato: usa o contrato sendo gerado quando disponível;
        // caso contrário, o mais recente da homologação (compatibilidade).
        $contratoSalvo = $validatedData['contrato']
            ?? Contrato::where('processo_id', $processo->id)
                ->where('homologacao_id', $homologacao?->id)
                ->latest('numero_sequencial')
                ->first();

        return [
            'processo' => $processo,
            'prefeitura' => $processo->prefeitura,
            // Fonte unificada dos campos preenchidos na finalização (Homologação > Finalização).
            // Permite que as views usem `$finalizacao->razao_social` sem se preocupar com
            // a modalidade — Concorrência/Inexigibilidade gravam na Homologação, Pregão/Dispensa
            // podem usar Homologação (multi) ou Finalização (legado).
            'finalizacao' => $fonteCabecalho,
            'contratacoes' => $contratacoes,
            'itensTabela' => $itensTabela,
            'valorTotalContrato' => $valorTotalContrato,
            'quantidadeTotalContrato' => $quantidadeTotalContrato,
            
            // VALOR POR EXTENSO
            'valorTotalPorExtenso' => $this->escreverValorPorExtenso($valorTotalContrato),
            
            // Dados formatados
            'dadosContratante' => $dadosContratante,
            'dadosContratado' => $dadosContratado,
            
            // Dados do contrato salvos
            'contratoSalvo' => $contratoSalvo,
            
            // Dados gerais
            'dataGeracao' => now()->format('d/m/Y H:i:s'),
            'dataSelecionada' => $validatedData['dataSelecionada'],
            'assinantes' => $validatedData['assinantes'],
            'hasSelectedAssinantes' => !empty($validatedData['assinantes']),
            'campos' => $validatedData['campos'],
            'dataAssinaturaFormatada' => !empty($validatedData['campos']['data_assinatura_contrato']) 
                ? \Carbon\Carbon::parse($validatedData['campos']['data_assinatura_contrato'])->format('d/m/Y')
                : ($contratoSalvo && $contratoSalvo->data_assinatura_contrato 
                    ? \Carbon\Carbon::parse($contratoSalvo->data_assinatura_contrato)->format('d/m/Y')
                    : null),
        ];
    }

    /**
     * Mescla Homologacao + Finalizacao em uma fonte única, dando prioridade à Homologação
     * mas caindo para a Finalização quando o campo da homologação estiver vazio.
     * Resolve o caso em que o usuário preencheu campos na finalização ANTES da homologação
     * ser auto-criada (dado fica em Finalizacao) e depois gera contrato (lê de Homologacao).
     */
    private function mesclarFonteCabecalho($homologacao, $finalizacao): object
    {
        $campos = [
            'orgao_responsavel',
            'cargo_responsavel',
            'cnpj',
            'endereco',
            'responsavel',
            'cpf_responsavel',
            'razao_social',
            'cnpj_empresa_vencedora',
            'endereco_empresa_vencedora',
            'representante_legal_empresa',
            'cpf_representante',
            'valor_total',
        ];

        $resultado = new \stdClass();
        foreach ($campos as $campo) {
            $valorHomol = $homologacao?->{$campo};
            $valorFinal = $finalizacao?->{$campo};
            // Considera string vazia como ausente.
            $resultado->{$campo} = ($valorHomol !== null && $valorHomol !== '')
                ? $valorHomol
                : (($valorFinal !== null && $valorFinal !== '') ? $valorFinal : null);
        }

        return $resultado;
    }

    // Método para escrever valor por extenso
    private function escreverValorPorExtenso($valor): string
    {
        // Remover formatação se existir
        if (is_string($valor)) {
            $valor = preg_replace('/[^0-9,.]/', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
        
        $valor = floatval($valor);
        
        // Usar a classe helper
        return \App\Helpers\ValorPorExtenso::escrever($valor);
    }

    // ==============================================
    // MÉTODO PARA PREPARAR DADOS DO CONTRATADO
    // ==============================================
    private function prepararDadosContratado(Processo $processo, $contratacoes, ?Homologacao $homologacao = null): array
    {
        // Prioridade 1: Dados do vencedor associado às contratações do contrato atual
        if ($contratacoes->count() > 0) {
            $primeiroVencedor = $contratacoes->first()->vencedor;
            
            if ($primeiroVencedor) {
                return [
                    'razao_social' => $primeiroVencedor->razao_social,
                    'cnpj' => $primeiroVencedor->cnpj,
                    'cnpj_formatado' => $this->formatarCNPJ($primeiroVencedor->cnpj),
                    'endereco' => $primeiroVencedor->endereco,
                    'representante' => $primeiroVencedor->representante ?? 'Representante não informado',
                    'cpf_representante' => $primeiroVencedor->cpf ?? null,
                    'cpf_representante_formatado' => $primeiroVencedor->cpf 
                        ? $this->formatarCPF($primeiroVencedor->cpf)
                        : null,
                    'fonte_dados' => 'vencedor',
                ];
            }
        }

        // Prioridade 2: Homologação (fallback para processos sem contratações vinculadas)
        if ($homologacao && $homologacao->cnpj_empresa_vencedora) {
            return [
                'razao_social' => $homologacao->razao_social ?? 'XXXXXXXXXXXXX',
                'cnpj' => $homologacao->cnpj_empresa_vencedora,
                'cnpj_formatado' => $this->formatarCNPJ($homologacao->cnpj_empresa_vencedora),
                'endereco' => $homologacao->endereco_empresa_vencedora
                    ?? $homologacao->endereco
                    ?? 'Endereço não informado',
                'representante' => $homologacao->representante_legal_empresa ?? 'Representante não informado',
                'cpf_representante' => $homologacao->cpf_representante ?? null,
                'cpf_representante_formatado' => $homologacao->cpf_representante
                    ? $this->formatarCPF($homologacao->cpf_representante)
                    : null,
                'fonte_dados' => 'homologacao',
            ];
        }

        // Prioridade 3: Finalização (fallback legado)
        if ($processo->finalizacao && $processo->finalizacao->cnpj_empresa_vencedora) {
            return [
                'razao_social' => $processo->finalizacao->razao_social ?? 'XXXXXXXXXXXXX',
                'cnpj' => $processo->finalizacao->cnpj_empresa_vencedora,
                'cnpj_formatado' => $this->formatarCNPJ($processo->finalizacao->cnpj_empresa_vencedora),
                'endereco' => $processo->finalizacao->endereco ?? 'Endereço não informado',
                'representante' => $processo->finalizacao->representante_legal_empresa ?? 'Representante não informado',
                'cpf_representante' => $processo->finalizacao->cpf_representante ?? null,
                'cpf_representante_formatado' => $processo->finalizacao->cpf_representante
                    ? $this->formatarCPF($processo->finalizacao->cpf_representante)
                    : null,
                'fonte_dados' => 'finalizacao',
            ];
        }
        
        // Fallback
        return [
            'razao_social' => 'XXXXXXXXXXXXX',
            'cnpj' => 'XX.XXX.XXX/XXXX-XX',
            'cnpj_formatado' => 'XX.XXX.XXX/XXXX-XX',
            'endereco' => 'Endereço não informado',
            'representante' => 'Representante não informado',
            'cpf_representante' => 'XXX.XXX.XXX-XX',
            'cpf_representante_formatado' => 'XXX.XXX.XXX-XX',
            'fonte_dados' => 'fallback',
        ];
    }

    // ==============================================
    // MÉTODOS AUXILIARES
    // ==============================================
    private function formatarCNPJ($cnpj): string
    {
        if (!$cnpj) return 'XX.XXX.XXX/XXXX-XX';
        
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
        }
        
        return $cnpj;
    }

    private function formatarCPF($cpf): string
    {
        if (!$cpf) return 'XXX.XXX.XXX-XX';
        
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
        }
        
        return $cpf;
    }

    // Novo método para preparar itens para a tabela
    private function prepararItensParaTabela($processo, $contratacoes): array
    {
        $itens = [];
        $itemNumero = 1;

        // Ordenar contratações por lote e item (se houver)
        $contratacoesOrdenadas = $contratacoes->sortBy(function($c) {
            $loteNum = $c->lote->lote ?? '';
            $itemNum = $c->lote->item ?? 0;
            return sprintf('%05s-%010d', $loteNum, $itemNum);
        });

        $loteAtual = null;
        $isLoteProcess = $processo->tipo_contratacao?->getDisplayName() === 'LOTE';

        foreach ($contratacoesOrdenadas as $contratacao) {
            if ($contratacao->lote) {
                // Se o lote mudou e é um processo por lote, adiciona o cabeçalho do lote
                if ($isLoteProcess && $loteAtual !== $contratacao->lote->lote) {
                    $loteAtual = $contratacao->lote->lote;
                    $itens[] = [
                        'is_lote_header' => true,
                        'item' => $loteAtual,
                        'especificacao' => "LOTE " . $loteAtual . ($contratacao->lote->lote_nome ? " - " . $contratacao->lote->lote_nome : ""),
                        'unidade_medida' => '',
                        'quantidade' => '',
                        'valor_unitario' => '',
                        'valor_total' => '',
                        'valor_total_raw' => 0,
                        'marca' => '',
                        'modelo' => '',
                    ];
                }

                $itens[] = [
                    'item' => $contratacao->lote->item ?? $itemNumero++,
                    'especificacao' => $contratacao->lote->descricao ?? 'Não especificado',
                    'unidade_medida' => $contratacao->lote->unidade ?? '',
                    'quantidade' => number_format($contratacao->quantidade_contratada, 2, ',', '.'),
                    'valor_unitario' => 'R$ ' . number_format($contratacao->valor_unitario, 2, ',', '.'),
                    'valor_total' => 'R$ ' . number_format($contratacao->valor_total, 2, ',', '.'),
                    'valor_total_raw' => $contratacao->valor_total,
                    'marca' => $contratacao->lote->marca ?? '',
                    'modelo' => $contratacao->lote->modelo ?? '',
                ];
            }
        }

        return $itens;
    }

   private function determinarViewContrato(Processo $processo): string
    {
        $viewBase = 'Admin.Processos.contrato';

        $modalidade = $this->formatarNomeArquivo($processo->modalidade?->name ?? '');

        // Caso especial: Dispensa por Obra
        if (
            $processo->modalidade === ModalidadeEnum::DISPENSA
            && $processo->tipo_procedimento === TipoProcedimentoEnum::OBRA
        ) {
            $view = "{$viewBase}.{$modalidade}.obra.contrato";
        } else {
            $view = "{$viewBase}.{$modalidade}.contrato";
        }

        if (!view()->exists($view)) {
            throw new \Exception(
                "O modelo de contrato não foi encontrado. View: {$view}"
            );
        }

        return $view;
    }


    private function formatarNomeArquivo(string $nome): string
    {
        $nome = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome));
        return str_replace(' ', '_', $nome);
    }

    private function salvarDocumento(Processo $processo, $pdf, array $validatedData): string
    {
        $homologacao = $validatedData['homologacao'] ?? null;
        $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo);
        $subpasta = $this->gerarSubpasta($processo, $homologacao);

        $diretorio = public_path("uploads/contratos/{$subpasta}");
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0777, true);
        }

        $sufixoHomologacao = $homologacao ? "_h{$homologacao->numero_sequencial}" : '';
        $nomeArquivo = "contrato_{$numeroProcessoLimpo}{$sufixoHomologacao}_" . now()->format('Ymd_His') . '.pdf';
        $caminhoRelativo = "uploads/contratos/{$subpasta}/{$nomeArquivo}";
        $caminhoCompleto = "{$diretorio}/{$nomeArquivo}";

        $pdf->save($caminhoCompleto);
        $this->atualizarRegistroDocumento(
            $processo,
            $validatedData['dataSelecionada'],
            $caminhoRelativo,
            $homologacao?->id
        );

        // Arquivo próprio deste contrato (usado pela listagem e download por contrato).
        if (!empty($validatedData['contrato'])) {
            $validatedData['contrato']->update([
                'caminho' => $caminhoRelativo,
                'data_documento' => $validatedData['dataSelecionada'],
                'gerado_em' => now(),
            ]);
        }

        return $caminhoCompleto;
    }

    private function gerarSubpasta(Processo $processo, ?Homologacao $homologacao = null): string
    {
        if ($homologacao) {
            return "contratos/{$processo->id}/homologacao_{$homologacao->numero_sequencial}";
        }

        return "contratos/{$processo->id}";
    }

    private function atualizarRegistroDocumento(
        Processo $processo,
        string $dataSelecionada,
        string $caminhoRelativo,
        ?int $homologacaoId = null
    ): void {
        $query = Documento::where('processo_id', $processo->id)
            ->where('tipo_documento', 'contrato');

        if ($homologacaoId !== null) {
            $query->where('homologacao_id', $homologacaoId);
        } else {
            $query->whereNull('homologacao_id');
        }

        $documentoExistente = $query->first();

        if ($documentoExistente) {
            // NÃO remove o arquivo antigo: com múltiplos contratos por homologação, o
            // arquivo anterior ainda pode pertencer a outro contrato (contratos.caminho).
            // O registro em `documentos` apenas aponta para o contrato mais recente,
            // necessário para a montagem do processo completo.
            $documentoExistente->update([
                'data_selecionada' => $dataSelecionada,
                'caminho' => $caminhoRelativo,
                'gerado_em' => now(),
            ]);
        } else {
            Documento::create([
                'processo_id' => $processo->id,
                'homologacao_id' => $homologacaoId,
                'tipo_documento' => 'contrato',
                'data_selecionada' => $dataSelecionada,
                'caminho' => $caminhoRelativo,
                'gerado_em' => now(),
            ]);
        }
    }

    // =========================================================
    // MÉTODOS PRIVADOS - CARIMBAGEM (Ghostscript)
    // =========================================================

    private function mesclarPdfsComGhostscript(array $arquivos, string $outputPath): bool
    {
        $listaArquivos = null;

        try {
            $arquivosValidos = [];
            foreach ($arquivos as $index => $arquivo) {
                if (!file_exists($arquivo)) {
                    Log::error('Arquivo não encontrado para mesclagem - Contrato', ['arquivo' => $arquivo]);
                    return false;
                }

                $tamanho = filesize($arquivo);
                if ($tamanho === 0) {
                    Log::error('Arquivo vazio encontrado - Contrato', ['arquivo' => $arquivo]);
                    return false;
                }

                $arquivosValidos[] = $arquivo;
            }

            $listaArquivos = tempnam(sys_get_temp_dir(), 'gs_list_contrato_');
            file_put_contents($listaArquivos, implode("\n", $arquivosValidos));

            $comando = sprintf(
                'gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -dPDFSETTINGS=/prepress -sOutputFile="%s" @"%s"',
                $outputPath,
                $listaArquivos
            );

            Log::info('Executando Ghostscript - Contrato', [
                'comando' => $comando,
                'quantidade_arquivos' => count($arquivosValidos)
            ]);

            $output = [];
            $returnCode = 0;
            exec($comando . ' 2>&1', $output, $returnCode);

            sleep(2);

            $outputExiste = file_exists($outputPath);
            $outputTamanho = $outputExiste ? filesize($outputPath) : 0;

            if ($returnCode === 0 && $outputExiste && $outputTamanho > 0) {
                Log::info('PDFs mesclados com sucesso usando Ghostscript - Contrato', [
                    'arquivo_saida' => $outputPath,
                    'tamanho' => $outputTamanho
                ]);
                return true;
            } else {
                Log::error('Erro ao mesclar PDFs com Ghostscript - Contrato', [
                    'return_code' => $returnCode,
                    'arquivo_saida_existe' => $outputExiste,
                    'arquivo_saida_tamanho' => $outputTamanho
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao mesclar PDFs com Ghostscript - Contrato', [
                'erro' => $e->getMessage()
            ]);
            return false;
        } finally {
            if ($listaArquivos && file_exists($listaArquivos)) {
                unlink($listaArquivos);
            }
        }
    }

    // =========================================================
    // MÉTODOS AUXILIARES
    // =========================================================

    private function configurarFonte(Fpdi $pdf): void
    {
        $fontPath = public_path('storage/app/public/fonts/Aptos.ttf');
        if (file_exists($fontPath)) {
            $pdf->AddFont('Aptos', '', 'Aptos.ttf', true);
            $pdf->SetFont('Aptos', '', 8);
        } else {
            $pdf->SetFont('helvetica', '', 6);
        }
    }

    private function adicionarCarimbo(Fpdi $pdf, Processo $processo, int $paginaAtual, int $pageCountTotal): void
    {
        $pageWidth = $pdf->GetPageWidth();
        $pageHeight = $pdf->GetPageHeight();

        $boxWidth = 8;
        $boxHeight = 150;

        $x = $pageWidth - $boxWidth - 1;
        $y = ($pageHeight - $boxHeight) / 2;

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Rect($x, $y, $boxWidth, $boxHeight, 'D');
        $pdf->SetTextColor(0, 0, 0);

        // OBTER PÁGINA INICIAL DO CONTRATO (continuação da finalização)
        $paginaInicial = ($processo->contTotalPagePhase1 ?? 0) + ($processo->contTotalPagePhase2 ?? 0);

        // CALCULAR PÁGINA ABSOLUTA (contrato)
        $paginaAbsoluta = $paginaInicial + $paginaAtual;
        $totalAbsoluto = $paginaInicial + $pageCountTotal;
        $codigoAutenticacao = $processo->prefeitura->id . now()->format('HisdmY');
        $textoCarimbo = "Processo numerado por: {$processo->responsavel_numeracao} " .
            "Cargo: {$processo->unidade_numeracao} " .
            "Portaria nº {$processo->portaria_numeracao} " .
            "Pág. {$paginaAbsoluta} - " .
            "Documento gerado na Plataforma GestGov - Licenciado para Prefeitura de {$processo->prefeitura->cidade}. " .
            "Cod. de Autenticação: {$codigoAutenticacao} - Para autenticar acesse gestgov.com.br/autenticacao";

        $pdf->StartTransform();
        $rotateX = $x + ($boxWidth / 2);
        $rotateY = $y + ($boxHeight / 2);
        $pdf->Rotate(90, $rotateX, $rotateY);

        $textX = $rotateX - ($boxHeight / 2);
        $textY = $rotateY - ($boxWidth / 2);
        $pdf->SetXY($textX, $textY);

        $pdf->MultiCell($boxHeight, $boxWidth, $textoCarimbo, 0, 'C', false, 1, '', '', true, 0, false, true, 0, 'T', false);
        $pdf->StopTransform();
    }

    private function contarPaginasPdf(string $caminhoPdf): int
    {
        try {
            $pdf = new Fpdi();
            return $pdf->setSourceFile($caminhoPdf);
        } catch (\Exception $e) {
            Log::error('Erro ao contar páginas do contrato', [
                'caminho' => $caminhoPdf,
                'erro' => $e->getMessage()
            ]);
            return 0;
        }
    }
}