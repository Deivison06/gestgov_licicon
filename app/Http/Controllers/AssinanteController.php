<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssinanteRequest;
use App\Models\Prefeitura;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * CRUD da role `assinante`: users com is_assinante = true.
 * Não cria a role aqui — espera que AssinanteRoleSeeder já tenha rodado.
 */
class AssinanteController extends Controller
{
    public function index(Request $request)
    {
        $filtros = [
            'search'        => $request->input('search'),
            'prefeitura_id' => $request->input('prefeitura_id'),
            'status'        => $request->input('status', 'todos'),
        ];

        $query = User::query()
            ->where('is_assinante', true)
            ->with(['prefeitura', 'unidade']);

        if (!empty($filtros['search'])) {
            $termo = '%' . $filtros['search'] . '%';
            $query->where(function ($q) use ($termo) {
                $q->where('name', 'like', $termo)
                  ->orWhere('email', 'like', $termo)
                  ->orWhere('numero_portaria', 'like', $termo);
            });
        }

        if (!empty($filtros['prefeitura_id'])) {
            $query->where('prefeitura_id', $filtros['prefeitura_id']);
        }

        // status `ativo`/`inativo` é baseado em is_assinante — todos aqui são ativos
        // por filtro. Reservado para futura coluna `ativo` na users.
        if ($filtros['status'] === 'inativo') {
            $query->where('is_assinante', false);
        }

        $assinantes = $query->orderBy('name')->paginate(15)->withQueryString();

        $prefeituras = Prefeitura::orderBy('nome')->get();

        return view('Admin.Assinantes.index', compact('assinantes', 'prefeituras', 'filtros'));
    }

    public function create()
    {
        return view('Admin.Assinantes.create', [
            'prefeituras' => Prefeitura::orderBy('nome')->get(),
            'unidades'    => Unidade::orderBy('nome')->get(),
        ]);
    }

    public function store(AssinanteRequest $request)
    {
        try {
            $dados = $request->validated();

            DB::transaction(function () use ($dados) {
                $user = User::create([
                    'name'            => $dados['name'],
                    'email'           => $dados['email'],
                    'cpf'             => $dados['cpf'] ?? null,
                    'password'        => Hash::make($dados['password']),
                    'prefeitura_id'   => $dados['prefeitura_id'],
                    'unidade_id'      => $dados['unidade_id'] ?? null,
                    'numero_portaria' => $dados['numero_portaria'] ?? null,
                    'data_portaria'   => $dados['data_portaria'] ?? null,
                    'is_assinante'    => true,
                ]);

                $this->garantirRoleAssinante($user);
            });

            return redirect()->route('admin.assinantes.index')
                ->with('success', 'Assinante criado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar assinante', ['erro' => $e->getMessage()]);
            return redirect()->back()->withInput()
                ->with('error', 'Erro ao criar assinante: ' . $e->getMessage());
        }
    }

    public function edit(User $assinante)
    {
        // garante que é assinante mesmo (defesa, não confia só na URL)
        abort_unless($assinante->is_assinante, 404);

        return view('Admin.Assinantes.edit', [
            'assinante'   => $assinante,
            'prefeituras' => Prefeitura::orderBy('nome')->get(),
            'unidades'    => Unidade::where('prefeitura_id', $assinante->prefeitura_id)->orderBy('nome')->get(),
        ]);
    }

    public function update(AssinanteRequest $request, User $assinante)
    {
        abort_unless($assinante->is_assinante, 404);

        try {
            $dados = $request->validated();

            DB::transaction(function () use ($assinante, $dados) {
                $payload = [
                    'name'            => $dados['name'],
                    'email'           => $dados['email'],
                    'cpf'             => $dados['cpf'] ?? null,
                    'prefeitura_id'   => $dados['prefeitura_id'],
                    'unidade_id'      => $dados['unidade_id'] ?? null,
                    'numero_portaria' => $dados['numero_portaria'] ?? null,
                    'data_portaria'   => $dados['data_portaria'] ?? null,
                ];

                if (!empty($dados['password'])) {
                    $payload['password'] = Hash::make($dados['password']);
                }

                $assinante->update($payload);

                $this->garantirRoleAssinante($assinante);
            });

            return redirect()->route('admin.assinantes.index')
                ->with('success', 'Assinante atualizado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar assinante', ['erro' => $e->getMessage(), 'id' => $assinante->id]);
            return redirect()->back()->withInput()
                ->with('error', 'Erro ao atualizar assinante: ' . $e->getMessage());
        }
    }

    public function destroy(User $assinante)
    {
        abort_unless($assinante->is_assinante, 404);

        try {
            // Soft-disable: tira a flag e a role. Mantém o user (assinaturas antigas preservadas).
            DB::transaction(function () use ($assinante) {
                $assinante->is_assinante = false;
                $assinante->save();
                $assinante->removeRole('assinante');
            });

            return redirect()->route('admin.assinantes.index')
                ->with('success', 'Assinante desativado. Assinaturas antigas permanecem válidas.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao desativar assinante: ' . $e->getMessage());
        }
    }

    // ====================================================================
    // API: assinantes disponíveis (consumido pelo modal de seleção)
    // ====================================================================

