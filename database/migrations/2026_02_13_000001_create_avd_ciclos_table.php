<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('avd_ciclos', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('empresa_id');

      $table->string('titulo', 180);
      $table->timestamp('inicio_em')->nullable();
      $table->timestamp('fim_em')->nullable();

      // 180 / 360
      $table->string('tipo', 10)->default('180');

      // Divergência pode ser em % ou pontos (você pode padronizar em pontos 0-10, por exemplo)
      $table->string('divergencia_tipo', 10)->default('percent'); // percent|pontos
      $table->decimal('divergencia_valor', 10, 2)->default(0);

      $table->boolean('permitir_inicio_manual')->default(true);
      $table->boolean('permitir_reabrir')->default(false);

      // Pesos do cálculo (configurável)
      $table->decimal('peso_auto', 10, 2)->default(30);
      $table->decimal('peso_gestor', 10, 2)->default(70);
      $table->decimal('peso_pares', 10, 2)->default(0); // usado no 360

      // Status: aguardando | iniciada | encerrada | em_consenso
      $table->string('status', 20)->default('aguardando');

      // Mensagens WhatsApp e regras
      $table->text('msg_auto')->nullable();
      $table->text('msg_gestor')->nullable();
      $table->text('msg_pares')->nullable();
      $table->text('msg_consenso')->nullable();
      $table->text('msg_lembrete')->nullable();

      $table->integer('lembrete_cada_dias')->default(0); // 0 = desliga
      $table->boolean('parar_lembrete_apos_responder')->default(true);

      $table->timestamps();
      $table->softDeletes();

      $table->index(['empresa_id', 'status']);
      $table->index(['empresa_id', 'inicio_em']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('avd_ciclos');
  }
};
