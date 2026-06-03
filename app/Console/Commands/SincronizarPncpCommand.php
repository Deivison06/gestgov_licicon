<?php

namespace App\Console\Commands;

use App\Services\PncpSincronizarService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SincronizarPncpCommand extends Command
{
    protected $signature = 'pncp:sincronizar
        {--meses=6       : Quantos meses retroativos sincronizar (padrão: 6)}
        {--uf=PI         : Uma ou mais UFs separadas por vírgula (ex: PI,CE,MA). Padrão: PI}
        {--nordeste      : Sincroniza todos os 9 estados do Nordeste (ignora --uf)}
        {--todas-ufs     : Sincroniza todas as 27 UFs (ignora --uf)}
        {--incremental   : Sincroniza apenas os últimos 2 dias (atualização diária)}
        {--dias=2        : Quantidade de dias para o modo incremental}
        {--pausa=900     : Segundos de pausa entre UFs para evitar rate limit (padrão: 15 min)}';

    protected $description = 'Sincroniza contratações do PNCP (Pregão, Dispensa, Inexigibilidade) para cache local';

    protected const UFS_NORDESTE = ['AL','BA','CE','MA','PB','PE','PI','RN','SE'];

    protected const TODAS_UFS = [
        'AC','AL','AM','AP','BA','CE','DF','ES','GO',
        'MA','MG','MS','MT','PA','PB','PE','PI','PR',
        'RJ','RN','RO','RR','RS','SC','SE','SP','TO',
    ];

    protected array $nomeModalidade = [
        6 => 'Pregão Eletrônico',
        8 => 'Dispensa',
        9 => 'Inexigibilidade',
    ];

    public function handle(PncpSincronizarService $sincronizador): int
    {
        $incremental = $this->option('incremental');
        $meses       = (int) $this->option('meses');
        $dias        = (int) $this->option('dias');
        $pausaSeg    = (int) $this->option('pausa');

        // Resolve lista de UFs
        if ($this->option('todas-ufs')) {
            $ufs = self::TODAS_UFS;
        } elseif ($this->option('nordeste')) {
            $ufs = self::UFS_NORDESTE;
        } else {
            $ufs = array_filter(array_map(
                fn($u) => strtoupper(trim($u)),
                explode(',', $this->option('uf') ?: 'PI')
            ));
        }

        $modo = $incremental ? "incremental ({$dias} dias)" : "completo ({$meses} meses)";
        $this->info("=== Sincronização PNCP — {$modo} ===");
        $this->line('  Modalidades: Pregão Eletrônico · Dispensa · Inexigibilidade');
        $this->line('  UFs: ' . implode(', ', $ufs));
        if (count($ufs) > 1) {
            $pausaMin = round($pausaSeg / 60, 1);
            $this->line("  Pausa entre UFs: {$pausaMin} min");
        }
        $this->newLine();

        $totalErros = 0;
        $totalSync  = 0;

        foreach ($ufs as $indice => $uf) {
            // Pausa entre UFs (exceto antes da primeira)
            if ($indice > 0) {
                $this->newLine();
                $this->line("  <comment>Aguardando {$pausaSeg}s antes da próxima UF (rate limit)...</comment>");
                sleep($pausaSeg);
            }

            $modalidadeAtual = null;
            $modo = $incremental ? "incremental ({$dias} dias)" : "completo ({$meses} meses)";

            $this->newLine();
            $this->info("=== UF: {$uf} — modo {$modo} ===");

            $onProgress = function (int $total, int $modalidade, int $pagina, int $paginas) use (&$modalidadeAtual) {
                if ($modalidade !== $modalidadeAtual) {
                    $modalidadeAtual = $modalidade;
                    $nome = $this->nomeModalidade[$modalidade] ?? "Modalidade {$modalidade}";
                    $this->line('');
                    $this->line("  <comment>{$nome}</comment>");
                }
                $this->line("    Pág. {$pagina}/{$paginas} · {$total} sincronizados");
            };

            $inicio = microtime(true);

            $resultado = $incremental
                ? $sincronizador->sincronizarIncremental($dias, $uf, $onProgress)
                : $sincronizador->sincronizar($meses, $uf, $onProgress);

            $tempo = round(microtime(true) - $inicio, 1);

            $this->newLine();
            $this->table(
                ['UF', 'Sincronizados', 'Erros', 'Páginas', 'Tempo'],
                [[
                    $uf,
                    number_format($resultado['sincronizados'], 0, ',', '.'),
                    $resultado['erros'],
                    $resultado['paginas'],
                    "{$tempo}s",
                ]]
            );

            $totalSync  += $resultado['sincronizados'];
            $totalErros += $resultado['erros'];
        }

        Cache::forget('pncp_cache_disponivel');

        if (count($ufs) > 1) {
            $this->newLine();
            $this->info("=== Total geral: " . number_format($totalSync, 0, ',', '.') . " contratos sincronizados em " . count($ufs) . " UFs ===");
        }

        if ($totalErros > 0) {
            $this->warn('Sincronização concluída com erros. Verifique os logs para detalhes.');
            return self::FAILURE;
        }

        $this->info('Sincronização concluída com sucesso.');
        return self::SUCCESS;
    }
}
