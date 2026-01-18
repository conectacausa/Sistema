<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vinculo_grupos_telas_telas', function (Blueprint $table) {

            if (!Schema::hasColumn('vinculo_grupos_telas_telas', 'grupo_tela_id')) {
                $table->unsignedBigInteger('grupo_tela_id')->after('id');
            }

            if (!Schema::hasColumn('vinculo_grupos_telas_telas', 'tela_id')) {
                $table->unsignedBigInteger('tela_id')->after('grupo_tela_id');
            }

            if (!Schema::hasColumn('vinculo_grupos_telas_telas', 'ativo')) {
                $table->boolean('ativo')->default(true)->after('tela_id');
            }

            if (!Schema::hasColumn('vinculo_grupos_telas_telas', 'ordem')) {
                $table->unsignedInteger('ordem')->default(0)->after('ativo');
            }

            // Evita duplicidade do vínculo
            $table->unique(['grupo_tela_id', 'tela_id'], 'uniq_grupo_tela_tela');

            // Foreign keys (somente se as tabelas existirem)
            if (Schema::hasTable('grupos_telas')) {
                $table->foreign('grupo_tela_id')
                    ->references('id')
                    ->on('grupos_telas')
                    ->onDelete('cascade');
            }

            if (Schema::hasTable('telas')) {
                $table->foreign('tela_id')
                    ->references('id')
                    ->on('telas')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vinculo_grupos_telas_telas', function (Blueprint $table) {

            // remove FKs com segurança
            try { $table->dropForeign(['grupo_tela_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['tela_id']); } catch (\Throwable $e) {}

            try { $table->dropUnique('uniq_grupo_tela_tela'); } catch (\Throwable $e) {}

            $cols = ['ordem', 'ativo', 'tela_id', 'grupo_tela_id'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('vinculo_grupos_telas_telas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
