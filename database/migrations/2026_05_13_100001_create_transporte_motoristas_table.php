<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transporte_motoristas', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->string('nome', 191);
            $table->string('cpf', 20)->nullable();
            $table->string('cnh_numero', 50)->nullable();
            $table->string('cnh_categoria', 10)->nullable();
            $table->date('cnh_validade')->nullable();

            $table->string('telefone', 50)->nullable();
            $table->string('email', 191)->nullable();

            $table->string('status', 20)->default('ativo'); // ativo|inativo
            $table->text('observacoes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_motoristas');
    }
};
