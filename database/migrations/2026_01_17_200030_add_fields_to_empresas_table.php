<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // dados principais
            $table->string('razao_social', 255)->nullable()->after('id');
            $table->string('cnpj', 20)->nullable()->after('razao_social');
            $table->string('nome_fantasia', 255)->nullable()->after('cnpj');

            // contatos
            $table->string('telefone1', 30)->nullable()->after('nome_fantasia');
            $table->string('telefone2', 30)->nullable()->after('telefone1');
            $table->string('email', 190)->nullable()->after('telefone2');

            // endereço
            $table->string('logradouro', 255)->nullable()->after('email');
            $table->string('numero', 30)->nullable()->after('logradouro');
            $table->string('complemento', 120)->nullable()->after('numero');
            $table->string('bairro', 120)->nullable()->after('complemento');

            // relações
            $table->unsignedBigInteger('cidade_id')->nullable()->after('bairro');
            $table->unsignedBigInteger('estado_id')->nullable()->after('cidade_id');
            $table->unsignedBigInteger('pais_id')->nullable()->after('estado_id');

            $table->unsignedBigInteger('natureza_juridica_id')->nullable()->after('pais_id');
            $table->unsignedBigInteger('porte_empresa_id')->nullable()->after('natureza_juridica_id');

            // situação / datas
            $table->string('situacao_fiscal', 20)->default('ativo')->after('porte_empresa_id'); // ativo/suspenso/inativo
            $table->date('data_abertura')->nullable()->after('situacao_fiscal');

            $table->unsignedBigInteger('cnae_principal_id')->nullable()->after('data_abertura');

            // subdominio (o “submenu” do seu texto)
            $table->string('subdominio', 120)->nullable()->after('cnae_principal_id');

            // soft delete
            $table->softDeletes()->after('updated_at');
        });

        // índices/uniques e FKs (em bloco separado)
        Schema::table('empresas', function (Blueprint $table) {
            $table->unique('cnpj');
            $table->unique('subdominio');

            $table->index('razao_social');
            $table->index('nome_fantasia');

            $table->foreign('cidade_id')->references('id')->on('cidades')->nullOnDelete();
            $table->foreign('estado_id')->references('id')->on('estados')->nullOnDelete();
            $table->foreign('pais_id')->references('id')->on('paises')->nullOnDelete();

            $table->foreign('natureza_juridica_id')->references('id')->on('naturezas_juridicas')->nullOnDelete();
            $table->foreign('porte_empresa_id')->references('id')->on('portes_empresas')->nullOnDelete();

            $table->foreign('cnae_principal_id')->references('id')->on('cnaes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // derrubar FKs
            $table->dropForeign(['cidade_id']);
            $table->dropForeign(['estado_id']);
            $table->dropForeign(['pais_id']);
            $table->dropForeign(['natureza_juridica_id']);
            $table->dropForeign(['porte_empresa_id']);
            $table->dropForeign(['cnae_principal_id']);

            // derrubar índices/uniques
            $table->dropUnique(['cnpj']);
            $table->dropUnique(['subdominio']);

            // remover colunas
            $table->dropColumn([
                'razao_social','cnpj','nome_fantasia',
                'telefone1','telefone2','email',
                'logradouro','numero','complemento','bairro',
                'cidade_id','estado_id','pais_id',
                'natureza_juridica_id','porte_empresa_id',
                'situacao_fiscal','data_abertura',
                'cnae_principal_id','subdominio',
                'deleted_at'
            ]);
        });
    }
};
