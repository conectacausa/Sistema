<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('avd_perguntas', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('empresa_id');

      $table->unsignedBigInteger('ciclo_id');
      $table->unsignedBigInteger('pilar_id');

      $table->text('texto');
      $table->decimal('peso', 10, 2)->default(0); // soma por pilar = 100
      $table->integer('ordem')->default(0);
      $table->boolean('ativo')->default(true);

      // tipo: escala_1_5 | escala_1_10 | personalizada
      $table->string('tipo_resposta', 20)->default('escala_1_5');

      // Quando personalizada, guarda json com pesos/labels
      $table->jsonb('opcoes_json')->nullable();

      $table->boolean('justificativa_obrigatoria')->default(false);
      $table->boolean('permitir_comentario')->default(true);

      $table->timestamps();
      $table->softDeletes();

      $table->index(['empresa_id', 'ciclo_id', 'pilar_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('avd_perguntas');
  }
};
