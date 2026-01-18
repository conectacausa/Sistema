<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissao_modulo_tela', function (Blueprint $table) {

            if (!Schema::hasColumn('permissao_modulo_tela', 'permissao_id')) {
                $table->unsignedBigInteger('permissao_id')->after('id');
            }

            if (!Schema::hasColumn('permissao_modulo_tela', 'modulo_id')) {
                $table->unsignedBigInteger('modulo_id')->nullable()->after('permissao_id');
            }

            if (!Schema::hasColumn('permissao_modulo_tela', 'tela_id')) {
                $table->unsignedBigInteger('tela_id')->after('modulo_id');
            }

            if (!Schema::hasColumn('permissao_modulo_tela', 'ativo')) {
                $table->boolean('ativo')->default(true)->after('tela_id');
            }

            // Índice único (evita duplicidade do vínculo)
            // Preferi (permissao_id, tela_id) porque modulo_id pode ser derivado da tela
            $table->unique(['permissao_id', 'tela_id'], 'uniq_permissao_tela');

            // Foreign keys (somente se as tabelas existirem)
            if (Schema::hasTable('permissoes')) {
                $table->foreign('permissao_id')
                    ->references('id')
                    ->on('permissoes')
                    ->onDelete('cascade');
            }

            if (Schema::hasTable('modulos')) {
                $table->foreign('modulo_id')
                    ->references('id')
                    ->on('modulos')
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
        Schema::table('permissao_modulo_tela', function (Blueprint $table) {

            // remove FKs com segurança
            try { $table->dropForeign(['permissao_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['modulo_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['tela_id']); } catch (\Throwable $e) {}

            try { $table->dropUnique('uniq_permissao_tela'); } catch (\Throwable $e) {}

            $cols = ['ativo', 'tela_id', 'modulo_id', 'permissao_id'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('permissao_modulo_tela', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
