<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bolsa_estudos_processos')) return;

        Schema::table('bolsa_estudos_processos', function (Blueprint $table) {
            if (!Schema::hasColumn('bolsa_estudos_processos', 'lembrete_recibo_ativo')) {
                $table->boolean('lembrete_recibo_ativo')->default(false)->after('data_base');
            }
            if (!Schema::hasColumn('bolsa_estudos_processos', 'lembrete_recibo_dias_antes')) {
                $table->smallInteger('lembrete_recibo_dias_antes')->nullable()->after('lembrete_recibo_ativo');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bolsa_estudos_processos')) return;

        Schema::table('bolsa_estudos_processos', function (Blueprint $table) {
            if (Schema::hasColumn('bolsa_estudos_processos', 'lembrete_recibo_dias_antes')) {
                $table->dropColumn('lembrete_recibo_dias_antes');
            }
            if (Schema::hasColumn('bolsa_estudos_processos', 'lembrete_recibo_ativo')) {
                $table->dropColumn('lembrete_recibo_ativo');
            }
        });
    }
};
