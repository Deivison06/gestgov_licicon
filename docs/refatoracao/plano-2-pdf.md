# Plano 2 — Refatoração dos PDFs

> **Princípio inviolável:** **nenhum texto pode ser removido e nada pode ser resumido —
> tudo que existe é importante.** A refatoração apenas move o **invólucro**
> (DOCTYPE / head / style / body) para um layout base e extrai **estruturas repetidas**
> para componentes. Todo o conteúdo textual permanece **verbatim** em cada template.

Pastas no escopo:
- `resources/views/Admin/Processos/contrato`
- `resources/views/Admin/Processos/pdf`
- `resources/views/Admin/Processos/pdf-finalizacao`

---

## 📊 Diagnóstico (dados reais do código)

| Pasta | Arquivos `.blade.php` | Linhas |
|---|---|---|
| `pdf` | 192 | 107.391 |
| `pdf-finalizacao` | 39 | 10.201 |
| `contrato` | 8 | 7.500 |
| **Total** | **239** | **~125.000** |

- **0** uso de layout (`@extends`) — 238 arquivos são HTML standalone com `<!DOCTYPE>` + `<style>` próprio.
- CSS ~95% idêntico: entre as 12 variantes de um mesmo documento (`estudo_tecnico`) há apenas **4 versões distintas** de `<style>` (160–177 linhas cada).
- **226** arquivos contêm `signature-block` e `page-break`.
- Componentização atual quase nula (apenas `@include('…capa_edital')` em ~16 arquivos).

**Esqueleto repetido em todo arquivo:** `<!DOCTYPE>`, `<head>`, `@font-face` (Aptos / AptosExtraBold),
`@page { margin:0; size:A4 }`, `body` com fundo de timbre (`background-image: url(public_path($prefeitura->timbre))`),
`.page-break`, estilos de capa (`#cover-page`), `signature-block`, tabelas.

**Estrutura de `pdf/` (modalidade → segmento → tipo):**
`concorrencia`, `dispensa/{compras_lote,obra,servicos_lote}`,
`inexigibilidade/{artistico,fornecedor,imovel,tecnico}`,
`pregao_eletronico/{compras_item,compras_lote,servicos_item,servicos_lote}`.

**Tipos com mais variantes** (candidatos a começar): `capa_republicacao` (13),
`publicacoes_avisos_licitacao` / `parecer_juridico` / `minutas` / `formalizacao` /
`estudo_tecnico` / `disponibilidade_orçamento` / `capa` / `autorizacao_abertura_procedimento` (12 cada),
`termo_referencia` (11), `edital` / `avisos_licitacao` / `ata_registro_preco` / `analise_mercado` / `abertura_fase_externa` (8 cada).

---

## Fase 2.1 — Layout base

- [ ] Criar `pdf/layouts/documento.blade.php` (ou componente `<x-pdf-doc>`) contendo o invólucro comum:
  - `<!DOCTYPE>`, `<head>`, `<meta charset>`, `<title>` dinâmico.
  - `@font-face` (Aptos / AptosExtraBold), `@page`, `body` com fundo de timbre.
  - O **CSS comum** (page-break, `#cover-page`, `signature-block`, tabelas).
- [ ] Slots/stacks:
  - `$title` (título dinâmico do documento).
  - `{{ $slot }}` / `@yield('conteudo')` para o corpo.
  - **`@stack('estilos-extra')`** para o CSS que varia entre variantes
    (o comum vai para o base; os deltas — as 4 versões — viram `@push('estilos-extra')`).

> ⚠️ DomPDF é sensível a CSS herdado/ordem de regras. O `@stack` garante que cada
> variante preserve exatamente o seu CSS específico.

---

## Fase 2.2 — Componentes

Cada componente é um recorte **literal** do que já existe, parametrizado só nos pontos que variam.

- [ ] `<x-pdf.assinatura :assinante="…" :fallback="…">` — bloco de assinatura (226 arquivos).
      **Renderiza exatamente o mesmo bloco unitário** (sem juntar assinantes lado a lado —
      respeitando a regra já definida: assinante `[0]` continua `[0]`, `[1]` continua `[1]`).
- [ ] `<x-pdf.capa>` — página de capa / `#cover-page`.
- [ ] `<x-pdf.quebra>` — `<div class="page-break"></div>`.
- [ ] `<x-pdf.cabecalho>` — timbre / título de seção, onde houver padrão.

---

## Fase 2.3 — Migração por tipo de documento

- [ ] Um tipo por vez (ex.: `estudo_tecnico` nas 12 variantes → depois `edital`, `minutas`, `termo_referencia`, `contrato`…).
- [ ] Para cada arquivo:
  1. Trocar o invólucro pelo layout base; mover o CSS comum para o base e o específico para `@push('estilos-extra')`.
  2. Extrair os blocos repetidos (assinatura, capa, quebra) para componentes.
  3. **Manter 100% do texto** do corpo, sem reordenar nem resumir.
- [ ] Priorizar maior volume/repetição: `estudo_tecnico`, `edital`, `termo_referencia`, `contrato`.

---

## Fase 2.4 — Verificação "golden file" (garantia de zero perda)

Para **cada arquivo migrado**:

- [ ] Gerar o PDF **antes** e **depois** da migração (mesmos dados de entrada).
- [ ] Extrair o texto com `pdftotext` dos dois e **comparar com `diff` — tem que ser vazio**.
- [ ] Conferir que o **número de páginas** é idêntico.
- [ ] Só fazer merge da migração se o diff de texto for vazio.

> Isso prova objetivamente que **nenhum texto sumiu, foi resumido ou alterado**.

---

## Risco / mitigação

- **CSS herdado no DomPDF** → mitigado pelo `@stack('estilos-extra')` + golden-file por arquivo.
- **`contrato/` com arquivos enormes** (cláusulas jurídicas, até ~79 KB) → migração extra-cuidadosa, sempre com diff de texto antes de aceitar.
- **Variações sutis de CSS entre variantes** (4 versões) → não unificar à força; preservar os deltas via `@push`.

---

## Ordem sugerida

1. Fase 2.1 (layout base) + Fase 2.2 (componentes).
2. Fase 2.3 + 2.4: migrar tipo a tipo, do maior volume para o menor, sempre com golden-file.
3. `contrato/` por último (maior risco jurídico), com revisão de diff reforçada.

---

## Relação com o Plano 3 (frontend — futuro)

A componentização dos PDFs (layout + `<x-pdf.*>`) abre caminho para o Plano 3
(componentização do frontend da aplicação), que será detalhado depois.
