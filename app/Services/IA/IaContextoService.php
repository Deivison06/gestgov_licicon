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

            'orientacao' => 'O texto DEVE apresentar NO MÍNIMO TRÊS (3) soluções disponíveis no mercado que atendam ao objeto da contratação (utilize o problema e necessidade do caso concreto). Para CADA UMA das soluções apresente: 1) Nome ou descrição da solução; 2) Breve explicação de como funciona; 3) Principais vantagens; 4) Principais desvantagens. Ao final, apresente uma comparação entre as alternativas facilitando a identificação da solução mais adequada. Não cite marcas específicas, foque nos modelos e tipologias de serviço/produto.',

            'estrutura' => 'introdução → solução 1 (nome, como funciona, vantagens, desvantagens) → solução 2 (...) → solução 3 (...) → comparação final das alternativas',

            'pontos_criticos' => [
                'apresentar no mínimo três soluções de mercado',
                'detalhar funcionamento, vantagens e desvantagens de cada uma',
                'fazer comparação final facilitando a decisão',
                'não citar marcas específicas'
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

            'orientacao' => 'A justificativa NÃO pode ser genérica. Ela DEVE se basear ESPECIFICAMENTE no conteúdo fornecido em "Solução Escolhida pelo Usuário" que estará no contexto principal. Justifique especificamente a solução selecionada pelo usuário, comparando-a com as demais alternativas (se houver no mercado) e demonstrando, de forma técnica, por que ela é a mais adequada, considerando aspectos como economicidade, eficiência, qualidade, viabilidade e aderência à necessidade da contratação.',

            'estrutura' => 'contextualização da solução escolhida → comparação com as demais alternativas → justificativa técnica e econômica (eficiência/viabilidade) → conclusão da escolha',

            'pontos_criticos' => [
                'utilizar obrigatoriamente a "Solução Escolhida pelo Usuário" como base para a justificativa',
                'demonstrar vantagem técnica e econômica da solução escolhida',
                'não usar justificativas superficiais, genéricas ou vazias',
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

        if ($processo?->detalhe) {
            if ($campo === 'justificativa_solucao_escolhida' && !empty($processo->detalhe->solucao_escolhida)) {
                $solucao = strip_tags((string) $processo->detalhe->solucao_escolhida);
                if (trim($solucao) !== '') {
                    $contexto .= "\n\nSolução Escolhida pelo Usuário (BASE OBRIGATÓRIA PARA A JUSTIFICATIVA):\n" . $solucao;
                }
            }

            if ($campo === 'solucoes_disponivel_mercado') {
                if (!empty($processo->detalhe->problema_resolvido)) {
                    $contexto .= "\n\nProblema a ser resolvido pelo órgão:\n" . strip_tags((string) $processo->detalhe->problema_resolvido);
                }
                if (!empty($processo->detalhe->descricao_necessidade)) {
                    $contexto .= "\n\nDescrição da necessidade do órgão:\n" . strip_tags((string) $processo->detalhe->descricao_necessidade);
                }
            }
        }

        // No fluxo automático não há instrução do usuário. Quando houver, ela é
        // tratada apenas como complemento — o contexto principal é o nome resumido.
        $blocoInstrucao = $instrucaoUsuario !== ''
            ? "INSTRUÇÃO ADICIONAL DO USUÁRIO (complemento — priorize em caso de conflito):\n\n{$instrucaoUsuario}\n\n"
            : '';

        return <<<PROMPT
{$blocoInstrucao}==================================================
CONTEXTO PRINCIPAL (base da geração)
==================================================

{$contexto}

==================================================
COMO PROCEDER
==================================================

1. Analise CUIDADOSAMENTE o contexto acima antes de escrever.
2. Interprete a Identificação de Controle / Nome Resumido para compreender o objeto e a finalidade da contratação.
3. Produza o texto do campo solicitado de forma técnica, objetiva, completa e coerente.
4. Evite respostas genéricas ou superficiais, repetições e informações desnecessárias.
5. Use linguagem clara e natural, adequada a um documento OFICIAL de planejamento.
6. Não invente dados concretos (valores, prazos, quantidades, métricas) ausentes no contexto — use linguagem segura ("conforme estimativa da Administração", "nos prazos definidos em edital").

Escreva agora APENAS o conteúdo final do campo, em HTML conforme as regras.
PROMPT;
    }

    /**
     * Contexto da geração. Por decisão de produto, usa EXCLUSIVAMENTE o campo
     * "Identificação de Controle / Nome Resumido" (utilizado no quadro de
     * planejamento) como base — não o objeto completo do processo.
     */
    private function descreverProcesso(?Processo $processo): string
    {
        if (!$processo) {
            return '(Sem contexto de processo informado.)';
        }

        $nomeResumido = html_entity_decode(
            strip_tags((string) ($processo->nome_resumido ?? '')),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
        $nomeResumido = trim($nomeResumido);

        if ($nomeResumido === '') {
            return '(Identificação de Controle / Nome Resumido não informada para este processo. '
                . 'Gere um texto técnico e prudente para o campo solicitado, sem inventar dados concretos.)';
        }

        return 'Identificação de Controle / Nome Resumido (quadro de planejamento): '
            . mb_substr($nomeResumido, 0, 500);
    }
}