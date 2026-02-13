<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('avd_ciclo_participantes', function (Blueprint $table) {
      $table->bigIncrements('id');

      $table->unsignedBigInteger('empresa_id');
      $table->unsignedBigInteger('ciclo_id');

      $table->unsignedBigInteger('colaborador_id'); // avaliado
      $table->unsignedBigInteger('filial_id')->nullable();

      $table->unsignedBigInteger('gestor_usuario_id')->nullable(); // usuário gestor (se existir)
      $table->unsignedBigInteger('gestor_colaborador_id')->nullable(); // gestor como colaborador (se existir)

      // Telefone whatsapp pode ser ajustado manualmente
      $table->string('whatsapp', 30)->nullable();

      // Tokens únicos por participante (links públicos)
      $table->string('token_auto', 80)->nullable()->unique();
      $table->string('token_gestor', 80)->nullable()->unique();
      $table->string('token_pares', 80)->nullable()->unique();
      $table->string('token_consenso', 80)->nullable()->unique();

      // Notas calculadas
      $table->decimal('nota_auto', 10, 2)->nullable();
      $table->decimal('nota_pares', 10, 2)->nullable();
      $table->decimal('nota_gestor', 10, 2)->nullable();
      $table->decimal('nota_final', 10, 2)->nullable();

      // Pendente | Respondido | Divergente | Consenso OK | Finalizado
      $table->string('status', 20)->default('pendente');

      // Flags úteis
      $table->boolean('auto_respondido')->default(false);
      $table->boolean('gestor_respondido')->default(false);
      $table->boolean('pares_respondido')->default(false);
      $table->boolean('consenso_respondido')->default(false);

      $table->timestamps();
      $table->softDeletes();

      $table->unique(['ciclo_id', 'colaborador_id']);
      $table->index(['empresa_id', 'ciclo_id', 'status']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('avd_ciclo_participantes');
  }
};
