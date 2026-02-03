<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bolsa_estudos_processos')) {
            return;
        }

        // edital -> TEXT (suporta HTML grande)
        if (Schema::hasColumn('bolsa_estudos_processos', 'edital')) {
            DB::statement("ALTER TABLE bolsa_estudos_processos ALTER COLUMN edital TYPE TEXT");
        }

        // ciclo -> varchar(160) (alinha com validação do form)
        if (Schema::hasColumn('bolsa_estudos_processos', 'ciclo')) {
            DB::statement("ALTER TABLE bolsa_estudos_processos ALTER COLUMN ciclo TYPE VARCHAR(160)");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('bolsa_estudos_processos')) {
            return;
        }

        // Se quiser voltar:
        // ATENÇÃO: voltar edital para varchar(120) pode truncar dados existentes.
        if (Schema::hasColumn('bolsa_estudos_processos', 'edital')) {
            DB::statement("ALTER TABLE bolsa_estudos_processos ALTER COLUMN edital TYPE VARCHAR(120)");
        }

        if (Schema::hasColumn('bolsa_estudos_processos', 'ciclo')) {
            DB::statement("ALTER TABLE bolsa_estudos_processos ALTER COLUMN ciclo TYPE VARCHAR(120)");
        }
    }
};
