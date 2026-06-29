<?php

/**
 * Configuração da integração com a OpenAI (geração de texto com IA).
 *
 * Para trocar o modelo usado em produção, altere APENAS a variável de ambiente
 * `OPENAI_MODEL` no seu `.env` — nenhuma mudança de código é necessária.
 *
 * O array `catalogo` abaixo é apenas REFERÊNCIA (perfis de uso). Os ids exatos
 * disponíveis e seus preços devem ser confirmados no painel da sua conta OpenAI,
 * pois mudam com o tempo. Qualquer id válido pode ser usado em `OPENAI_MODEL`.
 */
return [

    'api_key'     => env('OPENAI_API_KEY'),
    'base_url'    => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),

    // Modelo efetivamente usado nas chamadas. Trocável por env.
    // Default seguro (gpt-4o-mini) caso a env não esteja definida.
    'model'       => env('OPENAI_MODEL', 'gpt-4o-mini'),

    'timeout'     => (int) env('OPENAI_TIMEOUT', 30),
    'max_tokens'  => (int) env('OPENAI_MAX_TOKENS', 1200),
    'temperature' => (float) env('OPENAI_TEMPERATURE', 0.3),

    /**
     * Catálogo de referência (perfis). Use o `id` desejado em OPENAI_MODEL.
     * Ajuste os ids conforme o que estiver disponível na sua conta.
     */
    'catalogo' => [
        'qualidade_maxima' => [
            'id'        => 'gpt-5.5',
            'titulo'    => 'Qualidade máxima',
            'uso'       => 'Documentos oficiais (controle interno, jurídico, TCE). Melhor raciocínio e redação.',
            'tradeoff'  => 'Mais caro e mais lento.',
        ],
        'equilibrio' => [
            'id'        => 'gpt-5-mini',
            'titulo'    => 'Equilíbrio',
            'uso'       => 'Uso geral do dia a dia. Boa qualidade com custo/velocidade razoáveis.',
            'tradeoff'  => 'Meio-termo entre custo e qualidade.',
        ],
        'economico' => [
            'id'        => 'gpt-4o-mini',
            'titulo'    => 'Econômico / rápido',
            'uso'       => 'Volume alto e rascunhos. Custo mínimo.',
            'tradeoff'  => 'Menor profundidade de raciocínio.',
        ],
    ],
];
