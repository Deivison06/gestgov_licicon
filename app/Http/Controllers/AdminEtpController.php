<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EtpService;
use App\Models\Prefeitura;

class AdminEtpController extends Controller
{
    protected $etpService;

    public function __construct(EtpService $etpService)
    {
        $this->etpService = $etpService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['prefeitura_id', 'secretaria_id', 'status', 'data_inicio', 'data_fim']);
        $etps = $this->etpService->getAllWithFilters($filters);
        $prefeituras = Prefeitura::orderBy('nome', 'asc')->get();

        return view('Admin.EtpsRecebidos.index', compact('etps', 'prefeituras', 'filters'));
    }

    public function show($id)
    {
        $etp = $this->etpService->findById($id);
        return view('Admin.EtpsRecebidos.show', compact('etp'));
    }

    public function alterarStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:em_analise,aprovado,recusado'
        ]);

        try {
            $this->etpService->updateStatus($id, $request->status);
            return redirect()->back()->with('success', 'Status do ETP atualizado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function vincularProcesso(Request $request, $id)
    {
        $request->validate([
            'processo_id' => 'required|exists:processos,id'
        ]);

        try {
            $this->etpService->vincularProcesso($id, $request->processo_id);
            return redirect()->route('admin.etps_recebidos.index')->with('success', 'ETP vinculado ao processo com sucesso.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
