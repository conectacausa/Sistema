<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('avd_notificacoes', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('empresa_id');

      $table->unsignedBigInteger('ciclo_id');
      $table->unsignedBigInteger('participante_id')->nullable();
      $table->unsignedBigInteger('avaliacao_id')->nullable();

      $table->string('canal', 20)->default('whatsapp');
      $table->string('tipo', 30); // convite_auto, convite_gestor, convite_pares, convite_consenso, lembrete
      $table->string('status', 20)->default('queued'); // queued|sent|error|skipped

      $table->integer('tentativas')->default(0);
      $table->timestamp('enviado_em')->nullable();
      $table->text('ultimo_erro')->nullable();

      $table->jsonb('payload')->nullable();

      $table->timestamps();

      $table->index(['empresa_id', 'ciclo_id', 'status']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('avd_notificacoes');
  }
};
