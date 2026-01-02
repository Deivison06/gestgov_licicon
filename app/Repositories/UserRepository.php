<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    protected $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

     public function getFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['roles', 'permissions', 'prefeitura'])
            ->orderBy('name');

        // Filtro de busca (nome, email, CPF)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        // Filtro por role
        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('id', $filters['role']);
            });
        }

        // Filtro por prefeitura
        if (!empty($filters['prefeitura_id'])) {
            $query->where('prefeitura_id', $filters['prefeitura_id']);
        }

        // Filtro por status (se houver campo active)
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $isActive = $filters['status'] === 'active';
            $query->where('active', $isActive);
        }

        return $query->paginate($perPage);
    }


    public function getAll(): Collection
    {
        return $this->model->with('roles')->get();
    }

    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('roles')->latest()->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return $this->model->with('roles')->find($id);
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function syncRoles(User $user, array $roles): void
    {
        $user->syncRoles($roles);
    }
}
