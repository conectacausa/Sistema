<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bolsa_estudos_solicitacoes')) {
            return;
        }

        Schema::table('bolsa_estudos_solicitacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('bolsa_estudos_solicitacoes', 'filial_id')) {
                // nullable para não quebrar linhas antigas; seu form já envia.
                $table->unsignedBigInteger('filial_id')->nullable()->index()->after('colaborador_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bolsa_estudos_solicitacoes')) {
            return;
        }

        Schema::table('bolsa_estudos_solicitacoes', function (Blueprint $table) {
            if (Schema::hasColumn('bolsa_estudos_solicitacoes', 'filial_id')) {
                $table->dropColumn('filial_id');
            }
        });
    }
};
