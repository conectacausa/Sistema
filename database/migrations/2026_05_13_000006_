<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('avd_avaliacoes', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('empresa_id');

      $table->unsignedBigInteger('ciclo_id');
      $table->unsignedBigInteger('participante_id'); // avd_ciclo_participantes.id

      // auto | gestor | pares | consenso
      $table->string('tipo', 20);

      // quem respondeu (quando aplicável)
      $table->unsignedBigInteger('respondente_usuario_id')->nullable();
      $table->unsignedBigInteger('respondente_colaborador_id')->nullable();

      // Token público principal desta avaliação
      $table->string('token', 80)->unique();

      // pendente | respondido | bloqueado | expirado
      $table->string('status', 20)->default('pendente');

      $table->timestamp('respondido_em')->nullable();
      $table->decimal('nota_calculada', 10, 2)->nullable();

      // Justificativa/observações (consenso, por ex.)
      $table->text('justificativa')->nullable();
      $table->text('comentario')->nullable();

      $table->timestamps();

      $table->index(['empresa_id', 'ciclo_id', 'tipo']);
      $table->index(['participante_id', 'tipo']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('avd_avaliacoes');
  }
};
