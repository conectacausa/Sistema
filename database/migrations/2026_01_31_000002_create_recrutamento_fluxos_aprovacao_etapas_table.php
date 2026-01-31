<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recrutamento_fluxos_aprovacao_etapas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('fluxo_id');

            $table->unsignedInteger('ordem');

            // 'aprovacao' => aprova/reprova | 'ciencia' => só ciente
            $table->string('tipo', 20); // validação no app

            $table->unsignedBigInteger('aprovador_usuario_id');

            // Prazo em horas (você pode tratar como SLA)
            $table->unsignedInteger('prazo_horas')->nullable();

            $table->boolean('ativo')->default(true);

            $table->timestamps();

            $table->index(['empresa_id', 'fluxo_id']);
            $table->unique(['fluxo_id', 'ordem']); // ordem única dentro do fluxo

            $table->foreign('empresa_id')
                ->references('id')->on('empresas')
                ->onDelete('cascade');

            $table->foreign('fluxo_id')
                ->references('id')->on('recrutamento_fluxos_aprovacao')
                ->onDelete('cascade');

            $table->foreign('aprovador_usuario_id')
                ->references('id')->on('usuarios')
                ->onDelete('restrict'); // evita remover usuário que é aprovador sem tratar
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recrutamento_fluxos_aprovacao_etapas');
    }
};
