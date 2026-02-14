<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avd_consensos', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('ciclo_id')->index();
            $table->unsignedBigInteger('participante_id')->index(); // avd_ciclo_participantes.id

            // Nota final após consenso
            $table->decimal('nota_final', 6, 2)->nullable();

            // Regras do consenso
            $table->text('justificativa')->nullable();   // no fluxo: obrigatória (regra na aplicação)
            $table->text('comentario_rh')->nullable();  // opcional

            // Auditoria
            $table->unsignedBigInteger('criado_por_usuario_id')->nullable()->index();

            $table->timestamps();

            // 1 consenso por participante dentro do ciclo
            $table->unique(['ciclo_id', 'participante_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avd_consensos');
    }
};
