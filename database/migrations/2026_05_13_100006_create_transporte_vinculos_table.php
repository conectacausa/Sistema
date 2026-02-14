<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transporte_vinculos', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('linha_id')->constrained('transporte_linhas');
            $table->foreignId('parada_id')->nullable()->constrained('transporte_paradas');

            $table->string('tipo_acesso', 20)->default('cartao'); // cartao|ticket
            $table->string('numero_cartao', 50)->nullable();
            $table->string('numero_vale_ticket', 50)->nullable();

            $table->decimal('valor_passagem', 10, 2)->default(0);

            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable(); // encerramento

            $table->string('status', 20)->default('ativo'); // ativo|encerrado
            $table->text('observacoes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'status']);
            $table->index(['numero_cartao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_vinculos');
    }
};
