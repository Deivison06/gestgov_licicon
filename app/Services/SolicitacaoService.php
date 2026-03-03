<?php

namespace App\Services;

use App\Repositories\SolicitacaoRepository;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SolicitacaoService
{
    protected $repository;

    public function __construct(SolicitacaoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Cria uma nova solicitação e a primeira mensagem
     */
    public function criarSolicitacao(array $data, ?UploadedFile $anexo = null)
    {
        return DB::transaction(function () use ($data, $anexo) {
            // 1. Criar Ticket
            $solicitacao = $this->repository->create([
                'user_id' => $data['user_id'],
                'prefeitura_id' => $data['prefeitura_id'],
                'tipo' => $data['tipo'],
                'assunto' => $data['assunto'],
                'status' => 'aberta',
            ]);

            // 2. Tratar anexo se houver
            $anexoPath = null;
            if ($anexo) {
                $anexoPath = $this->uploadAnexo($anexo);
            }

            // 3. Criar Primeira Mensagem
            $this->repository->addMessage([
                'solicitacao_id' => $solicitacao->id,
                'user_id' => $data['user_id'],
                'mensagem' => $data['mensagem'],
                'anexo_path' => $anexoPath,
            ]);

            return $solicitacao;
        });
    }

    /**
     * Adiciona uma resposta a uma solicitação existente
     */
    public function enviarResposta($solicitacaoId, array $data, ?UploadedFile $anexo = null)
    {
        return DB::transaction(function () use ($solicitacaoId, $data, $anexo) {
            $solicitacao = $this->repository->findById($solicitacaoId);
            
            if ($solicitacao->estaFinalizada()) {
                throw new Exception("Não é possível responder a uma solicitação finalizada.");
            }

            $anexoPath = null;
            if ($anexo) {
                $anexoPath = $this->uploadAnexo($anexo);
            }

            // Adicionar mensagem
            $this->repository->addMessage([
                'solicitacao_id' => $solicitacaoId,
                'user_id' => $data['user_id'],
                'mensagem' => $data['mensagem'],
                'anexo_path' => $anexoPath,
            ]);

            // Determinar novo status
            // Regra: Se quem responde NÃO é o criador, status = 'recebida'
            // Se quem responde É o criador, status = 'aguardando_resposta'
            $novoStatus = ($data['user_id'] == $solicitacao->user_id) ? 'aguardando_resposta' : 'recebida';
            
            $this->repository->updateStatus($solicitacaoId, $novoStatus);

            return $solicitacao;
        });
    }

    public function finalizar($solicitacaoId)
    {
        return $this->repository->updateStatus($solicitacaoId, 'finalizada');
    }

    private function uploadAnexo(UploadedFile $file): string
    {
        $nomeArquivo = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $destino = public_path('uploads/solicitacoes/anexos');

        if (!file_exists($destino)) {
            mkdir($destino, 0755, true);
        }

        $file->move($destino, $nomeArquivo);
        return 'uploads/solicitacoes/anexos/' . $nomeArquivo;
    }

    public function getById($id)
    {
        return $this->repository->findById($id);
    }

    public function listByPrefeitura($prefeituraId, $filters = [])
    {
        $filters['prefeitura_id'] = $prefeituraId;
        return $this->repository->listPaginated($filters);
    }

    public function listAll($filters = [])
    {
        return $this->repository->listPaginated($filters);
    }
}
