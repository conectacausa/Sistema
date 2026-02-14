<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transporte_inspecoes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->foreignId('veiculo_id')->constrained('transporte_veiculos');
            $table->foreignId('linha_id')->nullable()->constrained('transporte_linhas');

            $table->dateTime('data_inspecao')->nullable();
            $table->string('status', 20)->default('pendente'); // aprovado|reprovado|pendente
            $table->date('validade_ate')->nullable();

            $table->json('checklist_json')->nullable(); // baseado no anexo (a gente modela na view)
            $table->text('observacoes')->nullable();

            $table->foreignId('usuario_id')->nullable()->constrained('usuarios'); // quem preencheu

            $table->timestamps();

            $table->index(['empresa_id', 'status']);
            $table->index(['veiculo_id', 'data_inspecao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_inspecoes');
    }
};
