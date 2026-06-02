<?php

namespace App\Services\IA;

use App\Models\Processo;

/**
 * Serviço responsável por:
 * - validar campos permitidos para geração via IA;
 * - montar prompts especializados;
 * - contextualizar o processo administrativo;
 * - orientar a IA para respostas técnicas e não genéricas.
 */
class IaContextoService
{
    /**
     * Campos autorizados para geração via IA.
     */
    public const CAMPOS_PERMITIDOS = [

        // =========================
        // DFD
        // =========================

        'justificativa' => [
            'titulo' => 'Justificativa da Necessidade da Contratação (DFD)',

            'orientacao' => 'Apresente a motivação para realização da contratação, demonstrando o interesse público,
                a finalidade administrativa atendida e o respaldo na Lei nº 14.133/2021.
                Vincule a contratação à competência institucional do órgão.',

            'estrutura' => 'necessidade → impacto administrativo → justificativa da contratação',

            'pontos_criticos' => [
                'demonstrar interesse público',
                'evidenciar necessidade administrativa concreta',
                'não utilizar justificativas genéricas',
            ],
        ],

        'descricao_necessidade_autorizacao' => [
            'titulo' => 'Descrição da Necessidade para Autorização (DFD)',

            'orientacao' => 'Descreva objetivamente a necessidade administrativa que justifica a abertura do processo,
                demonstrando que o atendimento da demanda é indispensável ao funcionamento regular do órgão.',

            'estrutura' => 'contextualização → necessidade → impacto da ausência da contratação',

            'pontos_criticos' => [
                'demonstrar indispensabilidade',
                'evidenciar continuidade do serviço público',
            ],
        ],

        // =========================
        // ETP
        // =========================

        'problema_resolvido' => [
            'titulo' => 'Problema a ser Resolvido (ETP)',

            'orientacao' => 'Descreva em texto resumido o problema identificado pela Administração que motiva a contratação.
                Aponte consequências da não contratação. Evite listas; use parágrafos corridos.',

            'estrutura' => 'problema → impacto → consequência administrativa',

            'pontos_criticos' => [
                'explicar problema real',
                'demonstrar prejuízo operacional',
                'não antecipar solução',
            ],
        ],

        'descricao_necessidade' => [
            'titulo' => 'Descrição da Necessidade (ETP)',

            'orientacao' => 'Detalhe a necessidade pública a ser atendida e o público-alvo direta e indiretamente impactado.
                Indique de forma genérica volumes esperados se o usuário não fornecer dados concretos.',

            'estrutura' => 'necessidade → público impactado → resultado esperado',

            'pontos_criticos' => [
                'contextualizar necessidade pública',
                'demonstrar impacto institucional',
            ],
        ],

        'solucoes_disponivel_mercado' => [
            'titulo' => 'Soluções Disponíveis no Mercado (ETP)',

            'orientacao' => 'Discorra sobre as alternativas de mercado para atender à necessidade,
                descrevendo padrões usuais de fornecimento, modelos contratuais
                e vantagens/desvantagens comparativas. Não cite marcas específicas.',

            'estrutura' => 'alternativas → comparação → vantagens/desvantagens',

            'pontos_criticos' => [
                'não citar marcas',
                'comparar soluções',
                'demonstrar análise de mercado',
            ],
        ],

        'incluir_requisito_cada_caso_concreto' => [
            'titulo' => 'Requisitos da Contratação para o Caso Concreto (ETP)',

            'orientacao' => 'Enumere os requisitos técnicos, operacionais, de qualidade e prazo
                necessários para atendimento da demanda administrativa.',

            'estrutura' => 'requisitos técnicos → operacionais → qualidade → prazo',

            'pontos_criticos' => [
                'ser objetivo',
                'não inventar especificações',
                'relacionar requisito com necessidade administrativa',
            ],
        ],

        'justificativa_solucao_escolhida' => [
            'titulo' => 'Justificativa da Solução Escolhida (ETP)',

            'orientacao' => 'Explique tecnicamente por que a solução adotada é a mais adequada entre as alternativas,
                considerando economicidade, eficiência, qualidade e aderência à necessidade.',

            'estrutura' => 'alternativas → análise → escolha → benefício',

            'pontos_criticos' => [
                'demonstrar vantagem técnica',
                'demonstrar economicidade',
                'não usar justificativas superficiais',
            ],
        ],

        'resultado_pretendidos' => [
            'titulo' => 'Resultados Pretendidos com a Contratação (ETP)',

            'orientacao' => 'Descreva os benefícios esperados pela Administração
                em termos de eficiência, qualidade, economicidade e continuidade dos serviços.',

            'estrutura' => 'benefícios → impacto → melhoria operacional',

            'pontos_criticos' => [
                'demonstrar resultado administrativo',
                'não inventar métricas',
            ],
        ],

        'impacto_ambiental' => [
            'titulo' => 'Impactos Ambientais e Medidas Mitigadoras (ETP)',

            'orientacao' => 'Avalie os possíveis impactos ambientais decorrentes do objeto contratado
                e as medidas mitigadoras aplicáveis, considerando critérios de sustentabilidade
                previstos na Lei nº 14.133/2021.',

            'estrutura' => 'impactos → riscos → mitigação → sustentabilidade',

            'pontos_criticos' => [
                'considerar sustentabilidade',
                'considerar descarte adequado',
                'considerar logística reversa',
            ],
        ],

        'riscos_extra' => [
            'titulo' => 'Riscos Extras da Contratação (ETP)',

            'orientacao' => 'Aponte riscos adicionais não cobertos pelos itens padrão do Mapa de Riscos,
                considerando aspectos operacionais, contratuais, técnicos, orçamentários e de
                fiscalização específicos do objeto. Para cada risco, sugira a medida mitigadora
                correspondente. Mantenha a análise objetiva e fundamentada no caso concreto.',

            'estrutura' => 'identificação do risco → probabilidade/impacto → medida mitigadora',

            'pontos_criticos' => [
                'identificar riscos concretos do objeto, não riscos genéricos',
                'sempre vincular risco a uma medida mitigadora',
                'evitar duplicar riscos já tratados no Mapa de Riscos padrão',
                'não inventar dados quantitativos (probabilidade em %, valores etc.)',
            ],
        ],
    ];

