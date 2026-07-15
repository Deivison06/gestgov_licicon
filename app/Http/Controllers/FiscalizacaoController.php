<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Processo;
use App\Models\Unidade;
use App\Models\Vencedor;
use App\Models\Prefeitura;
use App\Models\Fiscalizacao;
use App\Models\ContratoManual;
use Illuminate\Http\Request;
use App\Enums\TipoFiscalizacaoEnum;
use App\Enums\ConclusaoFiscalEnum;
use App\Http\Requests\FiscalizacaoRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class FiscalizacaoController extends Controller
{
    // =========================================================
    // INDEX — Listagem de Fiscalizações
    // =========================================================
    public function index(Request $request)
    {
        $user = auth()->user();
        $userPrefeituraId = $user->prefeitura_id;
        $isPrefeituraUser = $user->hasRole('prefeitura') && $userPrefeituraId;
        $isLiciconAdmin = $user->hasAnyRole(['diretor_licicon', 'gerente_licicon']);

        $query = Fiscalizacao::with(['fiscalizavel', 'prefeitura', 'user'])->latest();

        // 1. ISOLAMENTO DE UNIDADE (Fiscais Restritos)
        // Usa a tabela explícita ou relação com User criador do processo para evitar erro 1054
        if ($user->unidade_id && !$isLiciconAdmin) {
            $query->whereHasMorph('fiscalizavel', [ContratoManual::class, Contrato::class], function ($q, $type) use ($user) {
                if ($type === ContratoManual::class) {
                    $q->where('contratos_manuais.unidade_id', $user->unidade_id);
                } else {
                    $q->whereHas('processo.user', function($u) use ($user) {
                        $u->where('unidade_id', $user->unidade_id);
                    });
                }
            });
        }

        // 2. BUSCA DE DADOS PARA FILTROS
        $prefeituras = $isPrefeituraUser
            ? Prefeitura::where('id', $userPrefeituraId)->get()
            : Prefeitura::orderBy('nome')->get();

        $unidades = $isPrefeituraUser
            ? Unidade::where('prefeitura_id', $userPrefeituraId)->orderBy('nome')->get()
            : Unidade::orderBy('nome')->get();

        // 3. FILTRO DE UNIDADE VIA REQUEST (Seleção na tela)
        if ($request->filled('unidade_id')) {
            $unidadeFiltro = $request->unidade_id;
            $query->whereHasMorph('fiscalizavel', [ContratoManual::class, Contrato::class], function ($q, $type) use ($unidadeFiltro) {
                if ($type === ContratoManual::class) {
                    $q->where('contratos_manuais.unidade_id', $unidadeFiltro);
                } else {
                    $q->whereHas('processo.user', fn($u) => $u->where('unidade_id', $unidadeFiltro));
                }
            });
        }

        // Multi-tenant para admin
        if (!$isPrefeituraUser && $request->filled('prefeitura_id')) {
            $query->where('prefeitura_id', $request->prefeitura_id);
        }

        // Filtro por tipo de contrato
        if ($request->filled('tipo_contrato')) {
            $query->where('tipo_contrato', $request->tipo_contrato);
        }

        // Filtro de pesquisa livre
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('numero_fiscalizacao', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('execucao_objeto', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('qualidade_entregas', 'LIKE', "%{$searchTerm}%");
            });
        }

        $fiscalizacoes = $query->paginate(10);

        $fiscalizacoes->getCollection()->transform(function ($fiscalizacao) {
            $fiscalizacao->contrato_info = $this->extrairInfoContrato($fiscalizacao);
            return $fiscalizacao;
        });

        $tiposFiscalizacao = TipoFiscalizacaoEnum::cases();

        return view('Admin.Fiscalizacoes.index', compact(
            'fiscalizacoes',
            'tiposFiscalizacao',
            'prefeituras',
            'unidades',
            'isPrefeituraUser'
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
                ContratoManual::class
            ];

            if (in_array($type, $allowedTypes)) {
                $contratoModel = $type::find($id);

                if ($contratoModel) {
                    $fiscFake = new Fiscalizacao([
                        'fiscalizavel_id' => $id,
                        'fiscalizavel_type' => $type
                    ]);

                    $fiscFake->setRelation('fiscalizavel', $contratoModel);

                    $contratoPreSelecionado = $this->extrairInfoContrato($fiscFake);
                    $contratoPreSelecionado['id'] = $id . '|' . $type;
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
            'dados_recebidos' => $request->except(['_token'])
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
                'contrato_id' => $fiscalizacao->fiscalizavel_id
            ]);

            DB::commit();

            return redirect()
                ->route('admin.fiscalizacoes.index')
                ->with('success', 'Fiscalização cadastrada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Erro ao salvar fiscalização', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()->with('error', 'Erro ao salvar fiscalização: ' . $e->getMessage());
        }
    }

    // =========================================================
    // SHOW — Visualizar Fiscalização
    // =========================================================
    public function show($id)
    {
        $fiscalizacao = Fiscalizacao::with(['fiscalizavel', 'prefeitura.unidades', 'user', 'fotos'])->findOrFail($id);

        $this->authorizeAccess($fiscalizacao);

        $fiscalizacao->contrato_info = $this->extrairInfoContrato($fiscalizacao);

        return view('Admin.Fiscalizacoes.show', compact('fiscalizacao'));
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
            'assinantes'           => ['nullable', 'array'],
            'assinantes.*.nome'    => ['required', 'string', 'max:255'],
            'assinantes.*.cargo'   => ['nullable', 'string', 'max:255'],
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
            'fotos'   => ['required', 'array'],
            'fotos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ]);

        $destino = public_path('uploads/fiscalizacoes/fotos');
        if (!file_exists($destino)) {
            mkdir($destino, 0755, true);
        }

        $ordem = (int) $fiscalizacao->fotos()->max('ordem');

        foreach ($request->file('fotos') as $arquivo) {
            $nome = 'Fisc_' . $fiscalizacao->id . '_' . time() . '_' . Str::random(6) . '.' . $arquivo->getClientOriginalExtension();
            $arquivo->move($destino, $nome);

            $fiscalizacao->fotos()->create([
                'caminho' => 'uploads/fiscalizacoes/fotos/' . $nome,
                'ordem'   => ++$ordem,
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

        $fiscalizacao->contrato_info = $this->extrairInfoContrato($fiscalizacao);

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
            'fiscalizacao_id' => $fiscalizacao->id
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
                'fiscalizacao_id' => $fiscalizacao->id
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
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()->with('error', 'Erro ao atualizar: ' . $e->getMessage());
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
            'fiscalizacao_id' => $fiscalizacao->id
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
                'fiscalizacao_id' => $fiscalizacao->id
            ]);

            return redirect()
                ->route('admin.fiscalizacoes.index')
                ->with('success', 'Fiscalização excluída com sucesso!');

        } catch (\Exception $e) {
            Log::error('❌ Erro ao excluir fiscalização', [
                'fiscalizacao_id' => $fiscalizacao->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erro ao excluir: ' . $e->getMessage());
        }
    }

    /**
     * Faz o upload da imagem do relatório fotográfico e retorna o caminho relativo.
     */
    private function uploadRelatorioFotografico($arquivo, string $numeroFiscalizacao): string
    {
        $numeroLimpo = preg_replace('/[^A-Za-z0-9\-]/', '_', $numeroFiscalizacao);
        $nomeArquivo = 'Fiscalizacao_' . time() . '_' . $numeroLimpo . '.' . $arquivo->getClientOriginalExtension();
        $caminho = 'uploads/fiscalizacoes/' . $nomeArquivo;

        if (!file_exists(public_path('uploads/fiscalizacoes'))) {
            mkdir(public_path('uploads/fiscalizacoes'), 0755, true);
        }

        $arquivo->move(public_path('uploads/fiscalizacoes'), $nomeArquivo);

        return $caminho;
    }

    /**
     * Remove tags HTML e decodifica entidades para exibir texto puro.
     */
    private function limparHtml(?string $texto): string
    {
        if (!$texto) return '—';

        $texto = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = strip_tags($texto);

        return trim(preg_replace('/\s+/', ' ', $texto));
    }

    // =========================================================
    // BUSCAR CONTRATOS — Endpoint AJAX para Select2
    // =========================================================
    public function buscarContratos(Request $request)
    {
        $user = auth()->user();
        $termo = $request->get('q', '');
        $isLiciconAdmin = $user->hasAnyRole(['diretor_licicon', 'gerente_licicon']);

        if (strlen($termo) < 2) {
            return response()->json(['results' => []]);
        }

        $resultados = [];

        // 1. FILTRO CONTRATOS MANUAIS
        $contratosManual = ContratoManual::with(['empresa', 'secretaria'])
            ->where('prefeitura_id', $user->prefeitura_id)
            ->when($user->unidade_id && !$isLiciconAdmin, function($q) use ($user) {
                return $q->where('unidade_id', $user->unidade_id);
            })
            ->where(function ($q) use ($termo) {
                $q->where('numero_contrato', 'LIKE', "%{$termo}%")
                ->orWhere('objeto', 'LIKE', "%{$termo}%")
                ->orWhereHas('empresa', fn($e) => $e->where('razao_social', 'LIKE', "%{$termo}%"));
            })
            ->limit(10)
            ->get();

        foreach ($contratosManual as $cm) {
            $resultados[] = [
                'id'               => $cm->id . '|App\\Models\\ContratoManual',
                'text'             => ($cm->numero_contrato ?: 'S/N') . ' — ' . Str::limit($this->limparHtml($cm->objeto), 60) . ' (' . ($cm->empresa?->razao_social ?? 'Sem empresa') . ')',
                'numero_contrato'  => $cm->numero_contrato,
                'objeto'           => $this->limparHtml($cm->objeto),
                'numero_processo'  => $cm->numero_processo,
                'modalidade'       => $cm->modalidade?->getDisplayName() ?? '—',
                'secretaria'       => $cm->secretaria?->nome ?? '—',
                'razao_social'     => $cm->empresa?->razao_social ?? '—',
                'cnpj'             => $cm->empresa?->cnpj_formatado ?? '—',
                'endereco'         => $cm->empresa?->endereco ?? '—',
                'representante'    => $cm->empresa?->representante ?? '—',
                'origem'           => 'Contrato Manual',
            ];
        }

        // 2. FILTRO CONTRATOS DO SISTEMA (Processos)
        $processos = Processo::with(['contrato', 'vencedores', 'prefeitura', 'detalhe'])
            ->has('contrato')
            ->where('prefeitura_id', $user->prefeitura_id)
            ->when($user->unidade_id && !$isLiciconAdmin, function($q) use ($user) {
                // Filtra através do usuário criador do processo
                return $q->whereHas('user', fn($u) => $u->where('unidade_id', $user->unidade_id));
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
                'id'               => $proc->contrato?->id . '|App\\Models\\Contrato',
                'text'             => ($proc->contrato?->numero_contrato ?: 'S/N') . ' — ' . Str::limit($this->limparHtml($proc->objeto), 60) . ' (' . ($vencedor?->razao_social ?? 'Sem empresa') . ')',
                'numero_contrato'  => $proc->contrato?->numero_contrato,
                'objeto'           => $this->limparHtml($proc->objeto),
                'numero_processo'  => $proc->numero_processo,
                'modalidade'       => $proc->modalidade?->getDisplayName() ?? '—',
                'secretaria'       => $proc->unidade_numeracao ?? $proc->detalhe?->secretaria ?? '—',
                'razao_social'     => $vencedor?->razao_social ?? '—',
                'cnpj'             => $vencedor?->cnpj_formatado ?? $vencedor?->cpf_formatado ?? '—',
                'endereco'         => $vencedor?->endereco ?? '—',
                'representante'    => $vencedor?->representante ?? '—',
                'origem'           => 'Contrato do Sistema',
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

    /**
     * Extrai informações do contrato para exibição unificada
     */
    private function extrairInfoContrato(Fiscalizacao $fiscalizacao): array
    {
        $contrato = $fiscalizacao->fiscalizavel;

        if (!$contrato) {
            return [
                'numero_contrato' => '—',
                'objeto'          => '—',
                'numero_processo' => '—',
                'modalidade'      => '—',
                'secretaria'      => '—',
                'razao_social'    => '—',
                'cnpj'            => '—',
                'endereco'        => '—',
                'representante'   => '—',
                'origem'          => '—',
            ];
        }

        if ($contrato instanceof ContratoManual) {
            return [
                'numero_contrato' => $contrato->numero_contrato ?? '—',
                'objeto'          => $this->limparHtml($contrato->objeto),
                'numero_processo' => $contrato->numero_processo ?? '—',
                'modalidade'      => $contrato->modalidade?->getDisplayName() ?? '—',
                'secretaria'      => $contrato->secretaria->nome ?? '—',
                'razao_social'    => $contrato->empresa->razao_social ?? '—',
                'cnpj'            => $contrato->empresa->cnpj_formatado ?? '—',
                'endereco'        => $contrato->empresa->endereco ?? '—',
                'representante'   => $contrato->empresa->representante ?? '—',
                'origem'          => 'Contrato Manual',
            ];
        }

        if ($contrato instanceof Contrato) {
            $processo = $contrato->processo;
            $vencedor = $processo?->vencedores?->first();
            return [
                'numero_contrato' => $contrato->numero_contrato ?? '—',
                'objeto'          => $this->limparHtml($processo->objeto),
                'numero_processo' => $processo->numero_processo ?? '—',
                'modalidade'      => $processo->modalidade?->getDisplayName() ?? '—',
                'secretaria'      => $processo->unidade_numeracao ?? $processo->detalhe?->secretaria ?? '—',
                'razao_social'    => $vencedor?->razao_social ?? '—',
                'cnpj'            => $vencedor?->cnpj_formatado ?? $vencedor?->cpf_formatado ?? '—',
                'endereco'        => $vencedor?->endereco ?? '—',
                'representante'   => $vencedor?->representante ?? '—',
                'origem'          => 'Contrato do Sistema',
            ];
        }

        return [
            'numero_contrato' => '—',
            'objeto'          => '—',
            'numero_processo' => '—',
            'modalidade'      => '—',
            'secretaria'      => '—',
            'razao_social'    => '—',
            'cnpj'            => '—',
            'endereco'        => '—',
            'representante'   => '—',
            'origem'          => '—',
        ];
    }

    public function gerarRelatorio($id)
    {
        $fiscalizacao = Fiscalizacao::with(['fiscalizavel', 'prefeitura', 'user'])->findOrFail($id);

        $this->authorizeAccess($fiscalizacao);

        $fiscalizacao->contrato_info = $this->extrairInfoContrato($fiscalizacao);
        $pdf = Pdf::loadView('Admin.Fiscalizacoes.relatorio', compact('fiscalizacao'));

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true
        ]);

        $nomeArquivo = "Relatorio_Fiscalizacao_" . str_replace(['/', '\\'], '_', $fiscalizacao->numero_fiscalizacao) . ".pdf";
        return $pdf->stream($nomeArquivo);
    }

    public function imprimirRelatorioTecnico($id)
    {
        $fiscalizacao = Fiscalizacao::with(['fiscalizavel', 'prefeitura', 'user'])->findOrFail($id);
        $this->authorizeAccess($fiscalizacao);

        $fiscalizacao->contrato_info = $this->extrairInfoContrato($fiscalizacao);
        
        $pdf = Pdf::loadView('Admin.Fiscalizacoes.relatorio_tecnico', compact('fiscalizacao'));
        $pdf->setPaper('a4', 'portrait');

        $numeroLimpo = str_replace(['/', '\\'], '_', $fiscalizacao->numero_fiscalizacao);

        return $pdf->stream("Relatorio_Tecnico_{$numeroLimpo}.pdf");
    }

    public function imprimirNotificacoes($id)
    {
        $fiscalizacao = Fiscalizacao::with(['fiscalizavel', 'prefeitura', 'user'])->findOrFail($id);
        $this->authorizeAccess($fiscalizacao);

        $fiscalizacao->contrato_info = $this->extrairInfoContrato($fiscalizacao);
        
        $pdf = Pdf::loadView('Admin.Fiscalizacoes.notificacoes', compact('fiscalizacao'));
        $pdf->setPaper('a4', 'portrait');

        $numeroLimpo = str_replace(['/', '\\'], '_', $fiscalizacao->numero_fiscalizacao);

        return $pdf->stream("Notificacoes_{$numeroLimpo}.pdf");
    }

    public function selecionarContrato(Request $request)
    {
        $user = auth()->user();
        $isLiciconAdmin = $user->hasAnyRole(['diretor_licicon', 'gerente_licicon']);
        $prefeituraId = $user->prefeitura_id;

        $manuais = ContratoManual::with(['empresa', 'fiscalizacoes'])
            ->where('prefeitura_id', $prefeituraId)
            ->when($user->unidade_id && !$isLiciconAdmin, function($q) use ($user) {
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
                    'ultima_fiscalizacao' => $ultima?->data_fiscalizacao ?? null,
                    'ultima_fiscalizacao_id' => $ultima?->id ?? null,
                    'origem' => 'Manual'
                ];
            });

        $sistema = Processo::with(['contrato.fiscalizacoes', 'vencedores'])
            ->has('contrato')
            ->where('prefeitura_id', $prefeituraId)
            ->when($user->unidade_id && !$isLiciconAdmin, function($q) use ($user) {
                return $q->whereHas('user', fn($u) => $u->where('unidade_id', $user->unidade_id));
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
                    'ultima_fiscalizacao' => $ultima?->data_fiscalizacao ?? null,
                    'ultima_fiscal_id' => $ultima?->id ?? null,
                    'origem' => 'Sistema'
                ];
            });

        $todosContratos = $manuais->concat($sistema);

        $empresas = $todosContratos->groupBy('empresa_nome')->map(function ($contratos, $nome) {
            return [
                'nome' => $nome,
                'cnpj' => $contratos->first()['empresa_cnpj'],
                'contratos' => $contratos->sortBy(function($c) {
                    return $c['ultima_fiscalizacao'] ? 1 : 0;
                })->values(),
                'pendentes' => $contratos->whereNull('ultima_fiscalizacao')->count()
            ];
        })->sortBy('nome');

        return view('Admin.Fiscalizacoes.selecionar-contrato', compact('empresas'));
    }
}
