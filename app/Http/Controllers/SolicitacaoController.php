<?php

namespace App\Http\Controllers;

use App\Services\SolicitacaoService;
use Illuminate\Http\Request;
use App\Models\Etp;
use App\Models\Processo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SolicitacaoController extends Controller
{
    protected $service;

    public function __construct(SolicitacaoService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        Log::info('[Solicitacao@index] Listando solicitações', [
            'user_id'      => $user->id,
            'role'         => $user->getRoleNames(),
            'filters'      => $request->all(),
            'prefeitura_id'=> $user->prefeitura_id ?? null,
        ]);

        if ($user->hasRole('prefeitura')) {
            $solicitacoes = $this->service->listByPrefeitura($user->prefeitura_id, $request->all());
        } else {
            $solicitacoes = $this->service->listAll($request->all());
        }

        Log::info('[Solicitacao@index] Concluído', [
            'total' => $solicitacoes->total(),
        ]);

        return view('Admin.Solicitacoes.index', compact('solicitacoes'));
    }

    public function create()
    {
        $user = Auth::user();

        Log::info('[Solicitacao@create] Abrindo formulário', [
            'user_id' => $user->id,
            'role'    => $user->getRoleNames(),
        ]);

        $prefeituras = [];

        if (!$user->hasRole('prefeitura')) {
            $prefeituras = \App\Models\Prefeitura::all();
            Log::info('[Solicitacao@create] Prefeituras carregadas', [
                'total' => $prefeituras->count(),
            ]);
        }

        return view('Admin.Solicitacoes.create', compact('prefeituras'));
    }

    public function store(Request $request)
    {
        Log::info('[Solicitacao@store] Iniciando', [
            'user_id'         => Auth::id(),
            'has_file'        => $request->hasFile('anexo'),
            'all_files'       => array_keys($request->allFiles()),
            'content_type'    => $request->header('Content-Type'),
            'content_length'  => $request->header('Content-Length'),
            'post_max_size'   => ini_get('post_max_size'),
            'upload_max_file' => ini_get('upload_max_filesize'),
            'input_keys'      => array_keys($request->all()),
        ]);

        if ($request->hasFile('anexo')) {
            $file = $request->file('anexo');
            Log::info('[Solicitacao@store] Arquivo detectado', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getClientMimeType(),
                'size_bytes'    => $file->getSize(),
                'size_mb'       => round($file->getSize() / 1048576, 2) . 'MB',
                'extension'     => $file->getClientOriginalExtension(),
                'is_valid'      => $file->isValid(),
                'error_code'    => $file->getError(),
                'error_message' => $file->getErrorMessage(),
                'tmp_path'      => $file->getRealPath(),
            ]);
        } else {
            Log::warning('[Solicitacao@store] Nenhum arquivo recebido', [
                'raw_files' => $_FILES,
                'php_error' => error_get_last(),
            ]);
        }

        $request->validate([
            'prefeitura_id' => 'required|exists:prefeituras,id',
            'tipo'          => 'required|in:correcao,reclamacao,outros',
            'assunto'       => 'required|string|max:255',
            'mensagem'      => 'required|string',
            'anexo'         => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:102400',
        ]);

        Log::info('[Solicitacao@store] Validação passou');

        $user = Auth::user();
        $data = $request->all();
        $data['user_id'] = $user->id;

        if ($user->hasRole('prefeitura')) {
            $data['prefeitura_id'] = $user->prefeitura_id;
            Log::info('[Solicitacao@store] Prefeitura forçada pelo role', [
                'prefeitura_id' => $data['prefeitura_id'],
            ]);
        }

        $this->service->criarSolicitacao($data, $request->file('anexo'));

        Log::info('[Solicitacao@store] Solicitação criada com sucesso', [
            'user_id'       => $user->id,
            'prefeitura_id' => $data['prefeitura_id'],
        ]);

        return redirect()->route('admin.solicitacoes.index')
            ->with('success', 'Solicitação enviada com sucesso!');
    }

    public function show($id)
    {
        Log::info('[Solicitacao@show] Exibindo solicitação', [
            'solicitacao_id' => $id,
            'user_id'        => Auth::id(),
        ]);

        $solicitacao = $this->service->getById($id);

        Log::info('[Solicitacao@show] Solicitação carregada', [
            'status'         => $solicitacao->status,
            'total_mensagens'=> $solicitacao->mensagens->count(),
        ]);

        return view('Admin.Solicitacoes.show', compact('solicitacao'));
    }

    public function responder(Request $request, $id)
    {
        Log::info('[Solicitacao@responder] Iniciando', [
            'solicitacao_id' => $id,
            'user_id'        => Auth::id(),
            'has_file'       => $request->hasFile('anexo'),
            'content_length' => $request->header('Content-Length'),
        ]);

        if ($request->hasFile('anexo')) {
            $file = $request->file('anexo');
            Log::info('[Solicitacao@responder] Arquivo detectado', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getClientMimeType(),
                'size_bytes'    => $file->getSize(),
                'size_mb'       => round($file->getSize() / 1048576, 2) . 'MB',
                'extension'     => $file->getClientOriginalExtension(),
                'is_valid'      => $file->isValid(),
                'error_code'    => $file->getError(),
                'error_message' => $file->getErrorMessage(),
                'tmp_path'      => $file->getRealPath(),
            ]);
        } else {
            Log::warning('[Solicitacao@responder] Nenhum arquivo recebido', [
                'raw_files' => $_FILES,
                'php_error' => error_get_last(),
            ]);
        }

        $request->validate([
            'mensagem' => 'required|string',
            'anexo'    => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:102400',
        ]);

        Log::info('[Solicitacao@responder] Validação passou');

        $user = Auth::user();
        $data = $request->all();
        $data['user_id'] = $user->id;

        try {
            $this->service->enviarResposta($id, $data, $request->file('anexo'));

            Log::info('[Solicitacao@responder] Resposta enviada com sucesso', [
                'solicitacao_id' => $id,
                'user_id'        => $user->id,
            ]);

            return back()->with('success', 'Resposta enviada com sucesso!');
        } catch (\Exception $e) {
            Log::error('[Solicitacao@responder] Erro ao enviar resposta', [
                'solicitacao_id' => $id,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function finalizar($id)
    {
        Log::info('[Solicitacao@finalizar] Finalizando solicitação', [
            'solicitacao_id' => $id,
            'user_id'        => Auth::id(),
        ]);

        $this->service->finalizar($id);

        Log::info('[Solicitacao@finalizar] Solicitação finalizada com sucesso', [
            'solicitacao_id' => $id,
        ]);

        return back()->with('success', 'Solicitação finalizada com sucesso!');
    }
}