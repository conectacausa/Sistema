<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vinculo_modulos_empresas', function (Blueprint $table) {

            if (!Schema::hasColumn('vinculo_modulos_empresas', 'empresa_id')) {
                $table->unsignedBigInteger('empresa_id')->after('id');
            }

            if (!Schema::hasColumn('vinculo_modulos_empresas', 'modulo_id')) {
                $table->unsignedBigInteger('modulo_id')->after('empresa_id');
            }

            if (!Schema::hasColumn('vinculo_modulos_empresas', 'ativo')) {
                $table->boolean('ativo')->default(true)->after('modulo_id');
            }

            if (!Schema::hasColumn('vinculo_modulos_empresas', 'ordem')) {
                $table->unsignedInteger('ordem')->default(0)->after('ativo');
            }

            // Índice único (empresa + módulo)
            $table->unique(['empresa_id', 'modulo_id'], 'uniq_empresa_modulo');

            // FKs (só adiciona se as tabelas existirem)
            if (Schema::hasTable('empresas')) {
                $table->foreign('empresa_id')
                    ->references('id')
                    ->on('empresas')
                    ->onDelete('cascade');
            }

            if (Schema::hasTable('modulos')) {
                $table->foreign('modulo_id')
                    ->references('id')
                    ->on('modulos')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vinculo_modulos_empresas', function (Blueprint $table) {

            // Remove FKs com segurança
            try { $table->dropForeign(['empresa_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['modulo_id']); } catch (\Throwable $e) {}

            try { $table->dropUnique('uniq_empresa_modulo'); } catch (\Throwable $e) {}

            $cols = ['ordem', 'ativo', 'modulo_id', 'empresa_id'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('vinculo_modulos_empresas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
