<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Prefeitura;
use Illuminate\Http\Request;
use App\Services\UserService;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\UsuarioRequest;
use Spatie\Permission\Models\Permission;

class UsuarioController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        try {
            // Obter parâmetros de filtro
            $filters = [
                'search' => $request->input('search'),
                'role' => $request->input('role'),
                'prefeitura_id' => $request->input('prefeitura_id'),
                'status' => $request->input('status', 'all')
            ];

            // Obter usuários com filtros
            $users = $this->userService->getFilteredUsers($filters, 10);

            // Obter dados para filtros
            $roles = Role::orderBy('name')->get();
            $prefeituras = Prefeitura::orderBy('nome')->get();

            return view('Admin.Usuarios.index', compact('users', 'roles', 'prefeituras', 'filters'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Erro ao carregar usuários: ' . $e->getMessage());
        }
    }


    public function create()
    {
        return view('Admin.Usuarios.create', [
            'roles' => Role::all(),
            'permissions' => Permission::all(),
            'prefeituras' => Prefeitura::orderBy('nome')->get() // Adicione esta linha
        ]);
    }

    public function store(UsuarioRequest $request)
    {
        try {
            $this->userService->createUser($request->validated());
            return redirect()->route('admin.usuarios.index')
                ->with('success', 'Usuário criado com sucesso!');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao criar usuário: ' . $e->getMessage());
        }
    }

    public function edit(User $usuario)
    {

        return view('Admin.Usuarios.edit', [
            'usuario' => $usuario,
            'roles' => Role::all(),
            'permissions' => Permission::all(),
            'prefeituras' => Prefeitura::orderBy('nome')->get() // Adicione esta linha
        ]);
    }

    public function update(UsuarioRequest $request, User $usuario)
    {
        try {
            $this->userService->updateUser($usuario, $request->validated());
            return redirect()->route('admin.users.index')
                ->with('success', 'Usuário atualizado com sucesso!');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }

    public function destroy(User $usuario)
    {
        try {
            $this->userService->deleteUser($usuario);
            return redirect()->route('admin.users.index')
                ->with('success', 'Usuário excluído com sucesso!');
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao excluir usuário: ' . $e->getMessage());
        }
    }

    public function toggleStatus(User $user)
    {
        try {
            $this->userService->toggleUserStatus($user);
            $status = $user->active ? 'ativado' : 'desativado';
            return redirect()->back()
                ->with('success', "Usuário {$status} com sucesso!");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao alterar status do usuário: ' . $e->getMessage());
        }
    }
}
