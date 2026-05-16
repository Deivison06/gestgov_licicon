# Documentação da Integração PNCP

Esta documentação detalha o funcionamento das consultas e a apresentação dos dados da API do Portal Nacional de Contratações Públicas (PNCP) implementada no sistema.

## 1. Arquitetura da Integração

A integração foi construída seguindo o padrão de Service Layer no Laravel, separando a lógica de comunicação externa da lógica de apresentação.

### Componentes Principais:
- **Service**: `app/Services/PncpService.php` - Responsável pelas requisições HTTP e normalização dos dados.
- **Controller**: `app/Http/Controllers/Api/PncpController.php` - Expõe os endpoints para o frontend.
- **Frontend**: `resources/views/Admin/Etps/create.blade.php` - Interface de usuário (Modal de consulta).

---

## 2. Fluxo de Consulta

O processo de consulta é dividido em duas etapas: busca de contratações e detalhamento de itens.

### A. Busca de Contratações (Editais)
Quando o usuário digita no campo de busca do modal (mínimo 3 caracteres), uma requisição é disparada após um *debounce* de 600ms.

- **Endpoint Local**: `/admin/etps/pncp/search?termo={termo}`
- **API PNCP Consultada**: `https://pncp.gov.br/api/search/`
- **Parâmetros Enviados**:
  - `q`: Termo de busca (ex: "Ar condicionado")
  - `tipos_documento`: `edital` (fixo)
  - `status`: `recebendo_proposta` (fixo para garantir editais ativos)
  - `pagina`: Número da página para paginação.

### B. Consulta de Itens da Contratação
Ao selecionar uma contratação na lista de resultados, o sistema busca os itens específicos daquela compra.

- **Endpoint Local**: `/admin/etps/pncp/items/{cnpj}/{ano}/{sequencial}`
- **API PNCP Consultada**: `https://pncp.gov.br/api/pncp/v1/orgaos/{cnpj}/compras/{ano}/{sequencial}/itens`
- **Lógica de Valor**: O sistema busca o valor de referência priorizando `valorUnitarioEstimado` e usando `valorUnitarioHomologado` como fallback.

---

## 3. Apresentação e Normalização dos Dados

Para garantir que a interface seja consistente e resiliente a mudanças na API do governo, os dados passam por uma camada de normalização.

### Dados da Contratação (Cards de Resultado):
| Campo PNCP | Campo Normalizado | Descrição |
| :--- | :--- | :--- |
| `orgao_cnpj` | `orgaoEntidade.cnpj` | CNPJ do órgão contratante |
| `orgao_nome` | `orgaoEntidade.razaoSocial` | Nome/Razão Social do órgão |
| `ano` | `anoCompra` | Ano da contratação |
| `numero_sequencial` | `sequencialCompra` | Número sequencial no PNCP |
| `modalidade_licitacao_nome` | `modalidadeNome` | Ex: Pregão Eletrônico, Dispensa |
| `description` | `objeto` | Descrição resumida do objeto |
| `data_publicacao_pncp` | `dataPublicacaoPncp` | Data de publicação no portal |

### Dados dos Itens (Tabela de Detalhes):
- **Número do Item**: Ordem cronológica do item na licitação.
- **Descrição**: Descrição detalhada do material ou serviço.
- **Quantidade e Unidade**: Ex: 10 Unidades, 50 Metros.
- **Valor Unitário**: Valor de referência formatado em BRL.
- **Valor Total**: Cálculo dinâmico de `Quantidade * Valor Unitário`.

---

## 4. Recursos Técnicos e Performance

- **Cache**: As consultas são cacheadas por 1 hora (`PNCP_CACHE_TTL`) para evitar requisições redundantes e melhorar a velocidade.
- **Segurança**: As buscas são registradas em log com o ID do usuário autenticado.
- **Resiliência**: Implementado mecanismo de `retry` (2 tentativas) e tratamento específico para erro `502` (comum na API do PNCP).
- **Interface**: Modal moderno com Glassmorphism, estados de *loading* animados e design responsivo.

---

*Gerado automaticamente para documentação técnica do projeto GestGov.*
