<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bolsa_estudos_documentos')) return;

        Schema::create('bolsa_estudos_documentos', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('processo_id');

            $table->unsignedBigInteger('solicitacao_id')->nullable();
            $table->unsignedBigInteger('competencia_id')->nullable();

            // 1 comprovante pagamento | 2 documento adicional
            $table->smallInteger('tipo')->default(2);

            $table->string('titulo', 255);
            $table->string('arquivo_path', 500);
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('tamanho')->nullable();

            // 0 aguardando aprovação | 1 reprovado | 2 aprovado
            $table->smallInteger('status')->default(0);
            $table->text('justificativa')->nullable();

            $table->unsignedBigInteger('aprovador_id')->nullable();
            $table->timestamp('aprovacao_at')->nullable();
            $table->string('aprovacao_ip', 45)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'processo_id']);
            $table->index(['empresa_id', 'solicitacao_id']);
            $table->index(['empresa_id', 'competencia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bolsa_estudos_documentos');
    }
};
