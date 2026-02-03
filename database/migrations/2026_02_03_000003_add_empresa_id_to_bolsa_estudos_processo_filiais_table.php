<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bolsa_estudos_processo_filiais')) {
            return;
        }

        Schema::table('bolsa_estudos_processo_filiais', function (Blueprint $table) {
            if (!Schema::hasColumn('bolsa_estudos_processo_filiais', 'empresa_id')) {
                // adiciona nullable primeiro para não quebrar dados existentes
                $table->unsignedBigInteger('empresa_id')->nullable()->after('id');
            }
        });

        // Preenche empresa_id a partir do processo (se tiver dados antigos)
        if (Schema::hasTable('bolsa_estudos_processos') &&
            Schema::hasColumn('bolsa_estudos_processos', 'empresa_id') &&
            Schema::hasColumn('bolsa_estudos_processo_filiais', 'processo_id')) {

            DB::statement("
                UPDATE bolsa_estudos_processo_filiais pf
                SET empresa_id = p.empresa_id
                FROM bolsa_estudos_processos p
                WHERE p.id = pf.processo_id
                  AND pf.empresa_id IS NULL
            ");
        }

        // Torna NOT NULL depois de preencher
        Schema::table('bolsa_estudos_processo_filiais', function (Blueprint $table) {
            if (Schema::hasColumn('bolsa_estudos_processo_filiais', 'empresa_id')) {
                DB::statement("ALTER TABLE bolsa_estudos_processo_filiais ALTER COLUMN empresa_id SET NOT NULL");
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bolsa_estudos_processo_filiais')) {
            return;
        }

        Schema::table('bolsa_estudos_processo_filiais', function (Blueprint $table) {
            if (Schema::hasColumn('bolsa_estudos_processo_filiais', 'empresa_id')) {
                $table->dropColumn('empresa_id');
            }
        });
    }
};
