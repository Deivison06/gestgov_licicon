<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prefeituras', function (Blueprint $table) {
            $table->string('cor', 7)->nullable()->after('cidade');
        });

        $cores = [
            'Alvorada do Gurguéia - PI' => '#14532d',
            'Bertolínia - PI'           => '#713f12',
            'Corrente - PI'             => '#431407',
            'Cristino Castro - PI'      => '#7f1d1d',
            'Curimatá - PI'             => '#4c1d95',
            'Currais - PI'              => '#1e3a8a',
            'Eliseu Martins - PI'       => '#134e4a',
            'Palmeira do Piauí - PI'    => '#365314',
            'Redenção do Gurguéia - PI' => '#831843',
            'Santa Luz - PI'            => '#111827',
        ];

        foreach ($cores as $cidade => $cor) {
            DB::table('prefeituras')->where('cidade', $cidade)->update(['cor' => $cor]);
        }
    }

    public function down(): void
    {
        Schema::table('prefeituras', function (Blueprint $table) {
            $table->dropColumn('cor');
        });
    }
};
