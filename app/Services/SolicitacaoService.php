<?php

namespace App\Services;

use App\Repositories\SolicitacaoRepository;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SolicitacaoService
{
    protected $repository;

    public function __construct(SolicitacaoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function criarSolicitacao(array $data, ?UploadedFile $anexo = null)
    {
        Log::info('[SolicitacaoService@criarSolicitacao] Iniciando', [
            'user_id'       => $data['user_id'] ?? null,
            'prefeitura_id' => $data['prefeitura_id'] ?? null,
            'tipo'          => $data['tipo'] ?? null,
            'tem_anexo'     => !is_null($anexo),
        ]);

        return DB::transaction(function () use ($data, $anexo) {
            $solicitacao = $this->repository->create([
                'user_id'       => $data['user_id'],
                'prefeitura_id' => $data['prefeitura_id'],
                'tipo'          => $data['tipo'],
                'assunto'       => $data['assunto'],
                'status'        => 'aberta',
            ]);

            Log::info('[SolicitacaoService@criarSolicitacao] Ticket criado', [
                'solicitacao_id' => $solicitacao->id,
            ]);

            $anexoPath = null;
            if ($anexo) {
                Log::info('[SolicitacaoService@criarSolicitacao] Processando anexo', [
                    'nome'      => $anexo->getClientOriginalName(),
                    'tamanho'   => $anexo->getSize(),
                    'is_valid'  => $anexo->isValid(),
                    'error'     => $anexo->getError(),
                ]);

                $anexoPath = $this->uploadAnexo($anexo);

                Log::info('[SolicitacaoService@criarSolicitacao] Anexo salvo', [
                    'path' => $anexoPath,
                ]);
            } else {
                Log::info('[SolicitacaoService@criarSolicitacao] Sem anexo, pulando upload');
            }

            $this->repository->addMessage([
                'solicitacao_id' => $solicitacao->id,
                'user_id'        => $data['user_id'],
                'mensagem'       => $data['mensagem'],
                'anexo_path'     => $anexoPath,
            ]);

            Log::info('[SolicitacaoService@criarSolicitacao] Mensagem inicial criada', [
                'tem_anexo_path' => !is_null($anexoPath),
                'anexo_path'     => $anexoPath,
            ]);

            return $solicitacao;
        });
    }

    public function enviarResposta($solicitacaoId, array $data, ?UploadedFile $anexo = null)
    {
        Log::info('[SolicitacaoService@enviarResposta] Iniciando', [
            'solicitacao_id' => $solicitacaoId,
            'user_id'        => $data['user_id'],
            'tem_anexo'      => !is_null($anexo),
        ]);

        return DB::transaction(function () use ($solicitacaoId, $data, $anexo) {
            $solicitacao = $this->repository->findById($solicitacaoId);

            Log::info('[SolicitacaoService@enviarResposta] Solicitação encontrada', [
                'status'     => $solicitacao->status,
                'criador_id' => $solicitacao->user_id,
            ]);

            if ($solicitacao->estaFinalizada()) {
                Log::warning('[SolicitacaoService@enviarResposta] Tentativa de responder solicitação finalizada', [
                    'solicitacao_id' => $solicitacaoId,
                ]);
                throw new Exception("Não é possível responder a uma solicitação finalizada.");
            }

            $anexoPath = null;
            if ($anexo) {
                Log::info('[SolicitacaoService@enviarResposta] Processando anexo', [
                    'nome'     => $anexo->getClientOriginalName(),
                    'tamanho'  => $anexo->getSize(),
                    'is_valid' => $anexo->isValid(),
                    'error'    => $anexo->getError(),
                ]);

                $anexoPath = $this->uploadAnexo($anexo);

                Log::info('[SolicitacaoService@enviarResposta] Anexo salvo', [
                    'path' => $anexoPath,
                ]);
            } else {
                Log::info('[SolicitacaoService@enviarResposta] Sem anexo, pulando upload');
            }

            $this->repository->addMessage([
                'solicitacao_id' => $solicitacaoId,
                'user_id'        => $data['user_id'],
                'mensagem'       => $data['mensagem'],
                'anexo_path'     => $anexoPath,
            ]);

            $novoStatus = ($data['user_id'] == $solicitacao->user_id) ? 'aguardando_resposta' : 'recebida';
            $this->repository->updateStatus($solicitacaoId, $novoStatus);

            Log::info('[SolicitacaoService@enviarResposta] Concluído', [
                'novo_status'    => $novoStatus,
                'tem_anexo_path' => !is_null($anexoPath),
            ]);

            return $solicitacao;
        });
    }

    public function finalizar($solicitacaoId)
    {
        Log::info('[SolicitacaoService@finalizar] Finalizando', [
            'solicitacao_id' => $solicitacaoId,
        ]);

        $result = $this->repository->updateStatus($solicitacaoId, 'finalizada');

        Log::info('[SolicitacaoService@finalizar] Concluído', [
            'solicitacao_id' => $solicitacaoId,
            'status'         => $result->status,
        ]);

        return $result;
    }

    public function getById($id)
    {
        Log::info('[SolicitacaoService@getById] Buscando solicitação', [
            'solicitacao_id' => $id,
        ]);

        $solicitacao = $this->repository->findById($id);

        Log::info('[SolicitacaoService@getById] Encontrada', [
            'solicitacao_id'  => $id,
            'status'          => $solicitacao->status,
            'total_mensagens' => $solicitacao->mensagens->count(),
        ]);

        return $solicitacao;
    }

    public function listByPrefeitura($prefeituraId, $filters = [])
    {
        Log::info('[SolicitacaoService@listByPrefeitura] Listando', [
            'prefeitura_id' => $prefeituraId,
            'filters'       => $filters,
        ]);

        $filters['prefeitura_id'] = $prefeituraId;
        $result = $this->repository->listPaginated($filters);

        Log::info('[SolicitacaoService@listByPrefeitura] Concluído', [
            'total' => $result->total(),
        ]);

        return $result;
    }

    public function listAll($filters = [])
    {
        Log::info('[SolicitacaoService@listAll] Listando todas', [
            'filters' => $filters,
        ]);

        $result = $this->repository->listPaginated($filters);

        Log::info('[SolicitacaoService@listAll] Concluído', [
            'total' => $result->total(),
        ]);

        return $result;
    }

    private function uploadAnexo(UploadedFile $file): string
    {
        $nomeArquivo = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $destino = public_path('uploads/solicitacoes/anexos');

        Log::info('[SolicitacaoService@uploadAnexo] Iniciando upload', [
            'destino'      => $destino,
            'nome_arquivo' => $nomeArquivo,
            'dir_exists'   => file_exists($destino),
            'dir_writable' => file_exists($destino) ? is_writable($destino) : 'dir não existe ainda',
            'public_path'  => public_path(),
        ]);

        if (!file_exists($destino)) {
            $criado = mkdir($destino, 0755, true);
            Log::info('[SolicitacaoService@uploadAnexo] Diretório criado', [
                'sucesso'  => $criado,
                'destino'  => $destino,
            ]);
        }

        $file->move($destino, $nomeArquivo);
        $pathFinal = 'uploads/solicitacoes/anexos/' . $nomeArquivo;

        Log::info('[SolicitacaoService@uploadAnexo] Arquivo salvo com sucesso', [
            'path_final'   => $pathFinal,
            'file_exists'  => file_exists(public_path($pathFinal)),
            'file_size'    => file_exists(public_path($pathFinal)) ? filesize(public_path($pathFinal)) : 'não encontrado',
        ]);

        return $pathFinal;
    }
}