    /**
     * @return list<string>
     */
    public function camposPermitidos(): array
    {
        return array_keys(self::CAMPOS_PERMITIDOS);
    }

    public function ehCampoPermitido(string $campo): bool
    {
        return array_key_exists($campo, self::CAMPOS_PERMITIDOS);
    }

    /**
     * @return array{system:string,user:string}
     */
    public function montarPrompt(
        string $campo,
        string $instrucaoUsuario,
        ?Processo $processo = null
    ): array {
        $config = self::CAMPOS_PERMITIDOS[$campo] ?? null;

        if (!$config) {
            throw new \InvalidArgumentException(
                "Campo de IA não permitido: {$campo}"
            );
        }

        return [
            'system' => $this->montarSystemPrompt($config),
            'user'   => $this->montarUserPrompt($instrucaoUsuario, $processo),
        ];
    }

    private function montarSystemPrompt(array $config): string
    {
        $titulo = $config['titulo'];
        $orientacao = $config['orientacao'];
        $estrutura = $config['estrutura'] ?? '';
        $pontosCriticos = implode(', ', $config['pontos_criticos'] ?? []);

        return <<<PROMPT
Você é um especialista em:
- contratações públicas;
- elaboração de DFD e ETP;
- planejamento administrativo;
- redação técnica oficial;
- Lei nº 14.133/2021.

Seu objetivo é produzir textos técnicos, maduros, contextualizados e compatíveis com análise de:
- controle interno;
- assessoria jurídica;
- tribunal de contas;
- setor de planejamento.

==================================================
ANÁLISE INTERNA OBRIGATÓRIA (NÃO EXIBIR)
==================================================

Antes de escrever, realize internamente as seguintes etapas:

1. COMPREENSÃO DO CASO
- Qual o problema administrativo?
- Qual necessidade pública está sendo atendida?
- Qual o impacto da ausência da contratação?
- O objeto indica aquisição, serviço, solução tecnológica, obra ou fornecimento contínuo?

2. ANÁLISE ADMINISTRATIVA
- O texto demonstra necessidade concreta?
- Existe coerência entre objeto e justificativa?
- A linguagem está compatível com documentos oficiais?

3. FUNDAMENTAÇÃO LEGAL
- Verifique pertinência da Lei nº 14.133/2021.
- Cite a lei apenas quando fizer sentido técnico.
- Nunca invente decretos, normas internas ou regulamentos.

4. TRATAMENTO DE LACUNAS
Se o usuário não informar:
- valores;
- quantidades;
- prazos;
- métricas;
- especificações técnicas;

utilize linguagem genérica e segura:
- "conforme estimativa da Administração";
- "nos prazos definidos em edital";
- "conforme necessidade administrativa".

Nunca invente dados concretos.

5. QUALIDADE DA REDAÇÃO
A resposta:
- NÃO pode parecer genérica;
- NÃO pode conter frases vazias;
- NÃO pode repetir ideias;
- DEVE possuir densidade técnica;
- DEVE contextualizar administrativamente;
- DEVE demonstrar consequência prática.

==================================================
EXEMPLOS DO QUE NÃO FAZER
==================================================

RUIM:
"A contratação é importante para atender as necessidades da Administração."

RUIM:
"Considerando a importância do objeto, verifica-se a necessidade da contratação."

RUIM:
"O serviço será executado conforme a legislação vigente."

Esses exemplos são superficiais e sem densidade técnica.

==================================================
ESTRUTURA MENTAL ESPERADA
==================================================

{$estrutura}

==================================================
PONTOS CRÍTICOS
==================================================

{$pontosCriticos}

==================================================
REGRAS OBRIGATÓRIAS
==================================================

1. Retorne APENAS HTML puro.

2. Utilize SOMENTE:
- <p>
- <strong>
- <em>
- <ul>
- <ol>
- <li>
- <br>

3. NÃO utilize:
- markdown;
- títulos;
- tabelas;
- divs;
- spans;
- estilos;
- links;
- emojis.

4. Linguagem:
- formal;
- técnica;
- impessoal;
- administrativa.

5. Não escreva:
- "segue abaixo";
- "conforme solicitado";
- "a seguir";
- "este documento visa".

6. Cada parágrafo deve conter informação técnica relevante.

7. O texto deve parecer elaborado para um caso concreto real.

8. Tamanho esperado:
entre 120 e 350 palavras.

==================================================
CAMPO SOLICITADO
==================================================

{$titulo}

==================================================
ORIENTAÇÃO ESPECÍFICA
==================================================

{$orientacao}
PROMPT;
    }

