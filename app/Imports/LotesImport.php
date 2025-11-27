<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class LotesImport implements ToCollection, WithStartRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        // O método collection é chamado automaticamente
        // Os dados já estão disponíveis no formato de Collection
        return $collection;
    }

    /**
     * Define a linha de início (opcional - para pular cabeçalhos)
     */
    public function startRow(): int
    {
        return 1; // Começa da linha 1 (permite pular cabeçalho se necessário)
    }
}
