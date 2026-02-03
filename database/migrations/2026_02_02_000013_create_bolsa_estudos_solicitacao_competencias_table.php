<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bolsa_estudos_solicitacao_competencias')) return;

        Schema::create('bolsa_estudos_solicitacao_competencias', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('solicitacao_id');

            // sempre usar o 1º dia do mês
            $table->date('competencia');

            $table->date('vencimento')->nullable();

            // 0 aguardando envio, 1 recibo enviado (aguardando aprovação), 2 recibo aprovado (vai p/ pagamento),
            // 3 pago, 4 recibo reprovado (reenviar)
            $table->smallInteger('status')->default(0);

            $table->decimal('valor_previsto', 12, 2)->default(0);
            $table->decimal('valor_comprovado', 12, 2)->nullable();

            $table->unsignedBigInteger('aprovador_id')->nullable();
            $table->timestamp('aprovacao_at')->nullable();
            $table->string('aprovacao_ip', 45)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'solicitacao_id']);
            $table->index(['empresa_id', 'competencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bolsa_estudos_solicitacao_competencias');
    }
};
