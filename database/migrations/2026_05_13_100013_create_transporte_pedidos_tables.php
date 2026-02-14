<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transporte_pedidos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('linha_id');
            $table->string('codigo', 80);
            $table->date('data_pedido')->default(DB::raw('CURRENT_DATE'));
            $table->string('status', 30)->default('aberto'); // aberto|pago|cancelado|...
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'linha_id']);
            $table->index(['linha_id', 'data_pedido']);
        });

        Schema::create('transporte_pedido_itens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('linha_id');

            $table->string('cartao_numero', 60)->nullable();
            $table->unsignedBigInteger('colaborador_id')->nullable();

            $table->decimal('valor', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['pedido_id']);
            $table->index(['linha_id']);
            $table->index(['cartao_numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_pedido_itens');
        Schema::dropIfExists('transporte_pedidos');
    }
};
