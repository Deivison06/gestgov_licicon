<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('processos', function (Blueprint $table) {
            $table->string('planejamento_status', 50)->default('em_elaboracao')->after('status');
            $table->dateTime('planejamento_data_abertura')->nullable()->after('planejamento_status');
            $table->dateTime('planejamento_fim_recurso')->nullable()->after('planejamento_data_abertura');
        });
    }

    public function down(): void {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropColumn(['planejamento_status', 'planejamento_data_abertura', 'planejamento_fim_recurso']);
        });
    }
};
