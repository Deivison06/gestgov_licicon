<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona campos do papel de "assinante institucional" diretamente no users.
     * Decisão pragmática para a migração inicial — quando o sistema evoluir para
     * múltiplas prefeituras por user, esses dados migrarão para uma tabela
     * `assinantes_dados` (assinante por prefeitura). Por ora, fica em users.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('numero_portaria')->nullable()->after('unidade_id');
            $table->date('data_portaria')->nullable()->after('numero_portaria');
            $table->boolean('is_assinante')->default(false)->after('data_portaria');

            $table->index('is_assinante');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_assinante']);
            $table->dropColumn(['numero_portaria', 'data_portaria', 'is_assinante']);
        });
    }
};
