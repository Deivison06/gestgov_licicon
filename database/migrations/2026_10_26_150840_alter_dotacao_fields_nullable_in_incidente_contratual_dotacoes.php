<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Antes, a tela de "Informação de Disponibilidade Orçamentária" do aditivo
 * exigia os 4 campos de dotação preenchidos juntos (validação required em
 * IncidenteContratualController::atualizarDotacoes) — usuário que preenchia
 * só 1 ou 2 campos não conseguia salvar nenhuma linha. Agora cada campo é
 * opcional; alterado via SQL puro (sem doctrine/dbal, que não está instalado).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE incidente_contratual_dotacoes MODIFY unidade_orcamentaria VARCHAR(255) NULL');
        DB::statement('ALTER TABLE incidente_contratual_dotacoes MODIFY classificacao_funcional_programatica VARCHAR(255) NULL');
        DB::statement('ALTER TABLE incidente_contratual_dotacoes MODIFY elemento_despesa VARCHAR(255) NULL');
        DB::statement('ALTER TABLE incidente_contratual_dotacoes MODIFY fonte_recurso VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE incidente_contratual_dotacoes MODIFY unidade_orcamentaria VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE incidente_contratual_dotacoes MODIFY classificacao_funcional_programatica VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE incidente_contratual_dotacoes MODIFY elemento_despesa VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE incidente_contratual_dotacoes MODIFY fonte_recurso VARCHAR(255) NOT NULL');
    }
};
