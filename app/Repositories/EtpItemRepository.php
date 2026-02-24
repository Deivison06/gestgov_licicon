<?php

namespace App\Repositories;

use App\Models\EtpItem;

class EtpItemRepository
{
    protected $model;

    public function __construct(EtpItem $model)
    {
        $this->model = $model;
    }

    public function getAll($perPage = 15)
    {
        return $this->model->orderBy('descricao_item', 'asc')->paginate($perPage);
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $item = $this->findById($id);
        $item->update($data);
        return $item;
    }

    public function delete($id)
    {
        $item = $this->findById($id);
        return $item->delete();
    }
}
