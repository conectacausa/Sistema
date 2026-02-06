<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bolsa_estudos_solicitacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('bolsa_estudos_solicitacoes', 'tipo')) {
                $table->smallInteger('tipo')->default(1)->after('percentual_concessao');
                // 1 = Nova concessão | 2 = Renovação
            }
        });
    }

    public function down(): void
    {
        Schema::table('bolsa_estudos_solicitacoes', function (Blueprint $table) {
            if (Schema::hasColumn('bolsa_estudos_solicitacoes', 'tipo')) {
                $table->dropColumn('tipo');
            }
        });
    }
};
