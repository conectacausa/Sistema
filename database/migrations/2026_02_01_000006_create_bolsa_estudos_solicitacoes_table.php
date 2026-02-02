<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bolsa_estudos_solicitacoes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->foreignId('processo_id')
                ->constrained('bolsa_estudos_processos')
                ->restrictOnDelete();

            // "código do colaborador" -> amarrado no id da tabela colaboradores
            $table->foreignId('colaborador_id')
                ->constrained('colaboradores')
                ->restrictOnDelete();

            $table->foreignId('curso_id')
                ->constrained('bolsa_estudos_cursos')
                ->restrictOnDelete();

            $table->decimal('valor_total_mensalidade', 12, 2)->default(0);
            $table->decimal('valor_concessao', 12, 2)->nullable();
            $table->decimal('valor_limite', 12, 2)->nullable();

            // 0=Digitacao 1=Reprovado 2=Aprovado 3=Em Analise
            $table->unsignedSmallInteger('status')->default(0);

            $table->foreignId('aprovador_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->timestamp('aprovacao_at')->nullable();
            $table->string('aprovacao_ip', 45)->nullable(); // ipv4/ipv6

            $table->timestamp('solicitacao_at')->useCurrent();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['processo_id', 'colaborador_id']);
            $table->index(['empresa_id', 'status']);
            $table->index(['processo_id']);
            $table->index(['colaborador_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bolsa_estudos_solicitacoes');
    }
};
