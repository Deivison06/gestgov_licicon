<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesquisa_preco_memoria_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('etp_item_id')->nullable()->constrained('etp_itens')->nullOnDelete();
            $table->unsignedInteger('ordem');
            $table->string('titulo')->nullable();
            $table->decimal('valor_adotado', 14, 4);
            $table->json('fontes');
            $table->timestamps();

            $table->unique(['processo_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesquisa_preco_memoria_itens');
    }
};
