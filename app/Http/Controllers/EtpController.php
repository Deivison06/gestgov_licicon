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
    // Validação base
    $rules = [
        'secretaria_id' => 'required|exists:unidades,id',
        'servidor_responsavel' => 'required|string|max:255',
        'objeto_licitacao' => 'required|string',
        'modalidade' => 'required|in:pregao,concorrencia,dispensa,inexigibilidade',
        'tipo_contratacao' => 'required_if:modalidade,pregao,dispensa|in:item,lote',
        'dotacao_orcamentaria' => 'required|string|max:255',
        'prazo_entrega' => 'required|string|max:255',
        'cotacao_path' => 'nullable|file|max:10240'
    ];

    // Validação para itens (sem lote)
    $rules['itens'] = 'required_if:tipo_contratacao,item|array';
    $rules['itens.*.item_id'] = 'required_if:tipo_contratacao,item|exists:etp_itens,id';
    $rules['itens.*.unidade'] = 'required_if:tipo_contratacao,item|string|max:100';
    $rules['itens.*.quantidade'] = 'required_if:tipo_contratacao,item|integer|min:1';

    // Validação para lotes
    $rules['lotes'] = 'required_if:tipo_contratacao,lote|array';
    $rules['lotes.*.nome'] = 'required_if:tipo_contratacao,lote|string|max:255';
    $rules['lotes.*.itens'] = 'required_if:tipo_contratacao,lote|array';
    $rules['lotes.*.itens.*.item_id'] = 'required_if:tipo_contratacao,lote|exists:etp_itens,id';
    $rules['lotes.*.itens.*.unidade'] = 'required_if:tipo_contratacao,lote|string|max:100';
    $rules['lotes.*.itens.*.quantidade'] = 'required_if:tipo_contratacao,lote|integer|min:1';

    $request->validate($rules);

    $data = $request->all();
    $data['prefeitura_id'] = auth()->user()->prefeitura_id;

    try {
        $this->etpService->store($data, $request->file('cotacao_path'));

        return redirect()
            ->route('admin.etps.index')
            ->with('success', 'ETP criado com sucesso.');

    } catch (\Exception $e) {
        return back()
            ->withInput()
            ->with('error', 'Erro ao criar ETP: ' . $e->getMessage());
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
