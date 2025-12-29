<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToDocumentosTable extends Migration
{
    public function up()
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->decimal('valor_total', 15, 2)->nullable()->after('gerado_em');
            $table->integer('quantidade_itens')->nullable()->after('valor_total');
            $table->json('campos')->nullable()->after('quantidade_itens');
            $table->json('assinantes')->nullable()->after('campos');
            $table->json('contratacoes_selecionadas')->nullable()->after('assinantes');
        });
    }

    public function down()
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropColumn(['valor_total', 'quantidade_itens', 'campos', 'assinantes', 'contratacoes_selecionadas']);
        });
    }
}