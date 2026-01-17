<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();

            $table->string('razao_social', 255);
            $table->string('cnpj', 20)->unique();
            $table->string('nome_fantasia', 255)->nullable();

            $table->string('telefone1', 30)->nullable();
            $table->string('telefone2', 30)->nullable();
            $table->string('email', 190)->nullable();

            $table->string('logradouro', 255)->nullable();
            $table->string('numero', 30)->nullable();
            $table->string('complemento', 120)->nullable();
            $table->string('bairro', 120)->nullable();

            $table->foreignId('cidade_id')->nullable()->constrained('cidades');
            $table->foreignId('estado_id')->nullable()->constrained('estados');
            $table->foreignId('pais_id')->nullable()->constrained('paises');

            $table->foreignId('natureza_juridica_id')->nullable()->constrained('naturezas_juridicas');
            $table->foreignId('porte_empresa_id')->nullable()->constrained('portes_empresas');

            $table->enum('situacao_fiscal', ['ativo', 'suspenso', 'inativo'])->default('ativo');
            $table->date('data_abertura')->nullable();

            $table->foreignId('cnae_principal_id')->nullable()->constrained('cnaes');

            // você escreveu “submenu” — aqui faz mais sentido ser subdomínio do cliente
            $table->string('subdominio', 120)->nullable()->unique(); // ex: "empresa1"

            $table->timestamps();
            $table->softDeletes();

            $table->index('razao_social');
            $table->index('nome_fantasia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
