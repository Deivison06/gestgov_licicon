<?php

namespace App\Http\Controllers;

use App\Enums\StatusOcorrenciaEnum;
use App\Http\Controllers\Concerns\ExtraiInfoContrato;
use App\Http\Requests\OcorrenciaRequest;
use App\Models\Contrato;
use App\Models\ContratoManual;
use App\Models\Ocorrencia;
use App\Models\Prefeitura;
use App\Models\Processo;
use App\Models\Unidade;
use App\Models\User;
use App\Services\OcorrenciaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OcorrenciaController extends Controller
{
    use ExtraiInfoContrato;

    public function __construct(protected OcorrenciaService $ocorrenciaService) {}

    // =========================================================
    // INDEX — Listagem de Ocorrências
    // =========================================================
    public function index(Request $request)
    {
        $user = auth()->user();
        $userPrefeituraId = $user->prefeitura_id;
        $isPrefeituraUser = $user->hasRole('prefeitura') && $userPrefeituraId;
        $isLiciconAdmin = $user->hasAnyRole(['diretor_licicon', 'gerente_licicon']);

        $query = Ocorrencia::with(['fiscalizavel', 'prefeitura', 'user'])->latest();

        // Isolamento de unidade (mesma regra de Fiscalização)
        if ($user->unidade_id && ! $isLiciconAdmin) {
            $query->whereHasMorph('fiscalizavel', [ContratoManual::class, Contrato::class], function ($q, $type) use ($user) {
                if ($type === ContratoManual::class) {
                    $q->where('contratos_manuais.unidade_id', $user->unidade_id);
                } else {
                    $q->whereHas('processo.user', function ($u) use ($user) {
                        $u->where('unidade_id', $user->unidade_id);
                    });
                }
            });
        }

        $prefeituras = $isPrefeituraUser
            ? Prefeitura::where('id', $userPrefeituraId)->get()
            : Prefeitura::orderBy('nome')->get();

        $unidades = $isPrefeituraUser
            ? Unidade::where('prefeitura_id', $userPrefeituraId)->orderBy('nome')->get()
            : Unidade::orderBy('nome')->get();

        if ($request->filled('unidade_id')) {
            $unidadeFiltro = $request->unidade_id;
            $query->whereHasMorph('fiscalizavel', [ContratoManual::class, Contrato::class], function ($q, $type) use ($unidadeFiltro) {
                if ($type === ContratoManual::class) {
                    $q->where('contratos_manuais.unidade_id', $unidadeFiltro);
                } else {
                    $q->whereHas('processo.user', fn ($u) => $u->where('unidade_id', $unidadeFiltro));
                }
            });
        }

        if (! $isPrefeituraUser && $request->filled('prefeitura_id')) {
            $query->where('prefeitura_id', $request->prefeitura_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->situacao);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('numero_ocorrencia', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('descricao_fato', 'LIKE', "%{$searchTerm}%");
            });
        }

        $ocorrencias = $query->paginate(10);

        $ocorrencias->getCollection()->transform(function ($ocorrencia) {
            $ocorrencia->contrato_info = $this->extrairInfoContrato($ocorrencia->fiscalizavel);

            return $ocorrencia;
        });

        return view('Admin.Ocorrencias.index', compact(
            'ocorrencias',
            'prefeituras',
            'unidades',
            'isPrefeituraUser'
        ));
    }

    // =========================================================
    // SELECIONAR CONTRATO — Tela intermediária antes de criar
    // =========================================================
    public function selecionarContrato(Request $request)
    {
        $user = auth()->user();
        $isLiciconAdmin = $user->hasAnyRole(['diretor_licicon', 'gerente_licicon']);
        $isPrefeituraUser = $user->hasRole('prefeitura') && $user->prefeitura_id;
        $prefeituraId = $isPrefeituraUser ? $user->prefeitura_id : $request->input('prefeitura_id');

        $prefeituras = $isPrefeituraUser
            ? Prefeitura::where('id', $user->prefeitura_id)->get()
            : Prefeitura::orderBy('nome')->get();

        $manuais = ContratoManual::with(['empresa', 'ocorrencias', 'secretaria'])
            ->when($prefeituraId, fn ($q) => $q->where('prefeitura_id', $prefeituraId))
            ->when($user->unidade_id && ! $isLiciconAdmin, function ($q) use ($user) {
                return $q->where('unidade_id', $user->unidade_id);
            })
            ->get()
            ->map(function ($item) {
                $ultima = $item->ocorrencias->first();

                return [
                    'id' => $item->id,
                    'type' => 'App\\Models\\ContratoManual',
                    'numero' => $item->numero_contrato ?? 'S/N',
                    'objeto' => $this->limparHtml($item->objeto),
                    'empresa_nome' => $item->empresa->razao_social ?? 'Sem Empresa',
                    'empresa_cnpj' => $item->empresa->cnpj ?? '',
                    'secretaria' => $item->secretaria?->nome ?? 'Não informada',
                    'ultima_ocorrencia' => $ultima?->data_ocorrencia ?? null,
                    'ultima_ocorrencia_id' => $ultima?->id ?? null,
                    'origem' => 'Manual',
                ];
            });

        $sistema = Processo::with(['contrato.ocorrencias', 'vencedores', 'detalhe'])
            ->has('contrato')
            ->when($prefeituraId, fn ($q) => $q->where('prefeitura_id', $prefeituraId))
            ->when($user->unidade_id && ! $isLiciconAdmin, function ($q) use ($user) {
                return $q->whereHas('user', fn ($u) => $u->where('unidade_id', $user->unidade_id));
            })
            ->get()
            ->map(function ($item) {
                $vencedor = $item->vencedores->first();
                $ultima = $item->contrato->ocorrencias->first();

                return [
                    'id' => $item->contrato->id,
                    'type' => 'App\\Models\\Contrato',
                    'numero' => $item->contrato->numero_contrato ?? 'S/N',
                    'objeto' => $this->limparHtml($item->objeto),
                    'empresa_nome' => $vencedor->razao_social ?? 'Sem Empresa',
                    'empresa_cnpj' => $vencedor->cnpj ?? $vencedor->cpf ?? '',
                    'secretaria' => $item->unidade_numeracao ?? $item->detalhe?->secretaria ?? 'Não informada',
                    'ultima_ocorrencia' => $ultima?->data_ocorrencia ?? null,
                    'ultima_ocorrencia_id' => $ultima?->id ?? null,
                    'origem' => 'Sistema',
                ];
            });

        $todosContratos = $manuais->concat($sistema);

        $empresas = $todosContratos->groupBy('empresa_nome')->map(function ($contratos, $nome) {
            return [
                'nome' => $nome,
                'cnpj' => $contratos->first()['empresa_cnpj'],
                'contratos' => $contratos->sortBy(function ($c) {
                    return $c['ultima_ocorrencia'] ? 1 : 0;
                })->values(),
                'sem_registro' => $contratos->whereNull('ultima_ocorrencia')->count(),
            ];
        })->sortBy('nome');

        $secretariasDisponiveis = $todosContratos->pluck('secretaria')->unique()->sort()->values();
        $empresasDisponiveis = $empresas->keys()->sort()->values();

        return view('Admin.Ocorrencias.selecionar-contrato', compact(
            'empresas',
            'prefeituras',
            'isPrefeituraUser',
            'prefeituraId',
            'secretariasDisponiveis',
            'empresasDisponiveis'
        ));
    }

    // =========================================================
    // CREATE — Formulário de Criação
    // =========================================================
    public function create(Request $request)
    {
        $user = auth()->user();
        $isPrefeituraUser = $user->hasRole('prefeitura') && $user->prefeitura_id;

        $contratoPreSelecionado = null;

        if ($request->has(['id', 'type'])) {
            $id = $request->id;
            $type = $request->type;

            $allowedTypes = [Contrato::class, ContratoManual::class];

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

        return view('Admin.Ocorrencias.create', compact('isPrefeituraUser', 'contratoPreSelecionado'));
    }

    // =========================================================
    // STORE — Salvar nova Ocorrência
    // =========================================================
    public function store(OcorrenciaRequest $request)
    {
        $user = auth()->user();

        try {
            $prefeituraId = $this->resolverPrefeituraId($request);

            if ($user->hasRole('prefeitura') && $user->prefeitura_id) {
                if ($prefeituraId != $user->prefeitura_id) {
                    return back()->withInput()->with('error', 'Você só pode registrar ocorrências para sua própria prefeitura.');
                }
            }

            $dados = $request->validated();
            unset($dados['anexos_fato']);
            $dados['prefeitura_id'] = $prefeituraId;
            $dados['user_id'] = $user->id;
            $dados['tipo_comprovacao'] = $this->normalizarTipoComprovacao($request);

            $ocorrencia = $this->ocorrenciaService->registrar($dados, $request->file('anexos_fato', []));

            return redirect()
                ->route('admin.ocorrencias.show', $ocorrencia->id)
                ->with('success', 'Ocorrência registrada com sucesso!');
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('❌ Erro ao registrar ocorrência', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Erro ao registrar: '.$e->getMessage());
        }
    }

    // =========================================================
    // SHOW — Visualizar Ocorrência
    // =========================================================
    public function show($id)
    {
        $ocorrencia = Ocorrencia::with(['fiscalizavel', 'prefeitura.unidades', 'user', 'anexos'])->findOrFail($id);

        $this->authorizeAccess($ocorrencia);

        $ocorrencia->contrato_info = $this->extrairInfoContrato($ocorrencia->fiscalizavel);

        $fiscais = User::with('unidade')
            ->where('prefeitura_id', $ocorrencia->prefeitura_id)
            ->orderBy('name')
            ->get(['id', 'name', 'unidade_id'])
            ->map(fn ($u) => [
                'id' => $u->id,
                'nome' => $u->name,
                'unidade' => $u->unidade->nome ?? null,
            ])
            ->values();

        return view('Admin.Ocorrencias.show', compact('ocorrencia', 'fiscais'));
    }

    // =========================================================
    // EDIT — Formulário de Edição
    // =========================================================
    public function edit($id)
    {
        $ocorrencia = Ocorrencia::with(['fiscalizavel', 'prefeitura', 'user', 'anexos'])->findOrFail($id);

        $this->authorizeAccess($ocorrencia);

        if ($ocorrencia->status === StatusOcorrenciaEnum::CONCLUIDA) {
            return redirect()
                ->route('admin.ocorrencias.show', $ocorrencia->id)
                ->with('error', 'Esta ocorrência já foi concluída e não pode mais ser editada.');
        }

        $user = auth()->user();
        $isPrefeituraUser = $user->hasRole('prefeitura') && $user->prefeitura_id;

        $ocorrencia->contrato_info = $this->extrairInfoContrato($ocorrencia->fiscalizavel);

        return view('Admin.Ocorrencias.edit', compact('ocorrencia', 'isPrefeituraUser'));
    }

    // =========================================================
    // UPDATE — Atualizar Ocorrência
    // =========================================================
    public function update(OcorrenciaRequest $request, $id)
    {
        $ocorrencia = Ocorrencia::findOrFail($id);

        $this->authorizeAccess($ocorrencia);

        $user = auth()->user();
        if ($user->hasRole('prefeitura') && $user->prefeitura_id && $ocorrencia->prefeitura_id != $user->prefeitura_id) {
            abort(403, 'Acesso não autorizado.');
        }

        try {
            $dados = $request->validated();
            unset($dados['anexos_fato']);
            $dados['tipo_comprovacao'] = $this->normalizarTipoComprovacao($request);

            $this->ocorrenciaService->atualizar($ocorrencia, $dados);

            if ($request->hasFile('anexos_fato')) {
                $this->ocorrenciaService->anexar($ocorrencia, 'fato', $request->file('anexos_fato'));
            }

            return redirect()
                ->route('admin.ocorrencias.show', $ocorrencia->id)
                ->with('success', 'Ocorrência atualizada com sucesso!');
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('❌ Erro ao atualizar ocorrência', ['ocorrencia_id' => $ocorrencia->id, 'error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Erro ao atualizar: '.$e->getMessage());
        }
    }

    // =========================================================
    // DESTROY — Excluir Ocorrência (só permitido em rascunho)
    // =========================================================
    public function destroy($id)
    {
        $ocorrencia = Ocorrencia::findOrFail($id);

        $this->authorizeAccess($ocorrencia);

        if ($ocorrencia->status !== StatusOcorrenciaEnum::RASCUNHO) {
            return back()->with('error', 'Só é possível excluir ocorrências em rascunho. Esta já foi registrada.');
        }

        foreach ($ocorrencia->anexos as $anexo) {
            $this->ocorrenciaService->removerAnexo($anexo);
        }

        $ocorrencia->delete();

        return redirect()
            ->route('admin.ocorrencias.index')
            ->with('success', 'Ocorrência (rascunho) excluída com sucesso!');
    }

    // =========================================================
    // CONCLUIR — Transição REGISTRADA → CONCLUIDA
    // =========================================================
    public function concluir($id)
    {
        $ocorrencia = Ocorrencia::findOrFail($id);
        $this->authorizeAccess($ocorrencia);

        try {
            $this->ocorrenciaService->concluir($ocorrencia);

            return back()->with('success', 'Ocorrência concluída com sucesso!');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // ANEXOS — Upload/Remoção genérica por categoria
    // =========================================================
    public function uploadAnexo(Request $request, $id)
    {
        $ocorrencia = Ocorrencia::findOrFail($id);
        $this->authorizeAccess($ocorrencia);

        $data = $request->validate([
            'categoria' => 'required|string|in:fato,resposta,correcao',
            'anexos' => 'required|array|min:1',
            'anexos.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        $this->ocorrenciaService->anexar($ocorrencia, $data['categoria'], $request->file('anexos'));

        return back()->with('success', 'Anexo(s) adicionado(s) com sucesso.');
    }

    public function deleteAnexo($id, $anexoId)
    {
        $ocorrencia = Ocorrencia::findOrFail($id);
        $this->authorizeAccess($ocorrencia);

        $anexo = $ocorrencia->anexos()->findOrFail($anexoId);
        $this->ocorrenciaService->removerAnexo($anexo);

        return back()->with('success', 'Anexo removido com sucesso.');
    }

    // =========================================================
    // ATESTO DE CORREÇÃO
    // =========================================================
    public function salvarAtesto(Request $request, $id)
    {
        $ocorrencia = Ocorrencia::findOrFail($id);
        $this->authorizeAccess($ocorrencia);

        $data = $request->validate([
            'correcao_descricao' => 'required|string',
            'correcao_data' => 'required|date',
            'correcao_elementos_comprobatorios' => 'nullable|string',
            'anexos_correcao' => 'nullable|array',
            'anexos_correcao.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        try {
            $this->ocorrenciaService->registrarAtesto($ocorrencia, $data, $request->file('anexos_correcao', []));

            return back()->with('success', 'Atesto de correção registrado com sucesso.');
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Salva os assinantes (servidores da prefeitura) do registro de ocorrência.
     * Mesmo componente/padrão usado em Fiscalização.
     */
    public function salvarAssinantes(Request $request, $id)
    {
        $ocorrencia = Ocorrencia::findOrFail($id);
        $this->authorizeAccess($ocorrencia);

        $data = $request->validate([
            'assinantes' => ['nullable', 'array'],
            'assinantes.*.nome' => ['required', 'string', 'max:255'],
            'assinantes.*.cargo' => ['nullable', 'string', 'max:255'],
            'assinantes.*.unidade' => ['nullable', 'string', 'max:255'],
        ]);

        $ocorrencia->update([
            'assinantes' => array_values($data['assinantes'] ?? []),
        ]);

        return back()->with('success', 'Assinantes do registro atualizados com sucesso.');
    }

    // =========================================================
    // PDFs
    // =========================================================
    public function gerarRegistro($id)
    {
        $ocorrencia = Ocorrencia::with(['fiscalizavel', 'prefeitura', 'user', 'anexos'])->findOrFail($id);
        $this->authorizeAccess($ocorrencia);

        $ocorrencia->contrato_info = $this->extrairInfoContrato($ocorrencia->fiscalizavel);

        $pdf = Pdf::loadView('Admin.Ocorrencias.registro', compact('ocorrencia'));
        $pdf->setPaper('a4', 'portrait');

        $numeroLimpo = str_replace(['/', '\\'], '_', $ocorrencia->numero_ocorrencia);

        return $pdf->stream("Registro_Ocorrencia_{$numeroLimpo}.pdf");
    }

    public function gerarNotificacoes($id)
    {
        $ocorrencia = Ocorrencia::with(['fiscalizavel', 'prefeitura', 'user'])->findOrFail($id);
        $this->authorizeAccess($ocorrencia);

        $ocorrencia->contrato_info = $this->extrairInfoContrato($ocorrencia->fiscalizavel);

        $pdf = Pdf::loadView('Admin.Ocorrencias.notificacoes', compact('ocorrencia'));
        $pdf->setPaper('a4', 'portrait');

        $numeroLimpo = str_replace(['/', '\\'], '_', $ocorrencia->numero_ocorrencia);

        return $pdf->stream("Notificacoes_Ocorrencia_{$numeroLimpo}.pdf");
    }

    public function gerarAtesto($id)
    {
        $ocorrencia = Ocorrencia::with(['fiscalizavel', 'prefeitura', 'user'])->findOrFail($id);
        $this->authorizeAccess($ocorrencia);

        if (! $ocorrencia->correcao_descricao || ! $ocorrencia->correcao_data) {
            return back()->with('error', 'Preencha o Atesto de Correção antes de gerar o documento.');
        }

        $ocorrencia->contrato_info = $this->extrairInfoContrato($ocorrencia->fiscalizavel);

        $pdf = Pdf::loadView('Admin.Ocorrencias.atesto', compact('ocorrencia'));
        $pdf->setPaper('a4', 'portrait');

        $numeroLimpo = str_replace(['/', '\\'], '_', $ocorrencia->numero_ocorrencia);

        return $pdf->stream("Atesto_Correcao_{$numeroLimpo}.pdf");
    }

    // =========================================================
    // Helpers privados
    // =========================================================

    /**
     * Autoriza o acesso à ocorrência (multi-tenant), mesma regra usada em
     * FiscalizacaoController::authorizeAccess().
     */
    private function authorizeAccess(Ocorrencia $ocorrencia)
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon'])) {
            return;
        }

        if ($user->hasRole('prefeitura') && $ocorrencia->prefeitura_id != $user->prefeitura_id) {
            abort(403, 'Acesso não autorizado à prefeitura.');
        }

        if ($user->unidade_id) {
            $fiscalizavel = $ocorrencia->fiscalizavel;

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
     * Resolve a prefeitura_id a partir do contrato selecionado, mesma regra
     * usada em FiscalizacaoController::resolverPrefeituraId().
     */
    private function resolverPrefeituraId(Request $request): int
    {
        $tipo = $request->input('fiscalizavel_type');
        $id = $request->input('fiscalizavel_id');

        if ($tipo === 'App\\Models\\ContratoManual') {
            return ContratoManual::findOrFail($id)->prefeitura_id;
        }

        if ($tipo === 'App\\Models\\Contrato') {
            return Contrato::findOrFail($id)->processo->prefeitura_id;
        }

        throw new \Exception('Tipo de contrato inválido.');
    }

    /**
     * Normaliza o checkbox de "meio de comprovação", garantindo que todas as
     * chaves de Ocorrencia::TIPOS_COMPROVACAO sejam gravadas (marcadas ou
     * não), já que checkboxes desmarcados simplesmente não chegam no request.
     */
    private function normalizarTipoComprovacao(Request $request): array
    {
        return collect(array_keys(Ocorrencia::TIPOS_COMPROVACAO))
            ->mapWithKeys(fn ($chave) => [$chave => $request->boolean("tipo_comprovacao.$chave")])
            ->all();
    }
}
