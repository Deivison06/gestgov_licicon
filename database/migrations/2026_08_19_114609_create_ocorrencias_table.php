<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocorrencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prefeitura_id');
            $table->morphs('fiscalizavel'); // fiscalizavel_id + fiscalizavel_type (Contrato | ContratoManual)

            $table->string('numero_ocorrencia');
            $table->date('data_ocorrencia');
            $table->string('local')->nullable();

            $table->text('descricao_fato');
            $table->text('obrigacao_descumprida')->nullable();
            $table->string('prazo_resposta')->nullable(); // texto livre ("10 dias úteis", data, etc.)

            $table->json('tipo_comprovacao')->nullable(); // {fotografias, videos, relatorio, email, mensagem, outros: bool}
            $table->string('tipo_comprovacao_outro')->nullable(); // texto quando "outros" marcado

            $table->string('situacao')->nullable(); // SituacaoOcorrenciaEnum: regularizada, nao_regularizada, encaminhada_gestor
            $table->string('status')->default('rascunho'); // StatusOcorrenciaEnum: rascunho, registrada, concluida

            $table->json('assinantes')->nullable(); // mesmo formato/uso de Fiscalizacao->assinantes

            // Resposta da empresa (comprovação da correção em andamento)
            $table->date('resposta_registrada_em')->nullable();

            // Atesto de Correção
            $table->text('correcao_descricao')->nullable();
            $table->date('correcao_data')->nullable();
            $table->text('correcao_elementos_comprobatorios')->nullable();

            // Referência da notificação gerada (usada no texto do Atesto)
            $table->string('notificacao_numero')->nullable();
            $table->date('notificacao_expedida_em')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index('prefeitura_id');
            $table->foreign('prefeitura_id')->references('id')->on('prefeituras')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocorrencias');
    }
};
