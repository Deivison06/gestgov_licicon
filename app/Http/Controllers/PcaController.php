<?php

namespace App\Http\Controllers;

use App\Models\Pca;
use App\Models\Prefeitura;
use App\Models\Unidade;
use App\Services\PcaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PcaController extends Controller
{
    protected $pcaService;

    public function __construct(PcaService $pcaService)
    {
        $this->pcaService = $pcaService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isPrefeituraUser = $user->hasRole('prefeitura') && $user->prefeitura_id;
        
        $filters = $request->only(['search', 'status', 'exercicio', 'prefeitura_id']);

        if ($isPrefeituraUser) {
            $pcas = $this->pcaService->getByPrefeituraId($user->prefeitura_id, $filters);
        } else {
            $pcas = $this->pcaService->getAll($filters);
        }

        $prefeituras = [];
        if (!$isPrefeituraUser) {
            $prefeituras = Prefeitura::orderBy('nome')->get();
        }

        return view('Admin.Pcas.index', compact('pcas', 'isPrefeituraUser', 'prefeituras'));
    }

    public function create()
    {
        $user = auth()->user();
        $isPrefeituraUser = $user->hasRole('prefeitura') && $user->prefeitura_id;
        
        if ($isPrefeituraUser) {
            $prefeituras = Prefeitura::where('id', $user->prefeitura_id)->get();
            $secretarias = Unidade::where('prefeitura_id', $user->prefeitura_id)
                ->orderBy('nome')->get();
        } else {
            $prefeituras = Prefeitura::orderBy('nome')->get();
            $secretarias = Unidade::orderBy('nome')->get();
        }

        $modalidades = [
            'Pregão',
            'Concorrência',
            'Dispensa',
            'Inexigibilidade',
            'Concurso',
            'Leilão',
            'Diálogo Competitivo'
        ];

        return view('Admin.Pcas.create', compact('prefeituras', 'secretarias', 'isPrefeituraUser', 'modalidades'));
    }

    public function store(Request $request)
    {
        Log::info('📦 Criando PCA', ['user_id' => auth()->id(), 'data' => $request->except(['_token'])]);

        $request->validate([
            'prefeitura_id' => 'required|exists:prefeituras,id',
            'exercicio' => 'required|string',
            'numero_pca' => 'nullable|string',
        ]);

        try {
            $user = auth()->user();
            if ($user->hasRole('prefeitura') && $user->prefeitura_id != $request->prefeitura_id) {
                return back()->withInput()->with('error', 'Ação não permitida para a prefeitura selecionada.');
            }
            $pca = $this->pcaService->store($request->all());

            return redirect()->route('admin.pcas.index')
                ->with('success', 'Plano de Contratação Anual criado com sucesso!');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erro ao salvar PCA: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $pca = $this->pcaService->findById($id);
        
        if (!$pca) {
            abort(404);
        }

        $this->authorizeAccess($pca);

        return view('Admin.Pcas.show', compact('pca'));
    }

    public function edit($id)
    {
        $pca = $this->pcaService->findById($id);
        
        if (!$pca) {
            abort(404);
        }

        $this->authorizeAccess($pca);

        $user = auth()->user();
        $isPrefeituraUser = $user->hasRole('prefeitura') && $user->prefeitura_id;
        
        if ($isPrefeituraUser) {
            $prefeituras = Prefeitura::where('id', $user->prefeitura_id)->get();
            $secretarias = Unidade::where('prefeitura_id', $user->prefeitura_id)->orderBy('nome')->get();
        } else {
            $prefeituras = Prefeitura::orderBy('nome')->get();
            $secretarias = Unidade::where('prefeitura_id', $pca->prefeitura_id)->orderBy('nome')->get();
        }

        $modalidades = [
            'Pregão',
            'Concorrência',
            'Dispensa',
            'Inexigibilidade',
            'Concurso',
            'Leilão',
            'Diálogo Competitivo'
        ];

        $equipeElaboracaoJson = json_encode($pca->equipe_elaboracao ?? []);

        return view('Admin.Pcas.edit', compact('pca', 'prefeituras', 'secretarias', 'isPrefeituraUser', 'modalidades', 'equipeElaboracaoJson'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'exercicio' => 'required|string',
            'numero_pca' => 'nullable|string',
            'prefeitura_id' => 'required|exists:prefeituras,id'
        ]);

        try {
            $pca = $this->pcaService->findById($id);
            if (!$pca) {
                abort(404);
            }

            $this->authorizeAccess($pca);

            $this->pcaService->update($id, $request->all());

            return redirect()->route('admin.pcas.index')
                ->with('success', 'Plano de Contratação Anual atualizado com sucesso!');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erro ao atualizar PCA: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $pca = $this->pcaService->findById($id);
            if (!$pca) {
                abort(404);
            }

            $this->authorizeAccess($pca);
            $this->pcaService->delete($id);

            return redirect()->route('admin.pcas.index')
                ->with('success', 'Plano de Contratação Anual excluído com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao excluir PCA: ' . $e->getMessage());
        }
    }

    public function gerarPdf($id)
    {
        $pca = $this->pcaService->findById($id);
        
        if (!$pca) {
            abort(404);
        }

        $this->authorizeAccess($pca);

        $pdf = Pdf::loadView('Admin.Pcas.pdf.pca', compact('pca'));
        
        return $pdf->download("PCA_{$pca->exercicio}.pdf");
    }

    private function authorizeAccess($pca)
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['diretor_licicon', 'gerente_licicon', 'colaborador_licicon', 'diretoria'])) {
            return;
        }

        if ($user->hasRole('prefeitura') && $pca->prefeitura_id != $user->prefeitura_id) {
            abort(403, 'Acesso não autorizado.');
        }
    }
}
