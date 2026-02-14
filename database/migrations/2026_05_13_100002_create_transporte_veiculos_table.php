<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transporte_veiculos', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->string('tipo', 30)->default('onibus'); // onibus|van|carro...
            $table->string('placa', 20)->nullable();
            $table->string('renavam', 30)->nullable();
            $table->string('chassi', 40)->nullable();

            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->integer('ano')->nullable();

            $table->integer('capacidade_passageiros')->nullable();
            $table->integer('inspecao_cada_meses')->default(6);

            $table->string('status', 20)->default('ativo'); // ativo|inativo|manutencao
            $table->text('observacoes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'status']);
            $table->index(['empresa_id', 'placa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_veiculos');
    }
};
