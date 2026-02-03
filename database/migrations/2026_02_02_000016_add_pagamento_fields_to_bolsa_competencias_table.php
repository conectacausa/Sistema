<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bolsa_estudos_solicitacao_competencias')) return;

        Schema::table('bolsa_estudos_solicitacao_competencias', function (Blueprint $table) {
            if (!Schema::hasColumn('bolsa_estudos_solicitacao_competencias', 'pago_at')) {
                $table->timestamp('pago_at')->nullable()->after('aprovacao_ip');
            }
            if (!Schema::hasColumn('bolsa_estudos_solicitacao_competencias', 'pago_por')) {
                $table->unsignedBigInteger('pago_por')->nullable()->after('pago_at');
            }
            if (!Schema::hasColumn('bolsa_estudos_solicitacao_competencias', 'pago_ip')) {
                $table->string('pago_ip', 45)->nullable()->after('pago_por');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bolsa_estudos_solicitacao_competencias')) return;

        Schema::table('bolsa_estudos_solicitacao_competencias', function (Blueprint $table) {
            if (Schema::hasColumn('bolsa_estudos_solicitacao_competencias', 'pago_ip')) $table->dropColumn('pago_ip');
            if (Schema::hasColumn('bolsa_estudos_solicitacao_competencias', 'pago_por')) $table->dropColumn('pago_por');
            if (Schema::hasColumn('bolsa_estudos_solicitacao_competencias', 'pago_at')) $table->dropColumn('pago_at');
        });
    }
};
