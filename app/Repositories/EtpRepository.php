<?php

namespace App\Repositories;

use App\Models\Etp;

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

        if (isset($filters['status']) && $filters['status']) {
             $query->where('status', $filters['status']);
        }

         if (isset($filters['data_inicio']) && $filters['data_inicio']) {
             $query->whereDate('created_at', '>=', $filters['data_inicio']);
        }

         if (isset($filters['data_fim']) && $filters['data_fim']) {
              $query->whereDate('created_at', '<=', $filters['data_fim']);
         }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById($id)
    {
        return $this->model->with(['prefeitura', 'secretaria', 'itens'])->findOrFail($id);
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
}
