<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recrutamento_fluxos_aprovacao', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('empresa_id');
            $table->string('nome', 150);
            $table->text('descricao')->nullable();

            $table->boolean('ativo')->default(true);

            $table->unsignedBigInteger('created_by_usuario_id')->nullable();

            $table->timestamps();

            $table->index(['empresa_id', 'ativo']);

            $table->foreign('empresa_id')
                ->references('id')->on('empresas')
                ->onDelete('cascade');

            $table->foreign('created_by_usuario_id')
                ->references('id')->on('usuarios')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recrutamento_fluxos_aprovacao');
    }
};
