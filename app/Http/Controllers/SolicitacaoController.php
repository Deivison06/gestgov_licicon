<?php

namespace App\Http\Controllers;

use App\Services\SolicitacaoService;
use Illuminate\Http\Request;
use App\Models\Etp;
use App\Models\Processo;
use Illuminate\Support\Facades\Auth;

class SolicitacaoController extends Controller
{
    protected $service;

    public function __construct(SolicitacaoService $service)
    {
        $this->service = $service;
    }

    /**
     * Lista todas as solicitações da prefeitura do usuário
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->hasRole('prefeitura')) {
            $solicitacoes = $this->service->listByPrefeitura($user->prefeitura_id, $request->all());
        } else {
            // Se não for prefeitura, vê tudo (separado por prefeitura na view)
            $solicitacoes = $this->service->listAll($request->all());
        }

        return view('Admin.Solicitacoes.index', compact('solicitacoes'));
    }

    /**
     * Exibe o formulário de criação vinculado a um modelo
     */
    public function create()
    {
        $user = Auth::user();
        $prefeituras = [];
        
        if (!$user->hasRole('prefeitura')) {
            $prefeituras = \App\Models\Prefeitura::all();
        }

        return view('Admin.Solicitacoes.create', compact('prefeituras'));
    }

    /**
     * Salva a nova solicitação
     */
    public function store(Request $request)
    {
        $request->validate([
            'prefeitura_id' => 'required|exists:prefeituras,id',
            'tipo' => 'required|in:correcao,reclamacao,outros',
            'assunto' => 'required|string|max:255',
            'mensagem' => 'required|string',
            'anexo' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:10240',
        ]);

        $user = Auth::user();
        $data = $request->all();
        $data['user_id'] = $user->id;

        // Se for usuário de prefeitura, força a prefeitura dele
        if ($user->hasRole('prefeitura')) {
            $data['prefeitura_id'] = $user->prefeitura_id;
        }

        $this->service->criarSolicitacao($data, $request->file('anexo'));

        return redirect()->route('admin.solicitacoes.index')
            ->with('success', 'Solicitação enviada com sucesso!');
    }

    /**
     * Exibe a timeline da solicitação
     */
    public function show($id)
    {
        $solicitacao = $this->service->getById($id);
        return view('Admin.Solicitacoes.show', compact('solicitacao'));
    }

    /**
     * Envia uma resposta técnica
     */
    public function responder(Request $request, $id)
    {
        $request->validate([
            'mensagem' => 'required|string',
            'anexo' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:10240',
        ]);

        $user = Auth::user();
        $data = $request->all();
        $data['user_id'] = $user->id;

        try {
            $this->service->enviarResposta($id, $data, $request->file('anexo'));
            return back()->with('success', 'Resposta enviada com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Finaliza o ticket
     */
    public function finalizar($id)
    {
        $this->service->finalizar($id);
        return back()->with('success', 'Solicitação finalizada com sucesso!');
    }
}
