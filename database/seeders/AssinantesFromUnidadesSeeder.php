<?php

namespace Database\Seeders;

use App\Models\Prefeitura;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Migra Unidades → Users com role `assinante`, deduplicando por
 * (lower(servidor_responsavel) + prefeitura_id). Mesma pessoa em N unidades da
 * mesma prefeitura vira 1 user, vinculado à primeira unidade encontrada.
 *
 *  php artisan db:seed --class=AssinantesFromUnidadesSeeder
 *
 * Idempotente: roda quantas vezes precisar; só cria/atualiza, nunca duplica.
 * Pré-requisito: AssinanteRoleSeeder já rodou (role `assinante` existe).
 *
 * O relatório de senhas iniciais é gravado em:
 *   storage/app/seeders/assinantes-credenciais-{timestamp}.csv
 * Esse arquivo é a única forma de saber as senhas geradas — entregue ao
 * administrador da prefeitura e DELETE depois.
 */
class AssinantesFromUnidadesSeeder extends Seeder
{
    private string $emailDomainSuffix = 'assinantes.gestgov.local';

    public function run(): void
    {
        $role = Role::where('name', 'assinante')->where('guard_name', 'web')->first();

        if (!$role) {
            $this->command->error('Role `assinante` não existe. Rode primeiro: php artisan db:seed --class=AssinanteRoleSeeder');
            return;
        }

        $unidades = Unidade::query()
            ->whereNotNull('servidor_responsavel')
            ->where('servidor_responsavel', '!=', '')
            ->orderBy('prefeitura_id')
            ->orderBy('id')
            ->get();

        if ($unidades->isEmpty()) {
            $this->command->warn('Nenhuma unidade com servidor_responsavel preenchido.');
            return;
        }

        $relatorio = [];
        $criados = 0;
        $atualizados = 0;
        $ignorados = 0;

        // Deduplica por (prefeitura_id + servidor normalizado)
        $vistos = []; // chave => user_id

        DB::transaction(function () use ($unidades, $role, &$relatorio, &$criados, &$atualizados, &$ignorados, &$vistos) {
            foreach ($unidades as $unidade) {
                $servidor = trim((string) $unidade->servidor_responsavel);
                $chave = $unidade->prefeitura_id . '|' . Str::lower(Str::ascii($servidor));

                // Já criou este assinante (em outra unidade da mesma prefeitura) — pula.
                if (isset($vistos[$chave])) {
                    $ignorados++;
                    continue;
                }

                $prefeitura = Prefeitura::find($unidade->prefeitura_id);
                if (!$prefeitura) {
                    $ignorados++;
                    continue;
                }

                // Tenta achar user existente por nome + prefeitura primeiro
                $user = User::where('prefeitura_id', $unidade->prefeitura_id)
                    ->whereRaw('LOWER(name) = ?', [Str::lower($servidor)])
                    ->first();

                $senhaInicial = null;

                if ($user) {
                    // Já existe — só completa campos faltantes e marca como assinante
                    $alteracoes = [];
                    if (!$user->unidade_id) {
                        $alteracoes['unidade_id'] = $unidade->id;
                    }
                    if (!$user->numero_portaria && $unidade->numero_portaria) {
                        $alteracoes['numero_portaria'] = $unidade->numero_portaria;
                    }
                    if (!$user->data_portaria && $unidade->data_portaria) {
                        $alteracoes['data_portaria'] = $unidade->data_portaria;
                    }
                    if (!$user->is_assinante) {
                        $alteracoes['is_assinante'] = true;
                    }
                    if (!empty($alteracoes)) {
                        $user->forceFill($alteracoes)->save();
                        $atualizados++;
                    } else {
                        $ignorados++;
                    }
                } else {
                    $email = $this->gerarEmailUnico($servidor, $prefeitura);
                    $senhaInicial = Str::random(12);

                    $user = User::create([
                        'name'             => $servidor,
                        'email'            => $email,
                        'password'         => Hash::make($senhaInicial),
                        'prefeitura_id'    => $unidade->prefeitura_id,
                        'unidade_id'       => $unidade->id,
                        'numero_portaria'  => $unidade->numero_portaria,
                        'data_portaria'    => $unidade->data_portaria,
                        'is_assinante'     => true,
                    ]);
                    $criados++;
                }

                // Garante a role atribuída (não dispara duplicado)
                if (!$user->hasRole('assinante')) {
                    $user->assignRole($role);
                }

                $vistos[$chave] = $user->id;

                if ($senhaInicial) {
                    $relatorio[] = [
                        'prefeitura' => $prefeitura->nome ?? $prefeitura->cidade ?? "#{$prefeitura->id}",
                        'nome'       => $user->name,
                        'email'      => $user->email,
                        'unidade'    => $unidade->nome,
                        'numero_portaria' => $user->numero_portaria,
                        'data_portaria'   => optional($user->data_portaria)->format('d/m/Y'),
                        'senha_inicial'   => $senhaInicial,
                    ];
                }
            }
        });

        $this->command->info("Assinantes criados: {$criados}");
        $this->command->info("Atualizados:        {$atualizados}");
        $this->command->info("Já existiam:        {$ignorados}");

        if (!empty($relatorio)) {
            $caminho = $this->salvarRelatorio($relatorio);
            $this->command->warn("⚠  Senhas iniciais gravadas em: {$caminho}");
            $this->command->warn("⚠  Entregue ao admin da prefeitura e DELETE em seguida.");
        }
    }

    /**
     * Gera um e-mail único e estável para o assinante.
     *   joao.silva.bertolinia@assinantes.gestgov.local
     * Em caso de colisão, anexa sufixo numérico.
     */
    private function gerarEmailUnico(string $nome, Prefeitura $prefeitura): string
    {
        $slugNome = Str::slug(Str::lower(Str::ascii($nome)), '.');
        $slugPref = Str::slug(Str::lower(Str::ascii($prefeitura->cidade ?? $prefeitura->nome ?? "p{$prefeitura->id}")), '');

        $base = "{$slugNome}.{$slugPref}@{$this->emailDomainSuffix}";

        if (!User::where('email', $base)->exists()) {
            return $base;
        }

        // Colisão — anexa contador
        for ($i = 2; $i < 1000; $i++) {
            $candidato = "{$slugNome}.{$slugPref}-{$i}@{$this->emailDomainSuffix}";
            if (!User::where('email', $candidato)->exists()) {
                return $candidato;
            }
        }

        throw new \RuntimeException("Não foi possível gerar e-mail único para '{$nome}'.");
    }

    private function salvarRelatorio(array $linhas): string
    {
        $dir = storage_path('app/seeders');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $caminho = $dir . '/assinantes-credenciais-' . now()->format('Ymd_His') . '.csv';
        $fp = fopen($caminho, 'w');

        fputcsv($fp, ['Prefeitura', 'Nome', 'E-mail', 'Unidade primária', 'Nº Portaria', 'Data Portaria', 'Senha inicial']);
        foreach ($linhas as $l) {
            fputcsv($fp, [
                $l['prefeitura'],
                $l['nome'],
                $l['email'],
                $l['unidade'],
                $l['numero_portaria'],
                $l['data_portaria'],
                $l['senha_inicial'],
            ]);
        }

        fclose($fp);
        return $caminho;
    }
}
