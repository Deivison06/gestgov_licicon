<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use App\Models\Contrato;
use App\Models\Processo;
use App\Models\Prefeitura;
use Illuminate\Http\Request;
use App\Models\ContratoManual;
use Illuminate\Support\Carbon;
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

        // Upload da capa
        if ($request->hasFile('capa')) {
            $capaName = time() . '_capa.' . $request->file('capa')->getClientOriginalExtension();
            $request->file('capa')->move(public_path('uploads/prefeituras'), $capaName);
            $data['capa'] = 'uploads/prefeituras/' . $capaName;
        }

        // Upload do timbre
        if ($request->hasFile('timbre')) {
            $timbreName = time() . '_timbre.' . $request->file('timbre')->getClientOriginalExtension();
            $request->file('timbre')->move(public_path('uploads/prefeituras'), $timbreName);
            $data['timbre'] = 'uploads/prefeituras/' . $timbreName;
        }
        // Upload do capa_edital
        if ($request->hasFile('capa_edital')) {
            $capa_editalName = time() . '_capa_edital.' . $request->file('capa_edital')->getClientOriginalExtension();
            $request->file('capa_edital')->move(public_path('uploads/prefeituras'), $capa_editalName);
            $data['capa_edital'] = 'uploads/prefeituras/' . $capa_editalName;
        }

        try {
            Prefeitura::create($data);
            return redirect()->route('admin.prefeituras.index')->with('success', 'Prefeitura cadastrada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao cadastrar prefeitura: ' . $e->getMessage())->withInput();
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

        // Upload da capa (se houver)
        if ($request->hasFile('capa')) {
            $capaName = time() . '_capa.' . $request->file('capa')->getClientOriginalExtension();
            $request->file('capa')->move(public_path('uploads/prefeituras'), $capaName);
            $data['capa'] = 'uploads/prefeituras/' . $capaName;
        }

        // Upload do timbre (se houver)
        if ($request->hasFile('timbre')) {
            $timbreName = time() . '_timbre.' . $request->file('timbre')->getClientOriginalExtension();
            $request->file('timbre')->move(public_path('uploads/prefeituras'), $timbreName);
            $data['timbre'] = 'uploads/prefeituras/' . $timbreName;
        }
        // Upload do capa_edital (se houver)
        if ($request->hasFile('capa_edital')) {
            $capa_editalName = time() . '_capa_edital.' . $request->file('capa_edital')->getClientOriginalExtension();
            $request->file('capa_edital')->move(public_path('uploads/prefeituras'), $capa_editalName);
            $data['capa_edital'] = 'uploads/prefeituras/' . $capa_editalName;
        }

        try {
            $prefeitura->update($data);
            return redirect()->route('admin.prefeituras.index')->with('success', 'Prefeitura atualizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar prefeitura: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $prefeitura = Prefeitura::findOrFail($id);

            // Remove unidades
            $prefeitura->unidades()->delete();

            // Remove prefeitura
            $prefeitura->delete();

            DB::commit();

            return redirect()->route('admin.prefeituras.index')->with('success', 'Prefeitura excluída com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao excluir prefeitura: ' . $e->getMessage());
        }
    }

    public function dashboard(Request $request)
    {
        // Dashboard geral ou por cidade específica
        $cidadeId = $request->query('cidade');

        if ($cidadeId) {
            // Dashboard específico da cidade
            $prefeitura = Prefeitura::findOrFail($cidadeId);
            $processos = Processo::where('prefeitura_id', $cidadeId)
                ->with(['prefeitura', 'vencedores'])
                ->get();
        } else {
            // Dashboard geral
            $prefeitura = null;
            $processos = Processo::with(['prefeitura', 'vencedores'])->get();
        }

        // Contar por modalidade
        $pregioes = $processos->where('modalidade.value', 4)->count(); // PREGAO_ELETRONICO
        $dispensas = $processos->where('modalidade.value', 2)->count(); // DISPENSA
        $inexigibilidades = $processos->where('modalidade.value', 3)->count(); // INEXIGIBILIDADE
        $concorrencia = $processos->where('modalidade.value', 1)->count(); // CONCORRENCIA

        // Contar por status
        $emAndamento = $processos->where('status.value', 'analise')->count();
        $concluido = $processos->where('status.value', 'aprovado')->count();
        $cancelados = $processos->where('status.value', 'aprovado')->count();

        // Listar prefeituras para o filtro
        $prefeituras = Prefeitura::all();

        return view('dashboard', compact(
            'prefeitura',
            'processos',
            'prefeituras',
            'pregioes',
            'dispensas',
            'inexigibilidades',
            'concorrencia',
            'emAndamento',
            'concluido'
        ));
    }

    private function getAtividadesRecentes($processos, $contratosManuais)
    {
        $atividades = [];

        // Processos recentes
        $processosRecentes = $processos->sortByDesc('created_at')->take(3);
        foreach ($processosRecentes as $processo) {
            $atividades[] = [
                'type' => 'processo',
                'description' => "Processo #{$processo->numero_processo} criado",
                'time' => $processo->created_at->diffForHumans()
            ];
        }

        // Contratos manuais recentes
        $contratosRecentes = $contratosManuais->sortByDesc('created_at')->take(2);
        foreach ($contratosRecentes as $contrato) {
            $atividades[] = [
                'type' => 'contrato',
                'description' => "Contrato #{$contrato->numero_contrato} criado",
                'time' => $contrato->created_at->diffForHumans()
            ];
        }

        // Ordenar por data mais recente
        usort($atividades, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($atividades, 0, 5); // Limitar a 5 atividades
    }
}
