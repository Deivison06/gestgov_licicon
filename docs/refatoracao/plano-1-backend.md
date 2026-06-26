# Plano 1 — Refatoração Backend

> **Foco principal (definido pelo dono do projeto):** refatorar os 4 controllers de
> domínio — `ProcessoController`, `FinalizacaoProcessoController`,
> `ContratoProcessoController`, `AtaController` — e **todos os services** que eles usam,
> criando **abstratos** (Controller / Service / Repository) e a **camada de Repository +
> Services** própria desses domínios (Processo, Finalização, Contrato, Ata).
>
> **Princípio inviolável:** lógica **idêntica** — mesma resposta HTTP/JSON, mesmo
> comportamento, mesmo schema. Simplificar é bem-vindo, desde que o resultado não mude.
> Execução **por fases** (uma por domínio); cada fase é validada antes da próxima.

---

## ✅ Já concluído

### Fase 1.1 — Fundações (helpers/traits)
- `App\Http\Controllers\Concerns\RespondsWithJson` — `jsonOk()`, `jsonFail()`, `tryJson()` (no `Controller` base).
- `App\Support\FileStorage` — `salvar()`, `remover()`, `caminhoAbsoluto()`.
- `App\Models\Concerns\BelongsToPrefeitura` — scope `daPrefeitura($id)`.
- **Pilotos migrados:** `FinalizacaoProcessoController` (respostas JSON), `FinalizacaoService::salvarAnexo` (FileStorage), `User` + `AssinanteController` (scope).

### Abstratos (foundation para os domínios)
- `App\Repositories\Interfaces\RepositoryInterface` — contrato genérico enxuto.
- `App\Repositories\AbstractRepository` — CRUD Eloquent genérico (find/findOrFail/all/paginate/create/update/delete/newQuery).
- `App\Services\AbstractService` — `transacao(callable, contextoLog)` (DB::transaction + log + relança).
- `App\Http\Controllers\AbstractController` — herda os helpers JSON e já traz `dispararDownloadEmLote(processoId, fase)` (padrão duplicado entre Processo/Finalização).

> ⚠️ A migração dos repos genéricos pré-existentes (User/Etp/Pca/Solicitacao) **saiu de escopo** —
> não fazem parte do alvo. O `AbstractRepository` será usado pelos **novos** repositórios de domínio.

---

## 🎯 Alvos e seus services

| Domínio | Controller | Linhas | Services |
|---|---|---|---|
| **Processo** | `ProcessoController` | 1376 | `ProcessoService` (524), `ProcessoDocumentoService` (618), `ProcessoPdfService` (1289) |
| **Finalização** | `FinalizacaoProcessoController` | 242 | `FinalizacaoService` (232), `FinalizacaoDocumentoService` (513), `FinalizacaoPdfService` (1257), `FinalizacaoVencedorService` (306), `HomologacaoService` (232) |
| **Contrato** | `ContratoProcessoController` | 1605 | (sem injeção no construtor — usa services inline; **a investigar** na fase) |
| **Ata** | `AtaController` | 506 | `AtaService` (273), `AtaContratacaoService` (502), `AtaDocumentoService` (195), `AtaPdfService` (926) |

---

## 🔁 Padrão de refatoração aplicado a CADA domínio

Para cada domínio (Processo, Finalização, Contrato, Ata), na fase respectiva:

1. **Repository** — criar `XxxRepository extends AbstractRepository`; mover para ele todo o acesso ao Eloquent (queries, `where`, `with`, `find/create/update/delete`) hoje espalhado no service/controller.
2. **Service(s)** — `XxxService extends AbstractService`; usar `transacao()` no lugar dos `DB::transaction`/try-catch manuais; consumir o Repository; deduplicar métodos repetidos entre os services do domínio (e entre domínios → sobe para `AbstractService`/helper).
3. **Controller** — `XxxController extends AbstractController`; deixar **fino** (valida → chama service → `jsonOk`/`jsonFail`/`tryJson`); usar `dispararDownloadEmLote` onde aplicável; tirar regra de negócio que vazou para o controller.
4. **Validação** — teste de caracterização **antes** (congela a saída atual) onde não há cobertura; `php artisan test` verde; smoke manual do fluxo (gerar documento, salvar campo, download).

> Os 3 PDF services (`ProcessoPdfService`, `FinalizacaoPdfService`, `AtaPdfService`) têm
> métodos duplicados entre si (ex.: `processarAnexos`, junção via Ghostscript). Quando 2+
> domínios forem refatorados, esse comum sobe para um `AbstractPdfService` + colaboradores
> (`PdfMergeService`, `AnexoResolver`).

---

## 📅 Fases de execução (uma por domínio)

> Ordem sugerida: do menor/mais conhecido para o maior/mais arriscado, para firmar o padrão antes dos controllers gigantes.

- [ ] **Fase 1.2 — Finalização** (já iniciada na 1.1; menor superfície) → `FinalizacaoRepository`, slim services, slim controller.
- [ ] **Fase 1.3 — Ata** (`AtaController` + 4 services).
- [ ] **Fase 1.4 — Processo** (`ProcessoController` 1376 + 3 services).
- [ ] **Fase 1.5 — Contrato** (`ContratoProcessoController` 1605, métodos gordos, maior risco — investigar services usados inline).
- [ ] **Fase 1.6 — `AbstractPdfService`** (consolida o comum dos 3 PDF services após os domínios).

---

## Estratégia de segurança (transversal)

1. Teste de caracterização **antes** de refatorar onde não há cobertura.
2. Commits pequenos e reversíveis, **um domínio por vez**.
3. **Nenhuma** alteração de rota, contrato de API, middleware ou schema.
4. `php artisan test` verde a cada passo + smoke manual dos fluxos críticos.
