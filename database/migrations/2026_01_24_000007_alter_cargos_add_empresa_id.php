<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            if (!Schema::hasColumn('cargos', 'empresa_id')) {
                // nullable para não quebrar registros existentes
                $table->unsignedBigInteger('empresa_id')->nullable()->index()->after('id');
            }
        });

        // FK separado (evita problemas em alguns ambientes)
        Schema::table('cargos', function (Blueprint $table) {
            // cria FK somente se não existir (Laravel não tem hasForeign, então mantemos simples)
            $table->foreign('empresa_id')
                ->references('id')
                ->on('empresas')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            // remove FK e coluna
            try { $table->dropForeign(['empresa_id']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('cargos', 'empresa_id')) {
                $table->dropColumn('empresa_id');
            }
        });
    }
};