    private function montarUserPrompt(
        string $instrucaoUsuario,
        ?Processo $processo
    ): string {
        $instrucaoUsuario = trim($instrucaoUsuario);

        $contexto = $this->descreverProcesso($processo);

        return <<<PROMPT
INSTRUÇÃO DO USUÁRIO:

{$instrucaoUsuario}

==================================================
CONTEXTO DO PROCESSO
==================================================

{$contexto}

==================================================
ORIENTAÇÃO FINAL
==================================================

Produza uma redação:
- técnica;
- aprofundada;
- contextualizada;
- não genérica;
- aderente à Lei nº 14.133/2021;
- compatível com documentos oficiais de contratação pública.

Escreva agora.
PROMPT;
    }

    private function descreverProcesso(?Processo $processo): string
    {
        if (!$processo) {
            return '(Sem contexto de processo informado.)';
        }

        $linhas = [];

        if ($processo->objeto) {
            $objeto = html_entity_decode(
                strip_tags((string) $processo->objeto),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );

            $linhas[] = '- Objeto: ' . mb_substr($objeto, 0, 800);
        }

        if ($processo->modalidade) {
            $modalidade = is_object($processo->modalidade)
                ? ($processo->modalidade->getDisplayName()
                    ?? $processo->modalidade->name)
                : $processo->modalidade;

            $linhas[] = '- Modalidade: ' . $modalidade;
        }

        if ($processo->tipo_contratacao) {
            $tipo = is_object($processo->tipo_contratacao)
                ? ($processo->tipo_contratacao->getDisplayName()
                    ?? $processo->tipo_contratacao->name)
                : $processo->tipo_contratacao;

            $linhas[] = '- Tipo de contratação: ' . $tipo;
        }

        if ($processo->tipo_procedimento) {
            $procedimento = is_object($processo->tipo_procedimento)
                ? ($processo->tipo_procedimento->getDisplayName()
                    ?? $processo->tipo_procedimento->name)
                : $processo->tipo_procedimento;

            $linhas[] = '- Tipo de procedimento: ' . $procedimento;
        }

        if ($processo->numero_processo) {
            $linhas[] = '- Número do processo: ' . $processo->numero_processo;
        }

        if (!empty($processo->secretaria?->nome)) {
            $linhas[] = '- Secretaria demandante: ' . $processo->secretaria->nome;
        }

        if (!empty($processo->finalidade_contratacao)) {
            $linhas[] = '- Finalidade da contratação: '
                . strip_tags($processo->finalidade_contratacao);
        }

        if (!empty($processo->impacto_nao_contratacao)) {
            $linhas[] = '- Impacto da não contratação: '
                . strip_tags($processo->impacto_nao_contratacao);
        }

        if (!empty($processo->justificativa)) {
            $linhas[] = '- Justificativa existente: '
                . mb_substr(strip_tags($processo->justificativa), 0, 500);
        }

        if (!empty($processo->observacoes)) {
            $linhas[] = '- Observações relevantes: '
                . mb_substr(strip_tags($processo->observacoes), 0, 500);
        }

        if (empty($linhas)) {
            return '(Processo informado, porém sem dados relevantes.)';
        }

        return implode("\n", $linhas);
    }
}