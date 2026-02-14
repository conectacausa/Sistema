<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transporte_linhas', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->string('nome', 191);
            $table->string('tipo_linha', 20)->default('fretada'); // publica|fretada
            $table->string('controle_acesso', 20)->default('cartao'); // cartao|ticket

            $table->foreignId('motorista_id')->constrained('transporte_motoristas');
            $table->foreignId('veiculo_id')->constrained('transporte_veiculos');

            $table->string('status', 20)->default('ativo'); // ativo|inativo
            $table->text('observacoes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_linhas');
    }
};
