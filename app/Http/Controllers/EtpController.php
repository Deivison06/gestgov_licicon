<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EtpService;
use App\Models\Unidade;
use App\Models\User;
use App\Services\EtpItemService;

class EtpController extends Controller
{
    protected $etpService;
    protected $etpItemService;

    public function __construct(EtpService $etpService, EtpItemService $etpItemService)
    {
        $this->etpService = $etpService;
        $this->etpItemService = $etpItemService;
    }

    public function index(Request $request)
    {
        $prefeituraId = auth()->user()->prefeitura_id;
        $filters = $request->only(['status']);
        $etps = $this->etpService->getByPrefeituraId($prefeituraId, $filters);

        return view('Admin.Etps.index', compact('etps', 'filters'));
    }

    public function create()
    {
        $prefeituraId = auth()->user()->prefeitura_id;

        $secretarias = Unidade::where('prefeitura_id', $prefeituraId)->orderBy('nome', 'asc')->get();

        $itens = $this->etpItemService->getAllForSelect();

        return view('Admin.Etps.create', compact('secretarias', 'itens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'secretaria_id' => 'required',
            'servidor_responsavel' => 'required|string|max:255',
            'objeto_licitacao' => 'required|string',
            'modalidade' => 'required|in:pregao,concorrencia,dispensa,inexigibilidade',
            'dotacao_orcamentaria' => 'required|string|max:255',
            'tipo_contratacao' => 'required_if:modalidade,pregao,dispensa|in:item,lote',
            'nome_lote' => 'required_if:tipo_contratacao,lote|nullable|string|max:255',
            'prazo_entrega' => 'required|string|max:255',
            'itens_ids' => 'required_if:modalidade,pregao,dispensa|array',
            'itens_ids.*' => 'exists:etp_itens,id',
            'cotacao_path' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'
        ]);

        $data = $request->all();
        $data['prefeitura_id'] = auth()->user()->prefeitura_id;

        // Se for concorrência ou inexigibilidade, não precisa de tipo de contratação nem itens
        if (in_array($data['modalidade'], ['concorrencia', 'inexigibilidade'])) {
            $data['tipo_contratacao'] = 'item'; // Valor default para o banco
            $data['itens_ids'] = []; // Garante que não tenha itens caso enviado
        }

        try {
            $this->etpService->store($data, $request->file('cotacao_path'));
            return redirect()->route('admin.etps.index')->with('success', 'ETP Solicitado com sucesso. Aguardando análise.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $etp = $this->etpService->findById($id);

        if ($etp->prefeitura_id !== auth()->user()->prefeitura_id) {
            abort(403, 'Acesso negado.');
        }

        return view('Admin.Etps.show', compact('etp'));
    }
}
