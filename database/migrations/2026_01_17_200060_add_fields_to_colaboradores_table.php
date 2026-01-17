<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            // sexo já existe no banco, então NÃO mexemos nele.

            if (!Schema::hasColumn('colaboradores', 'cpf')) {
                $table->string('cpf', 11)->nullable()->after('id');
            }

            if (!Schema::hasColumn('colaboradores', 'nome')) {
                $table->string('nome', 255)->nullable()->after('cpf');
            }

            if (!Schema::hasColumn('colaboradores', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // índices
        Schema::table('colaboradores', function (Blueprint $table) {
            // criar unique com nome fixo pra facilitar
            // (se já existir por algum motivo, a migration ainda assim passaria, pois cpf não existia antes)
            if (Schema::hasColumn('colaboradores', 'cpf')) {
                $table->unique('cpf', 'colaboradores_cpf_unique');
            }

            if (Schema::hasColumn('colaboradores', 'nome')) {
                $table->index('nome', 'colaboradores_nome_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            // remover índices
            try { $table->dropUnique('colaboradores_cpf_unique'); } catch (\Throwable $e) {}
            try { $table->dropIndex('colaboradores_nome_index'); } catch (\Throwable $e) {}

            // remover colunas que essa migration adiciona
            if (Schema::hasColumn('colaboradores', 'cpf')) {
                $table->dropColumn('cpf');
            }
            if (Schema::hasColumn('colaboradores', 'nome')) {
                $table->dropColumn('nome');
            }
            if (Schema::hasColumn('colaboradores', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }

            // NÃO remover sexo no down, pois já existia antes
        });
    }
};
