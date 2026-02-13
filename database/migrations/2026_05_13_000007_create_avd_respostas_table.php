<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('avd_respostas', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('empresa_id');

      $table->unsignedBigInteger('avaliacao_id'); // avd_avaliacoes.id
      $table->unsignedBigInteger('pergunta_id');  // avd_perguntas.id

      // valor numérico padronizado (1-5 / 1-10 / peso custom)
      $table->decimal('valor', 10, 2)->nullable();

      $table->text('justificativa')->nullable();
      $table->text('comentario')->nullable();

      $table->timestamps();

      $table->unique(['avaliacao_id', 'pergunta_id']);
      $table->index(['empresa_id', 'avaliacao_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('avd_respostas');
  }
};
