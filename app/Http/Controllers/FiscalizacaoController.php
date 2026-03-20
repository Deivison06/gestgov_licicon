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

        $query = Fiscalizacao::with(['fiscalizavel', 'prefeitura', 'user'])
            ->latest();

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

        // Carregar dados do contrato para exibição
        $fiscalizacoes->getCollection()->transform(function ($fiscalizacao) {
            $fiscalizacao->contrato_info = $this->extrairInfoContrato($fiscalizacao);
            return $fiscalizacao;
        });

        $tiposFiscalizacao = TipoFiscalizacaoEnum::cases();

        if ($isPrefeituraUser) {
            $prefeituras = Prefeitura::where('id', $userPrefeituraId)->get();
        } else {
            $prefeituras = Prefeitura::orderBy('nome')->get();
        }

        return view('Admin.Fiscalizacoes.index', compact(
            'fiscalizacoes',
            'tiposFiscalizacao',
            'prefeituras',
            'isPrefeituraUser'
        ));
    }

    // =========================================================
    // CREATE — Formulário de Criação
    // =========================================================
    public function create()
    {
        $user = auth()->user();
        $isPrefeituraUser = $user->hasRole('prefeitura') && $user->prefeitura_id;

        if ($isPrefeituraUser) {
            $prefeituras = Prefeitura::where('id', $user->prefeitura_id)->get();
        } else {
            $prefeituras = Prefeitura::all();
        }

        $tiposFiscalizacao = TipoFiscalizacaoEnum::cases();
        $conclusoes = ConclusaoFiscalEnum::cases();

        return view('Admin.Fiscalizacoes.create', compact(
            'prefeituras',
            'isPrefeituraUser',
            'tiposFiscalizacao',
            'conclusoes'
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
        $fiscalizacao = Fiscalizacao::with(['fiscalizavel', 'prefeitura', 'user'])->findOrFail($id);

        $this->authorizeAccess($fiscalizacao);

        $fiscalizacao->contrato_info = $this->extrairInfoContrato($fiscalizacao);

        return view('Admin.Fiscalizacoes.show', compact('fiscalizacao'));
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
     * Remove tags HTML e decodifica entidades para exibir texto puro.
     */
    private function limparHtml(?string $texto): string
    {
        if (!$texto) return '—';

        // Converte entidades (ex: &ccedil; -> ç, &nbsp; -> espaço)
        $texto = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Remove as tags literais (<p>, <div>, etc)
        $texto = strip_tags($texto);

        // Remove quebras de linha e espaços duplos resultantes da limpeza
        return trim(preg_replace('/\s+/', ' ', $texto));
    }

    // =========================================================
    // BUSCAR CONTRATOS — Endpoint AJAX para Select2
    // =========================================================
    public function buscarContratos(Request $request)
    {
        $user = auth()->user();
        $termo = $request->get('q', '');

        if (strlen($termo) < 2) {
            return response()->json(['results' => []]);
        }

        $resultados = [];

        // ---- 1. Buscar em ContratoManual ----
        $queryManual = ContratoManual::with(['empresa', 'secretaria'])
            ->where(function ($q) use ($termo) {
                $q->where('numero_contrato', 'LIKE', "%{$termo}%")
                    ->orWhere('objeto', 'LIKE', "%{$termo}%")
                    ->orWhere('numero_processo', 'LIKE', "%{$termo}%")
                    ->orWhereHas('empresa', function ($q2) use ($termo) {
                        $q2->where('razao_social', 'LIKE', "%{$termo}%")
                            ->orWhere('cnpj', 'LIKE', "%{$termo}%");
                    });
            });

        if ($user->hasRole('prefeitura') && $user->prefeitura_id) {
            $queryManual->where('prefeitura_id', $user->prefeitura_id);
        }

        $contratosManual = $queryManual->limit(10)->get();

        foreach ($contratosManual as $cm) {
            $objetoLimpo = $this->limparHtml($cm->objeto);
            $resultados[] = [
                'id'               => $cm->id . '|App\\Models\\ContratoManual',
                'text'             => ($cm->numero_contrato ?: 'S/N') . ' — ' . \Str::limit($cm->objeto, 60) . ' (' . ($cm->empresa?->razao_social ?? 'Sem empresa') . ')',
                'numero_contrato'  => $cm->numero_contrato,
                'objeto'           => $objetoLimpo,
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

        // ---- 2. Buscar em Contrato (via Processo) ----
        $queryProcesso = Processo::with(['contrato', 'vencedores', 'prefeitura', 'detalhe'])
            ->has('contrato')
            ->where(function ($q) use ($termo) {
                $q->where('numero_processo', 'LIKE', "%{$termo}%")
                    ->orWhere('objeto', 'LIKE', "%{$termo}%")
                    ->orWhereHas('contrato', function ($q2) use ($termo) {
                        $q2->where('numero_contrato', 'LIKE', "%{$termo}%");
                    })
                    ->orWhereHas('vencedores', function ($q2) use ($termo) {
                        $q2->where(function($group) use ($termo) {
                            $group->where('razao_social', 'LIKE', "%{$termo}%")
                                ->orWhere('cnpj', 'LIKE', "%{$termo}%")
                                ->orWhere('cpf', 'LIKE', "%{$termo}%");
                        });
                    });
            });

        if ($user->hasRole('prefeitura') && $user->prefeitura_id) {
            $queryProcesso->where('prefeitura_id', $user->prefeitura_id);
        }

        $processos = $queryProcesso->limit(10)->get();

        foreach ($processos as $proc) {
            $vencedor = $proc->vencedores->first();
            $objetoLimpo = $this->limparHtml($proc->objeto);
            $resultados[] = [
                'id'               => $proc->contrato?->id . '|App\\Models\\Contrato',
                'text'             => ($proc->contrato?->numero_contrato ?: 'S/N') . ' — ' . \Str::limit($proc->objeto, 60) . ' (' . ($vencedor?->razao_social ?? 'Sem empresa') . ')',
                'numero_contrato'  => $proc->contrato?->numero_contrato,
                'objeto'           => $objetoLimpo,
                'numero_processo'  => $proc->numero_processo,
                'modalidade'       => $proc->modalidade?->getDisplayName() ?? '—',
                'secretaria'       => $proc->detalhe?->unidade_numeracao ?? $proc->prefeitura?->nome ?? '—',
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
            Log::warning('🚫 Tentativa de acesso não autorizado à fiscalização', [
                'user_id' => $user->id,
                'fiscalizacao_prefeitura_id' => $fiscalizacao->prefeitura_id,
                'user_prefeitura_id' => $user->prefeitura_id
            ]);
            abort(403, 'Acesso não autorizado.');
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
                'objeto'          => $this->limparHtml($contrato->objeto), // TRATADO
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
                'objeto'          => $this->limparHtml($processo->objeto), // TRATADO
                'numero_processo' => $processo->numero_processo ?? '—',
                'modalidade'      => $processo->modalidade?->getDisplayName() ?? '—',
                'secretaria'      => $processo->detalhe->unidade_numeracao ?? $processo->prefeitura->nome ?? '—',
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

    // =========================================================
    // GERAR PDF — Relatório de Fiscalização
    // =========================================================
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
}
