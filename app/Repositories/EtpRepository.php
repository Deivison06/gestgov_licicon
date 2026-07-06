<?php

namespace App\Repositories;

use App\Models\Etp;
use App\Models\EtpLote;
class EtpRepository
{
    protected $model;

    public function __construct(Etp $model)
    {
        $this->model = $model;
    }

    public function getByPrefeituraId($prefeituraId, $filters = [], $perPage = 15)
    {
         $query = $this->model->where('prefeitura_id', $prefeituraId)
                             ->with(['secretaria']);

         if (isset($filters['status']) && $filters['status']) {
             $query->where('status', $filters['status']);
         }

         return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getAllWithFilters($filters = [], $perPage = 15)
    {
        $query = $this->model->with(['prefeitura', 'secretaria']);

        if (isset($filters['prefeitura_id']) && $filters['prefeitura_id']) {
            $query->where('prefeitura_id', $filters['prefeitura_id']);
        }

        if (isset($filters['secretaria_id']) && $filters['secretaria_id']) {
            $query->where('secretaria_id', $filters['secretaria_id']);
         }

        if (isset($filters['status']) && $filters['status'] === 'pendente_lancamento') {
            // ETPs aprovados que ainda não foram vinculados a um processo
            $query->where('status', 'aprovado')->whereNull('processo_id');
        } elseif (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        } else {
            // Se NÃO vier filtro de status, excluir pendente
            $query->where('status', '!=', 'pendente');
        }


         if (isset($filters['data_inicio']) && $filters['data_inicio']) {
             $query->whereDate('created_at', '>=', $filters['data_inicio']);
        }

         if (isset($filters['data_fim']) && $filters['data_fim']) {
              $query->whereDate('created_at', '<=', $filters['data_fim']);
         }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function countPendentesLancamento()
    {
        return $this->model->where('status', 'aprovado')->whereNull('processo_id')->count();
    }

    public function findById($id)
    {
        return $this->model
            ->with([
                'prefeitura',
                'secretaria',
                'itens',       // necessário para ETPs do tipo 'item', 'servicos', 'compras'
                'lotes.itens', // necessário para ETPs do tipo 'lote'
            ])
            ->findOrFail($id);
    }


    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $etp = $this->findById($id);
        $etp->update($data);
        return $etp;
    }

    public function syncItens(Etp $etp, array $itens)
    {
        $etp->itens()->sync($itens);
    }

    public function createLote(Etp $etp, array $data)
{
    return $etp->lotes()->create($data);
}

public function syncItensLote(EtpLote $lote, array $itens)
{
    $lote->itens()->sync($itens);
}

}
