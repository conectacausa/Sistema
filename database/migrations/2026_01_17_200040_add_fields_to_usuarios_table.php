<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // dados principais
            $table->string('nome_completo', 255)->nullable()->after('id');
            $table->string('cpf', 11)->nullable()->after('nome_completo');

            // relacionamentos
            $table->unsignedBigInteger('empresa_id')->nullable()->after('cpf');
            $table->unsignedBigInteger('permissao_id')->nullable()->after('empresa_id');
            $table->unsignedBigInteger('colaborador_id')->nullable()->after('permissao_id');

            // contato / acesso
            $table->string('email', 190)->nullable()->after('colaborador_id');
            $table->string('telefone', 30)->nullable()->after('email');
            $table->string('senha', 255)->nullable()->after('telefone');

            // status e controles
            $table->string('status', 20)->default('ativo')->after('senha'); // ativo/inativo
            $table->date('data_expiracao')->nullable()->after('status');
            $table->boolean('salarios')->default(false)->after('data_expiracao');
            $table->boolean('operador_whatsapp')->default(false)->after('salarios');

            // foto
            $table->string('foto', 255)->nullable()->after('operador_whatsapp');

            // soft delete
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('usuarios', function (Blueprint $table) {
            // uniques/indices
            $table->unique('cpf');
            $table->index('empresa_id');
            $table->index('permissao_id');
            $table->index('colaborador_id');

            // FKs
            $table->foreign('empresa_id')->references('id')->on('empresas')->nullOnDelete();
            $table->foreign('permissao_id')->references('id')->on('permissoes')->nullOnDelete();
            $table->foreign('colaborador_id')->references('id')->on('colaboradores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // drop FKs
            $table->dropForeign(['empresa_id']);
            $table->dropForeign(['permissao_id']);
            $table->dropForeign(['colaborador_id']);

            // drop uniques/indices
            $table->dropUnique(['cpf']);
            $table->dropIndex(['empresa_id']);
            $table->dropIndex(['permissao_id']);
            $table->dropIndex(['colaborador_id']);

            // drop columns
            $table->dropColumn([
                'nome_completo', 'cpf',
                'empresa_id', 'permissao_id', 'colaborador_id',
                'email', 'telefone', 'senha',
                'status', 'data_expiracao', 'salarios', 'operador_whatsapp',
                'foto',
                'deleted_at'
            ]);
        });
    }
};
