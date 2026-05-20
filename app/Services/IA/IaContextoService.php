<?php

namespace App\Services\IA;

use App\Models\Processo;

/**
 * Mapeia cada campo (DFD/ETP) à sua orientação de IA específica e monta
 * os prompts (system + user) que vão para a OpenAI.
 *
 * Adicionar um novo campo = adicionar uma entrada em CAMPOS_PERMITIDOS.
 */
class IaContextoService
{
    /**
     * Whitelist autoritativa. Só estes campos podem ser gerados via IA.
     */
    public const CAMPOS_PERMITIDOS = [

        // ---- DFD (Documento de Formalização da Demanda) ----
        'justificativa' => [
            'titulo'  => 'Justificativa da Necessidade da Contratação (DFD)',
            'orientacao' => 'Apresente a motivação para realização da contratação, demonstrando o interesse público, '
                . 'a finalidade administrativa atendida e o respaldo na Lei nº 14.133/2021. '
                . 'Vincule a contratação à competência institucional do órgão.',
        ],
        'descricao_necessidade_autorizacao' => [
            'titulo' => 'Descrição da Necessidade para Autorização (DFD)',
            'orientacao' => 'Descreva objetivamente a necessidade administrativa que justifica a abertura do processo, '
                . 'demonstrando que o atendimento da demanda é indispensável ao funcionamento regular do órgão.',
        ],

        // ---- ETP (Estudo Técnico Preliminar) ----
        'problema_resolvido' => [
            'titulo' => 'Problema a ser Resolvido (ETP)',
            'orientacao' => 'Descreva em texto resumido o problema identificado pela Administração que motiva a contratação. '
                . 'Aponte consequências da não contratação. Evite listas; use parágrafos corridos.',
        ],
        'descricao_necessidade' => [
            'titulo' => 'Descrição da Necessidade (ETP)',
            'orientacao' => 'Detalhe a necessidade pública a ser atendida e o público-alvo direta e indiretamente impactado. '
                . 'Indique de forma genérica volumes esperados se o usuário não fornecer dados concretos.',
        ],
        'solucoes_disponivel_mercado' => [
            'titulo' => 'Soluções Disponíveis no Mercado (ETP)',
            'orientacao' => 'Discorra sobre as alternativas de mercado para atender à necessidade, '
                . 'descrevendo padrões usuais de fornecimento, modelos contratuais (entrega única, contínua, registro de preços) '
                . 'e vantagens/desvantagens comparativas. Não cite marcas específicas.',
        ],
        'incluir_requisito_cada_caso_concreto' => [
            'titulo' => 'Requisitos da Contratação para o Caso Concreto (ETP)',
            'orientacao' => 'Enumere em texto corrido (i, ii, iii) os requisitos técnicos, de qualidade e de prazo '
                . 'que o objeto deve atender. Vincule cada requisito à necessidade administrativa correspondente.',
        ],
        'justificativa_solucao_escolhida' => [
            'titulo' => 'Justificativa da Solução Escolhida (ETP)',
            'orientacao' => 'Explique tecnicamente por que a solução adotada é a mais adequada entre as alternativas, '
                . 'considerando economicidade, eficiência, qualidade e aderência à necessidade.',
        ],
        'resultado_pretendidos' => [
            'titulo' => 'Resultados Pretendidos com a Contratação (ETP)',
            'orientacao' => 'Descreva os benefícios esperados pela Administração (eficiência, economia, qualidade dos serviços públicos). '
                . 'Sempre que possível indique benefícios em termos qualitativos; só cite números se o usuário forneceu.',
        ],
        'impacto_ambiental' => [
            'titulo' => 'Impactos Ambientais e Medidas Mitigadoras (ETP)',
            'orientacao' => 'Avalie os possíveis impactos ambientais decorrentes do objeto contratado e as medidas mitigadoras '
                . 'aplicáveis. Considere logística reversa, descarte adequado, eficiência energética e critérios de sustentabilidade '
                . 'previstos no art. 11, IV, e no art. 144 da Lei 14.133/2021.',
        ],
    ];

    /**
     * Retorna apenas os nomes dos campos permitidos (para FormRequest/whitelist).
     *
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
     * Monta os prompts (system + user) para enviar à OpenAI.
     *
     * @return array{system: string, user: string}
     */
    public function montarPrompt(string $campo, string $instrucaoUsuario, ?Processo $processo = null): array
    {
        $config = self::CAMPOS_PERMITIDOS[$campo] ?? null;
        if (!$config) {
            throw new \InvalidArgumentException("Campo de IA não permitido: {$campo}");
        }

        $system = $this->montarSystemPrompt($config['titulo'], $config['orientacao']);
        $user   = $this->montarUserPrompt($instrucaoUsuario, $processo);

        return ['system' => $system, 'user' => $user];
    }

