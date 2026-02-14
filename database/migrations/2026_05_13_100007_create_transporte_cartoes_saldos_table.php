<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transporte_cartoes_saldos', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->string('numero_cartao', 50);
            $table->decimal('saldo', 10, 2)->default(0);

            $table->date('data_referencia')->nullable();
            $table->string('origem', 50)->default('importacao');

            $table->timestamps();

            $table->index(['empresa_id', 'numero_cartao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_cartoes_saldos');
    }
};