    /**
     * GET /admin/assinantes/disponiveis
     * Retorna JSON paginado de assinantes ativos da prefeitura, prontos para
     * serem selecionados num modal de geração de documento.
     */
    public function disponiveis(Request $request)
    {
        $request->validate([
            'prefeitura_id' => ['required', 'integer', 'exists:prefeituras,id'],
            'search'        => ['nullable', 'string', 'max:80'],
        ]);

        $query = User::query()
            ->where('is_assinante', true)
            ->where('prefeitura_id', $request->integer('prefeitura_id'))
            ->with('unidade');

        if ($search = $request->input('search')) {
            $termo = '%' . $search . '%';
            $query->where(function ($q) use ($termo) {
                $q->where('name', 'like', $termo)
                  ->orWhere('email', 'like', $termo);
            });
        }

        $items = $query->orderBy('name')->paginate(20);

        return response()->json([
            'data' => $items->map(fn ($u) => [
                'id'              => $u->id,
                'name'            => $u->name,
                'email'           => $u->email,
                'unidade'         => optional($u->unidade)->nome,
                'numero_portaria' => $u->numero_portaria,
                'data_portaria'   => optional($u->data_portaria)->format('d/m/Y'),
            ]),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    // ====================================================================
    // Importação CSV
    // ====================================================================

    public function mostrarImportarCsv()
    {
        return view('Admin.Assinantes.importar-csv', [
            'prefeituras' => Prefeitura::orderBy('nome')->get(),
        ]);
    }

    public function processarImportarCsv(Request $request)
    {
        $request->validate([
            'arquivo'       => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'prefeitura_id' => ['required', 'integer', 'exists:prefeituras,id'],
        ]);

        $prefeituraId = (int) $request->input('prefeitura_id');
        $caminho = $request->file('arquivo')->getRealPath();
        $fp = fopen($caminho, 'r');

        if ($fp === false) {
            return back()->with('error', 'Não foi possível ler o arquivo CSV.');
        }

        $cabecalho = $this->normalizarCabecalho(fgetcsv($fp, 0, ',') ?: []);
        $necessarios = ['nome', 'email'];
        $faltando = array_diff($necessarios, $cabecalho);

        if (!empty($faltando)) {
            fclose($fp);
            return back()->with('error', 'CSV inválido. Cabeçalhos obrigatórios: ' . implode(', ', $necessarios)
                . '. Faltando: ' . implode(', ', $faltando));
        }

        $criados = 0;
        $atualizados = 0;
        $erros = [];
        $linha = 1; // já consumiu cabeçalho

        DB::transaction(function () use ($fp, $cabecalho, $prefeituraId, &$criados, &$atualizados, &$erros, &$linha) {
            while (($row = fgetcsv($fp, 0, ',')) !== false) {
                $linha++;
                if ($this->linhaVazia($row)) continue;

                $assoc = $this->mapearLinha($cabecalho, $row);

                if (empty($assoc['nome']) || empty($assoc['email'])) {
                    $erros[] = "Linha {$linha}: nome ou email vazio.";
                    continue;
                }

                if (!filter_var($assoc['email'], FILTER_VALIDATE_EMAIL)) {
                    $erros[] = "Linha {$linha}: e-mail '{$assoc['email']}' inválido.";
                    continue;
                }

                $existente = User::where('email', $assoc['email'])->first();

                if ($existente) {
                    $payload = array_filter([
                        'prefeitura_id'   => $prefeituraId,
                        'numero_portaria' => $assoc['numero_portaria'] ?? null,
                        'data_portaria'   => $this->parseData($assoc['data_portaria'] ?? null),
                        'is_assinante'    => true,
                    ], fn($v) => $v !== null);
                    $existente->update($payload);
                    $this->garantirRoleAssinante($existente);
                    $atualizados++;
                } else {
                    $senha = Str::random(12);
                    $user = User::create([
                        'name'            => $assoc['nome'],
                        'email'           => $assoc['email'],
                        'password'        => Hash::make($senha),
                        'prefeitura_id'   => $prefeituraId,
                        'numero_portaria' => $assoc['numero_portaria'] ?? null,
                        'data_portaria'   => $this->parseData($assoc['data_portaria'] ?? null),
                        'is_assinante'    => true,
                    ]);
                    $this->garantirRoleAssinante($user);
                    $criados++;

                    // Anota a senha em sessão para o relatório final.
                    session()->push('csv_credenciais_geradas', [
                        'nome'  => $user->name,
                        'email' => $user->email,
                        'senha' => $senha,
                    ]);
                }
            }
        });

        fclose($fp);

        return redirect()->route('admin.assinantes.index')
            ->with('success', "Importação concluída: {$criados} criados, {$atualizados} atualizados, "
                . count($erros) . ' erros.')
            ->with('csv_erros', $erros);
    }

    // ====================================================================
    // Helpers
    // ====================================================================

    private function garantirRoleAssinante(User $user): void
    {
        $role = Role::where('name', 'assinante')->where('guard_name', 'web')->first();
        if (!$role) {
            throw new \RuntimeException("Role 'assinante' não existe. Rode: php artisan db:seed --class=AssinanteRoleSeeder");
        }
        if (!$user->hasRole($role)) {
            $user->assignRole($role);
        }
    }

    private function normalizarCabecalho(array $cabecalho): array
    {
        return array_map(
            fn($c) => Str::lower(Str::snake(trim($c))),
            $cabecalho
        );
    }

    private function mapearLinha(array $cabecalho, array $row): array
    {
        $assoc = [];
        foreach ($cabecalho as $i => $coluna) {
            $assoc[$coluna] = isset($row[$i]) ? trim($row[$i]) : null;
        }
        return $assoc;
    }

    private function linhaVazia(array $row): bool
    {
        return count(array_filter($row, fn($c) => trim((string) $c) !== '')) === 0;
    }

    private function parseData(?string $valor): ?string
    {
        if (!$valor) return null;
        try {
            // Aceita dd/mm/yyyy ou yyyy-mm-dd
            $valor = trim($valor);
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valor)) {
                return \Carbon\Carbon::createFromFormat('d/m/Y', $valor)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse($valor)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
