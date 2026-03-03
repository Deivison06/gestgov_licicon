<?php

namespace App\Repositories;

use App\Models\Solicitacao;
use App\Models\SolicitacaoMensagem;

class SolicitacaoRepository
{
    protected $model;
    protected $messageModel;

    public function __construct(Solicitacao $model, SolicitacaoMensagem $messageModel)
    {
        $this->model = $model;
        $this->messageModel = $messageModel;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function addMessage(array $data)
    {
        return $this->messageModel->create($data);
    }

    public function findById($id)
    {
        return $this->model->with(['usuario', 'mensagens.usuario'])->findOrFail($id);
    }


    public function updateStatus($id, $status)
    {
        $solicitacao = $this->model->findOrFail($id);
        $solicitacao->update(['status' => $status]);
        return $solicitacao;
    }

    public function listPaginated(array $filters = [], $perPage = 15)
    {
        $query = $this->model->with(['usuario', 'prefeitura']);

        if (isset($filters['prefeitura_id'])) {
            $query->where('prefeitura_id', $filters['prefeitura_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('updated_at', 'desc')->paginate($perPage);
    }
}
