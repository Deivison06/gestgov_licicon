<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class MemoriaCalculoImportService
{
    /**
     * Converte as linhas brutas (coluna A = rótulo/texto, coluna B = valor) da planilha
     * "MEMÓRIA DE CÁLCULO" em um array de itens prontos para persistir em
     * pesquisa_preco_memoria_itens.
     *
     * @return array<int, array{ordem:int, titulo:?string, valor_adotado:float, fontes:array}>
     */
    public function parse(Collection $rows): array
    {
        $itens = [];
        $itemAtual = null;
        $fonteAtual = null;
        $aguardandoUrl = false;

        foreach ($rows as $row) {
            $colA = trim((string) ($row[0] ?? ''));
            $colB = $row[1] ?? null;

            if ($colA === '') {
                continue;
            }

            if (preg_match('/^Item\s+(\d+)\s*[-–—]\s*(.+)$/u', $colA, $m)) {
                $this->fecharFonte($itemAtual, $fonteAtual);
                $this->fecharItem($itens, $itemAtual);

                $resto = $m[2];
                $titulo = Str::contains($resto, ':') ? trim(Str::before($resto, ':')) : trim($resto);

                $itemAtual = [
                    'ordem' => (int) $m[1],
                    'titulo' => $titulo,
                    'valor_adotado' => null,
                    'fontes' => [],
                ];
                $fonteAtual = null;
                $aguardandoUrl = false;

                continue;
            }

            if ($itemAtual === null) {
                // Linha fora de qualquer bloco de item — ignora silenciosamente (ex.: cabeçalho/rodapé).
                continue;
            }

            if (preg_match('/^Fonte\s+(\d+)\s*·\s*(.+)$/u', $colA, $m)) {
                $this->fecharFonte($itemAtual, $fonteAtual);

                $resto = trim($m[2]);
                $fonteAtual = [
                    'ordem' => (int) $m[1],
                    'tipo' => null,
                    'orgao_nome' => null,
                    'uf' => null,
                    'descricao' => null,
                    'qtd_comprada' => null,
                    'unidade_comprada' => null,
                    'data_referencia' => null,
                    'processo_origem' => null,
                    'fornecedor_nome' => null,
                    'fornecedor_cnpj' => null,
                    'link' => null,
                    'identificador' => null,
                    'valor_unitario' => $colB !== null && $colB !== '' ? (float) $colB : null,
                ];

                if (Str::startsWith($resto, 'Painel TCE/PI')) {
                    $fonteAtual['tipo'] = 'painel_tce';
                    $orgaoUf = trim(ltrim(Str::after($resto, 'Painel TCE/PI'), "· \t"));

                    if (preg_match('/^(.*)\s+[-–—]\s+([A-Za-zÀ-ÿ]{2})$/u', $orgaoUf, $mm)) {
                        $fonteAtual['orgao_nome'] = trim($mm[1]);
                        $fonteAtual['uf'] = strtoupper($mm[2]);
                    } else {
                        $fonteAtual['orgao_nome'] = $orgaoUf !== '' ? $orgaoUf : null;
                    }
                } elseif (Str::contains($resto, 'Fornecedor local')) {
                    $fonteAtual['tipo'] = 'fornecedor_local';
                } else {
                    $fonteAtual['tipo'] = 'outro';
                    $fonteAtual['orgao_nome'] = $resto;
                }

                $aguardandoUrl = false;

                continue;
            }

            if ($fonteAtual === null) {
                continue;
            }

            if (preg_match('/^Descrição:\s*(.*)$/u', $colA, $m)) {
                $fonteAtual['descricao'] = trim($m[1]);

                continue;
            }

            if (preg_match('/^Qtd\.\s*comprada:\s*(.*)$/u', $colA, $m)) {
                foreach (explode('·', $m[1]) as $parte) {
                    $parte = trim($parte);

                    if (preg_match('/^([\d.,]+)\s+(.+)$/u', $parte, $mm)) {
                        $fonteAtual['qtd_comprada'] = $this->normalizarNumero($mm[1]);
                        $fonteAtual['unidade_comprada'] = trim($mm[2]);
                    } elseif (preg_match('/^Data:\s*(\d{2}\/\d{2}\/\d{4})$/u', $parte, $mm)) {
                        $fonteAtual['data_referencia'] = $mm[1];
                    } elseif (preg_match('/^Processo:\s*(.+)$/u', $parte, $mm)) {
                        $fonteAtual['processo_origem'] = trim($mm[1]);
                    }
                }

                continue;
            }

            if (preg_match('/^Fornecedor:\s*(.+)$/u', $colA, $m)) {
                $resto = trim($m[1]);

                if (preg_match('/^(.*?)\s*·\s*CNPJ\s+([\d.\/-]+)$/u', $resto, $mm)) {
                    $fonteAtual['fornecedor_nome'] = trim($mm[1]);
                    $fonteAtual['fornecedor_cnpj'] = $mm[2];
                } else {
                    $fonteAtual['fornecedor_nome'] = $resto;
                }

                continue;
            }

            if (preg_match('/^Link de acesso:\s*$/u', $colA)) {
                $aguardandoUrl = true;

                continue;
            }

            if ($aguardandoUrl) {
                $fonteAtual['link'] = $colA;
                $aguardandoUrl = false;

                continue;
            }

            if (preg_match('/^Identificador:\s*(.+)$/u', $colA, $m)) {
                $fonteAtual['identificador'] = trim($m[1]);

                continue;
            }

            if (preg_match('/^Valor adotado$/ui', $colA)) {
                $this->fecharFonte($itemAtual, $fonteAtual);
                $fonteAtual = null;
                $itemAtual['valor_adotado'] = $colB !== null && $colB !== '' ? (float) $colB : null;

                continue;
            }
        }

        $this->fecharFonte($itemAtual, $fonteAtual);
        $this->fecharItem($itens, $itemAtual);

        $this->validar($itens);

        return $itens;
    }

    private function fecharFonte(?array &$itemAtual, ?array $fonteAtual): void
    {
        if ($itemAtual !== null && $fonteAtual !== null) {
            $itemAtual['fontes'][] = $fonteAtual;
        }
    }

    private function fecharItem(array &$itens, ?array &$itemAtual): void
    {
        if ($itemAtual !== null) {
            $itens[] = $itemAtual;
            $itemAtual = null;
        }
    }

    private function normalizarNumero(string $valor): float
    {
        $valor = trim($valor);

        if (Str::contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return (float) $valor;
    }

    private function validar(array $itens): void
    {
        if (empty($itens)) {
            throw new RuntimeException('Não foi possível identificar nenhum item na planilha. Confira se o arquivo segue o modelo "MEMÓRIA DE CÁLCULO".');
        }

        foreach ($itens as $item) {
            if ($item['valor_adotado'] === null) {
                throw new RuntimeException("O item \"Item {$item['ordem']} — {$item['titulo']}\" não possui um valor adotado preenchido na planilha.");
            }
        }
    }
}
