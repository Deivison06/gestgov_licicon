<?php

namespace Tests\Unit\Services;

use App\Services\MemoriaCalculoImportService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MemoriaCalculoImportServiceTest extends TestCase
{
    #[DataProvider('separadores')]
    public function test_reconhece_cabecalho_de_item_com_lote(string $cabecalho): void
    {
        $rows = collect([
            [$cabecalho, null],
            ['Fonte 1 · Fornecedor local · ACME', 4.9],
            ['Valor adotado', 4.9],
        ]);

        $itens = (new MemoriaCalculoImportService)->parse($rows);

        $this->assertCount(1, $itens);
        $this->assertSame('1', $itens[0]['lote']);
        $this->assertSame(1, $itens[0]['numero_item']);
        $this->assertSame('AÇÚCAR', $itens[0]['titulo']);
        $this->assertSame(4.9, $itens[0]['valor_adotado']);
    }

    public static function separadores(): array
    {
        return [
            'ponto mediano' => ['Lote 1 · Item 01 — AÇÚCAR: detalhe'],
            'hifen' => ['Lote 1 - Item 01 — AÇÚCAR: detalhe'],
        ];
    }
}
