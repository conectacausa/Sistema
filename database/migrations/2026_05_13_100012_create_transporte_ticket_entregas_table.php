<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transporte_ticket_entregas', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('bloco_id')->constrained('transporte_ticket_blocos');

            $table->date('data_entrega')->nullable();
            $table->integer('quantidade_entregue')->default(0);

            $table->text('observacao')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_ticket_entregas');
    }
};
