<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('etp_lote_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etp_lote_id')->constrained()->onDelete('cascade');
            $table->foreignId('etp_item_id')->constrained('etp_itens')->onDelete('cascade');
            $table->string('unidade');
            $table->integer('quantidade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etp_lote_item');
    }
};
