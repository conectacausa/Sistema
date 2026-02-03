<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Garante que a tabela existe antes
        if (!Schema::hasTable('bolsa_estudos_processo_filiais')) {
            return;
        }

        Schema::table('bolsa_estudos_processo_filiais', function (Blueprint $table) {
            // Só adiciona se ainda não existir
            if (!Schema::hasColumn('bolsa_estudos_processo_filiais', 'deleted_at')) {
                $table->softDeletes(); // cria deleted_at nullable timestamp
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bolsa_estudos_processo_filiais')) {
            return;
        }

        Schema::table('bolsa_estudos_processo_filiais', function (Blueprint $table) {
            if (Schema::hasColumn('bolsa_estudos_processo_filiais', 'deleted_at')) {
                $table->dropSoftDeletes(); // remove deleted_at
            }
        });
    }
};
