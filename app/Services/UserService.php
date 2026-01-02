<?php

namespace App\Services;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Repositories\UserRepository;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers(): Collection
    {
        return $this->userRepository->getAll();
    }

     // Método para usuários com filtros
    public function getFilteredUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $users = $this->userRepository->getFiltered($filters, $perPage);

        $users->getCollection()->transform(function ($user) {
            $user->permissions_list = $user->getAllPermissions()->pluck('name');
            $user->role_names = $user->roles->pluck('name')->implode(', ');
            $user->prefeitura_nome = $user->prefeitura ? $user->prefeitura->nome : 'Não atribuída';
            return $user;
        });

        return $users;
    }

    public function getPaginatedUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->getFilteredUsers([], $perPage);
    }



    public function findUser(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function createUser(array $data): User
    {
        DB::beginTransaction();
        try {
            $user = $this->userRepository->create($data);

            // Sincronizar roles
            if (!empty($data['roles'])) {
                $roles = Role::whereIn('id', $data['roles'])->pluck('name')->toArray();
                $user->syncRoles($roles);
            }

            // Sincronizar permissions
            if (!empty($data['permissions'])) {
                $permissions = Permission::whereIn('id', $data['permissions'])->pluck('name')->toArray();
                $user->syncPermissions($permissions);
            }

            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar usuário: ' . $e->getMessage());
            throw new Exception('Erro ao criar usuário: ' . $e->getMessage());
        }
    }

    public function updateUser(User $user, array $data): User
    {
        DB::beginTransaction();

        try {
            // Remove password if empty
            if (empty($data['password'])) {
                unset($data['password']);
            }

            $this->userRepository->update($user, $data);

            if (isset($data['roles'])) {
                $this->userRepository->syncRoles($user, $data['roles']);
            }

            DB::commit();
            return $user->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar usuário: ' . $e->getMessage());
            throw new Exception('Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }

    public function deleteUser(User $user): bool
    {
        DB::beginTransaction();

        try {
            // Não permitir que o usuário se delete
            if ($user->id === auth()->id()) {
                throw new Exception('Você não pode excluir sua própria conta.');
            }

            $result = $this->userRepository->delete($user);
            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir usuário: ' . $e->getMessage());
            throw new Exception('Erro ao excluir usuário: ' . $e->getMessage());
        }
    }

    public function toggleUserStatus(User $user): User
    {
        $user->update(['active' => !$user->active]);
        return $user->refresh();
    }
}
