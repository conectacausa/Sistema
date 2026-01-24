<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vinculo_cargo_lotacao', function (Blueprint $table) {
            if (!Schema::hasColumn('vinculo_cargo_lotacao', 'setor_id')) {
                $table->unsignedBigInteger('setor_id')->nullable()->index()->after('filial_id');
            }
        });

        Schema::table('vinculo_cargo_lotacao', function (Blueprint $table) {
            // FK
            $table->foreign('setor_id')
                ->references('id')->on('setores')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });

        // Ajusta UNIQUE antigo -> novo (com setor)
        // Nome do unique antigo: uniq_vinc_cargo_lotacao
        // OBS: no PostgreSQL, dropUnique funciona com o nome do índice/constraint.
        Schema::table('vinculo_cargo_lotacao', function (Blueprint $table) {
            try { $table->dropUnique('uniq_vinc_cargo_lotacao'); } catch (\Throwable $e) {}
        });

        Schema::table('vinculo_cargo_lotacao', function (Blueprint $table) {
            $table->unique(['empresa_id', 'cargo_id', 'filial_id', 'setor_id'], 'uniq_vinc_cargo_lotacao2');
        });
    }

    public function down(): void
    {
        Schema::table('vinculo_cargo_lotacao', function (Blueprint $table) {
            try { $table->dropUnique('uniq_vinc_cargo_lotacao2'); } catch (\Throwable $e) {}
            try { $table->dropForeign(['setor_id']); } catch (\Throwable $e) {}

            if (Schema::hasColumn('vinculo_cargo_lotacao', 'setor_id')) {
                $table->dropColumn('setor_id');
            }
        });

        // volta unique antigo (sem setor)
        Schema::table('vinculo_cargo_lotacao', function (Blueprint $table) {
            $table->unique(['empresa_id', 'cargo_id', 'filial_id'], 'uniq_vinc_cargo_lotacao');
        });
    }
};
