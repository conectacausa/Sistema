<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bolsa_estudos_solicitacoes')) return;

        Schema::table('bolsa_estudos_solicitacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('bolsa_estudos_solicitacoes', 'filial_id')) {
                $table->unsignedBigInteger('filial_id')->nullable()->after('colaborador_id');
            }
            if (!Schema::hasColumn('bolsa_estudos_solicitacoes', 'percentual_concessao')) {
                $table->decimal('percentual_concessao', 5, 2)->nullable()->after('valor_total_mensalidade');
            }
            if (!Schema::hasColumn('bolsa_estudos_solicitacoes', 'justificativa_reprovacao')) {
                $table->text('justificativa_reprovacao')->nullable()->after('aprovacao_ip');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bolsa_estudos_solicitacoes')) return;

        Schema::table('bolsa_estudos_solicitacoes', function (Blueprint $table) {
            if (Schema::hasColumn('bolsa_estudos_solicitacoes', 'justificativa_reprovacao')) {
                $table->dropColumn('justificativa_reprovacao');
            }
            if (Schema::hasColumn('bolsa_estudos_solicitacoes', 'percentual_concessao')) {
                $table->dropColumn('percentual_concessao');
            }
            if (Schema::hasColumn('bolsa_estudos_solicitacoes', 'filial_id')) {
                $table->dropColumn('filial_id');
            }
        });
    }
};
