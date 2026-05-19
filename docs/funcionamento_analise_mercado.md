# Funcionamento da Análise de Mercado (PDF)

Este documento descreve o fluxo técnico de como os dados da "Análise de Mercado" são processados e exibidos no PDF do sistema GestGov.

## 1. Fluxo de Dados

O processo é dividido em três etapas principais: importação, persistência e renderização.

### 1.1. Importação (Excel)
Os dados da tabela de preços são originados de um arquivo de planilha (Excel/CSV) enviado pelo usuário na seção de Análise de Mercado do processo.

- **Serviço Responsável:** `App\Services\ProcessoService`
- **Método:** `processarPainelPrecos($file, ProcessoDetalhe $detalhe, string $campo)`

O sistema utiliza a biblioteca `PhpOffice\PhpSpreadsheet` para ler as colunas da planilha e mapeá-las para um array estruturado:

| Coluna Planilha | Campo no Sistema | Descrição |
| :--- | :--- | :--- |
| 0 | `item` | Identificador do item |
| 1 | `valor_tce_1` | Primeira cotação (TCE) |
| 2 | `valor_tce_2` | Segunda cotação (TCE) |
| 3 | `valor_tce_3` | Terceira cotação (TCE) |
| 4 | `fornecedor_local` | Valor/Nome do fornecedor local |
| 5 | `media` | Média dos valores |

### 1.2. Persistência (Banco de Dados)
Após o mapeamento, o array é convertido para uma string **JSON** (usando `json_encode`) e salvo no banco de dados.

- **Tabela:** `processo_detalhes`
- **Coluna:** `painel_preco_tce`
- **Modelo:** `App\Models\ProcessoDetalhe`

### 1.3. Renderização do PDF (Blade)
Ao gerar o PDF, o sistema lê esse JSON e o transforma de volta em um array para iterar na tabela HTML.

- **Arquivo View:** `resources/views/Admin/Processos/pdf/concorrencia/analise_mercado.blade.php`

**Lógica de extração:**
```php
@php
    $painel = is_array($detalhe->painel_preco_tce)
        ? $detalhe->painel_preco_tce
        : json_decode($detalhe->painel_preco_tce, true);
@endphp
```

**Lógica de exibição:**
O sistema utiliza um `@foreach ($painel as $item)` para percorrer cada linha e preencher as colunas da tabela no PDF.

---

## 2. Componentes Envolvidos

### Modelos
- **Processo:** Relacionamento principal.
- **ProcessoDetalhe:** Armazena o JSON na coluna `painel_preco_tce`.

### Serviços
- **ProcessoService:** Contém a lógica de processamento do arquivo Excel e normalização de valores.
- **ProcessoDocumentoService:** Define a configuração do documento e os campos necessários (como `painel_preco_tce` e `anexo_pdf_analise_mercado`).

---

## 3. Observações Técnicas Importantes

1. **Normalização de Valores:** O sistema possui uma função `normalizarValor` no `ProcessoService` que tenta tratar diferentes formatos de moeda (ponto vs vírgula). Atualmente, o campo `valor_tce_1` é o único que **não** passa por essa normalização durante a importação.
2. **Campos Vazios:** Se a planilha contiver linhas em branco, elas podem ser ignoradas ou gerar entradas vazias dependendo da estrutura do arquivo.
3. **Fallback:** Caso não existam dados no painel, o PDF exibe a mensagem "Nenhum dado disponível".
