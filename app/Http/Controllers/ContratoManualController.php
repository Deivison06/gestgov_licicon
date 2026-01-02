<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use App\Models\Processo;
use App\Models\Vencedor;
use App\Models\Prefeitura;
use Illuminate\Http\Request;
use App\Enums\ModalidadeEnum;
use App\Models\ContratoManual;
use App\Models\EmpresaContrato;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContratoManualController extends Controller
{
    public function index(Request $request)
    {
        // Determinar qual aba está ativa
        $abaAtiva = $request->get('tipo', 'manual'); // 'sistema' ou 'manual'
        
        // Obter o usuário logado
        $user = auth()->user();
        $userPrefeituraId = $user->prefeitura_id;
        
        // Verificar se é usuário da prefeitura
        $isPrefeituraUser = $user->hasRole('prefeitura') && $userPrefeituraId;
        
        if ($abaAtiva === 'sistema') {
            $query = Processo::with([
                    'prefeitura',
                    'contrato',
                    'vencedores'
                ])
                ->has('contrato')
                ->orderBy('created_at', 'desc');

            // Se for usuário da prefeitura, filtrar apenas pela prefeitura dele
            if ($isPrefeituraUser) {
                $query->where('prefeitura_id', $userPrefeituraId);
            } else {
                // Para admin/diretor, permitir filtro manual
                if ($request->filled('prefeitura_id')) {
                    $query->where('prefeitura_id', $request->prefeitura_id);
                }
            }

            // Modalidade (note: no Processo, modalidade é uma coluna enum)
            if ($request->filled('modalidade')) {
                $modalidadeValue = $request->modalidade;
                try {
                    $modalidadeEnum = ModalidadeEnum::tryFrom($modalidadeValue);
                    if ($modalidadeEnum) {
                        $query->where('modalidade', $modalidadeEnum->value);
                    }
                } catch (\Exception $e) {
                    // Se houver erro na conversão, ignora o filtro
                }
            }

            // Vencedor (empresa vencedora)
            if ($request->filled('vencedor_id')) {
                $query->whereHas('vencedores', function ($q) use ($request) {
                    $q->where('id', $request->vencedor_id);
                });
            }

            $contratos = $query->paginate(10);
            $tipoContratos = 'sistema';
        } else {
            $query = ContratoManual::with([
                'empresa',
                'secretaria',
                'prefeitura'
            ]);

            // Se for usuário da prefeitura, filtrar apenas pela prefeitura dele
            if ($isPrefeituraUser) {
                $query->where('prefeitura_id', $userPrefeituraId);
            } else {
                // Para admin/diretor, permitir filtro manual
                if ($request->filled('prefeitura_id')) {
                    $query->where('prefeitura_id', $request->prefeitura_id);
                }
            }

            // Modalidade
            if ($request->filled('modalidade')) {
                try {
                    $modalidadeEnum = ModalidadeEnum::tryFrom($request->modalidade);
                    if ($modalidadeEnum) {
                        $query->where('modalidade', $modalidadeEnum->value);
                    }
                } catch (\Exception $e) {
                    // Ignora erro na conversão
                }
            }

            // Empresa contratada
            if ($request->filled('empresa_id')) {
                $query->where('empresa_id', $request->empresa_id);
            }

            $contratos = $query->latest()->paginate(10);
            $tipoContratos = 'manual';
        }
        
        $modalidades = ModalidadeEnum::cases();
        
        // Para usuários da prefeitura, carregar apenas empresas da mesma prefeitura
        if ($isPrefeituraUser) {
            $empresas = EmpresaContrato::where('prefeitura_id', $userPrefeituraId)
                ->orderBy('razao_social')
                ->get();
            $prefeituras = Prefeitura::where('id', $userPrefeituraId)->get();
        } else {
            $empresas = EmpresaContrato::orderBy('razao_social')->get();
            $prefeituras = Prefeitura::orderBy('nome')->get();
        }
        
        // Para contratos do sistema, carrega todos os vencedores
        // Para usuários da prefeitura, filtrar vencedores da mesma prefeitura
        if ($isPrefeituraUser) {
            // CORREÇÃO AQUI: usar 'processo' no singular
            $vencedores = Vencedor::whereHas('processo', function($q) use ($userPrefeituraId) {
                    $q->where('prefeitura_id', $userPrefeituraId);
                })
                ->orderBy('razao_social')
                ->get();
        } else {
            $vencedores = Vencedor::orderBy('razao_social')->get();
        }

        return view(
            'Admin.contratos_externos.index',
            compact(
                'contratos',
                'empresas',
                'prefeituras',
                'modalidades',
                'vencedores',
                'tipoContratos',
                'abaAtiva',
                'isPrefeituraUser'
            )
        );
    }
    
    public function create()
    {
        $user = auth()->user();
        
        // Para usuários da prefeitura, mostrar apenas a prefeitura dele
        if ($user->hasRole('prefeitura') && $user->prefeitura_id) {
            $prefeituras = Prefeitura::where('id', $user->prefeitura_id)->get();
            
            // Buscar apenas unidades da mesma prefeitura
            $secretarias = Unidade::with('prefeitura')
                ->where('prefeitura_id', $user->prefeitura_id)
                ->orderBy('nome')
                ->get();
        } else {
            $prefeituras = Prefeitura::all();
            $secretarias = Unidade::with('prefeitura')
                ->orderBy('nome')
                ->get();
        }

        return view('Admin.contratos_externos.create', compact('prefeituras', 'secretarias'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        Log::info('Iniciando store do contrato', [
            'user_id' => $user->id,
            'prefeitura_id' => $user->prefeitura_id
        ]);
        
        // 1. Validação Unificada SEM prefixo processo.
        $request->validate([
            // Dados do Contrato
            'prefeitura_id'     => 'required|exists:prefeituras,id',
            'numero_processo'   => 'required|string',
            'numero_contrato'   => 'nullable|string',
            'modalidade'        => 'nullable|string',
            'tipo_contrato'     => 'required|in:Compras,Serviço',
            'unidade_id'        => 'required|exists:unidades,id',
            'objeto'            => 'required|string',

            // Financeiro e Vigência
            'valor_total'       => 'required',
            'data_assinatura'   => 'nullable|date',
            'data_inicio'       => 'nullable|date',
            'data_finalizacao'  => 'required|date',

            // Arquivo
            'arquivo_contrato'  => 'nullable|file|mimes:pdf|max:5120',

            // Dados da Empresa
            'empresa.razao_social' => 'required|string',
            'empresa.cnpj'         => 'required|string',
            'empresa.representante'=> 'nullable|string',
            'empresa.endereco'     => 'required|string',
        ]);
        
        // Para usuários da prefeitura, verificar se estão tentando criar contrato para outra prefeitura
        if ($user->hasRole('prefeitura') && $user->prefeitura_id) {
            if ($request->prefeitura_id != $user->prefeitura_id) {
                Log::warning('Usuário da prefeitura tentando criar contrato para outra prefeitura', [
                    'user_prefeitura_id' => $user->prefeitura_id,
                    'request_prefeitura_id' => $request->prefeitura_id
                ]);
                return back()->withInput()->with('error', 'Você só pode criar contratos para sua própria prefeitura.');
            }
        }

        DB::beginTransaction();

        try {
            $prefeituraId = $request->input('prefeitura_id');
            $unidadeId = $request->input('unidade_id');
            
            Log::info('Processando empresa', [
                'cnpj' => $request->input('empresa.cnpj'),
                'prefeitura_id' => $prefeituraId,
                'unidade_id' => $unidadeId
            ]);
            
            // Verificar se a unidade pertence à prefeitura selecionada
            $unidade = Unidade::find($unidadeId);
            if ($unidade && $unidade->prefeitura_id != $prefeituraId) {
                throw new \Exception('A unidade selecionada não pertence à prefeitura informada.');
            }
            
            // A. Tratamento da Empresa (Busca ou Cria)
            $cnpjLimpo = preg_replace('/[^0-9]/', '', $request->input('empresa.cnpj'));

            // Busca empresa pelo CNPJ e Prefeitura
            $empresa = EmpresaContrato::where('cnpj', $cnpjLimpo)
                ->where('prefeitura_id', $prefeituraId)
                ->first();
            
            if (!$empresa) {
                // Cria uma nova empresa para esta prefeitura
                $empresa = EmpresaContrato::create([
                    'cnpj' => $cnpjLimpo,
                    'razao_social' => $request->input('empresa.razao_social'),
                    'representante' => $request->input('empresa.representante'),
                    'endereco' => $request->input('empresa.endereco'),
                    'prefeitura_id' => $prefeituraId,
                ]);
                Log::info('Nova empresa criada para contrato manual', [
                    'empresa_id' => $empresa->id,
                    'prefeitura_id' => $prefeituraId
                ]);
            } else {
                // Atualiza a empresa existente na mesma prefeitura
                $empresa->update([
                    'razao_social' => $request->input('empresa.razao_social'),
                    'representante' => $request->input('empresa.representante'),
                    'endereco' => $request->input('empresa.endereco'),
                ]);
                Log::info('Empresa existente atualizada', [
                    'empresa_id' => $empresa->id,
                    'prefeitura_id' => $prefeituraId
                ]);
            }

            // B. Tratamento de Valores Monetários
            $valorTotal = $request->input('valor_total');
            Log::debug('Valor total recebido', ['valor' => $valorTotal]);
            
            $valorTotalFloat = (float) str_replace(["R$\u{A0}", "R$", ".", ","], ["", "", "", "."], $valorTotal);
            Log::debug('Valor convertido', ['valor_float' => $valorTotalFloat]);

            // C. Upload do Arquivo - SALVANDO NA NOVA PASTA
            $caminhoArquivo = null;
            if ($request->hasFile('arquivo_contrato')) {
                $arquivo = $request->file('arquivo_contrato');
                $numeroContrato = preg_replace('/[^A-Za-z0-9\-]/', '_', $request->input('numero_contrato'));
                
                // Gera um nome único para o arquivo
                $nomeArquivo = "Contrato_{$numeroContrato}." . $arquivo->getClientOriginalExtension();
                
                // Define o caminho completo
                $caminho = 'uploads/contratos_externos/' . $nomeArquivo;
                
                // Salva o arquivo no diretório public
                $arquivo->move(public_path('uploads/contratos_externos'), $nomeArquivo);
                
                $caminhoArquivo = $caminho;
                Log::info('Arquivo uploadado', [
                    'caminho' => $caminhoArquivo,
                    'nome_arquivo' => $nomeArquivo
                ]);
            }

            // D. Criação do Contrato
            $dadosContrato = [
                'empresa_id'       => $empresa->id,
                'prefeitura_id'    => $prefeituraId,
                'unidade_id'       => $unidadeId,
                'numero_processo'  => $request->input('numero_processo'),
                'numero_contrato'  => $request->input('numero_contrato'),
                'modalidade'       => $request->input('modalidade'),
                'tipo_contrato'    => $request->input('tipo_contrato'),
                'objeto'           => $request->input('objeto'),
                'valor_total'      => $valorTotalFloat,
                'data_assinatura'  => $request->input('data_assinatura'),
                'data_inicio'      => $request->input('data_inicio'),
                'data_finalizacao' => $request->input('data_finalizacao'),
                'arquivo_contrato' => $caminhoArquivo,
            ];

            Log::debug('Dados para criar contrato:', $dadosContrato);

            $contrato = ContratoManual::create($dadosContrato);

            Log::info('Contrato criado com sucesso', [
                'contrato_id' => $contrato->id,
                'numero_processo' => $contrato->numero_processo,
                'empresa_id' => $empresa->id,
                'prefeitura_id' => $prefeituraId,
                'unidade_id' => $unidadeId
            ]);

            DB::commit();

            // Redireciona para o EDIT para que o usuário cadastre os LOTES
            return redirect()
                ->route('contratos.edit', $contrato->id)
                ->with('success', 'Contrato cadastrado! Agora adicione os Lotes e Itens.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao salvar contrato', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->with('error', 'Erro ao salvar contrato: ' . $e->getMessage());
        }
    }

    public function edit(ContratoManual $contrato)
    {
        Log::info('Acessando edição do contrato', ['contrato_id' => $contrato->id]);
        
        $this->authorizeAccess($contrato);

        // Carrega lotes e empresa para a view de edição
        $contrato->load(['empresa', 'secretaria']);

        $user = auth()->user();
        
        // Para usuários da prefeitura, mostrar apenas a prefeitura dele
        if ($user->hasRole('prefeitura') && $user->prefeitura_id) {
            $prefeituras = Prefeitura::where('id', $user->prefeitura_id)->get();
            
            // Buscar apenas unidades da mesma prefeitura
            $secretarias = Unidade::with('prefeitura')
                ->where('prefeitura_id', $user->prefeitura_id)
                ->orderBy('nome')
                ->get();
        } else {
            $prefeituras = Prefeitura::all();
            $secretarias = Unidade::with('prefeitura')
                ->orderBy('nome')
                ->get();
        }

        return view('Admin.contratos_externos.edit', compact('contrato', 'prefeituras', 'secretarias'));
    }

    public function update(Request $request, ContratoManual $contrato)
    {
        Log::info('Iniciando update do contrato', [
            'contrato_id' => $contrato->id
        ]);
        
        $this->authorizeAccess($contrato);
        
        // Para usuários da prefeitura, verificar se estão tentando editar contrato de outra prefeitura
        $user = auth()->user();
        if ($user->hasRole('prefeitura') && $user->prefeitura_id) {
            if ($contrato->prefeitura_id != $user->prefeitura_id) {
                Log::warning('Usuário da prefeitura tentando editar contrato de outra prefeitura', [
                    'user_prefeitura_id' => $user->prefeitura_id,
                    'contrato_prefeitura_id' => $contrato->prefeitura_id
                ]);
                abort(403, 'Acesso não autorizado.');
            }
            
            // Forçar prefeitura_id do usuário
            $request->merge(['prefeitura_id' => $user->prefeitura_id]);
        }

        $request->validate([
            'prefeitura_id'     => 'required|exists:prefeituras,id',
            'numero_processo'   => 'required|string',
            'data_finalizacao'  => 'required|date',
            'valor_total'       => 'required',
            'unidade_id'        => 'required|exists:unidades,id',
            'arquivo_contrato'  => 'nullable|file|mimes:pdf|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $prefeituraId = $request->input('prefeitura_id');
            
            // Verificar se a unidade pertence à prefeitura selecionada
            $unidade = Unidade::find($request->input('unidade_id'));
            if ($unidade && $unidade->prefeitura_id != $prefeituraId) {
                throw new \Exception('A unidade selecionada não pertence à prefeitura informada.');
            }
            
            // Tratamento do Valor
            $valorTotal = $request->input('valor_total');
            Log::debug('Valor total para update', ['valor' => $valorTotal]);
            
            if (is_string($valorTotal)) {
                $valorTotalFloat = (float) str_replace(["R$\u{A0}", "R$", ".", ","], ["", "", "", "."], $valorTotal);
            } else {
                $valorTotalFloat = $valorTotal;
            }
            
            Log::debug('Valor convertido para update', ['valor_float' => $valorTotalFloat]);

            $dadosAtualizar = [
                'prefeitura_id'     => $prefeituraId,
                'numero_processo'   => $request->input('numero_processo'),
                'numero_contrato'   => $request->input('numero_contrato'),
                'modalidade'        => $request->input('modalidade'),
                'tipo_contrato'     => $request->input('tipo_contrato'),
                'objeto'            => $request->input('objeto'),
                'valor_total'       => $valorTotalFloat,
                'data_assinatura'   => $request->input('data_assinatura'),
                'data_inicio'       => $request->input('data_inicio'),
                'data_finalizacao'  => $request->input('data_finalizacao'),
                'unidade_id'        => $request->input('unidade_id'),
            ];

            Log::debug('Dados para atualizar', $dadosAtualizar);

            // Tratamento de Arquivo na Edição - SALVANDO NA NOVA PASTA
            if ($request->hasFile('arquivo_contrato')) {
                Log::info('Arquivo recebido para atualização');
                
                // Deletar arquivo antigo se existir
                if ($contrato->arquivo_contrato && file_exists(public_path($contrato->arquivo_contrato))) {
                    unlink(public_path($contrato->arquivo_contrato));
                    Log::info('Arquivo antigo deletado', ['caminho' => $contrato->arquivo_contrato]);
                }
                
                $arquivo = $request->file('arquivo_contrato');
                $numeroContrato = preg_replace('/[^A-Za-z0-9\-]/', '_', $request->input('numero_contrato'));
                
                // Gera um nome único para o arquivo
                $nomeArquivo = "Contrato_{$numeroContrato}." . $arquivo->getClientOriginalExtension();
                
                // Define o caminho completo
                $caminho = 'uploads/contratos_externos/' . $nomeArquivo;
                
                // Salva o arquivo no diretório public
                $arquivo->move(public_path('uploads/contratos_externos'), $nomeArquivo);
                
                $dadosAtualizar['arquivo_contrato'] = $caminho;
                Log::info('Novo arquivo salvo', ['caminho' => $caminho]);
            }

            $contrato->update($dadosAtualizar);
            Log::info('Contrato atualizado com sucesso');

            DB::commit();

            return redirect()->route('contratos.index')
                ->with('success', 'Contrato atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao atualizar contrato', [
                'contrato_id' => $contrato->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->with('error', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }

    public function updateEmpresa(Request $request, $id)
    {
        Log::info('Iniciando update da empresa', [
            'contrato_id' => $id
        ]);
        
        $request->validate([
            'razao_social' => 'required|string',
            'cnpj'         => 'required|string',
            'endereco'     => 'required|string',
        ]);

        try {
            $contrato = ContratoManual::with('empresa')->findOrFail($id);
            
            // Verificar acesso
            $this->authorizeAccess($contrato);
            
            Log::debug('Contrato encontrado', [
                'contrato_id' => $contrato->id,
                'empresa_id' => $contrato->empresa->id
            ]);
            
            // Atualiza a empresa vinculada a este contrato
            $contrato->empresa->update($request->all());
            Log::info('Empresa atualizada com sucesso');

            return response()->json(['success' => true, 'message' => 'Dados da empresa atualizados!']);
            
         } catch (\Exception $e) {
             Log::error('Erro ao atualizar empresa', [
                 'contrato_id' => $id,
                 'error' => $e->getMessage(),
                 'trace' => $e->getTraceAsString()
             ]);
             
             return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
         }
    }

    public function destroy(ContratoManual $contrato)
    {
        Log::info('Iniciando exclusão do contrato', [
            'contrato_id' => $contrato->id
        ]);
        
        $this->authorizeAccess($contrato);
        
        // Para usuários da prefeitura, verificar se estão tentando excluir contrato de outra prefeitura
        $user = auth()->user();
        if ($user->hasRole('prefeitura') && $user->prefeitura_id) {
            if ($contrato->prefeitura_id != $user->prefeitura_id) {
                Log::warning('Usuário da prefeitura tentando excluir contrato de outra prefeitura', [
                    'user_prefeitura_id' => $user->prefeitura_id,
                    'contrato_prefeitura_id' => $contrato->prefeitura_id
                ]);
                abort(403, 'Acesso não autorizado.');
            }
        }

        try {
            // Deletar arquivo físico se existir
            if ($contrato->arquivo_contrato && file_exists(public_path($contrato->arquivo_contrato))) {
                unlink(public_path($contrato->arquivo_contrato));
                Log::info('Arquivo do contrato deletado', ['caminho' => $contrato->arquivo_contrato]);
            }

            $contrato->delete();
            Log::info('Contrato deletado com sucesso');

            return redirect()->route('contratos.index')
                ->with('success', 'Contrato excluído com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao excluir contrato', [
                'contrato_id' => $contrato->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Erro ao excluir: ' . $e->getMessage());
        }
    }

    
    /**
     * Autoriza o acesso ao contrato
     */
    private function authorizeAccess(ContratoManual $contrato)
    {
        $user = auth()->user();
        
        // Se o usuário for admin/diretor/gerente, permite acesso
        if ($user->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon'])) {
            return;
        }
        
        // Se for usuário da prefeitura, verifica se o contrato pertence à mesma prefeitura
        if ($user->hasRole('prefeitura') && $contrato->prefeitura_id != $user->prefeitura_id) {
            Log::warning('Tentativa de acesso não autorizado', [
                'user_id' => $user->id,
                'contrato_prefeitura_id' => $contrato->prefeitura_id,
                'user_prefeitura_id' => $user->prefeitura_id
            ]);
            
            abort(403, 'Acesso não autorizado.');
        }
    }
}