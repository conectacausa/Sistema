<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transporte_cartao_usos', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->string('numero_cartao', 50);
            $table->dateTime('data_hora_uso');

            $table->decimal('valor', 10, 2)->nullable();

            $table->foreignId('linha_id')->nullable()->constrained('transporte_linhas');
            $table->foreignId('parada_id')->nullable()->constrained('transporte_paradas');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');

            $table->string('origem', 50)->default('importacao');

            $table->timestamps();

            $table->index(['empresa_id', 'numero_cartao']);
            $table->index(['empresa_id', 'data_hora_uso']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_cartao_usos');
    }
};
