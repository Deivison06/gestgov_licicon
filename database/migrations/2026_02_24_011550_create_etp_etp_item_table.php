<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etp_etp_item', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('etp_id');
            $table->unsignedBigInteger('etp_item_id');

            $table->timestamps();

            $table->foreign('etp_id')->references('id')->on('etps')->onDelete('cascade');
            $table->foreign('etp_item_id')->references('id')->on('etp_itens')->onDelete('cascade');

            $table->unique(['etp_id', 'etp_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etp_etp_item');
    }
};
