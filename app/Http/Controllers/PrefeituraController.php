<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use App\Models\Contrato;
use App\Models\Processo;
use App\Models\Prefeitura;
use Illuminate\Http\Request;
use App\Models\ContratoManual;
use App\Enums\ModalidadeEnum;
use App\Enums\ProcessoStatusEnum;
use Illuminate\Support\Facades\DB;

class PrefeituraController extends Controller
{
    public function index()
    {
        $prefeituras = Prefeitura::all();
        return view('Admin.Prefeituras.index', compact('prefeituras'));
    }

    public function create()
    {
        return view('Admin.Prefeituras.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18|unique:prefeituras,cnpj',
            'endereco' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'telefone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'autoridade_competente' => 'required|string|max:255',
            'capa' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'timbre' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'capa_edital' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only([
            'nome',
            'cnpj',
            'endereco',
            'cidade',
            'telefone',
            'email',
            'autoridade_competente'
        ]);

        // Upload de arquivos
        $files = ['capa', 'timbre', 'capa_edital'];
        foreach ($files as $file) {
            if ($request->hasFile($file)) {
                $fileName = time() . '_' . $file . '.' . $request->file($file)->getClientOriginalExtension();
                $request->file($file)->move(public_path('uploads/prefeituras'), $fileName);
                $data[$file] = 'uploads/prefeituras/' . $fileName;
            }
        }

        try {
            Prefeitura::create($data);
            return redirect()->route('admin.prefeituras.index')
                ->with('success', 'Prefeitura cadastrada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao cadastrar prefeitura: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $prefeitura = Prefeitura::with('unidades')->findOrFail($id);
        return view('Admin.Prefeituras.show', compact('prefeitura'));
    }

    public function edit($id)
    {
        $prefeitura = Prefeitura::with('unidades')->findOrFail($id);
        return view('Admin.Prefeituras.edit', compact('prefeitura'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18|unique:prefeituras,cnpj,' . $id,
            'endereco' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'telefone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'autoridade_competente' => 'required|string|max:255',
            'capa' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'timbre' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'capa_edital' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $prefeitura = Prefeitura::findOrFail($id);
        $data = $request->only([
            'nome',
            'cnpj',
            'endereco',
            'cidade',
            'telefone',
            'email',
            'autoridade_competente'
        ]);

        // Upload de arquivos
        $files = ['capa', 'timbre', 'capa_edital'];
        foreach ($files as $file) {
            if ($request->hasFile($file)) {
                $fileName = time() . '_' . $file . '.' . $request->file($file)->getClientOriginalExtension();
                $request->file($file)->move(public_path('uploads/prefeituras'), $fileName);
                $data[$file] = 'uploads/prefeituras/' . $fileName;
            }
        }

        try {
            $prefeitura->update($data);
            return redirect()->route('admin.prefeituras.index')
                ->with('success', 'Prefeitura atualizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao atualizar prefeitura: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $prefeitura = Prefeitura::findOrFail($id);
            $prefeitura->unidades()->delete();
            $prefeitura->delete();

            DB::commit();

            return redirect()->route('admin.prefeituras.index')
                ->with('success', 'Prefeitura excluída com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Erro ao excluir prefeitura: ' . $e->getMessage());
        }
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();

        $prefeituras = Prefeitura::all();

        // Base query
        $processosQuery = Processo::with(['prefeitura', 'vencedores']);

        /*
        |--------------------------------------------------------------------------
        | REGRA DE VISIBILIDADE POR PERFIL
        |--------------------------------------------------------------------------
        */

        // Se for prefeitura → só vê os dela
        if ($user->hasRole('prefeitura')) {

            $prefeitura = $user->prefeitura; // relacionamento do user
            $processosQuery->where('prefeitura_id', $user->prefeitura_id);

        } else {
            // Admin, gerente e colaborador veem tudo
            $prefeitura = null;

            // Se quiser manter filtro manual por cidade apenas para admin
            if ($request->filled('cidade')) {
                $prefeitura = Prefeitura::findOrFail($request->cidade);
                $processosQuery->where('prefeitura_id', $request->cidade);
            }
        }

        $processos = $processosQuery->get();

        // Estatísticas otimizadas
        $modalidades = [
            'pregoes' => ModalidadeEnum::PREGAO_ELETRONICO->value,
            'dispensas' => ModalidadeEnum::DISPENSA->value,
            'inexigibilidades' => ModalidadeEnum::INEXIGIBILIDADE->value,
            'concorrencia' => ModalidadeEnum::CONCORRENCIA->value,
        ];

        $contadoresModalidade = [];
        foreach ($modalidades as $key => $value) {
            $contadoresModalidade[$key] = $processos->filter(function($processo) use ($value) {
                $modalidade = $processo->modalidade instanceof ModalidadeEnum
                    ? $processo->modalidade->value
                    : $processo->modalidade;
                return $modalidade == $value;
            })->count();
        }

        // Estatísticas de status
        $statusAtivos = [
            ProcessoStatusEnum::EM_ANDAMENTO,
            ProcessoStatusEnum::REPUBLICADO,
        ];

        $statusFinalizados = [
            ProcessoStatusEnum::FINALIZADO,
        ];

        

        $emAndamento = $processos->filter(function($processo) use ($statusAtivos) {
            if ($processo->status instanceof ProcessoStatusEnum) {
                return in_array($processo->status, $statusAtivos);
            }
            return in_array($processo->status, array_column($statusAtivos, 'value'));
        })->count();

        $finalizadosTotal = $processos->filter(function($processo) use ($statusFinalizados) {
            if ($processo->status instanceof ProcessoStatusEnum) {
                return in_array($processo->status, $statusFinalizados);
            }
            return in_array($processo->status, array_column($statusFinalizados, 'value'));
        })->count();

        $totalProcessos = $processos->count();

        return view('dashboard', array_merge(
            [
                'prefeitura' => $prefeitura,
                'processos' => $processos,
                'prefeituras' => $prefeituras,
                'emAndamento' => $emAndamento,
                'finalizadosTotal' => $finalizadosTotal,
                'totalProcessos' => $totalProcessos,
                'ativos' => $emAndamento, // Ativos são os em andamento
            ],
            $contadoresModalidade
        ));
    }
}
