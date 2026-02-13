<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('avd_pilares', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('empresa_id');
      $table->unsignedBigInteger('ciclo_id');

      $table->string('nome', 120);
      $table->decimal('peso', 10, 2)->default(0); // soma = 100
      $table->integer('ordem')->default(0);
      $table->boolean('ativo')->default(true);

      $table->timestamps();
      $table->softDeletes();

      $table->index(['empresa_id', 'ciclo_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('avd_pilares');
  }
};
