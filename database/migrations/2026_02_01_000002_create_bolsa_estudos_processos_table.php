<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bolsa_estudos_processos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->string('ciclo', 60);              // ex: 2026/1
            $table->string('edital', 120)->nullable(); // pode ser nº/descrição

            $table->timestamp('inscricoes_inicio_at')->nullable();
            $table->timestamp('inscricoes_fim_at')->nullable();

            $table->decimal('orcamento_mensal', 12, 2)->default(0);
            $table->unsignedInteger('meses_duracao')->default(0);

            // 0=rascunho/inativo, 1=ativo, 2=encerrado (ajustável)
            $table->unsignedSmallInteger('status')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bolsa_estudos_processos');
    }
};
