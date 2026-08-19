<?php

namespace App\Http\Controllers;

use App\Enums\ConclusaoFiscalEnum;
use App\Enums\TipoFiscalizacaoEnum;
use App\Http\Controllers\Concerns\ExtraiInfoContrato;
use App\Http\Requests\FiscalizacaoRequest;
use App\Models\Contrato;
use App\Models\ContratoManual;
use App\Models\Fiscalizacao;
use App\Models\Ocorrencia;
use App\Models\Prefeitura;
use App\Models\Processo;
use App\Models\Unidade;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FiscalizacaoController extends Controller
{
    use ExtraiInfoContrato;

    // =========================================================
    // INDEX — Listagem de Fiscalizações
    // =========================================================
    public function index(Request $request)
    {
        $user = auth()->user();
        $userPrefeituraId = $user->prefeitura_id;
        $isPrefeituraUser = $user->hasRole('prefeitura') && $userPrefeituraId;
        $isLiciconAdmin = $user->hasAnyRole(['diretor_licicon', 'gerente_licicon']);
        $activeTab = $request->get('tab', 'fiscalizacoes');

        // 1. BUSCA DE DADOS PARA FILTROS
        $prefeituras = $isPrefeituraUser
            ? Prefeitura::where('id', $userPrefeituraId)->get()
            : Prefeitura::orderBy('nome')->get();

        $unidades = $isPrefeituraUser
            ? Unidade::where('prefeitura_id', $userPrefeituraId)->orderBy('nome')->get()
            : Unidade::orderBy('nome')->get();

        // 2. QUERY FISCALIZACOES
        $queryFisc = Fiscalizacao::with(['fiscalizavel', 'prefeitura', 'user'])->latest();

        if ($user->unidade_id && ! $isLiciconAdmin) {
            $queryFisc->whereHasMorph('fiscalizavel', [ContratoManual::class, Contrato::class], function ($q, $type) use ($user) {
                if ($type === ContratoManual::class) {
                    $q->where('contratos_manuais.unidade_id', $user->unidade_id);
                } else {
                    $q->whereHas('processo.user', function ($u) use ($user) {
                        $u->where('unidade_id', $user->unidade_id);
                    });
                }
            });
        }

        if ($request->filled('unidade_id')) {
            $unidadeFiltro = $request->unidade_id;
            $queryFisc->whereHasMorph('fiscalizavel', [ContratoManual::class, Contrato::class], function ($q, $type) use ($unidadeFiltro) {
                if ($type === ContratoManual::class) {
                    $q->where('contratos_manuais.unidade_id', $unidadeFiltro);
                } else {
                    $q->whereHas('processo.user', fn ($u) => $u->where('unidade_id', $unidadeFiltro));
                }
            });
        }

        if (! $isPrefeituraUser && $request->filled('prefeitura_id')) {
            $queryFisc->where('prefeitura_id', $request->prefeitura_id);
        }

        if ($request->filled('tipo_contrato')) {
            $queryFisc->where('tipo_contrato', $request->tipo_contrato);
        }

        if ($request->filled('search') && $activeTab === 'fiscalizacoes') {
            $searchTerm = $request->search;
            $queryFisc->where(function ($q) use ($searchTerm) {
                $q->where('numero_fiscalizacao', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('execucao_objeto', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('qualidade_entregas', 'LIKE', "%{$searchTerm}%");
            });
        }

        $fiscalizacoes = $queryFisc->paginate(10, ['*'], 'page_fisc');

        $fiscalizacoes->getCollection()->transform(function ($fiscalizacao) {
            $fiscalizacao->contrato_info = $this->extrairInfoContrato($fiscalizacao->fiscalizavel);
            return $fiscalizacao;
        });

        // 3. QUERY OCORRENCIAS
        $queryOcor = Ocorrencia::with(['fiscalizavel', 'prefeitura', 'user'])->latest();

        if ($user->unidade_id && ! $isLiciconAdmin) {
            $queryOcor->whereHasMorph('fiscalizavel', [ContratoManual::class, Contrato::class], function ($q, $type) use ($user) {
                if ($type === ContratoManual::class) {
                    $q->where('contratos_manuais.unidade_id', $user->unidade_id);
                } else {
                    $q->whereHas('processo.user', function ($u) use ($user) {
                        $u->where('unidade_id', $user->unidade_id);
                    });
                }
            });
        }

        if ($request->filled('unidade_id')) {
            $unidadeFiltro = $request->unidade_id;
            $queryOcor->whereHasMorph('fiscalizavel', [ContratoManual::class, Contrato::class], function ($q, $type) use ($unidadeFiltro) {
                if ($type === ContratoManual::class) {
                    $q->where('contratos_manuais.unidade_id', $unidadeFiltro);
                } else {
                    $q->whereHas('processo.user', fn ($u) => $u->where('unidade_id', $unidadeFiltro));
                }
            });
        }

        if (! $isPrefeituraUser && $request->filled('prefeitura_id')) {
            $queryOcor->where('prefeitura_id', $request->prefeitura_id);
        }

        if ($request->filled('status')) {
            $queryOcor->where('status', $request->status);
        }

        if ($request->filled('situacao')) {
            $queryOcor->where('situacao', $request->situacao);
        }

        if ($request->filled('search') && $activeTab === 'ocorrencias') {
            $searchTerm = $request->search;
            $queryOcor->where(function ($q) use ($searchTerm) {
                $q->where('numero_ocorrencia', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('descricao_fato', 'LIKE', "%{$searchTerm}%");
            });
        }

        $ocorrencias = $queryOcor->paginate(10, ['*'], 'page_ocor');

        $ocorrencias->getCollection()->transform(function ($ocorrencia) {
            $ocorrencia->contrato_info = $this->extrairInfoContrato($ocorrencia->fiscalizavel);
            return $ocorrencia;
        });

        $tiposFiscalizacao = TipoFiscalizacaoEnum::cases();

        return view('Admin.Fiscalizacoes.index', compact(
            'fiscalizacoes',
            'ocorrencias',
            'tiposFiscalizacao',
            'prefeituras',
            'unidades',
            'isPrefeituraUser',
            'activeTab'
        ));
    }

    // =========================================================
    // CREATE — Formulário de Criação
    // =========================================================
    public function create(Request $request)
    {
        $user = auth()->user();
        $isPrefeituraUser = $user->hasRole('prefeitura') && $user->prefeitura_id;

        $tiposFiscalizacao = TipoFiscalizacaoEnum::cases();
        $conclusoes = ConclusaoFiscalEnum::cases();

        $contratoPreSelecionado = null;

        // Se vierem ID e TYPE da Lobby, carregam os dados para pré-seleção
        if ($request->has(['id', 'type'])) {
            $id = $request->id;
            $type = $request->type;

            $allowedTypes = [
                Contrato::class,
                ContratoManual::class,
            ];

            if (in_array($type, $allowedTypes)) {
                $contratoModel = $type::find($id);

                if ($contratoModel) {
                    $contratoPreSelecionado = $this->extrairInfoContrato($contratoModel);
                    $contratoPreSelecionado['id'] = $id.'|'.$type;
                    $contratoPreSelecionado['id_puro'] = $id;
                    $contratoPreSelecionado['type_puro'] = $type;
                }
            }
        }

        return view('Admin.Fiscalizacoes.create', compact(
            'isPrefeituraUser',
            'tiposFiscalizacao',
            'conclusoes',
            'contratoPreSelecionado'
        ));
    }

    // =========================================================
    // STORE — Salvar nova Fiscalização
    // =========================================================
    public function store(FiscalizacaoRequest $request)
    {
        Log::info('📋 Iniciando store da fiscalização', [
            'user_id' => auth()->id(),
            'dados_recebidos' => $request->except(['_token']),
        ]);

        $user = auth()->user();

        DB::beginTransaction();

        try {
            // Determinar prefeitura_id com base no contrato selecionado
            $prefeituraId = $this->resolverPrefeituraId($request);

            // Verificar acesso multi-tenant
            if ($user->hasRole('prefeitura') && $user->prefeitura_id) {
                if ($prefeituraId != $user->prefeitura_id) {
                    Log::warning('🚫 Usuário da prefeitura tentando criar fiscalização para outra prefeitura');

                    return back()->withInput()->with('error', 'Você só pode criar fiscalizações para sua própria prefeitura.');
                }
            }

            $dados = $request->validated();
            $dados['prefeitura_id'] = $prefeituraId;
            $dados['user_id'] = $user->id;
            $dados['checklist_fiscalizacao'] = in_array($dados['tipo_contrato'], ['compras', 'servicos'], true)
                ? $this->normalizarChecklist($request)
                : null;

            if ($request->hasFile('relatorio_fotografico')) {
                $dados['relatorio_fotografico'] = $this->uploadRelatorioFotografico(
                    $request->file('relatorio_fotografico'),
                    $dados['numero_fiscalizacao']
                );
            } else {
                unset($dados['relatorio_fotografico']);
            }

            $fiscalizacao = Fiscalizacao::create($dados);

            Log::info('✅ Fiscalização criada com sucesso', [
                'fiscalizacao_id' => $fiscalizacao->id,
                'tipo' => $fiscalizacao->tipo_contrato,
                'contrato_type' => $fiscalizacao->fiscalizavel_type,
                'contrato_id' => $fiscalizacao->fiscalizavel_id,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.fiscalizacoes.index')
                ->with('success', 'Fiscalização cadastrada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Erro ao salvar fiscalização', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Erro ao salvar fiscalização: '.$e->getMessage());
        }
    }

    // =========================================================
    // SHOW — Visualizar Fiscalização
    // =========================================================
    public function show($id)
    {
        $fiscalizacao = Fiscalizacao::with(['fiscalizavel', 'prefeitura.unidades', 'user', 'fotos'])->findOrFail($id);

        $this->authorizeAccess($fiscalizacao);

        $fiscalizacao->contrato_info = $this->extrairInfoContrato($fiscalizacao->fiscalizavel);

        // Usuários (fiscais/servidores) da mesma prefeitura, para seleção como assinantes.
        // Exclui os cadastros institucionais (a própria prefeitura), mantendo pessoas físicas.
        $fiscais = \App\Models\User::with('unidade')
            ->where('prefeitura_id', $fiscalizacao->prefeitura_id)
            ->orderBy('name')
            ->get(['id', 'name', 'unidade_id'])
            ->map(fn ($u) => [
                'id' => $u->id,
                'nome' => $u->name,
                'unidade' => $u->unidade->nome ?? null,
            ])
            ->values();

        return view('Admin.Fiscalizacoes.show', compact('fiscalizacao', 'fiscais'));
    }

    /**
     * Salva os assinantes (servidores da prefeitura) do relatório de fiscalização.
     * Impressos para assinatura física — sem assinatura eletrônica.
     */
    public function salvarAssinantes(Request $request, $id)
    {
        $fiscalizacao = Fiscalizacao::findOrFail($id);
        $this->authorizeAccess($fiscalizacao);

        $data = $request->validate([
            'assinantes' => ['nullable', 'array'],
            'assinantes.*.nome' => ['required', 'string', 'max:255'],
            'assinantes.*.cargo' => ['nullable', 'string', 'max:255'],
            'assinantes.*.unidade' => ['nullable', 'string', 'max:255'],
        ]);

        $fiscalizacao->update([
            'assinantes' => array_values($data['assinantes'] ?? []),
        ]);

        return back()->with('success', 'Assinantes do relatório atualizados com sucesso.');
    }

    /**
     * Upload de múltiplas imagens do Relatório Fotográfico da fiscalização.
     */
    public function uploadFotos(Request $request, $id)
    {
        $fiscalizacao = Fiscalizacao::findOrFail($id);
        $this->authorizeAccess($fiscalizacao);

        $request->validate([
            'fotos' => ['required', 'array'],
            'fotos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ]);

        $destino = public_path('uploads/fiscalizacoes/fotos');
        if (! file_exists($destino)) {
            mkdir($destino, 0755, true);
        }

        $ordem = (int) $fiscalizacao->fotos()->max('ordem');

        foreach ($request->file('fotos') as $arquivo) {
            $nome = 'Fisc_'.$fiscalizacao->id.'_'.time().'_'.Str::random(6).'.'.$arquivo->getClientOriginalExtension();
            $arquivo->move($destino, $nome);

            $fiscalizacao->fotos()->create([
                'caminho' => 'uploads/fiscalizacoes/fotos/'.$nome,
                'ordem' => ++$ordem,
            ]);
        }

        return back()->with('success', 'Fotos adicionadas ao relatório fotográfico.');
    }

    /**
     * Remove uma imagem do Relatório Fotográfico.
     */
    public function deleteFoto($id, $fotoId)
    {
        $fiscalizacao = Fiscalizacao::findOrFail($id);
        $this->authorizeAccess($fiscalizacao);

        $foto = $fiscalizacao->fotos()->findOrFail($fotoId);

        if ($foto->caminho && file_exists(public_path($foto->caminho))) {
            @unlink(public_path($foto->caminho));
        }
        $foto->delete();

        return back()->with('success', 'Foto removida do relatório fotográfico.');
    }

    // =========================================================
    // EDIT — Formulário de Edição
    // =========================================================
    public function edit($id)
    {
        $fiscalizacao = Fiscalizacao::with(['fiscalizavel', 'prefeitura', 'user'])->findOrFail($id);

        $this->authorizeAccess($fiscalizacao);

        $user = auth()->user();
        $isPrefeituraUser = $user->hasRole('prefeitura') && $user->prefeitura_id;

        $tiposFiscalizacao = TipoFiscalizacaoEnum::cases();
        $conclusoes = ConclusaoFiscalEnum::cases();

        $fiscalizacao->contrato_info = $this->extrairInfoContrato($fiscalizacao->fiscalizavel);

        return view('Admin.Fiscalizacoes.edit', compact(
            'fiscalizacao',
            'isPrefeituraUser',
            'tiposFiscalizacao',
            'conclusoes'
        ));
    }

    // =========================================================
    // UPDATE — Atualizar Fiscalização
    // =========================================================
    public function update(FiscalizacaoRequest $request, $id)
    {
        $fiscalizacao = Fiscalizacao::findOrFail($id);

        $this->authorizeAccess($fiscalizacao);

        Log::info('🔄 Iniciando update da fiscalização', [
            'fiscalizacao_id' => $fiscalizacao->id,
        ]);

        $user = auth()->user();

        if ($user->hasRole('prefeitura') && $user->prefeitura_id) {
            if ($fiscalizacao->prefeitura_id != $user->prefeitura_id) {
                abort(403, 'Acesso não autorizado.');
            }
        }

        DB::beginTransaction();

        try {
            $dados = $request->validated();
            $dados['checklist_fiscalizacao'] = in_array($dados['tipo_contrato'], ['compras', 'servicos'], true)
                ? $this->normalizarChecklist($request)
                : null;

            if ($request->hasFile('relatorio_fotografico')) {
                if ($fiscalizacao->relatorio_fotografico && file_exists(public_path($fiscalizacao->relatorio_fotografico))) {
                    unlink(public_path($fiscalizacao->relatorio_fotografico));
                }

                $dados['relatorio_fotografico'] = $this->uploadRelatorioFotografico(
                    $request->file('relatorio_fotografico'),
                    $fiscalizacao->numero_fiscalizacao
                );
            } else {
                unset($dados['relatorio_fotografico']);
            }

            $fiscalizacao->update($dados);

            Log::info('✅ Fiscalização atualizada com sucesso', [
                'fiscalizacao_id' => $fiscalizacao->id,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.fiscalizacoes.index')
                ->with('success', 'Fiscalização atualizada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Erro ao atualizar fiscalização', [
                'fiscalizacao_id' => $fiscalizacao->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Erro ao atualizar: '.$e->getMessage());
        }
    }

    // =========================================================
    // DESTROY — Excluir Fiscalização
    // =========================================================
    public function destroy($id)
    {
        $fiscalizacao = Fiscalizacao::findOrFail($id);

        $this->authorizeAccess($fiscalizacao);

        Log::info('🗑️ Iniciando exclusão da fiscalização', [
            'fiscalizacao_id' => $fiscalizacao->id,
        ]);

        $user = auth()->user();
        if ($user->hasRole('prefeitura') && $user->prefeitura_id) {
            if ($fiscalizacao->prefeitura_id != $user->prefeitura_id) {
                abort(403, 'Acesso não autorizado.');
            }
        }

        try {
            $fiscalizacao->delete();

            Log::info('✅ Fiscalização deletada com sucesso', [
                'fiscalizacao_id' => $fiscalizacao->id,
            ]);

            return redirect()
                ->route('admin.fiscalizacoes.index')
                ->with('success', 'Fiscalização excluída com sucesso!');

        } catch (\Exception $e) {
            Log::error('❌ Erro ao excluir fiscalização', [
                'fiscalizacao_id' => $fiscalizacao->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erro ao excluir: '.$e->getMessage());
        }
    }

    /**
     * Faz o upload da imagem do relatório fotográfico e retorna o caminho relativo.
     */
    private function uploadRelatorioFotografico($arquivo, string $numeroFiscalizacao): string
    {
        $numeroLimpo = preg_replace('/[^A-Za-z0-9\-]/', '_', $numeroFiscalizacao);
        $nomeArquivo = 'Fiscalizacao_'.time().'_'.$numeroLimpo.'.'.$arquivo->getClientOriginalExtension();
        $caminho = 'uploads/fiscalizacoes/'.$nomeArquivo;

        if (! file_exists(public_path('uploads/fiscalizacoes'))) {
            mkdir(public_path('uploads/fiscalizacoes'), 0755, true);
        }

        $arquivo->move(public_path('uploads/fiscalizacoes'), $nomeArquivo);

        return $caminho;
    }

    /**
     * Normaliza o checklist de verificação inicial, garantindo que todas as
     * chaves de Fiscalizacao::CHECKLIST_ITENS sejam gravadas (marcadas ou não),
     * já que checkboxes desmarcados simplesmente não chegam no request.
     */
    private function normalizarChecklist(Request $request): array
    {
        return collect(array_keys(Fiscalizacao::CHECKLIST_ITENS))
            ->mapWithKeys(fn ($chave) => [$chave => $request->boolean("checklist_fiscalizacao.$chave")])
            ->all();
    }

    // =========================================================
    // BUSCAR CONTRATOS — Endpoint AJAX para Select2
    // =========================================================
    public function buscarContratos(Request $request)
    {
        $user = auth()->user();
        $termo = $request->get('q', '');
        $isLiciconAdmin = $user->hasAnyRole(['diretor_licicon', 'gerente_licicon']);
        // Só restringe por prefeitura quem de fato está vinculado a uma (role "prefeitura").
        // Usuários Licicon sem prefeitura_id (ex.: gerente_licicon) atuam sobre todas.
        $isPrefeituraUser = $user->hasRole('prefeitura') && $user->prefeitura_id;

        if (strlen($termo) < 2) {
            return response()->json(['results' => []]);
        }

        $resultados = [];

        // 1. FILTRO CONTRATOS MANUAIS
        $contratosManual = ContratoManual::with(['empresa', 'secretaria'])
            ->when($isPrefeituraUser, function ($q) use ($user) {
                return $q->where('prefeitura_id', $user->prefeitura_id);
            })
            ->when($user->unidade_id && ! $isLiciconAdmin, function ($q) use ($user) {
                return $q->where('unidade_id', $user->unidade_id);
            })
            ->where(function ($q) use ($termo) {
                $q->where('numero_contrato', 'LIKE', "%{$termo}%")
                    ->orWhere('objeto', 'LIKE', "%{$termo}%")
                    ->orWhereHas('empresa', fn ($e) => $e->where('razao_social', 'LIKE', "%{$termo}%"));
            })
            ->limit(10)
            ->get();

        foreach ($contratosManual as $cm) {
            $resultados[] = [
                'id' => $cm->id.'|App\\Models\\ContratoManual',
                'text' => ($cm->numero_contrato ?: 'S/N').' — '.Str::limit($this->limparHtml($cm->objeto), 60).' ('.($cm->empresa?->razao_social ?? 'Sem empresa').')',
                'numero_contrato' => $cm->numero_contrato,
                'objeto' => $this->limparHtml($cm->objeto),
                'numero_processo' => $cm->numero_processo,
                'modalidade' => $cm->modalidade?->getDisplayName() ?? '—',
                'secretaria' => $cm->secretaria?->nome ?? '—',
                'razao_social' => $cm->empresa?->razao_social ?? '—',
                'cnpj' => $cm->empresa?->cnpj_formatado ?? '—',
                'endereco' => $cm->empresa?->endereco ?? '—',
                'representante' => $cm->empresa?->representante ?? '—',
                'origem' => 'Contrato Manual',
            ];
        }

        // 2. FILTRO CONTRATOS DO SISTEMA (Processos)
        $processos = Processo::with(['contrato', 'vencedores', 'prefeitura', 'detalhe'])
            ->has('contrato')
            ->when($isPrefeituraUser, function ($q) use ($user) {
                return $q->where('prefeitura_id', $user->prefeitura_id);
            })
            ->when($user->unidade_id && ! $isLiciconAdmin, function ($q) use ($user) {
                // Filtra através do usuário criador do processo
                return $q->whereHas('user', fn ($u) => $u->where('unidade_id', $user->unidade_id));
            })
            ->where(function ($q) use ($termo) {
                $q->where('numero_processo', 'LIKE', "%{$termo}%")
                    ->orWhere('objeto', 'LIKE', "%{$termo}%")
                    ->orWhereHas('contrato', function ($q2) use ($termo) {
                        $q2->where('numero_contrato', 'LIKE', "%{$termo}%");
                    })
                    ->orWhereHas('vencedores', function ($q2) use ($termo) {
                        $q2->where('razao_social', 'LIKE', "%{$termo}%")
                            ->orWhere('cnpj', 'LIKE', "%{$termo}%")
                            ->orWhere('cpf', 'LIKE', "%{$termo}%");
                    });
            })
            ->limit(10)
            ->get();

        foreach ($processos as $proc) {
            $vencedor = $proc->vencedores->first();
            $resultados[] = [
                'id' => $proc->contrato?->id.'|App\\Models\\Contrato',
                'text' => ($proc->contrato?->numero_contrato ?: 'S/N').' — '.Str::limit($this->limparHtml($proc->objeto), 60).' ('.($vencedor?->razao_social ?? 'Sem empresa').')',
                'numero_contrato' => $proc->contrato?->numero_contrato,
                'objeto' => $this->limparHtml($proc->objeto),
                'numero_processo' => $proc->numero_processo,
                'modalidade' => $proc->modalidade?->getDisplayName() ?? '—',
                'secretaria' => $proc->unidade_numeracao ?? $proc->detalhe?->secretaria ?? '—',
                'razao_social' => $vencedor?->razao_social ?? '—',
                'cnpj' => $vencedor?->cnpj_formatado ?? $vencedor?->cpf_formatado ?? '—',
                'endereco' => $vencedor?->endereco ?? '—',
                'representante' => $vencedor?->representante ?? '—',
                'origem' => 'Contrato do Sistema',
            ];
        }

        return response()->json(['results' => $resultados]);
    }

    /**
     * Autoriza o acesso à fiscalização (multi-tenant)
     */
    private function authorizeAccess(Fiscalizacao $fiscalizacao)
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon'])) {
            return;
        }

        if ($user->hasRole('prefeitura') && $fiscalizacao->prefeitura_id != $user->prefeitura_id) {
            abort(403, 'Acesso não autorizado à prefeitura.');
        }

        if ($user->unidade_id) {
            $fiscalizavel = $fiscalizacao->fiscalizavel;

            if ($fiscalizavel instanceof ContratoManual) {
                if ($fiscalizavel->unidade_id != $user->unidade_id) {
                    abort(403, 'Acesso negado: Este contrato manual pertence a outra unidade.');
                }
            } elseif ($fiscalizavel instanceof Contrato) {
                if ($fiscalizavel->processo->user->unidade_id != $user->unidade_id) {
                    abort(403, 'Acesso negado: Este contrato do sistema pertence a outra unidade.');
                }
            }
        }
    }

    /**
     * Resolve a prefeitura_id a partir do contrato selecionado
     */
    private function resolverPrefeituraId(Request $request): int
    {
        $tipo = $request->input('fiscalizavel_type');
        $id = $request->input('fiscalizavel_id');

        if ($tipo === 'App\\Models\\ContratoManual') {
            $contrato = ContratoManual::findOrFail($id);

            return $contrato->prefeitura_id;
        }

        if ($tipo === 'App\\Models\\Contrato') {
            $contrato = Contrato::findOrFail($id);
            $processo = $contrato->processo;

            return $processo->prefeitura_id;
        }

        throw new \Exception('Tipo de contrato inválido.');
    }

    public function gerarRelatorio($id)
    {
        $fiscalizacao = Fiscalizacao::with(['fiscalizavel', 'prefeitura', 'user'])->findOrFail($id);

        $this->authorizeAccess($fiscalizacao);

        $fiscalizacao->contrato_info = $this->extrairInfoContrato($fiscalizacao->fiscalizavel);
        $pdf = Pdf::loadView('Admin.Fiscalizacoes.relatorio', compact('fiscalizacao'));

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        $nomeArquivo = 'Relatorio_Fiscalizacao_'.str_replace(['/', '\\'], '_', $fiscalizacao->numero_fiscalizacao).'.pdf';

        return $pdf->stream($nomeArquivo);
    }

    public function imprimirRelatorioTecnico($id)
    {
        $fiscalizacao = Fiscalizacao::with(['fiscalizavel', 'prefeitura', 'user'])->findOrFail($id);
        $this->authorizeAccess($fiscalizacao);

        $fiscalizacao->contrato_info = $this->extrairInfoContrato($fiscalizacao->fiscalizavel);

        $pdf = Pdf::loadView('Admin.Fiscalizacoes.relatorio_tecnico', compact('fiscalizacao'));
        $pdf->setPaper('a4', 'portrait');

        $numeroLimpo = str_replace(['/', '\\'], '_', $fiscalizacao->numero_fiscalizacao);

        return $pdf->stream("Relatorio_Tecnico_{$numeroLimpo}.pdf");
    }

    public function imprimirNotificacoes($id)
    {
        $fiscalizacao = Fiscalizacao::with(['fiscalizavel', 'prefeitura', 'user'])->findOrFail($id);
        $this->authorizeAccess($fiscalizacao);

        $fiscalizacao->contrato_info = $this->extrairInfoContrato($fiscalizacao->fiscalizavel);

        $pdf = Pdf::loadView('Admin.Fiscalizacoes.notificacoes', compact('fiscalizacao'));
        $pdf->setPaper('a4', 'portrait');

        $numeroLimpo = str_replace(['/', '\\'], '_', $fiscalizacao->numero_fiscalizacao);

        return $pdf->stream("Notificacoes_{$numeroLimpo}.pdf");
    }

    public function selecionarContrato(Request $request)
    {
        $user = auth()->user();
        $isLiciconAdmin = $user->hasAnyRole(['diretor_licicon', 'gerente_licicon']);
        // Só existe UMA prefeitura possível para quem tem a role "prefeitura".
        // Demais perfis (equipe Licicon) podem atuar sobre qualquer prefeitura e,
        // por isso, recebem um filtro opcional em vez de ficarem restritos à sua própria
        // prefeitura_id (que normalmente é NULL para esses usuários).
        $isPrefeituraUser = $user->hasRole('prefeitura') && $user->prefeitura_id;
        $prefeituraId = $isPrefeituraUser ? $user->prefeitura_id : $request->input('prefeitura_id');

        $prefeituras = $isPrefeituraUser
            ? Prefeitura::where('id', $user->prefeitura_id)->get()
            : Prefeitura::orderBy('nome')->get();

        $manuais = ContratoManual::with(['empresa', 'fiscalizacoes', 'secretaria'])
            ->when($prefeituraId, fn ($q) => $q->where('prefeitura_id', $prefeituraId))
            ->when($user->unidade_id && ! $isLiciconAdmin, function ($q) use ($user) {
                return $q->where('unidade_id', $user->unidade_id);
            })
            ->get()
            ->map(function ($item) {
                $ultima = $item->fiscalizacoes->first();

                return [
                    'id' => $item->id,
                    'type' => 'App\\Models\\ContratoManual',
                    'numero' => $item->numero_contrato ?? 'S/N',
                    'objeto' => $this->limparHtml($item->objeto),
                    'empresa_nome' => $item->empresa->razao_social ?? 'Sem Empresa',
                    'empresa_cnpj' => $item->empresa->cnpj ?? '',
                    'secretaria' => $item->secretaria?->nome ?? 'Não informada',
                    'ultima_fiscalizacao' => $ultima?->data_fiscalizacao ?? null,
                    'ultima_fiscalizacao_id' => $ultima?->id ?? null,
                    'origem' => 'Manual',
                ];
            });

        $sistema = Processo::with(['contrato.fiscalizacoes', 'vencedores', 'detalhe'])
            ->has('contrato')
            ->when($prefeituraId, fn ($q) => $q->where('prefeitura_id', $prefeituraId))
            ->when($user->unidade_id && ! $isLiciconAdmin, function ($q) use ($user) {
                return $q->whereHas('user', fn ($u) => $u->where('unidade_id', $user->unidade_id));
            })
            ->get()
            ->map(function ($item) {
                $vencedor = $item->vencedores->first();
                $ultima = $item->contrato->fiscalizacoes->first();

                return [
                    'id' => $item->contrato->id,
                    'type' => 'App\\Models\\Contrato',
                    'numero' => $item->contrato->numero_contrato ?? 'S/N',
                    'objeto' => $this->limparHtml($item->objeto),
                    'empresa_nome' => $vencedor->razao_social ?? 'Sem Empresa',
                    'empresa_cnpj' => $vencedor->cnpj ?? $vencedor->cpf ?? '',
                    'secretaria' => $item->unidade_numeracao ?? $item->detalhe?->secretaria ?? 'Não informada',
                    'ultima_fiscalizacao' => $ultima?->data_fiscalizacao ?? null,
                    'ultima_fiscal_id' => $ultima?->id ?? null,
                    'origem' => 'Sistema',
                ];
            });

        $todosContratos = $manuais->concat($sistema);

        $empresas = $todosContratos->groupBy('empresa_nome')->map(function ($contratos, $nome) {
            return [
                'nome' => $nome,
                'cnpj' => $contratos->first()['empresa_cnpj'],
                'contratos' => $contratos->sortBy(function ($c) {
                    return $c['ultima_fiscalizacao'] ? 1 : 0;
                })->values(),
                'pendentes' => $contratos->whereNull('ultima_fiscalizacao')->count(),
            ];
        })->sortBy('nome');

        // Opções para os filtros rápidos (client-side) de secretaria e empresa,
        // já restritas ao conjunto de contratos efetivamente carregado.
        $secretariasDisponiveis = $todosContratos->pluck('secretaria')->unique()->sort()->values();
        $empresasDisponiveis = $empresas->keys()->sort()->values();

        return view('Admin.Fiscalizacoes.selecionar-contrato', compact(
            'empresas',
            'prefeituras',
            'isPrefeituraUser',
            'prefeituraId',
            'secretariasDisponiveis',
            'empresasDisponiveis'
        ));
    }
}
