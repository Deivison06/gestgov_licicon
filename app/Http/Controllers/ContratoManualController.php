<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use App\Models\Prefeitura;
use Illuminate\Http\Request;
use App\Models\ContratoManual;
use App\Models\EmpresaContrato;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContratoManualController extends Controller
{
    public function index(Request $request)
    {
        // Query base SEM usuário
        $query = ContratoManual::with(['empresa', 'secretaria', 'prefeitura']);

        // Filtro por empresa
        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        // Filtro por prefeitura
        if ($request->filled('prefeitura_id')) {
            $query->where('prefeitura_id', $request->prefeitura_id);
        }

        $contratos = $query->latest()->paginate(10);

        // Prefeituras para filtro
        $prefeituras = Prefeitura::orderBy('nome')->get();

        // Empresas para filtro
        $empresasQuery = EmpresaContrato::orderBy('razao_social');

        if ($request->filled('prefeitura_id')) {
            $empresasQuery->where('prefeitura_id', $request->prefeitura_id);
        }

        $empresas = $empresasQuery->get();

        return view(
            'Admin.contratos_externos.index',
            compact('contratos', 'empresas', 'prefeituras')
        );
    }

    public function create()
    {
        $prefeituras = Prefeitura::all();

        // Busca TODAS as unidades
        $secretarias = Unidade::with('prefeitura')
                            ->orderBy('nome')
                            ->get();

        return view('Admin.contratos_externos.create', compact('prefeituras', 'secretarias'));
    }

    public function store(Request $request)
    {
        Log::info('Iniciando store do contrato');
        
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

        DB::beginTransaction();

        try {
            $prefeituraId = $request->input('prefeitura_id');
            $unidadeId = $request->input('unidade_id');
            
            Log::info('Processando empresa', [
                'cnpj' => $request->input('empresa.cnpj'),
                'prefeitura_id' => $prefeituraId,
                'unidade_id' => $unidadeId
            ]);
            
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

            // C. Upload do Arquivo
            $caminhoArquivo = null;
            if ($request->hasFile('arquivo_contrato')) {
                $caminhoArquivo = $request->file('arquivo_contrato')->store('contratos', 'public');
                Log::info('Arquivo uploadado', ['caminho' => $caminhoArquivo]);
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

        $prefeituras = Prefeitura::all();
        
        // Busca TODAS as unidades
        $secretarias = Unidade::with('prefeitura')
                             ->orderBy('nome')
                             ->get();

        return view('Admin.contratos_externos.edit', compact('contrato', 'prefeituras', 'secretarias'));
    }

    public function update(Request $request, ContratoManual $contrato)
    {
        Log::info('Iniciando update do contrato', [
            'contrato_id' => $contrato->id
        ]);
        
        $this->authorizeAccess($contrato);

        $request->validate([
            'prefeitura_id'     => 'required|exists:prefeituras,id',
            'numero_processo'   => 'required|string',
            'data_finalizacao'  => 'required|date',
            'valor_total'       => 'required',
            'unidade_id'        => 'required|exists:unidades,id',
        ]);

        DB::beginTransaction();

        try {
            $prefeituraId = $request->input('prefeitura_id');
            
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

            // Tratamento de Arquivo na Edição
            if ($request->hasFile('arquivo_contrato')) {
                Log::info('Arquivo recebido para atualização');
                if ($contrato->arquivo_contrato) {
                    Storage::disk('public')->delete($contrato->arquivo_contrato);
                    Log::info('Arquivo antigo deletado');
                }
                $dadosAtualizar['arquivo_contrato'] = $request->file('arquivo_contrato')->store('contratos', 'public');
                Log::info('Novo arquivo salvo', ['caminho' => $dadosAtualizar['arquivo_contrato']]);
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

        try {
            if ($contrato->arquivo_contrato) {
                Storage::disk('public')->delete($contrato->arquivo_contrato);
                Log::info('Arquivo do contrato deletado');
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
        
        // Se o usuário for admin, permite acesso a todos os contratos
        if ($user->hasRole('admin')) {
            return;
        }
        
        // Se for usuário comum, verifica se o contrato pertence à mesma prefeitura
        if ($user->hasRole('usuario') && $contrato->prefeitura_id != $user->prefeitura_id) {
            Log::warning('Tentativa de acesso não autorizado', [
                'user_id' => $user->id,
                'contrato_prefeitura_id' => $contrato->prefeitura_id,
                'user_prefeitura_id' => $user->prefeitura_id
            ]);
            
            abort(403, 'Acesso não autorizado.');
        }
    }
}