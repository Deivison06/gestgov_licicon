<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->foreignId('homologacao_id')
                ->nullable()
                ->after('processo_id')
                ->constrained('homologacoes')
                ->nullOnDelete();
            $table->index('homologacao_id');
        });
    }

    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['homologacao_id']);
            $table->dropIndex(['homologacao_id']);
            $table->dropColumn('homologacao_id');
        });
    }
};
