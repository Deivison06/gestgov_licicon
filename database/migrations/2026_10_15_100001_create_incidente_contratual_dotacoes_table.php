<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidente_contratual_dotacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incidente_contratual_id')
                ->constrained('incidentes_contratuais')
                ->cascadeOnDelete();
            $table->string('unidade_orcamentaria');
            $table->string('classificacao_funcional_programatica');
            $table->string('elemento_despesa');
            $table->string('fonte_recurso');
            $table->decimal('valor', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidente_contratual_dotacoes');
    }
};
