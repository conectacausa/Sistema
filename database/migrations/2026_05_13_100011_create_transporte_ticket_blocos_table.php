<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transporte_ticket_blocos', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->string('codigo_bloco', 80)->nullable();
            $table->integer('quantidade_tickets')->default(0);
            $table->integer('viagens_por_ticket')->default(1);

            $table->string('status', 20)->default('disponivel'); // disponivel|em_uso|encerrado
            $table->text('observacoes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_ticket_blocos');
    }
};
