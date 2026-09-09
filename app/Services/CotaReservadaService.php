<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Cota Reservada ME/EPP: divide, só EM MEMÓRIA (nunca persiste no ETP), cada
 * lote marcado em dois "lotes efetivos" — Ampla Concorrência (75%) e Cota
 * Reservada para ME e EPP (25%, arredondado para cima) — para fins de geração
 * de documentos do processo (TR/Edital) e exportação BNC. O ETP Inteligente
 * original nunca é alterado; a config de quais lotes estão marcados vive em
 * `ProcessoDetalhe.lotes_cota_reservada` (JSON: [{etp_lote_id, ativo}, ...]).
 *
 * As constantes de sufixo são a fonte única de verdade do nome usado em:
 * - ProcessoPdfService::construirItensTr() (TR/Edital)
 * - ProcessoBncExportService (planilha BNC)
 * - ProcessoTceExportService (comparação textual best-effort no lote_nome
 *   digitado na Finalização — não há FK entre o lote homologado e o ETP)
 */
class CotaReservadaService
{
    public const SUFIXO_AMPLA_CONCORRENCIA = ' (Ampla Concorrência)';

    public const SUFIXO_COTA_RESERVADA = ' (Cota Reservada para ME e EPP)';

    /** Percentual da cota reservada — o restante fica com a Ampla Concorrência. */
    public const PERCENTUAL_RESERVADO = 0.25;

    /**
     * Abaixo desta quantidade, ceil(qtd * 0.25) zeraria a Ampla Concorrência
     * (qtd=1 -> reservada=1, ampla=0). Itens com quantidade menor que este
     * limite NÃO são divididos: ficam 100% em Ampla Concorrência, mesmo que
     * o lote esteja marcado.
     */
    public const QUANTIDADE_MINIMA_PARA_DIVIDIR = 2;

    /**
     * Extrai a lista de etp_lote_id marcados como "ativo" a partir do JSON
     * salvo em ProcessoDetalhe::lotes_cota_reservada.
     *
     * @return int[]
     */
    public function loteIdsAtivos(?array $config): array
    {
        return collect($config ?? [])
            ->filter(fn ($linha) => ($linha['ativo'] ?? false) === true)
            ->pluck('etp_lote_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Recebe os lotes "crus" do ETP (Collection<EtpLote> com `itens` eager
     * loaded, cada item com pivot->unidade/quantidade) + a lista de
     * etp_lote_id marcados, e devolve a lista de "lotes efetivos": um mesmo
     * etp_lote_id pode gerar 1 ou 2 entradas no array de saída.
     *
     * Formato de cada entrada:
     * [
     *     'etp_lote_id' => 5,
     *     'nome' => 'Lote 1' | 'Lote 1 (Ampla Concorrência)' | 'Lote 1 (Cota Reservada para ME e EPP)',
     *     'cota_reservada' => null|false|true, // null = lote não dividido
     *     'itens' => [
     *         ['etp_item_id' => 10, 'descricao_item' => '...', 'unidade' => 'UN', 'quantidade' => 75.0],
     *         ...
     *     ],
     * ]
     *
     * @param  Collection<int, \App\Models\EtpLote>  $lotesEtp
     * @param  int[]  $loteIdsReservados
     * @return array<int, array>
     */
    public function dividirItensPorLote(Collection $lotesEtp, array $loteIdsReservados): array
    {
        $resultado = [];

        foreach ($lotesEtp as $lote) {
            $marcado = in_array($lote->id, $loteIdsReservados, true);

            if (! $marcado) {
                $resultado[] = $this->loteInalterado($lote);

                continue;
            }

            $itensAmpla = [];
            $itensReservada = [];
            $algumItemDividido = false;

            foreach ($lote->itens as $item) {
                $quantidade = (int) round((float) $item->pivot->quantidade);

                if ($quantidade < self::QUANTIDADE_MINIMA_PARA_DIVIDIR) {
                    // Quantidade pequena demais para dividir com sentido (zeraria
                    // um dos lados) — fica 100% em Ampla Concorrência.
                    $itensAmpla[] = $this->itemParaArray($item, $quantidade);

                    continue;
                }

                $algumItemDividido = true;
                $reservada = (int) ceil($quantidade * self::PERCENTUAL_RESERVADO);
                $ampla = $quantidade - $reservada;

                $itensAmpla[] = $this->itemParaArray($item, $ampla);
                $itensReservada[] = $this->itemParaArray($item, $reservada);
            }

            if (! $algumItemDividido) {
                // Lote marcado, mas nenhum item pôde ser dividido (todos abaixo
                // do mínimo) — mantém o lote como estava, sem sufixo, para não
                // exibir "(Ampla Concorrência)" sem contrapartida reservada.
                $resultado[] = $this->loteInalterado($lote);

                continue;
            }

            $resultado[] = [
                'etp_lote_id' => $lote->id,
                'nome' => $lote->nome.self::SUFIXO_AMPLA_CONCORRENCIA,
                'cota_reservada' => false,
                'itens' => $itensAmpla,
            ];

            if (! empty($itensReservada)) {
                $resultado[] = [
                    'etp_lote_id' => $lote->id,
                    'nome' => $lote->nome.self::SUFIXO_COTA_RESERVADA,
                    'cota_reservada' => true,
                    'itens' => $itensReservada,
                ];
            }
        }

        return $resultado;
    }

    /**
     * Verifica se um nome de lote (ex.: digitado/importado na Finalização)
     * indica que se trata do lote de cota reservada — comparação textual,
     * já que a tabela `lotes` (pós-homologação) não tem FK para o ETP.
     */
    public function nomeIndicaCotaReservada(?string $loteNomeDigitado): bool
    {
        return str_ends_with(trim((string) $loteNomeDigitado), trim(self::SUFIXO_COTA_RESERVADA));
    }

    /**
     * Remove o sufixo de Ampla Concorrência/Cota Reservada de um nome de lote,
     * devolvendo o nome "base" do lote no ETP. Útil para reencontrar o
     * `EtpLote` original a partir de um `lote_nome` digitado/importado na
     * Finalização (que carrega o nome já sufixado, já que é isso que sai
     * no TR/Edital/BNC).
     */
    public function removerSufixos(?string $loteNome): string
    {
        $nome = trim((string) $loteNome);

        foreach ([self::SUFIXO_AMPLA_CONCORRENCIA, self::SUFIXO_COTA_RESERVADA] as $sufixo) {
            if (str_ends_with($nome, trim($sufixo))) {
                return trim(substr($nome, 0, -strlen(trim($sufixo))));
            }
        }

        return $nome;
    }

    private function loteInalterado($lote): array
    {
        return [
            'etp_lote_id' => $lote->id,
            'nome' => $lote->nome,
            'cota_reservada' => null,
            'itens' => $lote->itens->map(
                fn ($item) => $this->itemParaArray($item, (int) round((float) $item->pivot->quantidade))
            )->all(),
        ];
    }

    private function itemParaArray($item, int $quantidade): array
    {
        return [
            'etp_item_id' => $item->id,
            'descricao_item' => $item->descricao_item,
            'unidade' => $item->pivot->unidade,
            'quantidade' => $quantidade,
        ];
    }
}
