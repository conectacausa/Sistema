<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('filiais', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('razao_social');
            $table->string('cnpj', 14);
            $table->string('nome_fantasia')->nullable();

            $table->string('telefone1')->nullable();
            $table->string('telefone2')->nullable();
            $table->string('email')->nullable();

            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();

            $table->foreignId('cidade_id')
                ->nullable()
                ->constrained('cidades')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('estado_id')
                ->nullable()
                ->constrained('estados')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('pais_id')
                ->nullable()
                ->constrained('paises')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('natureza_juridica_id')
                ->nullable()
                ->constrained('naturezas_juridicas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('porte_empresa_id')
                ->nullable()
                ->constrained('portes_empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('situacao_fiscal')->nullable();
            $table->date('data_abertura')->nullable();

            $table->foreignId('cnae_principal_id')
                ->nullable()
                ->constrained('cnaes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->softDeletes();
            $table->timestamps();

            // Índices úteis
            $table->index(['empresa_id']);
            $table->index(['cidade_id']);
            $table->index(['cnpj']);

            // Regras de unicidade (ajuste conforme regra do negócio)
            $table->unique(['empresa_id', 'cnpj']); // evita CNPJ duplicado dentro da mesma empresa (tenant)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filiais');
    }
};
