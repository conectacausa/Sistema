<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('avd_ciclo_unidades', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('empresa_id');
      $table->unsignedBigInteger('ciclo_id');
      $table->unsignedBigInteger('filial_id'); // sua unidade/filial

      $table->timestamps();

      $table->unique(['ciclo_id', 'filial_id']);
      $table->index(['empresa_id', 'ciclo_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('avd_ciclo_unidades');
  }
};
