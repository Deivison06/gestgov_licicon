<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            // Decisões de abertura do aditivo
            $table->string('tipo_objeto_contrato')->nullable()->after('categoria');
            $table->boolean('contrato_continuo')->nullable()->after('tipo_objeto_contrato');

            // Natureza explícita da alteração de valor — substitui a inferência
            // por sinal de percentual_valor (coluna legada, mantida somente leitura).
            $table->string('natureza_alteracao')->nullable()->after('percentual_valor');
            $table->decimal('percentual_acrescimo', 10, 2)->nullable()->after('natureza_alteracao');
            $table->decimal('percentual_supressao', 10, 2)->nullable()->after('percentual_acrescimo');

            // Compras e Serviços — Prazo e Valor: valor estimado para o período prorrogado
            $table->boolean('tem_valor_periodo_prorrogado')->default(false)->after('meses_prorrogacao');
            $table->decimal('valor_periodo_prorrogado', 15, 2)->nullable()->after('tem_valor_periodo_prorrogado');

            // Obras — Solicitação / limite legal aplicável
            $table->decimal('percentual_execucao_fisica', 5, 2)->nullable();
            $table->string('limite_legal_aditivo_obras')->nullable();

            // Novo ato: Aceite e Documentação de Regularidade da Contratada
            $table->string('arquivo_aceite_documentacao_path')->nullable();

            // Novo ato: Parecer Técnico (só Obras)
            $table->string('nome_engenheiro_parecer_tecnico')->nullable();
            $table->string('cargo_engenheiro_parecer_tecnico')->nullable();
            $table->string('registro_profissional_parecer_tecnico')->nullable();
            $table->string('situacao_execucao_contratual')->nullable();
            $table->json('fatores_atraso')->nullable();
            $table->text('descricao_fatores_atraso')->nullable();
            $table->string('responsabilidade_atraso')->nullable();
            $table->text('texto_vantajosidade_tecnica')->nullable();
            $table->text('conclusao_tecnica')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('incidentes_contratuais', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_objeto_contrato',
                'contrato_continuo',
                'natureza_alteracao',
                'percentual_acrescimo',
                'percentual_supressao',
                'tem_valor_periodo_prorrogado',
                'valor_periodo_prorrogado',
                'percentual_execucao_fisica',
                'limite_legal_aditivo_obras',
                'arquivo_aceite_documentacao_path',
                'nome_engenheiro_parecer_tecnico',
                'cargo_engenheiro_parecer_tecnico',
                'registro_profissional_parecer_tecnico',
                'situacao_execucao_contratual',
                'fatores_atraso',
                'descricao_fatores_atraso',
                'responsabilidade_atraso',
                'texto_vantajosidade_tecnica',
                'conclusao_tecnica',
            ]);
        });
    }
};
