# Plano de Execução — Sistema de Assinatura Digital (estilo SEI)

> **Documento vivo.** Fonte de verdade do projeto. Toda decisão arquitetural,
> alteração de escopo e decisão de produto deve ser refletida aqui antes de
> virar código.

**Autor:** Equipe GestGov Licitações
**Última atualização:** 2026-05-30
**Status:** Fase 0 — Planejamento (aprovado)
**Prefeitura piloto:** Corrente

---

## Sumário

1. [Decisões aprovadas](#1-decisões-aprovadas)
2. [Decisões em aberto](#2-decisões-em-aberto)
3. [Arquitetura proposta](#3-arquitetura-proposta)
4. [Modelagem de banco](#4-modelagem-de-banco)
5. [Fluxo de assinatura](#5-fluxo-de-assinatura)
6. [Fluxo de validação pública](#6-fluxo-de-validação-pública)
7. [Estratégia de permissões](#7-estratégia-de-permissões)
8. [Alterações no sistema atual](#8-alterações-no-sistema-atual)
9. [PDFs + QRCode](#9-pdfs--qrcode)
10. [Notificações](#10-notificações)
11. [Regras de negócio](#11-regras-de-negócio)
12. [Fluxo inspirado no SEI](#12-fluxo-inspirado-no-sei)
13. [Riscos e cuidados](#13-riscos-e-cuidados)
14. [Roadmap por fases](#14-roadmap-por-fases)
15. [Estratégia de migração Unidades → Users](#15-estratégia-de-migração-unidades--users)
16. [Melhorias futuras](#16-melhorias-futuras)
17. [Glossário](#17-glossário)
18. [Changelog deste documento](#18-changelog-deste-documento)

---

## 1. Decisões aprovadas

| # | Decisão | Resposta |
|---|---|---|
| 1 | Plano de execução validado pelo product owner | ✅ Validado |
| 2 | Prefeitura piloto | ✅ Corrente |
| 3 | Documento permite regeração após algumas assinaturas | ✅ Pode regerar — cria nova versão, assinaturas antigas ficam atreladas à versão antiga |
| 4 | Comportamento ao recusar assinatura | ✅ **Cancela toda a rodada** — solicitações pendentes viram `cancelada`, assinaturas já feitas viram inválidas. Operador edita → nova versão → todos reassinam |
| 5 | E-mail externo aos assinantes | ✅ **NÃO no MVP.** Notificação somente in-app (sininho). E-mail externo poderá ser adicionado em fase futura, sem urgência |
| 6 | Certificado digital ICP-Brasil | ✅ **Assinatura eletrônica simples no MVP** (Lei 14.063/2020). ICP-Brasil fica como item de roadmap (Fase 3+) |

---

## 2. Decisões em aberto

Nenhuma decisão pendente neste momento. Todas as escolhas estruturais do MVP estão fixadas na Seção 1.

Novas decisões podem surgir durante a execução das fases (ex.: design da página pública, layout do bloco de assinatura). Quando aparecerem, serão registradas aqui com `🟡 PENDENTE` antes de virarem código.

---

## 3. Arquitetura proposta

### 3.1 Modelo conceitual

```
┌──────────────┐      ┌──────────────────────┐      ┌──────────────────┐
│ User         │─────►│ SolicitacaoAssinatura│─────►│ DocumentoVersao  │
│ (assinante)  │ N:M  │ (state machine)      │  N:1 │ (Polimórfico:    │
│  + Role      │      │   - status           │      │  Processo/Ata/   │
└──────────────┘      │   - ordem            │      │  Contrato/Etp)   │
                      │   - expires_at       │      └──────────────────┘
                      └──────────────────────┘              │
                              │                             │
                              ▼                             ▼
                      ┌──────────────────┐         ┌──────────────────┐
                      │ AssinaturaDigital│         │  caminho_pdf     │
                      │   - hash         │         │  hash_sha256     │
                      │   - ip           │         │  versao          │
                      │   - assinado_em  │         └──────────────────┘
                      │   - codigo_verif │
                      └──────────────────┘
                              │
                              ▼
                      ┌──────────────────┐
                      │ AssinaturaLog    │
                      │  (toda ação)     │
                      └──────────────────┘
```

### 3.2 Camadas

- **Domain Services:** `AssinaturaService`, `SolicitacaoService`, `DocumentoHashService`, `ValidacaoPublicaService`, `AssinaturaConsolidacaoService`
- **Notifications:** Laravel Notifications nativo (tabela `notifications` já existe) + Mail
- **PDF Pipeline:** estampagem de assinaturas + rodapé + QRCode após a última assinatura via FPDI
- **Public:** rota não-autenticada `/autenticar/{codigo}` (rate-limited)
- **Auth:** User com role `assinante` (Spatie) + middlewares + policies

---

## 4. Modelagem de banco

10 tabelas novas (algumas já existem na infra Spatie). Detalhamento individual abaixo.

### 4.1 `users` (alteração — já feita)

Campos adicionados em `2026_05_30_000001_add_assinante_fields_to_users_table.php`:
- `numero_portaria` (string nullable)
- `data_portaria` (date nullable)
- `is_assinante` (boolean, default false, index)

> Esses campos ficam em `users` por simplicidade do MVP. Quando o sistema
> evoluir para "um user pertence a N prefeituras", migrarão para uma tabela
> `assinantes_dados` com chave composta `(user_id, prefeitura_id)`.

### 4.2 `documento_versoes`

Cada geração/regeneração do PDF cria uma versão. Imutabilidade real.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint pk | |
| documentavel_type | string | morph |
| documentavel_id | bigint | morph |
| versao | int | autoincrementa por documentavel |
| caminho_pdf | string | path canônico (rascunho) |
| hash_sha256 | char(64) | hash do arquivo |
| gerado_por_user_id | fk users | |
| gerado_em | timestamp | |
| assinaturas_consolidadas_em | timestamp nullable | quando ficou imutável |
| caminho_pdf_assinado | string nullable | versão final com selos+QR |
| hash_pdf_assinado | char(64) nullable | |

Índice único: `(documentavel_type, documentavel_id, versao)`.

### 4.3 `solicitacoes_assinatura`

A "ordem de serviço" para o assinante. Uma por (versao, assinante).

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint pk | |
| documento_versao_id | fk | |
| assinante_user_id | fk users | |
| solicitado_por_user_id | fk users | |
| status | enum | `pendente`, `assinada`, `recusada`, `cancelada`, `expirada` |
| ordem | smallint | `0`=paralelo, `1..n`=sequencial |
| obrigatoria | boolean | default true |
| solicitado_em | timestamp | |
| expires_at | timestamp nullable | |
| processada_em | timestamp nullable | |
| token_acesso | char(64) unique | URL única (opcional, para e-mail) |
| motivo_recusa | text nullable | |

Índices: `(assinante_user_id, status)`, `(documento_versao_id, ordem)`.

### 4.4 `assinaturas_digitais`

A "prova" criptográfica da assinatura.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint pk | |
| solicitacao_assinatura_id | fk | |
| documento_versao_id | fk | redundante para query, ok |
| assinante_user_id | fk users | |
| **hash_documento_no_momento** | char(64) | snapshot do PDF — se mudar, assinatura quebra |
| **hash_cadeia_anterior** | char(64) nullable | hash da assinatura anterior — encadeamento |
| **hash_proprio** | char(64) | sha256(hash_doc + hash_anterior + assinante_id + timestamp) |
| codigo_verificador | char(20) unique | "0023776255" estilo SEI |
| ip | string | |
| user_agent | string | |
| assinado_em | timestamp | |
| metadados | json nullable | nome/cargo/matricula congelados, dispositivo |

Único: `(solicitacao_assinatura_id)`.

### 4.5 `assinatura_logs`

Audit trail completo. Toda ação importante grava aqui.

| Campo | Tipo |
|---|---|
| id | bigint pk |
| acao | enum (`criada`, `notificada`, `visualizada`, `assinada`, `recusada`, `cancelada`, `expirada`, `regerada`) |
| solicitacao_assinatura_id | fk nullable |
| documento_versao_id | fk nullable |
| user_id | fk users nullable |
| ip | string nullable |
| user_agent | text nullable |
| metadados | json |
| created_at | timestamp |

### 4.6 `consultas_publicas`

Logs de quem consultou o QR/código verificador na página pública.

| Campo | Tipo |
|---|---|
| id | bigint pk |
| codigo_verificador | char(20) |
| documento_versao_id | fk nullable |
| ip | string |
| user_agent | text |
| sucesso | boolean |
| consultado_em | timestamp |

---

## 5. Fluxo de assinatura

### 5.1 Geração do documento

1. Operador clica "Gerar PDF" e escolhe **N assinantes** (users com role `assinante` da mesma prefeitura)
2. Define **modo**: `paralelo` (default) ou `sequencial`
3. Define **prazo** (default: 7 dias úteis)
4. Sistema:
   - Gera PDF rascunho via DomPDF
   - Calcula `hash_sha256`
   - Persiste `documento_versoes` (versão N+1)
   - Cria N `solicitacoes_assinatura` com `status=pendente` + `ordem`
   - Dispara notificações (todas em paralelo, ou só ordem 1 em sequencial)
   - Marca o documentavel como `aguardando_assinatura`

### 5.2 Assinante recebe e visualiza

- Sininho do sistema mostra notificação (decisão 5: não há e-mail externo no MVP)
- "Central de Assinaturas" prioriza por urgência (`expires_at` próximo no topo)
- Clica → PDF embedded + metadados + ações: **Assinar** | **Recusar** | **Devolver com observação**

> O campo `token_acesso` em `solicitacoes_assinatura` continua sendo gerado e
> persistido — fica reservado para a fase futura onde e-mail externo for
> liberado. Não é consumido por nenhum fluxo do MVP.

### 5.3 Assinatura efetivada

- Modal de confirmação: "Você está prestes a assinar [doc]. Sua assinatura é juridicamente vinculante. Confirma?"
- Backend (transação + lock pessimista):
  1. Re-valida `hash_sha256` do PDF
  2. Calcula `hash_cadeia_anterior` (último `hash_proprio` da versão)
  3. Calcula `hash_proprio = sha256(hash_doc + hash_anterior + user_id + microtime)`
  4. Gera `codigo_verificador` (20 chars, único)
  5. Persiste `assinaturas_digitais`
  6. Atualiza `solicitacoes_assinatura.status = assinada`
  7. Loga em `assinatura_logs`
  8. Se sequencial → notifica próximo
  9. Se foi a última → **consolida** (estampagem + QR + rodapé + `caminho_pdf_assinado`)

### 5.4 Recusa

Comportamento conforme decisão 2.1.

### 5.5 Expiração

- Job diário (Schedule) marca `expires_at < now()` como `expirada`
- Notifica operador → prorroga ou cancela rodada

---

## 6. Fluxo de validação pública

### 6.1 Páginas

- `GET /autenticar` — formulário com input "Código verificador"
- `GET /autenticar/{codigo}` — direto via QR
- Rate-limit: **10 consultas/min/IP**

### 6.2 Resposta (exemplo de layout)

```
┌──────────────────────────────────────────────────────────────┐
│  ✓ Documento autêntico                                       │
│                                                              │
│  Tipo: Edital de Pregão Eletrônico 025/2026                  │
│  Versão: 3 (final)                                           │
│  Hash: a7b8034be...                                          │
│  Gerado em: 12/05/2026 às 20:48                              │
│                                                              │
│  Assinantes (4):                                             │
│  ✓ Jefferson Lucas Matias Sousa — Auditor Fiscal Ambiental   │
│    Matrícula 366914-9 — 12/05/2026 às 20:48                  │
│  ✓ João Evangelista de Sena Júnior — Coordenador             │
│    Matrícula 441780-1 — 12/05/2026 às 20:48                  │
│  ✓ ...                                                       │
│                                                              │
│  [Baixar PDF assinado]   [Ver histórico completo]            │
└──────────────────────────────────────────────────────────────┘
```

### 6.3 Cadeia de hash

A cadeia `hash_cadeia_anterior → hash_proprio` permite detectar inserção/remoção de assinaturas. Cada validação **re-calcula** a cadeia e compara — se quebrar, status = "alterado".

---

## 7. Estratégia de permissões

Reutiliza `spatie/laravel-permission` (já instalado).

### 7.1 Roles

| Role | Descrição | Status |
|---|---|---|
| `admin` | Tudo (mantém atual) | ✅ existe |
| `diretor_licicon` | Tudo dentro da prefeitura | ✅ existe |
| `gerente_licicon` | Quase tudo (sem gerenciar usuários) | ✅ existe |
| `colaborador_licicon` | Operacional | ✅ existe |
| `prefeitura` | Acesso operacional da prefeitura | ✅ existe |
| **`assinante`** | **Apenas assinar documentos** | ✅ criado (AssinanteRoleSeeder) |

### 7.2 Permissions

Já criadas em `AssinanteRoleSeeder`:
- `documento.assinar`
- `documento.recusar_assinatura`
- `documento.ver_pendencias`
- `documento.ver_historico_proprio`

A criar nas fases seguintes:
- `documento.gerar`
- `documento.escolher_assinantes`
- `documento.cancelar_solicitacao`
- `documento.ver_historico`
- `assinatura.consultar_publicamente` (público)
- `central_assinaturas.ver`

### 7.3 Middlewares customizados

- `EnsureAssinante` — role `assinante` ativa na prefeitura X
- `EnsureDocumentoAssinavel` — versão pendente E user é assinante autorizado
- `ThrottleConsultaPublica` — rate-limit por IP

### 7.4 Policies

- `SolicitacaoAssinaturaPolicy@assinar(user, solicitacao)`:
  - É o `assinante_user_id`
  - `status === pendente`
  - Não expirou
  - Se sequencial, é a vez dele
- `DocumentoVersaoPolicy@ver(user, versao)`:
  - É solicitado para assinar OU
  - É admin OU
  - É o operador que criou

---

## 8. Alterações no sistema atual

| Componente | Mudança |
|---|---|
| `Unidade` model | **Mantém** (conceito organizacional), mas deixa de carregar `servidor_responsavel` / `numero_portaria` para assinatura. Vira read-only. |
| Campo `assinantes` JSON em `Documento`, `Contrato`, `AtaRegistroPreco`, `ContratoManual`, `Etp` | Renomeado para `assinantes_legados` durante transição. Substituído pela tabela `solicitacoes_assinatura`. |
| Selects de "Unidade" nos forms (15+ controllers) | Substituídos por modal "Selecionar Assinantes" — busca paginada de users com role `assinante` da prefeitura. |
| ~40 PDFs blade | Bloco de assinatura passa a renderizar a partir de `documento_versoes.assinaturas_digitais`. Componente reutilizável `<x-bloco-assinaturas :versao="..." />`. |
| `ProcessoPdfService`, `FinalizacaoPdfService`, `AtaPdfService`, `ContratoProcessoController::gerarPdf` | **Não geram mais PDF final** — geram rascunho, salvam como `documento_versoes` e criam solicitações. PDF final vem do `AssinaturaConsolidacaoService`. |
| Botão "Baixar PDF" | Se versão não consolidada → rascunho com marca d'água "AGUARDANDO ASSINATURAS". Se consolidada → assinado. |
| `Admin/Processos/finalizar.blade.php` | Coluna "Status" no bloco-documentos ganha estados: `Rascunho`, `Aguardando 2/4 assinaturas`, `Assinado`, `Recusado`. |

---

## 9. PDFs + QRCode

### 9.1 Pacotes a instalar

- `simplesoftwareio/simple-qrcode` (QR — leve, sem GD pesado)
- `setasign/fpdi` (já presente — estampar selos sem regenerar)

### 9.2 Pipeline de consolidação

Disparado quando a última assinatura é registrada:

1. Lê `caminho_pdf` (rascunho)
2. Para cada assinatura na ordem: estampa bloco visual
3. Adiciona página/rodapé final com QR + código verificador + URL de validação
4. Calcula `hash_pdf_assinado`
5. Salva `caminho_pdf_assinado`

### 9.3 Bloco visual de assinatura

```
┌─────────────────────────────────────────────────────────────────────┐
│ [LOGO]  Documento assinado eletronicamente por JEFFERSON LUCAS      │
│  Sel.   MATIAS SOUSA — Matr.0366914-9, Auditor Fiscal Ambiental,    │
│         em 12/05/2026, às 20:48, conforme horário oficial de Brasília│
└─────────────────────────────────────────────────────────────────────┘
```

### 9.4 Rodapé final

- URL: `https://licitacoes.gestgov.com.br/autenticar/{codigo_verificador}`
- Texto: "A autenticidade deste documento pode ser conferida no site... informando o código verificador `0023776255` e o código CRC `A7B034BE`."
- CRC = primeiros 8 chars do hash do PDF assinado (legibilidade humana)

---

## 10. Notificações

Base: **Laravel Notifications** (tabela `notifications` já existe).

> **Decisão 5 (aprovada):** o MVP **não envia e-mail externo**. Toda comunicação
> com o assinante acontece dentro do sistema, via sininho. Esta seção descreve
> apenas o canal `database`. Se em fase futura o e-mail for liberado, basta
> adicionar `mail` ao array `via()` das Notification classes existentes — sem
> refactor estrutural.

### 10.1 Canais

- `database` — sininho no header (Alpine + polling de 30s)

### 10.2 Notification classes

Todas usam apenas `via(['database'])`:

- `NovaSolicitacaoAssinatura` — assinante recebe ao ser adicionado a uma rodada
- `SolicitacaoExpirando` — sininho 24h antes do `expires_at`
- `SolicitacaoExpirada` — operador + assinante são avisados
- `DocumentoTotalmenteAssinado` — para o operador que gerou
- `SolicitacaoRecusada` — para o operador (com motivo da recusa)
- `LembreteAssinaturaPendente` — semanal, com lista das pendências do assinante

### 10.3 UX do sininho

- Badge numérico no header com contagem de não-lidas
- Dropdown lista as últimas 10 com link direto para o documento
- Marca como lida ao clicar
- Página `/notificacoes` lista o histórico completo paginado
- Polling a cada 30s (atualiza badge sem refresh)

### 10.4 Tempo real (fase 7, opcional)

- **Laravel Reverb** (WebSocket nativo) + Echo
- Notificação aparece sem polling
- MVP usa polling — não bloqueia entrega

---

## 11. Regras de negócio

1. **Imutabilidade pós-assinatura:** após a 1ª solicitação assinada, o PDF rascunho dessa versão não pode mais ser editado. Para mudar = cancela rodada e cria nova versão. Decisão 3 confirma: pode regerar, mas é nova versão.
2. **Versionamento:** cada nova rodada = versão nova. Versões antigas ficam consultáveis publicamente.
3. **Sequencial vs paralelo:** paralelo default; sequencial opcional.
4. **Recusa (decisão 4):** cancela toda a rodada. Solicitações pendentes da mesma versão viram `cancelada`; assinaturas já feitas ficam atreladas à versão recusada (visíveis no histórico, mas não válidas para o documento final). Operador edita → nova versão → todos reassinam.
5. **Auto-assinatura proibida:** o user que gera o documento pode ser também assinante, mas precisa ser **adicionado explicitamente**.
6. **Múltiplas prefeituras (futuro):** um user pode ser assinante de N prefeituras. Filtragem por prefeitura atual.
7. **Inatividade:** `assinante` marcado `is_assinante=false` não aparece em novos selects, mas assinaturas antigas permanecem válidas.
8. **Prazo expirado:** solicitação expirada não pode ser assinada — só nova versão ou prorrogação manual.
9. **Cancelamento:** só `solicitado_por` ou `admin` cancela. Estado final.
10. **Assinante removido após assinar:** assinaturas antigas permanecem válidas. PII fica congelada em `assinaturas_digitais.metadados`.

---

## 12. Fluxo inspirado no SEI

| SEI | Sistema novo |
|---|---|
| Bloco de assinatura ao final do PDF | ✅ Idêntico — nome, cargo, matrícula, data/hora, base legal |
| Código verificador 10 dígitos | ✅ "0023776255" (mesmo formato) |
| CRC 8 chars | ✅ Primeiros 8 do SHA-256 |
| URL de validação no rodapé | ✅ Idêntico |
| Página pública de validação | ✅ Lista de assinantes + download |
| Base legal citada no carimbo | ✅ "Lei 14.133/2021, Art. 12, IV" (configurável por prefeitura) |
| Assinatura por certificado digital | ⏳ Fase 3 (decisão 2.3) |
| Bloco de assinatura em PDF separado se documento é muito longo | ✅ Apêndice no final |

---

## 13. Riscos e cuidados

### 13.1 Críticos

| Risco | Mitigação |
|---|---|
| Race condition na consolidação (2 assinantes finalizam quase ao mesmo tempo) | Lock pessimista `SELECT ... FOR UPDATE` na `documento_versoes` |
| Clock skew entre servidores | UTC no banco, exibir no fuso da prefeitura |
| PDF muito grande (>50MB) | Pipeline com FPDI usa muita memória — fallback Ghostscript (já fazemos) |
| Fila de jobs (consolidação de PDF) trava | Horizon ou Supervisor monitorando `queue:work`. MVP não tem e-mail, mas a fila ainda é usada para a consolidação assíncrona |
| Validação pública sob load | Cache Redis para documentos consolidados (imutáveis = cache eternal) |
| Recuperação de senha de assinante | Fluxo padrão Laravel + template específico + aviso "Assinante institucional" |
| Assinatura via mobile | PDF embed em viewport mobile é frágil — botão "Baixar e revisar" |

### 13.2 Edge cases

- User deletado fisicamente deixa FK órfã → `onDelete('restrict')` em `assinaturas_digitais.assinante_user_id`
- Mudança de cargo/matrícula do assinante → snapshot em `assinaturas_digitais.metadados`
- Documento regerado durante rodada ativa → bloqueado por regra #1
- Múltiplas abas → assinatura idempotente via `solicitacao_assinatura_id` único

### 13.3 Compliance

- **LGPD:** política de retenção de logs (5 anos), DPO da prefeitura aprova
- **Marco Civil:** registro de IP por 6 meses mínimo (vai em `assinatura_logs`)
- **ICP-Brasil:** integração via libs (futuro) — fase 3

---

## 14. Roadmap por fases

> Estimativa total: **5-7 semanas** com 1 dev focado.

### Fase 0 — Plano + alinhamento ✅ CONCLUÍDA
- Documento aprovado
- Prefeitura piloto definida (Corrente)

### Fase 1 — Infraestrutura de dados e roles ✅ CONCLUÍDA
- ✅ Migration `users` (numero_portaria, data_portaria, is_assinante)
- ✅ User model (fillable + casts + scope `assinantes`)
- ✅ `AssinanteRoleSeeder` (role + 4 permissions)
- ✅ `AssinantesFromUnidadesSeeder` (geração massiva de users) — **154 assinantes criados em produção (corrente)**
- ✅ Migrations das tabelas:
  - `documento_versoes` (13 colunas + índices)
  - `solicitacoes_assinatura` (14 colunas + índices)
  - `assinaturas_digitais` (14 colunas + cadeia de hash)
  - `assinatura_logs` (9 colunas, append-only)
  - `consultas_publicas` (7 colunas)
- ✅ Models + relations:
  - `App\Models\DocumentoVersao` (morphTo, hasMany solicitações/assinaturas/logs)
  - `App\Models\SolicitacaoAssinatura` (state machine com 5 status, scopes `pendentes`/`ativas`)
  - `App\Models\AssinaturaDigital` (cadeia de hash, CRC humano, verificação de integridade)
  - `App\Models\AssinaturaLog` (8 ações, sem updated_at)
  - `App\Models\ConsultaPublica` (sem timestamps Laravel — usa `consultado_em`)
- ⏳ Factories (Fase 2 — junto com cadastro UI)
- ⏳ Testes unitários (Fase 2)

### Fase 2 — Cadastro de assinantes e UI básica (3-4 dias)

**Entrega A ✅ CONCLUÍDA:**
- ✅ Rotas REST sob `/admin/assinantes` (index, create, store, edit, update, destroy)
- ✅ Rota de importação CSV (`/admin/assinantes/importar-csv`)
- ✅ `AssinanteController` (~250 linhas) — CRUD + CSV parser idempotente
- ✅ `AssinanteRequest` (FormRequest) — validação completa incluindo unique-ignore no update
- ✅ View `Admin/Assinantes/index.blade.php` — listagem com filtros (busca, prefeitura, status), paginação
- ✅ View `Admin/Assinantes/create.blade.php` — formulário com filtro dinâmico de unidade por prefeitura
- ✅ View `Admin/Assinantes/edit.blade.php` — formulário de edição (senha opcional)
- ✅ View `Admin/Assinantes/importar-csv.blade.php` — upload + formato esperado documentado na UI
- ✅ Soft-delete: `destroy()` desliga `is_assinante` + remove role, mas mantém o user (preserva assinaturas antigas)

**Entrega B ✅ CONCLUÍDA:**
- ✅ Factories para as 5 models novas + state `assinante()` em UserFactory
- ✅ 5 testes (24 testes, 70 assertions, **100% passando**) cobrindo:
  - `DocumentoVersaoTest` — factory, states `consolidada`, relação `solicitacoes`, `estaEditavel/isConsolidada`
  - `SolicitacaoAssinaturaTest` — state machine, scopes `pendentes`/`ativas`, `podeSerAssinada`, `estaExpirada`
  - `AssinaturaDigitalTest` — unicidade de `codigo_verificador` e `solicitacao_assinatura_id`, `crc_humano`
  - `AssinaturaLogTest` — constantes, append-only (sem `updated_at`), cast `metadados` array
  - `AssinanteCrudTest` (feature HTTP) — index com filtro, store cria + atribui role, update preserva senha, destroy soft-disable, 404 em user não-assinante
- ✅ Tela "Meu perfil" estendida: bloco "Dados de Assinatura" read-only para assinantes; "Excluir Conta" oculto pra eles (preserva integridade)
- ✅ Login reutiliza Fortify atual — sem mudanças
- ✅ **Bonus** — blindagem de 4 migrations MySQL-only para `RefreshDatabase` rodar em SQLite (testes)

### Fase 3 — Geração de documento com assinantes (5-7 dias)

**Chunk A ✅ CONCLUÍDO:**
- ✅ `DocumentoVersaoService::criarRascunho()` — versionamento + hash + log
- ✅ `DocumentoVersaoService::marcarComoRegerada()` — para recusas (decisão 4)
- ✅ `SolicitacaoService::criarRodada()` — paralelo + sequencial + validação de assinante
- ✅ `SolicitacaoService::cancelarRodada()` — implementa a decisão 4 (recusa cancela rodada toda)
- ✅ `PdfWatermarkService::aplicarMarcaDagua()` — FPDI + texto rotacionado 45° (ex.: "AGUARDANDO ASSINATURAS")
- ✅ 12 novos testes (DocumentoVersaoServiceTest + SolicitacaoServiceTest) — **107 assertions totais passando**

**Chunk B ✅ CONCLUÍDO:**
- ✅ Endpoint `GET /admin/assinantes/disponiveis` (JSON paginado por prefeitura + search)
- ✅ Componente Blade `<x-modal-selecionar-assinantes :prefeitura-id="X" />` — reutilizável, Alpine.js puro, sem dependências externas. Suporta paralelo/sequencial, reordenação com ↑↓, busca debounced, paginação
- ✅ Refactor `ContratoProcessoController::gerarPdf` — opt-in via campo `assinantes` no request: aplica marca d'água → cria `DocumentoVersao` → cria rodada de `SolicitacaoAssinatura`. Backward compatível (sem `assinantes` = comportamento legado)
- ✅ Link "ASSINANTES" adicionado ao aside do `layouts/app.blade.php`
- ✅ 5 testes novos do endpoint (`AssinantesDisponiveisEndpointTest`)
- ✅ **Total agora: 41 testes / 115 assertions / 100% passando**
- ⏳ Validar end-to-end com a prefeitura Corrente antes de expandir aos outros controllers (fica para a Fase 6)

### Fase 4 — Central de assinaturas + execução de assinatura (5-7 dias)

**Chunk A ✅ CONCLUÍDO:**
- ✅ `AssinaturaService::assinar()` com:
  - Lock pessimista em `DocumentoVersao` + `SolicitacaoAssinatura` (anti race condition na cadeia de hash)
  - Validação completa de pré-condições (status, expiração, ownership do user, sequencial respeita ordem)
  - Verificação de integridade do PDF (re-calcula hash e compara)
  - Construção da cadeia: `hash_proprio = sha256(hash_doc + hash_anterior + user_id + timestamp)`
  - Geração de `codigo_verificador` único (10 numéricos + 10 alfanuméricos = 20 chars)
  - Snapshot PII em `metadados` (nome, cargo, matrícula, portaria, prefeitura, unidade)
  - Auto-marcação de `assinaturas_consolidadas_em` na última assinatura (consolidação visual fica para Fase 5)
- ✅ `AssinaturaService::recusar()` com:
  - Validação de motivo obrigatório
  - Cancelamento da rodada inteira via `SolicitacaoService::cancelarRodada()` — implementa decisão 4
  - Logging completo (acao=`recusada` + acao=`cancelada` para cada solicitação afetada)
- ✅ 12 testes novos (`AssinaturaServiceTest`) — total acumulado **53 testes / 143 assertions / 100% passando**

**Chunk B ✅ CONCLUÍDO:**
- ✅ `AssinaturaController` (~165 linhas) — 5 endpoints: index/show/pdf/assinar/recusar
- ✅ Rotas sob `/minhas-assinaturas` com middleware `auth` + `verified`
- ✅ View `Assinaturas/index.blade.php` — resumo numérico + pendentes ordenados por urgência (vencidos → próximos 1d → 3d → demais) + histórico paginado
- ✅ View `Assinaturas/show.blade.php` — PDF embedded em `<embed>` (70vh) + dados da solicitação + ações Assinar/Recusar + modal de recusa com motivo
- ✅ Item "MINHAS ASSINATURAS" no aside com badge amarelo de contagem de pendências (1 query por request, só pra users `is_assinante=true`)
- ✅ 9 testes novos (`AssinaturaControllerTest`) — total acumulado **62 testes / 165 assertions / 100% passando**

**Chunk C ✅ CONCLUÍDO — Fase 4 completa:**
- ✅ 4 notification classes em `App\Notifications\Assinatura\`:
  - `NovaSolicitacaoAssinatura` — disparada para o assinante em modo paralelo (todos) ou sequencial (só o primeiro)
  - `SolicitacaoRecusada` — disparada para o solicitante após recusa
  - `DocumentoTotalmenteAssinado` — disparada para o operador na última assinatura
  - `SolicitacaoExpirando` — disparada 24h antes do prazo via Schedule
- ✅ Integração transparente via `DB::afterCommit()` — notificações só disparam se a transação principal for bem-sucedida
- ✅ Sequencial inteligente: ao assinar, notifica próximo automaticamente
- ✅ `NotificacaoController` — 3 endpoints JSON (index, marcar-lida, marcar-todas-lidas) para o sininho
- ✅ Sininho no header do `layouts/app.blade.php` — Alpine.js puro com polling de 30s, badge amarelo, dropdown com últimas 10
- ✅ `assinaturas:expirar-pendentes` console command com flags `--apenas-expirar` e `--apenas-lembrete` — idempotente via log
- ✅ Schedule diário às 06:00 (`Kernel.php`)
- ✅ 10 testes novos (`NotificacaoTest`) — total: **72 testes / 185 assertions / 100% passando**

### Fase 5 — Consolidação visual + QR + validação pública (5-7 dias)

**Chunk A ✅ CONCLUÍDO:**
- ✅ `AssinaturaConsolidacaoService` (250 linhas) com pipeline FPDI/TCPDF:
  - Copia todas as páginas do PDF rascunho preservando orientação original
  - Adiciona página final com título "ASSINATURAS DIGITAIS" + texto explicativo
  - Renderiza um bloco visual por assinatura (caixa com borda, nome em maiúsculas, portaria/cargo, data/hora, código verificador + CRC)
  - Renderiza rodapé com QR code (TCPDF nativo `write2DBarcode`) + texto de autenticação estilo SEI
  - Calcula `hash_pdf_assinado` e persiste tudo em `DocumentoVersao`
  - Idempotente — se já consolidada, retorna caminho existente
- ✅ Auto-disparo via `AssinaturaService::assinar()` — quando rodada conclui, consolida automaticamente em `DB::afterCommit()`
- ✅ `consolidarSeguro()` com try-catch — falha em consolidação não reverte a assinatura
- ✅ 6 testes novos (`AssinaturaConsolidacaoServiceTest`) — gera PDF real com TCPDF + valida número de páginas. Total acumulado: **78 testes / 198 assertions / 100% passando**

**Chunk B ✅ CONCLUÍDO — Fase 5 completa:**
- ✅ `ValidacaoPublicaService` (110 linhas):
  - `consultar()` — busca por código verificador, normaliza para uppercase, registra `ConsultaPublica` em todas as consultas (sucesso ou falha)
  - `caminhoDownload()` — retorna caminho do PDF assinado se autêntico
  - **Cache integrado** — TTL 1 dia para sucessos, 1 min para falhas (evita stress em códigos inválidos)
- ✅ `ValidacaoPublicaController` (75 linhas) — 4 endpoints: formulario, buscar, consultar, download
- ✅ Rotas sob `/autenticar` com `throttle:10,1` (10 req/min/IP) — **sem auth**
- ✅ Layout público mínimo (`_layout.blade.php`) — Tailwind CDN, sem dependência de build/Vite, sem sidebar/auth
- ✅ View `formulario.blade.php` — input + explicação amigável + box "Como funciona"
- ✅ View `resultado.blade.php` — split em sucesso (verde, lista de assinantes destacando o código consultado) e falha (vermelho, possíveis causas)
- ✅ `AssinaturaConsolidacaoService` agora usa `route('autenticar.formulario')` na URL do QR — fallback robusto pra testes
- ✅ Factory `AssinaturaDigitalFactory` alinhado com produção (gera código uppercase como AssinaturaService)
- ✅ 12 testes novos (`ValidacaoPublicaTest`) — Service (6) + HTTP Controller (6). Total final: **90 testes / 223 assertions / 100% passando**

### Fase 6 — Expansão e migração (1-2 semanas)

**Chunk A ✅ CONCLUÍDO:**
- ✅ `FinalizacaoPdfService::gerarPdf` refatorado para opt-in: aceita `assinantes` no requestData
- ✅ Construtor injeta `PdfWatermarkService`, `DocumentoVersaoService`, `SolicitacaoService` (promoted props)
- ✅ Novo método privado `iniciarRodadaAssinatura()` orquestra: marca d'água → DocumentoVersao polimórfico apontando para `Documento` → rodada
- ✅ Helper `resolverCaminhoAbsoluto()` aceita tanto path absoluto quanto relativo a `public_path()`
- ✅ Try-catch envolvendo a rodada: se assinatura falhar, PDF já salvo continua válido (operador pode disparar manualmente depois)
- ✅ Backward-compatible: sem `assinantes`, comportamento idêntico ao legado
- ✅ 3 testes via Reflection no método privado (`FinalizacaoPdfServiceAssinaturaTest`) — total: **93 testes / 231 assertions / 100% passando**

**Chunk B ✅ CONCLUÍDO:**
- ✅ `ProcessoPdfService` refatorado — mesmo padrão: construtor com 4 deps (1 original + 3 assinatura), `iniciarRodadaAssinatura()` privado, `resolverCaminhoAbsoluto()` helper. Aceita `assinantes` opt-in no requestData
- ✅ `AtaPdfService` refatorado — mesmo padrão, mas usa chave `rodada_assinantes` para evitar conflito com `assinantes` legado (lista de pessoas estampadas no PDF do contrato)
- ✅ Trait reutilizável `CriaProcessoMinimoTrait` extraída — `criarProcessoMinimo()`, `placeholdersParaNotNull()`, `criarPdfFake()` — compartilhada entre os 3 testes (Finalização, Processo, Ata)
- ✅ +4 testes via Reflection (`ProcessoPdfServiceAssinaturaTest`, `AtaPdfServiceAssinaturaTest`) — total: **97 testes / 243 assertions / 100% passando**

**Chunk C ✅ DECIDIDO (sem código) — Fase 6 completa:**

**Decisão final:** documentos antigos permanecem como estão, da forma que eram assinados antes.

> Não haverá migração dos `Documentos` legados (com campo JSON `assinantes`) para o
> novo modelo `DocumentoVersao` + `AssinaturaDigital`. Os documentos antigos continuam
> sendo exibidos/baixados pelo fluxo legado.
>
> **Implicações:**
> - O campo JSON `assinantes` em `documentos`, `contratos`, etc. **permanece** indefinidamente
> - Não há "switch-off" agressivo do código legado — os controllers/services antigos
>   continuam atendendo os documentos que ainda usam o fluxo antigo
> - **Apenas documentos gerados pelo opt-in `assinantes` (request)** usam o pipeline
>   novo (DocumentoVersao + rodada + consolidação + QR + validação pública)
> - **Coexistência de longo prazo** entre os dois sistemas — sem prazo de descontinuação
>
> **Vantagens:**
> - Zero risco de corrupção de dado histórico
> - Sem janela de migração / sem downtime
> - Reversibilidade total: se algo der errado no novo sistema, basta desativar o opt-in
>
> **Trade-off conhecido:**
> - Documentos antigos não terão página pública `/autenticar/{codigo}` — só os novos
> - O front-end precisará detectar o modelo de cada Documento (legado vs novo)
>   ao renderizar a tela de detalhes

### Fase 7 — Polimento (3-5 dias)

- WebSocket via Reverb (notificação real-time)
- Lembretes automáticos (Schedule)
- Relatórios: pendências por assinante, tempo médio
- Acessibilidade da página pública

---

## 15. Estratégia de migração Unidades → Users

### 15.1 Premissa

**Não delete `unidades` nem o campo `servidor_responsavel`.** Vire-os legado read-only.

### 15.2 Script de migração

Já implementado em `AssinantesFromUnidadesSeeder.php`:

```bash
# 1) Schema novo
php artisan migrate

# 2) Role + permissions
php artisan db:seed --class=AssinanteRoleSeeder

# 3) Gera users a partir das Unidades
php artisan db:seed --class=AssinantesFromUnidadesSeeder
```

Características:
- Deduplica por `(prefeitura_id, lower(servidor_responsavel))`
- Idempotente (pode reexecutar)
- Salva CSV de credenciais em `storage/app/seeders/`

### 15.3 Período de coexistência (2-3 semanas)

- Forms refatorados usam selects de Assinantes
- Forms ainda não refatorados continuam usando Unidade
- Dual-write: geração popula tanto `documento.assinantes` JSON quanto cria `solicitacoes_assinatura`

### 15.4 Switch-off

Quando 100% dos controllers estiverem refatorados:
- Migration renomeia `assinantes` → `assinantes_legados` em todas as tabelas
- Code freeze do código antigo
- Após 60 dias sem regressão → migration de drop dos campos legados

---

## 16. Melhorias futuras

### 16.1 Produto

1. **Assinatura em lote** — selecionar N pendências e assinar todas com 1 confirmação
2. **Delegação temporária** — assinante X delega para Y durante férias
3. **Comentários inline** — observação antes de assinar
4. **Diff visual entre versões** — quando documento é recusado e regerado
5. **Selo "documento válido por X dias"** — para atos com prazo curto
6. **API pública JSON** — `GET /api/v1/autenticar/{codigo}.json` para integrações

### 16.2 Técnico

7. **Backup automático imutável** — S3/MinIO com object lock
8. **App Mobile (PWA)** — mesma página otimizada
9. **Login via gov.br** — aumenta validade jurídica
10. **Hash em blockchain pública** — OpenTimestamps (overkill agora)
11. **Painel Compliance** — KPIs para admin
12. **Página de validação i18n** — multi-idiomas

### 16.3 DX

13. **Testes Pest** do fluxo completo (5 testes core)
14. **OpenAPI / Scribe** para documentação HTTP
15. **Feature flag** (`config/features.php`) por prefeitura para rollback instantâneo

---

## 17. Glossário

| Termo | Significado |
|---|---|
| **Assinante** | User com role `assinante` autorizado a assinar documentos |
| **Solicitação** | Pedido formal para um assinante específico assinar um documento (`solicitacoes_assinatura`) |
| **Versão** | Snapshot imutável de um documento (`documento_versoes`). Toda regeneração = nova versão |
| **Consolidação** | Processo de estampar todas assinaturas + QR no PDF final |
| **Rodada** | Conjunto de solicitações da mesma versão |
| **Código verificador** | String alfanumérica de 20 chars única que identifica uma assinatura na página pública |
| **CRC** | Primeiros 8 chars do SHA-256 do PDF assinado — checksum legível por humano |
| **Hash da cadeia** | Hash que conecta cada assinatura à anterior, criando um Merkle chain detectável |

---

## 18. Changelog deste documento

| Data | Versão | Mudança |
|---|---|---|
| 2026-05-30 | v1.0 | Versão inicial. Plano completo aprovado. Decisões 1-3 fixadas. Decisões 2.1, 2.2, 2.3 marcadas como pendentes. Fase 1 parcialmente concluída. |
| 2026-05-30 | v1.1 | Decisões 4 (recusa cancela rodada inteira), 5 (sem e-mail no MVP — apenas sininho) e 6 (assinatura eletrônica simples; ICP-Brasil fica no roadmap) aprovadas. Seção 10 (Notificações) reduzida ao canal `database`. Fluxo 5.2 e Fase 4 do roadmap ajustados. Risco de "fila de e-mail" substituído por risco genérico de fila de jobs (consolidação de PDF). Campo `token_acesso` mantido no schema como reserva futura. |
| 2026-05-30 | v1.2 | **Fase 1 concluída.** 5 migrations executadas em produção (corrente). 5 models criadas com state machines, scopes, cadeia de hash e relations. AssinantesFromUnidadesSeeder gerou 154 users com role `assinante` na prefeitura piloto. Próximo passo: Fase 2 (cadastro UI + factories). |
| 2026-05-30 | v1.3 | **Fase 2 — Entrega A concluída.** CRUD admin de assinantes operacional em `/admin/assinantes` (8 rotas, 1 controller, 1 form request, 4 views). Inclui filtros, paginação, importação CSV idempotente e soft-disable. Entrega B (factories + testes + perfil) pendente. |
| 2026-05-30 | v1.4 | **Fase 2 — Entrega B concluída — Fase 2 completa.** 6 factories (UserFactory ganhou state `assinante()` + 5 novas), 5 arquivos de teste com **24 testes / 70 assertions / 100% passando**, tela "Meu perfil" estendida com bloco "Dados de Assinatura" e ocultando "Excluir Conta" para assinantes. Bônus: 4 migrations MySQL-only foram blindadas com `DB::getDriverName()` para que `RefreshDatabase` funcione em SQLite (testes). |
| 2026-05-30 | v1.5 | **Fase 3 — Chunk A concluído.** 3 services criados em `App\Services\Assinatura\`: `DocumentoVersaoService` (versionamento + hash + log), `SolicitacaoService` (criar/cancelar rodada paralela/sequencial), `PdfWatermarkService` (FPDI + marca d'água diagonal). 2 arquivos de teste novos cobrindo +12 testes (total agora: **36 testes / 107 assertions / 100% passando**). Chunk B (modal + refactor ContratoProcessoController) pendente. |
| 2026-05-30 | v1.6 | **Fase 3 — Chunk B concluído — Fase 3 completa.** Endpoint `GET /admin/assinantes/disponiveis` (JSON paginado), componente Blade `<x-modal-selecionar-assinantes>` reutilizável (Alpine.js puro com paralelo/sequencial/reordenação/busca), refactor opt-in do `ContratoProcessoController::gerarPdf` integrando os 3 services em pipeline (watermark → versão → rodada). Link "ASSINANTES" adicionado ao aside. +5 testes (total: **41 testes / 115 assertions / 100% passando**). Validação end-to-end com prefeitura Corrente fica para Fase 6. |
| 2026-05-30 | v1.7 | **Fase 4 — Chunk A concluído.** `AssinaturaService` criado (363 linhas) com `assinar()` (lock pessimista + integridade + cadeia de hash + snapshot PII + auto-detecção de fim de rodada) e `recusar()` (motivo obrigatório + cancelamento de rodada integrado à decisão 4). Geração de código verificador único 10 numéricos + 10 alfa = 20 chars (formato SEI). +12 testes (`AssinaturaServiceTest`) — total: **53 testes / 143 assertions / 100% passando**. Chunks B (UI) e C (notificações) pendentes. |
| 2026-05-30 | v1.8 | **Fase 4 — Chunk B concluído.** Central de Assinaturas operacional em `/minhas-assinaturas`. AssinaturaController com 5 endpoints (index/show/pdf/assinar/recusar) + view de lista com badges de urgência + view de execução com PDF embedded + modal de recusa. Item "MINHAS ASSINATURAS" no aside com badge de pendências. +9 testes (`AssinaturaControllerTest`) — total: **62 testes / 165 assertions / 100% passando**. Chunk C (notificações + sininho) pendente. |
| 2026-05-30 | v1.9 | **Fase 4 — Chunk C concluído — Fase 4 completa.** 4 notification classes (`NovaSolicitacaoAssinatura`, `SolicitacaoRecusada`, `DocumentoTotalmenteAssinado`, `SolicitacaoExpirando`), integração com SolicitacaoService + AssinaturaService via `DB::afterCommit()`. Sequencial notifica próximo automaticamente. `NotificacaoController` com 3 endpoints JSON e sininho no header (Alpine + polling 30s). Console command `assinaturas:expirar-pendentes` com schedule diário às 06:00. +10 testes (`NotificacaoTest`) — total: **72 testes / 185 assertions / 100% passando**. |
| 2026-05-30 | v2.0 | **Fase 5 — Chunk A concluído.** `AssinaturaConsolidacaoService` (250 linhas) com pipeline FPDI/TCPDF: copia páginas originais preservando orientação, adiciona página final com blocos de assinatura estilo SEI (nome maiúsculo, portaria, data, código verificador, CRC) + QR code nativo TCPDF (sem dependência extra) + rodapé de autenticação. Auto-disparo via `AssinaturaService::assinar()` ao concluir rodada. Idempotente. +6 testes que geram PDF real com TCPDF — total: **78 testes / 198 assertions / 100% passando**. Chunk B (página pública `/autenticar/{codigo}`) pendente. |
| 2026-05-30 | v2.1 | **Fase 5 — Chunk B concluído — Fase 5 completa.** `ValidacaoPublicaService` + Controller + 4 rotas públicas sob `/autenticar` (`throttle:10,1`). Layout público mínimo Tailwind CDN, views formulário + resultado (sucesso/falha) estilo SEI. Cache de leitura (1d sucesso, 1min falha). `AssinaturaDigitalFactory` alinhado com produção (uppercase). +12 testes (`ValidacaoPublicaTest`) — total final: **90 testes / 223 assertions / 100% passando**. |
| 2026-05-30 | v2.2 | **Fase 6 — Chunk A concluído.** `FinalizacaoPdfService` refatorado: construtor injeta 3 services de assinatura (PdfWatermark + DocumentoVersao + Solicitacao), `gerarPdf()` aceita `assinantes` opt-in no requestData e orquestra o pipeline (watermark → versão polimórfica em `Documento` → rodada). Backward-compatible. Try-catch evita reverter PDF se assinatura falhar. +3 testes via Reflection — total: **93 testes / 231 assertions / 100% passando**. Chunks B (ProcessoPdfService + AtaPdfService) e C (backfill legado) pendentes. |
| 2026-05-30 | v2.3 | **Fase 6 — Chunk B concluído.** `ProcessoPdfService` e `AtaPdfService` refatorados no mesmo padrão. Ata usa chave `rodada_assinantes` (evita conflito com `assinantes` legado das pessoas estampadas no PDF). Trait `CriaProcessoMinimoTrait` extraída para reuso entre 3 testes. +4 testes — total: **97 testes / 243 assertions / 100% passando**. Chunk C (backfill) pendente. |
| 2026-05-30 | v2.4 | **Fase 6 — Chunk C decidido sem código — Fase 6 completa.** Decisão de produto: documentos antigos **NÃO serão migrados**. Mantêm-se com o JSON `assinantes` legado e fluxo de assinatura antigo. Apenas novos documentos (gerados com `assinantes` opt-in) usam DocumentoVersao + rodada + consolidação + QR. Coexistência de longo prazo. Vantagem: zero risco em dados históricos. Trade-off: documentos antigos não têm página pública de validação. Total mantido: **97 testes / 243 assertions / 100% passando**. |
| 2026-05-30 | v2.5 | **Integração frontend completada.** Identificado que o backend estava pronto mas o frontend nunca enviava `rodada_assinantes`. Resolvido: (1) padronizada a chave `rodada_assinantes` em TODOS os 4 services (ContratoProcessoController, FinalizacaoPdfService, ProcessoPdfService, AtaPdfService) para não conflitar com `assinantes` legado; (2) componente Blade `<x-modal-selecionar-assinantes>` agora expõe `window.assinaturaConfig` para vanilla JS; (3) novo `<x-assinatura-helper-script>` com `injetarRodadaAssinantesNaUrl()`, `injetarRodadaAssinantesNoBody()`, `notificarRodadaIniciada()`; (4) modal + helper incluídos nas 4 views (`iniciar.blade.php`, `finalizar.blade.php`, `contrato.blade.php`, `Atas/show.blade.php`); (5) cada função JS de geração de PDF agora chama o injetor antes do fetch. Total mantido: **97 testes / 243 assertions / 100% passando**. |
| 2026-05-30 | v2.6 | **Decisão de UX revisada — fluxo unificado pela Seleção de Assinantes existente.** Usuário não quer modal novo — a UI legada "Seleção de Assinantes" (com `Unidade → Responsável → Portaria → Data`) deve continuar como porta única, mas agora também disparar o novo fluxo digital. Implementado: (1) trait `ResolveLegacyAssinantesTrait` em `App\Services\Assinatura\` que detecta o formato e resolve nome → user_id automaticamente; (2) trait aplicada nos 4 services (ContratoProcessoController, FinalizacaoPdfService, ProcessoPdfService, AtaPdfService); (3) modal e helper removidos das 4 views (revertido); (4) JS das 4 views ajustada para mostrar mensagem amigável quando `data.assinatura` retorna; (5) +9 testes (`ResolveLegacyAssinantesTest`) cobrindo: formato novo, formato legado, case-insensitive, user sem flag, sequencial, prioridade rodada_assinantes > assinantes. Total: **106 testes / 258 assertions / 100% passando**. |

