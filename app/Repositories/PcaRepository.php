<?php

namespace App\Repositories;

use App\Models\Pca;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PcaRepository
{
    /**
     * Retorna os PCAs de uma prefeitura com filtros e paginação
     */
    public function getByPrefeituraId(int $prefeituraId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Pca::query()
            ->with(['prefeitura', 'itens', 'itens.unidade'])
            ->where('prefeitura_id', $prefeituraId);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('numero_pca', 'like', "%{$search}%")
                  ->orWhere('exercicio', 'like', "%{$search}%")
                  ->orWhereHas('itens.unidade', function ($subq) use ($search) {
                      $subq->where('nome', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['exercicio'])) {
            $query->where('exercicio', $filters['exercicio']);
        }

        return $query->latest()->paginate($perPage);
    }
    
    /**
     * Retorna todos os PCAs com filtros e paginação (para admin/diretor)
     */
    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Pca::query()
            ->with(['prefeitura', 'itens', 'itens.unidade']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('numero_pca', 'like', "%{$search}%")
                  ->orWhere('exercicio', 'like', "%{$search}%")
                  ->orWhereHas('itens.unidade', function ($subq) use ($search) {
                      $subq->where('nome', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['prefeitura_id'])) {
            $query->where('prefeitura_id', $filters['prefeitura_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['exercicio'])) {
            $query->where('exercicio', $filters['exercicio']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Busca um PCA por ID carregando os relacionamentos necessários
     */
    public function findById(int $id): ?Pca
    {
        return Pca::with(['prefeitura', 'itens', 'itens.unidade'])->find($id);
    }

    /**
     * Cria um novo PCA
     */
    public function create(array $data): Pca
    {
        return Pca::create($data);
    }

    /**
     * Atualiza um PCA
     */
    public function update(int $id, array $data): bool
    {
        $pca = $this->findById($id);
        if (!$pca) {
            return false;
        }

        return $pca->update($data);
    }

    /**
     * Sincroniza os itens de um PCA
     */
    public function syncItens(Pca $pca, array $itensData): void
    {
        $idsEnviados = array_filter(array_column($itensData, 'id'));

        $pca->itens()->whereNotIn('id', $idsEnviados)->delete();

        foreach ($itensData as $itemData) {
            $itemId = $itemData['id'] ?? null;
            if (empty($itemId)) {
                unset($itemData['id']);
            }

            if ($itemId) {
                $pca->itens()->where('id', $itemId)->update($itemData);
            } else {
                $pca->itens()->create($itemData);
            }
        }
    }
}