    private function montarSystemPrompt(string $titulo, string $orientacao): string
    {
        return <<<PROMPT
            Você é um assistente especializado em redação de documentos de contratações
            públicas (Lei nº 14.133/2021) para prefeituras brasileiras.

            REGRAS OBRIGATÓRIAS:
            1. Retorne APENAS o texto final, sem introdução ("Aqui está...", "Segue o texto:")
               nem despedida. Não envolva a resposta em ```html, ```, <html>, <body> ou
               qualquer wrapper — devolva direto o conteúdo formatado.
            2. FORMATE A RESPOSTA EM HTML usando APENAS estas tags:
                 <p>            — parágrafos (use sempre, um por bloco de ideia)
                 <strong>       — negrito (use com moderação, em termos técnicos-chave,
                                  números importantes ou referências legais)
                 <em>           — itálico (use só quando necessário para ênfase)
                 <ul> + <li>    — listas não ordenadas (bullets) quando enumerar itens
                 <ol> + <li>    — listas ordenadas (numeradas) quando a ordem importar
                 <br>           — quebra de linha dentro de um parágrafo (raro)
               PROIBIDO: Markdown (**negrito**, ## títulos, "- item"), <h1>-<h6>, <div>,
               <span>, atributos class/style/id, links <a>, imagens, tabelas, scripts.
            3. Tom: formal, técnico, impessoal (3ª pessoa: "verifica-se", "constata-se",
               "a Administração"). Nunca use "você", "a gente", "nós".
            4. Mantenha-se estritamente no escopo do campo solicitado.
               Não extrapole para outros campos do documento.
            5. Quando aplicável, faça referência à <strong>Lei nº 14.133/2021</strong>
               em negrito. Nunca invente números de decretos, leis municipais ou portarias
               específicas.
            6. Se o usuário não informou valores, quantidades ou prazos, escreva de forma
               genérica ("conforme estimativa em anexo", "no prazo estabelecido em edital").
               Nunca invente dados concretos.
            7. Tamanho: entre 80 e 350 palavras, salvo instrução explícita do usuário em
               contrário.

            EXEMPLO DE FORMATAÇÃO ESPERADA:
            <p>A presente contratação encontra respaldo na <strong>Lei nº 14.133/2021</strong>,
            tendo em vista a necessidade de...</p>
            <p>Verifica-se que os seguintes requisitos devem ser observados:</p>
            <ul>
              <li>Atendimento integral às especificações técnicas;</li>
              <li>Cumprimento dos prazos previstos em edital;</li>
              <li>Garantia mínima de <strong>12 meses</strong>.</li>
            </ul>
            <p>Diante do exposto, conclui-se pela viabilidade da contratação.</p>

            CAMPO SOLICITADO: {$titulo}
            ORIENTAÇÃO ESPECÍFICA: {$orientacao}
            PROMPT;
    }

    private function montarUserPrompt(string $instrucaoUsuario, ?Processo $processo): string
    {
        $instrucao = trim($instrucaoUsuario);
        $contexto  = $this->descreverProcesso($processo);

        return <<<PROMPT
            INSTRUÇÃO DO USUÁRIO:
            {$instrucao}

            CONTEXTO DO PROCESSO:
            {$contexto}

            Redija o texto agora.
            PROMPT;
    }

    private function descreverProcesso(?Processo $processo): string
    {
        if (!$processo) {
            return '(Sem contexto de processo — gere texto genérico.)';
        }

        $linhas = [];

        if ($processo->objeto) {
            $objeto = html_entity_decode(strip_tags((string) $processo->objeto), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $linhas[] = '- Objeto: ' . mb_substr($objeto, 0, 400);
        }
        if ($processo->modalidade) {
            $modalidade = is_object($processo->modalidade)
                ? ($processo->modalidade->getDisplayName() ?? $processo->modalidade->name)
                : $processo->modalidade;
            $linhas[] = '- Modalidade: ' . $modalidade;
        }
        if ($processo->tipo_contratacao) {
            $tipo = is_object($processo->tipo_contratacao)
                ? ($processo->tipo_contratacao->getDisplayName() ?? $processo->tipo_contratacao->name)
                : $processo->tipo_contratacao;
            $linhas[] = '- Tipo de contratação: ' . $tipo;
        }
        if ($processo->tipo_procedimento) {
            $proc = is_object($processo->tipo_procedimento)
                ? ($processo->tipo_procedimento->getDisplayName() ?? $processo->tipo_procedimento->name)
                : $processo->tipo_procedimento;
            $linhas[] = '- Tipo de procedimento: ' . $proc;
        }
        if ($processo->numero_processo) {
            $linhas[] = '- Número do processo: ' . $processo->numero_processo;
        }

        if (empty($linhas)) {
            return '(Processo informado, mas sem dados úteis ainda.)';
        }

        return implode("\n", $linhas);
    }
}
